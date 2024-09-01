<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Stripe\Stripe;
use Stripe\Charge;

class PaymentController extends Controller
{
    public function charge()
    {
        $stripeSecretKey = getenv('STRIPE_SECRET_KEY');
        Stripe::setApiKey($stripeSecretKey);

        $token = $this->request->getPost('stripeToken');

        try {
            $charge = Charge::create([
                'amount' => 1000, // amount in cents
                'currency' => 'usd',
                'description' => 'Example charge',
                'source' => $token,
            ]);

            return redirect()->to('/success')->with('success', 'Payment successful!');
        } catch (\Exception $e) {
            return redirect()->to('/error')->with('error', $e->getMessage());
        }
    }
}
