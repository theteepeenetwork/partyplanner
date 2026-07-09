<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;

/**
 * Time-based (hours-duration) service booking on the storefront:
 *  - a start time is required before the quote proceeds;
 *  - the chosen start + duration resolve the booked window, which is stored on
 *    the session quote and persisted onto the booking/booking_item;
 *  - a start time that collides with an existing slot (incl. setup/breakdown)
 *    is rejected.
 *
 * @internal
 */
final class TenantSlotBookingTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    private const BASE_DOMAIN = 'partyplanner.test';

    protected $namespace;
    private int $vendorId;
    private int $serviceId;
    private int $tierId;
    private string $date;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('tenant.baseDomain=' . self::BASE_DOMAIN);
        $_ENV['tenant.baseDomain']    = self::BASE_DOMAIN;
        $_SERVER['tenant.baseDomain'] = self::BASE_DOMAIN;

        $this->date     = date('Y-m-d', strtotime('+30 days'));
        $this->vendorId = $this->seedVendor();
        $this->seedDurationService();

        $this->db->table('vendor_sites')->insert([
            'vendor_id'     => $this->vendorId,
            'subdomain'     => 'dj',
            'business_name' => 'DJ Test Events',
            'status'        => 'active',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        putenv('tenant.baseDomain');
        unset($_ENV['tenant.baseDomain'], $_SERVER['tenant.baseDomain'], $_SERVER['HTTP_HOST']);
    }

    private function seedVendor(): int
    {
        $row = ['name' => 'DJ', 'username' => 'dj_' . uniqid(), 'email' => uniqid('dj_') . '@e.test', 'password' => 'h', 'role' => 'vendor'];
        if ($this->db->fieldExists('vendor_status', 'users')) {
            $row['vendor_status'] = 'approved';
        }
        $this->db->table('users')->insert($row);

        return (int) $this->db->insertID();
    }

    private function seedDurationService(): void
    {
        $this->db->table('services')->insert([
            'vendor_id'         => $this->vendorId,
            'title'             => 'Evening DJ Set',
            'status'            => 'active',
            'setup_minutes'     => 30,
            'breakdown_minutes' => 30,
        ]);
        $this->serviceId = (int) $this->db->insertID();

        $this->db->table('services_private_event_pricing')->insert([
            'service_id'   => $this->serviceId,
            'pricing_type' => 'custom_duration_pricing',
        ]);
        $pid = (int) $this->db->insertID();

        $this->db->table('services_custom_duration_pricing')->insert([
            'service_id'               => $this->serviceId,
            'private_event_pricing_id' => $pid,
            'duration_type'            => 'hour',
            'duration'                 => 3,
            'price'                    => 300,
        ]);
        $this->tierId = (int) $this->db->insertID();
    }

    private function onTenant(): void
    {
        $_SERVER['HTTP_HOST'] = 'dj.' . self::BASE_DOMAIN;
        service('routes')->resetRoutes();
    }

    /** @param array<string,mixed> $data */
    private function postQuote(array $data): TestResponse
    {
        $this->onTenant();
        $data[csrf_token()] = csrf_hash();

        return $this->post('/quote', $data);
    }

    private function seedBooking(string $start, string $end): void
    {
        $this->db->table('events')->insert(['user_id' => 1, 'title' => 'E', 'date' => $this->date]);
        $eventId = (int) $this->db->insertID();
        $this->db->table('bookings')->insert(['user_id' => 1, 'event_id' => $eventId, 'status' => 'confirmed']);
        $bookingId = (int) $this->db->insertID();
        $this->db->table('booking_items')->insert([
            'booking_id' => $bookingId, 'service_id' => $this->serviceId,
            'status' => 'accepted', 'start_time' => $start, 'end_time' => $end,
        ]);
    }

    public function testStartTimeIsRequiredForTimeBasedService(): void
    {
        $result = $this->postQuote([
            'service_id'     => $this->serviceId,
            'event_date'     => $this->date,
            'pricing_option' => 'duration_' . $this->tierId,
            // no start_time
        ]);

        $result->assertRedirect();
        $this->assertStringContainsString('/service/' . $this->serviceId, $result->getRedirectUrl());
        $this->assertMatchesRegularExpression('/time/i', (string) session('error'));
    }

    public function testQuoteResolvesAndStoresTheBookedWindow(): void
    {
        $result = $this->postQuote([
            'service_id'     => $this->serviceId,
            'event_date'     => $this->date,
            'pricing_option' => 'duration_' . $this->tierId,
            'start_time'     => '19:00',
        ]);

        $result->assertRedirectTo('http://dj.' . self::BASE_DOMAIN . '/checkout');

        $quote = session('tenant_quote');
        $this->assertSame('19:00:00', $quote['start_time']);
        $this->assertSame('22:00:00', $quote['end_time']); // 19:00 + 3h
    }

    public function testConflictingSlotIsRejected(): void
    {
        $this->seedBooking('20:00:00', '23:00:00'); // occupies 19:30–23:30 with buffers

        $result = $this->postQuote([
            'service_id'     => $this->serviceId,
            'event_date'     => $this->date,
            'pricing_option' => 'duration_' . $this->tierId,
            'start_time'     => '19:00', // 18:30–22:30 padded → overlaps
        ]);

        $result->assertRedirect();
        $this->assertStringContainsString('/service/' . $this->serviceId, $result->getRedirectUrl());
        // Booking-form context persists on the bounce-back (date + start time).
        $this->assertStringContainsString('date=' . $this->date, $result->getRedirectUrl());
        $this->assertStringContainsString('time=', $result->getRedirectUrl());
        $this->assertNull(session('tenant_quote'));
    }

    public function testNonConflictingSlotIsAccepted(): void
    {
        $this->seedBooking('10:00:00', '12:00:00'); // morning slot, clear of an evening set

        $result = $this->postQuote([
            'service_id'     => $this->serviceId,
            'event_date'     => $this->date,
            'pricing_option' => 'duration_' . $this->tierId,
            'start_time'     => '19:00',
        ]);

        $result->assertRedirectTo('http://dj.' . self::BASE_DOMAIN . '/checkout');
        $this->assertNotNull(session('tenant_quote'));
    }
}
