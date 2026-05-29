<?php

namespace App\Controllers;

use Stripe\Stripe;
use Stripe\PaymentIntent;

/**
 * Handles Stripe PaymentIntent creation for the legacy cart checkout flow.
 */
class PaymentController extends BaseController
{
    /**
     * Create a Stripe PaymentIntent for a fixed deposit amount and return the client secret as JSON.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function createPaymentIntent()
    {
        \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

        // For demo purposes, assume a fixed deposit amount. You should replace this with your actual logic.
        $depositAmount = 1500; // £5.00 or 500 pence

        // Create the PaymentIntent
        $intent = \Stripe\PaymentIntent::create([
            'amount' => $depositAmount,
            'currency' => 'gbp',
            'payment_method_types' => ['card'],
        ]);

        // Return the client secret
        return $this->response->setJSON([
            'clientSecret' => $intent->client_secret
        ]);
    }
}
