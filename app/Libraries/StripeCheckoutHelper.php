<?php

namespace App\Libraries;

/**
 * Stripe PaymentIntent helpers for event checkout.
 */
class StripeCheckoutHelper
{
    public function isConfigured(): bool
    {
        $key = getenv('STRIPE_SECRET_KEY');

        return is_string($key) && $key !== '';
    }

    /**
     * @return array{success: bool, payment_intent_id?: string, client_secret?: string, error?: string}
     */
    public function createPaymentIntent(int $amountPence, array $metadata = []): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Stripe not configured'];
        }

        \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

        try {
            $params = [
                'amount' => max(50, $amountPence),
                'currency' => 'gbp',
                'payment_method_types' => ['card'],
            ];
            if ($metadata !== []) {
                $params['metadata'] = $metadata;
            }
            $pi = \Stripe\PaymentIntent::create($params);

            return [
                'success' => true,
                'payment_intent_id' => $pi->id,
                'client_secret' => $pi->client_secret,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * @return array{success: bool, status?: string, error?: string}
     */
    public function verifyPaymentIntent(string $paymentIntentId): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Stripe not configured'];
        }

        \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

        try {
            $pi = \Stripe\PaymentIntent::retrieve($paymentIntentId);
            $ok = in_array($pi->status, ['succeeded', 'processing'], true);

            return [
                'success' => $ok,
                'status' => $pi->status,
                'error' => $ok ? null : 'Payment not completed (status: ' . $pi->status . ')',
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
