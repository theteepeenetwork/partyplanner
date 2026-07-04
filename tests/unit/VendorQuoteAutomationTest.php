<?php

namespace Tests\Unit;

use App\Libraries\VendorQuoteAutomation;
use App\Models\BookingItemModel;
use App\Models\BookingModel;
use App\Models\QuoteAutomationLogModel;
use App\Models\VendorQuoteSettingsModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class VendorQuoteAutomationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    public function testRejectsWhenAutoAcceptDisabled(): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('vendor_quote_settings')) {
            $this->markTestSkipped('vendor_quote_settings not present in test database');
        }

        $automation = new VendorQuoteAutomation();
        $result = $automation->evaluateAfterCheckout(
            ['id' => 1, 'price' => 100, 'event_date' => date('Y-m-d', strtotime('+30 days')), 'event_setting' => 'private'],
            ['total' => 100, 'warnings' => [], 'errors' => []],
            99,
            1
        );
        $this->assertFalse($result['auto_accepted']);
        $this->assertSame('auto_accept_disabled', $result['reason']);
    }

    public function testRejectsWhenBookingNotConfirmed(): void
    {
        $bookingModel = new BookingModel();
        $bookingModel->insert(['user_id' => 9201, 'event_id' => null, 'status' => 'pending']);
        $bookingId = (int) $bookingModel->getInsertID();

        $settingsModel = new VendorQuoteSettingsModel();
        $settingsModel->insert([
            'vendor_id' => 6001,
            'service_id' => null,
            'auto_accept_enabled' => 1,
        ]);

        $bookingItemModel = new BookingItemModel();
        $bookingItemModel->insert([
            'booking_id' => $bookingId,
            'service_id' => 1,
            'price' => 100,
            'status' => 'pending',
        ]);
        $itemId = (int) $bookingItemModel->getInsertID();

        $automation = new VendorQuoteAutomation();
        $result = $automation->evaluateAfterCheckout(
            ['id' => $itemId, 'price' => 100, 'event_date' => date('Y-m-d', strtotime('+30 days')), 'event_setting' => 'private'],
            ['total' => 100, 'warnings' => [], 'errors' => []],
            6001,
            1
        );

        $this->assertFalse($result['auto_accepted']);
        $this->assertSame('payment_not_confirmed', $result['reason']);

        $item = $bookingItemModel->find($itemId);
        $this->assertSame('pending', $item['status']);

        $logModel = new QuoteAutomationLogModel();
        $this->assertSame(0, $logModel->where('booking_item_id', $itemId)->countAllResults());
    }

    public function testAcceptsWhenBookingConfirmed(): void
    {
        $bookingModel = new BookingModel();
        $bookingModel->insert(['user_id' => 9202, 'event_id' => null, 'status' => 'confirmed']);
        $bookingId = (int) $bookingModel->getInsertID();

        $settingsModel = new VendorQuoteSettingsModel();
        $settingsModel->insert([
            'vendor_id' => 6002,
            'service_id' => null,
            'auto_accept_enabled' => 1,
        ]);

        $bookingItemModel = new BookingItemModel();
        $bookingItemModel->insert([
            'booking_id' => $bookingId,
            'service_id' => 1,
            'price' => 100,
            'status' => 'pending',
        ]);
        $itemId = (int) $bookingItemModel->getInsertID();

        $automation = new VendorQuoteAutomation();
        $result = $automation->evaluateAfterCheckout(
            ['id' => $itemId, 'price' => 100, 'event_date' => date('Y-m-d', strtotime('+30 days')), 'event_setting' => 'private'],
            ['total' => 100, 'warnings' => [], 'errors' => []],
            6002,
            1
        );

        $this->assertTrue($result['auto_accepted']);

        $item = $bookingItemModel->find($itemId);
        $this->assertSame('accepted', $item['status']);

        $logModel = new QuoteAutomationLogModel();
        $this->assertSame(1, $logModel->where('booking_item_id', $itemId)->where('action', 'auto_accept')->countAllResults());
    }
}
