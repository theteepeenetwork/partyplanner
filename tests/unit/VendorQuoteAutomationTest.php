<?php

namespace Tests\Unit;

use App\Libraries\EventBookingQuote;
use App\Libraries\VendorQuoteAutomation;
use App\Models\BookingItemModel;
use App\Models\BookingModel;
use App\Models\QuoteAutomationLogModel;
use App\Models\UnavailableDateModel;
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
        $this->assertSame('rules_matched', $result['reason']);

        $item = $bookingItemModel->find($itemId);
        $this->assertSame('accepted', $item['status']);

        $logModel = new QuoteAutomationLogModel();
        $this->assertSame(1, $logModel->where('booking_item_id', $itemId)->where('action', 'auto_accept')->countAllResults());
    }

    public function testRejectsWhenQuoteHasErrors(): void
    {
        $bookingModel = new BookingModel();
        $bookingModel->insert(['user_id' => 9205, 'event_id' => null, 'status' => 'confirmed']);
        $bookingId = (int) $bookingModel->getInsertID();

        $settingsModel = new VendorQuoteSettingsModel();
        $settingsModel->insert([
            'vendor_id' => 6005,
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
            ['total' => 100, 'warnings' => [], 'errors' => ['Guest count is required for this pricing model.']],
            6005,
            1
        );

        $this->assertFalse($result['auto_accepted']);
        $this->assertSame('quote_errors', $result['reason']);

        $item = $bookingItemModel->find($itemId);
        $this->assertSame('pending', $item['status']);

        $logModel = new QuoteAutomationLogModel();
        $this->assertSame(0, $logModel->where('booking_item_id', $itemId)->countAllResults());
    }

    public function testRejectsWhenTotalExceedsMaxAutoAcceptAmount(): void
    {
        $bookingModel = new BookingModel();
        $bookingModel->insert(['user_id' => 9206, 'event_id' => null, 'status' => 'confirmed']);
        $bookingId = (int) $bookingModel->getInsertID();

        $settingsModel = new VendorQuoteSettingsModel();
        $settingsModel->insert([
            'vendor_id' => 6006,
            'service_id' => null,
            'auto_accept_enabled' => 1,
            'max_auto_accept_amount' => 50,
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
            6006,
            1
        );

        $this->assertFalse($result['auto_accepted']);
        $this->assertSame('over_max_amount', $result['reason']);

        $item = $bookingItemModel->find($itemId);
        $this->assertSame('pending', $item['status']);

        $logModel = new QuoteAutomationLogModel();
        $this->assertSame(0, $logModel->where('booking_item_id', $itemId)->countAllResults());
    }

    public function testRejectsWhenEventSettingNotAllowed(): void
    {
        $bookingModel = new BookingModel();
        $bookingModel->insert(['user_id' => 9207, 'event_id' => null, 'status' => 'confirmed']);
        $bookingId = (int) $bookingModel->getInsertID();

        $settingsModel = new VendorQuoteSettingsModel();
        $settingsModel->insert([
            'vendor_id' => 6007,
            'service_id' => null,
            'auto_accept_enabled' => 1,
            'allowed_event_settings' => json_encode(['public'], JSON_UNESCAPED_UNICODE),
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
            6007,
            1
        );

        $this->assertFalse($result['auto_accepted']);
        $this->assertSame('event_setting_not_allowed', $result['reason']);

        $item = $bookingItemModel->find($itemId);
        $this->assertSame('pending', $item['status']);

        $logModel = new QuoteAutomationLogModel();
        $this->assertSame(0, $logModel->where('booking_item_id', $itemId)->countAllResults());
    }

    public function testRejectsWhenLeadTimeInsufficient(): void
    {
        $bookingModel = new BookingModel();
        $bookingModel->insert(['user_id' => 9208, 'event_id' => null, 'status' => 'confirmed']);
        $bookingId = (int) $bookingModel->getInsertID();

        $settingsModel = new VendorQuoteSettingsModel();
        $settingsModel->insert([
            'vendor_id' => 6008,
            'service_id' => null,
            'auto_accept_enabled' => 1,
            'min_lead_days' => 14,
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
            ['id' => $itemId, 'price' => 100, 'event_date' => date('Y-m-d', strtotime('+2 days')), 'event_setting' => 'private'],
            ['total' => 100, 'warnings' => [], 'errors' => []],
            6008,
            1
        );

        $this->assertFalse($result['auto_accepted']);
        $this->assertSame('insufficient_lead_time', $result['reason']);

        $item = $bookingItemModel->find($itemId);
        $this->assertSame('pending', $item['status']);

        $logModel = new QuoteAutomationLogModel();
        $this->assertSame(0, $logModel->where('booking_item_id', $itemId)->countAllResults());
    }

    public function testRejectsWhenVendorUnavailableOnEventDate(): void
    {
        $bookingModel = new BookingModel();
        $bookingModel->insert(['user_id' => 9209, 'event_id' => null, 'status' => 'confirmed']);
        $bookingId = (int) $bookingModel->getInsertID();

        $settingsModel = new VendorQuoteSettingsModel();
        $settingsModel->insert([
            'vendor_id' => 6009,
            'service_id' => null,
            'auto_accept_enabled' => 1,
            'blackout_respect' => 1,
        ]);

        $bookingItemModel = new BookingItemModel();
        $bookingItemModel->insert([
            'booking_id' => $bookingId,
            'service_id' => 1,
            'price' => 100,
            'status' => 'pending',
        ]);
        $itemId = (int) $bookingItemModel->getInsertID();

        $eventDate = date('Y-m-d', strtotime('+30 days'));

        $unavailableDateModel = new UnavailableDateModel();
        $unavailableDateModel->insert([
            'vendor_id' => 6009,
            'date' => $eventDate,
        ]);

        $automation = new VendorQuoteAutomation();
        $result = $automation->evaluateAfterCheckout(
            ['id' => $itemId, 'price' => 100, 'event_date' => $eventDate, 'event_setting' => 'private'],
            ['total' => 100, 'warnings' => [], 'errors' => []],
            6009,
            1
        );

        $this->assertFalse($result['auto_accepted']);
        $this->assertSame('unavailable', $result['reason']);

        $item = $bookingItemModel->find($itemId);
        $this->assertSame('pending', $item['status']);

        $logModel = new QuoteAutomationLogModel();
        $this->assertSame(0, $logModel->where('booking_item_id', $itemId)->countAllResults());
    }

    /**
     * The travel guard must match on the structured EventBookingQuote::WARNING_TRAVEL_OUT_OF_RADIUS
     * code (in $quote['warning_codes']), not by str_contains()-ing warning prose. This proves the
     * guard fires even though the warning text below deliberately does not contain any of the
     * old str_contains() match fragments ("exceeds the vendor", "beyond the maximum", "outside the
     * vendor") — only the structured code drives the rejection.
     */
    public function testRejectsTravelWarningViaStructuredCode(): void
    {
        $bookingModel = new BookingModel();
        $bookingModel->insert(['user_id' => 9203, 'event_id' => null, 'status' => 'confirmed']);
        $bookingId = (int) $bookingModel->getInsertID();

        $settingsModel = new VendorQuoteSettingsModel();
        $settingsModel->insert([
            'vendor_id' => 6003,
            'service_id' => null,
            'auto_accept_enabled' => 1,
            'require_within_travel_radius' => 1,
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
            [
                'total' => 100,
                'errors' => [],
                'warnings' => ['This venue is a long way off, please double check with us before booking.'],
                'warning_codes' => [EventBookingQuote::WARNING_TRAVEL_OUT_OF_RADIUS],
            ],
            6003,
            1
        );

        $this->assertFalse($result['auto_accepted']);
        $this->assertSame('travel_warning', $result['reason']);

        $item = $bookingItemModel->find($itemId);
        $this->assertSame('pending', $item['status']);
    }

    /**
     * A warning that is not the travel-radius code must not trip the travel guard, even when
     * require_within_travel_radius is enabled — proving the match is code-specific, not "any warning".
     */
    public function testDoesNotRejectOnUnrelatedWarningCodeWhenTravelRadiusRequired(): void
    {
        $bookingModel = new BookingModel();
        $bookingModel->insert(['user_id' => 9204, 'event_id' => null, 'status' => 'confirmed']);
        $bookingId = (int) $bookingModel->getInsertID();

        $settingsModel = new VendorQuoteSettingsModel();
        $settingsModel->insert([
            'vendor_id' => 6004,
            'service_id' => null,
            'auto_accept_enabled' => 1,
            'require_within_travel_radius' => 1,
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
            [
                'total' => 100,
                'errors' => [],
                'warnings' => ['Free postage applied (order over £50.00)'],
                'warning_codes' => [EventBookingQuote::WARNING_FREE_POSTAGE_APPLIED],
            ],
            6004,
            1
        );

        $this->assertTrue($result['auto_accepted']);
        $this->assertSame('rules_matched', $result['reason']);
    }
}
