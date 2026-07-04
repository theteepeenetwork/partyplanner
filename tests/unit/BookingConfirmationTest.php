<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Libraries\BookingConfirmation;
use App\Models\BookingItemModel;
use App\Models\BookingModel;
use App\Models\PaymentsModel;
use App\Models\QuoteAutomationLogModel;
use App\Models\ServiceModel;
use App\Models\VendorQuoteSettingsModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class BookingConfirmationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    private function seedBooking(array $overrides = []): int
    {
        $bookingModel = new BookingModel();
        $bookingModel->insert(array_merge([
            'user_id' => 9101,
            'event_id' => null,
            'status' => 'pending',
        ], $overrides));

        return (int) $bookingModel->getInsertID();
    }

    public function testConfirmByPaymentIntentFlipsBookingToConfirmed(): void
    {
        $bookingId = $this->seedBooking(['payment_intent_id' => 'pi_a3_1']);

        $paymentsModel = new PaymentsModel();
        $paymentsModel->insert([
            'booking_id' => $bookingId,
            'payment_intent_id' => 'pi_a3_1',
            'payment_status' => 'processing',
            'amount_paid' => 0,
        ]);

        $result = (new BookingConfirmation())->confirmByPaymentIntent('pi_a3_1', 20.0);

        $this->assertTrue($result['found']);

        $bookingModel = new BookingModel();
        $booking = $bookingModel->find($bookingId);
        $this->assertSame('confirmed', $booking['status']);

        $payment = $paymentsModel->where('payment_intent_id', 'pi_a3_1')->first();
        $this->assertSame('succeeded', $payment['payment_status']);
        $this->assertEqualsWithDelta(20.0, (float) $payment['amount_paid'], 0.001);
    }

    public function testConfirmByPaymentIntentInsertsPaymentWhenOnlyBookingExists(): void
    {
        $bookingId = $this->seedBooking(['payment_intent_id' => 'pi_a3_2']);

        $result = (new BookingConfirmation())->confirmByPaymentIntent('pi_a3_2', 15.0);

        $this->assertTrue($result['found']);

        $paymentsModel = new PaymentsModel();
        $payment = $paymentsModel->where('payment_intent_id', 'pi_a3_2')->first();
        $this->assertNotNull($payment);
        $this->assertSame('succeeded', $payment['payment_status']);
        $this->assertSame($bookingId, (int) $payment['booking_id']);

        $bookingModel = new BookingModel();
        $booking = $bookingModel->find($bookingId);
        $this->assertSame('confirmed', $booking['status']);
    }

    public function testConfirmBookingFlipsStatusWithoutPaymentIntent(): void
    {
        $bookingId = $this->seedBooking();

        (new BookingConfirmation())->confirmBooking($bookingId);

        $bookingModel = new BookingModel();
        $booking = $bookingModel->find($bookingId);
        $this->assertSame('confirmed', $booking['status']);
    }

    public function testMarkFailedLeavesBookingPendingAndSkipsAutomation(): void
    {
        $bookingId = $this->seedBooking(['payment_intent_id' => 'pi_a3_fail']);

        $paymentsModel = new PaymentsModel();
        $paymentsModel->insert([
            'booking_id' => $bookingId,
            'payment_intent_id' => 'pi_a3_fail',
            'payment_status' => 'processing',
            'amount_paid' => 0,
        ]);

        $serviceModel = new ServiceModel();
        $serviceModel->insert(['vendor_id' => 5001, 'title' => 'Test Service']);
        $serviceId = (int) $serviceModel->getInsertID();

        $settingsModel = new VendorQuoteSettingsModel();
        $settingsModel->insert([
            'vendor_id' => 5001,
            'service_id' => null,
            'auto_accept_enabled' => 1,
        ]);

        $bookingItemModel = new BookingItemModel();
        $bookingItemModel->insert([
            'booking_id' => $bookingId,
            'service_id' => $serviceId,
            'price' => 50.0,
            'status' => 'pending',
        ]);

        (new BookingConfirmation())->markFailedByPaymentIntent('pi_a3_fail');

        $bookingModel = new BookingModel();
        $booking = $bookingModel->find($bookingId);
        $this->assertSame('pending', $booking['status']);

        $payment = $paymentsModel->where('payment_intent_id', 'pi_a3_fail')->first();
        $this->assertSame('failed', $payment['payment_status']);

        $item = $bookingItemModel->where('booking_id', $bookingId)->first();
        $this->assertSame('pending', $item['status']);

        $logModel = new QuoteAutomationLogModel();
        $this->assertSame(0, $logModel->where('booking_item_id', $item['id'])->countAllResults());
    }

    public function testMarkFailedDoesNotDowngradeSucceededPayment(): void
    {
        $bookingId = $this->seedBooking(['payment_intent_id' => 'pi_a3_retry', 'status' => 'confirmed']);

        $paymentsModel = new PaymentsModel();
        $paymentsModel->insert([
            'booking_id' => $bookingId,
            'payment_intent_id' => 'pi_a3_retry',
            'payment_status' => 'succeeded',
            'amount_paid' => 20.0,
        ]);

        (new BookingConfirmation())->markFailedByPaymentIntent('pi_a3_retry');

        $payment = $paymentsModel->where('payment_intent_id', 'pi_a3_retry')->first();
        $this->assertSame('succeeded', $payment['payment_status']);
    }

    public function testConfirmByPaymentIntentIsIdempotentOnReplay(): void
    {
        $bookingId = $this->seedBooking(['payment_intent_id' => 'pi_a3_replay']);

        $paymentsModel = new PaymentsModel();
        $paymentsModel->insert([
            'booking_id' => $bookingId,
            'payment_intent_id' => 'pi_a3_replay',
            'payment_status' => 'processing',
            'amount_paid' => 0,
        ]);

        $serviceModel = new ServiceModel();
        $serviceModel->insert(['vendor_id' => 5002, 'title' => 'Replay Service']);
        $serviceId = (int) $serviceModel->getInsertID();

        $settingsModel = new VendorQuoteSettingsModel();
        $settingsModel->insert([
            'vendor_id' => 5002,
            'service_id' => null,
            'auto_accept_enabled' => 1,
        ]);

        $bookingItemModel = new BookingItemModel();
        $bookingItemModel->insert([
            'booking_id' => $bookingId,
            'service_id' => $serviceId,
            'price' => 50.0,
            'status' => 'pending',
            'quote_breakdown' => json_encode(['lines' => [], 'warnings' => [], 'errors' => []]),
        ]);
        $itemId = (int) $bookingItemModel->getInsertID();

        $confirmation = new BookingConfirmation();
        $confirmation->confirmByPaymentIntent('pi_a3_replay', 25.0);
        $confirmation->confirmByPaymentIntent('pi_a3_replay', 25.0);

        $paymentRows = $paymentsModel->where('payment_intent_id', 'pi_a3_replay')->findAll();
        $this->assertCount(1, $paymentRows);

        $bookingModel = new BookingModel();
        $booking = $bookingModel->find($bookingId);
        $this->assertSame('confirmed', $booking['status']);

        $item = $bookingItemModel->find($itemId);
        $this->assertSame('accepted', $item['status']);

        $logModel = new QuoteAutomationLogModel();
        $this->assertSame(1, $logModel->where('booking_item_id', $itemId)->where('action', 'auto_accept')->countAllResults());
    }

    public function testConfirmByPaymentIntentReturnsNotFoundForUnknownIntent(): void
    {
        $result = (new BookingConfirmation())->confirmByPaymentIntent('pi_does_not_exist', 10.0);

        $this->assertFalse($result['found']);

        $paymentsModel = new PaymentsModel();
        $this->assertSame(0, $paymentsModel->where('payment_intent_id', 'pi_does_not_exist')->countAllResults());
    }

    public function testConfirmBookingAutoAcceptsEligiblePendingItems(): void
    {
        $bookingId = $this->seedBooking();

        $serviceModel = new ServiceModel();
        $serviceModel->insert(['vendor_id' => 5003, 'title' => 'Eligible Service']);
        $eligibleServiceId = (int) $serviceModel->getInsertID();
        $serviceModel->insert(['vendor_id' => 5003, 'title' => 'Over Limit Service']);
        $overLimitServiceId = (int) $serviceModel->getInsertID();

        $settingsModel = new VendorQuoteSettingsModel();
        $settingsModel->insert([
            'vendor_id' => 5003,
            'service_id' => null,
            'auto_accept_enabled' => 1,
            'max_auto_accept_amount' => 100.0,
        ]);

        $bookingItemModel = new BookingItemModel();
        $bookingItemModel->insert([
            'booking_id' => $bookingId,
            'service_id' => $eligibleServiceId,
            'price' => 50.0,
            'status' => 'pending',
            'quote_breakdown' => json_encode(['lines' => [], 'warnings' => [], 'errors' => []]),
        ]);
        $eligibleItemId = (int) $bookingItemModel->getInsertID();

        $bookingItemModel->insert([
            'booking_id' => $bookingId,
            'service_id' => $overLimitServiceId,
            'price' => 500.0,
            'status' => 'pending',
            'quote_breakdown' => json_encode(['lines' => [], 'warnings' => [], 'errors' => []]),
        ]);
        $overLimitItemId = (int) $bookingItemModel->getInsertID();

        (new BookingConfirmation())->confirmBooking($bookingId);

        $eligibleItem = $bookingItemModel->find($eligibleItemId);
        $this->assertSame('accepted', $eligibleItem['status']);

        $overLimitItem = $bookingItemModel->find($overLimitItemId);
        $this->assertSame('pending', $overLimitItem['status']);

        $logModel = new QuoteAutomationLogModel();
        $this->assertSame(0, $logModel->where('booking_item_id', $overLimitItemId)->countAllResults());
    }
}
