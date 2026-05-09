<?php

namespace App\Controllers;

<<<<<<< HEAD
use Stripe\Webhook;
use Stripe\Stripe;

class WebhookController extends BaseController
{
    public function handle()
    {
        \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

        // Retrieve the request's body and parse it as JSON
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $endpoint_secret = getenv('STRIPE_WEBHOOK_SECRET'); // Set this from the Stripe Dashboard

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                // Update your booking and mark it as confirmed
                break;
            case 'payment_intent.payment_failed':
                // Handle payment failure
                break;
            // ... handle other event types as needed
        }

        http_response_code(200);
=======
use App\Models\PaymentsModel;

class WebhookController extends BaseController
{
    public function stripe()
    {
        // Retrieve the raw request body from Stripe
        $input = @file_get_contents("php://input");
        $event = json_decode($input);

        // Verify the event type is payment_intent.succeeded
        if (isset($event->type) && $event->type == 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object; // The PaymentIntent object

            // Find the payment record in your database using the Payment Intent ID
            $paymentsModel = new PaymentsModel();
            $payment = $paymentsModel->where('payment_intent_id', $paymentIntent->id)->first();

            // Update the payment status to "succeeded"
            if ($payment) {
                $paymentsModel->update($payment['id'], [
                    'payment_status' => 'succeeded'
                ]);
            }
        }

        // Respond with 200 OK to acknowledge receipt of the event
        return $this->response->setStatusCode(200);
>>>>>>> 648c0f070acc4c3ee38e07810be1a97650ad6ff6
    }
}
