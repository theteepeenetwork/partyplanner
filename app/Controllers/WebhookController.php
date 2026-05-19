<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\PaymentsModel;

class WebhookController extends BaseController
{
    public function stripe()
    {
        $secretKey = getenv('STRIPE_SECRET_KEY');
        $endpointSecret = getenv('STRIPE_WEBHOOK_SECRET');
        if (!$secretKey || !$endpointSecret) {
            http_response_code(500);
            exit();
        }

        \Stripe\Stripe::setApiKey($secretKey);

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            http_response_code(400);
            exit();
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $this->handlePaymentSucceeded($paymentIntent->id, (float) ($paymentIntent->amount_received / 100));
                break;
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $paymentsModel = new PaymentsModel();
                $paymentsModel->where('payment_intent_id', $paymentIntent->id)
                    ->set(['payment_status' => 'failed'])
                    ->update();
                break;
        }

        http_response_code(200);
    }

    private function handlePaymentSucceeded(string $paymentIntentId, float $amountPaid): void
    {
        $paymentsModel = new PaymentsModel();
        $payment = $paymentsModel->where('payment_intent_id', $paymentIntentId)->first();
        if ($payment) {
            $paymentsModel->update($payment['id'], [
                'payment_status' => 'succeeded',
                'amount_paid' => $amountPaid,
            ]);

            return;
        }

        $bookingModel = new BookingModel();
        $booking = $bookingModel->where('payment_intent_id', $paymentIntentId)->first();
        if ($booking) {
            $paymentsModel->insert([
                'booking_id' => $booking['id'],
                'payment_intent_id' => $paymentIntentId,
                'payment_status' => 'succeeded',
                'amount_paid' => $amountPaid,
                'currency' => 'gbp',
                'payment_method' => 'stripe',
                'payment_type' => 'deposit',
                'description' => 'Stripe webhook deposit',
            ]);
        }
    }
}
