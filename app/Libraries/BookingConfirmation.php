<?php

namespace App\Libraries;

use App\Models\BookingItemModel;
use App\Models\BookingModel;
use App\Models\EventModel;
use App\Models\PaymentsModel;
use App\Models\ServiceModel;

/**
 * Owns the "payment is confirmed" transition for a booking: flips bookings.status
 * to 'confirmed' and runs vendor auto-accept automation for its still-pending items.
 *
 * This is the single choke point both the synchronous checkout path (EventController)
 * and the asynchronous Stripe webhook path (WebhookController) call into, so automation
 * can never run before a booking is actually confirmed as paid.
 */
class BookingConfirmation
{
    /**
     * Flip a booking to 'confirmed' (idempotent: only moves it out of 'pending', so it
     * never overwrites an admin-set status such as cancelled/declined/completed, and a
     * replayed call is a no-op) then run automation for any still-pending items.
     *
     * @param int $bookingId The booking primary key.
     * @return void
     */
    public function confirmBooking(int $bookingId): void
    {
        $bookingModel = new BookingModel();
        $bookingModel->where('id', $bookingId)
            ->where('status', 'pending')
            ->set(['status' => 'confirmed'])
            ->update();

        $this->runAutomationForBooking($bookingId);
    }

    /**
     * Resolve a Stripe PaymentIntent to a payments row (updating it, or inserting one if the
     * webhook is the first to see this PI), then confirm the associated booking.
     *
     * @param string $paymentIntentId Stripe PaymentIntent ID.
     * @param float  $amountPaid      Amount captured, in major currency units (e.g. GBP).
     * @return array{found: bool} Whether a payments row or booking was found for this PI.
     */
    public function confirmByPaymentIntent(string $paymentIntentId, float $amountPaid): array
    {
        $paymentsModel = new PaymentsModel();
        $payment = $paymentsModel->where('payment_intent_id', $paymentIntentId)->first();

        if ($payment) {
            $paymentsModel->update($payment['id'], [
                'payment_status' => 'succeeded',
                'amount_paid' => $amountPaid,
            ]);

            $this->confirmBooking((int) $payment['booking_id']);

            return ['found' => true];
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

            $this->confirmBooking((int) $booking['id']);

            return ['found' => true];
        }

        return ['found' => false];
    }

    /**
     * Mark the payment for a PaymentIntent as failed. Never downgrades a payment that has
     * already succeeded (a customer can retry the same PI after a failure, so a later
     * out-of-order 'failed' webhook must not clobber a genuine success). The booking stays
     * 'pending' and automation never runs, since nothing was confirmed.
     *
     * @param string $paymentIntentId Stripe PaymentIntent ID.
     * @return void
     */
    public function markFailedByPaymentIntent(string $paymentIntentId): void
    {
        $paymentsModel = new PaymentsModel();
        $paymentsModel->where('payment_intent_id', $paymentIntentId)
            ->where('payment_status !=', 'succeeded')
            ->set(['payment_status' => 'failed'])
            ->update();
    }

    /**
     * Evaluate vendor auto-accept automation for every still-pending item on a booking.
     * Replay-safe by construction: a replayed call finds no 'pending' items once they have
     * already been accepted (or otherwise touched), so it is a no-op.
     *
     * @param int $bookingId The booking primary key.
     * @return void
     */
    private function runAutomationForBooking(int $bookingId): void
    {
        $bookingModel = new BookingModel();
        $booking = $bookingModel->find($bookingId);
        if (!$booking) {
            return;
        }

        $eventModel = new EventModel();
        $event = !empty($booking['event_id']) ? $eventModel->find($booking['event_id']) : null;

        $bookingItemModel = new BookingItemModel();
        $items = $bookingItemModel->where('booking_id', $bookingId)->where('status', 'pending')->findAll();
        if ($items === []) {
            return;
        }

        $serviceModel = new ServiceModel();
        $automation = new VendorQuoteAutomation();
        $analytics = new QuoteAnalyticsRecorder();

        foreach ($items as $item) {
            $service = $serviceModel->find($item['service_id']);
            if (!$service) {
                continue;
            }

            $qd = json_decode($item['quote_breakdown'] ?? '', true);
            $quoteDetail = is_array($qd) ? $qd : [];
            if (!isset($quoteDetail['warnings'])) {
                $warnings = json_decode($item['quote_warnings'] ?? '', true);
                $quoteDetail['warnings'] = is_array($warnings) ? $warnings : [];
            }

            $joinedItem = array_merge($item, [
                'id' => $item['id'],
                'event_title' => $event['title'] ?? null,
                'event_date' => $event['date'] ?? null,
                'event_setting' => $event['event_setting'] ?? 'private',
            ]);
            $quoteForAutomation = array_merge($quoteDetail, ['total' => (float) $item['price']]);

            $autoResult = $automation->evaluateAfterCheckout(
                $joinedItem,
                $quoteForAutomation,
                (int) $service['vendor_id'],
                (int) $item['service_id']
            );

            if ($autoResult['auto_accepted']) {
                $analytics->recordAccepted((int) $service['vendor_id'], (int) $item['service_id'], true);
            }
        }
    }
}
