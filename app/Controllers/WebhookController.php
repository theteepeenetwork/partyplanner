<?php

namespace App\Controllers;

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
    }
}
