<?php

namespace App\Controllers;

use App\Libraries\BookingConfirmation;

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
                $result = (new BookingConfirmation())->confirmByPaymentIntent(
                    $paymentIntent->id,
                    (float) ($paymentIntent->amount_received / 100)
                );

                if (!$result['found']) {
                    // If this PaymentIntent carries our checkout metadata, the booking row
                    // simply hasn't been inserted yet (processCheckout is still running).
                    // Respond 500 so Stripe retries with backoff until it lands, instead of
                    // silently dropping the event. Unrelated PaymentIntents (no event_id
                    // metadata) get a 200 no-op to avoid infinite retries.
                    if (!empty($paymentIntent->metadata->event_id ?? null)) {
                        log_message('warning', 'Stripe webhook: payment_intent.succeeded for {pi} found no matching booking/payment yet; requesting retry.', ['pi' => $paymentIntent->id]);
                        http_response_code(500);

                        return;
                    }
                }
                break;
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                (new BookingConfirmation())->markFailedByPaymentIntent($paymentIntent->id);
                break;
        }

        http_response_code(200);
    }
}
