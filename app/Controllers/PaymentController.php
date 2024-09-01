<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PaymentController extends Controller
{
    public function checkout()
    {
        // Load Stripe configuration
        $config = config('Stripe');
        $publishableKey = $config->publishableKey;
        $secretKey = $config->secretKey;

        // Set up Stripe API key
        Stripe::setApiKey($secretKey);

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Example Product',
                        ],
                        'unit_amount' => 2000, // Amount in cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => base_url('payment/success'),
                'cancel_url' => base_url('payment/cancel'),
            ]);

            return redirect()->to($session->url);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create Stripe session: ' . $e->getMessage());
        }
    }

    public function success()
    {
        return view('payment/success');
    }

    public function cancel()
    {
        return view('payment/cancel');
    }
}
