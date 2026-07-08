<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Multi-service tenant landing (mode B, tenant/home.php) — the storefront
 * redesign. A vendor with 2+ active services renders the card lander (mode A
 * is covered by TenantStorefrontTest). Asserts the redesigned surfaces:
 *
 *  - hero scrim carries name, tagline, coverage + a single "Get an instant
 *    quote" CTA (one label everywhere; the old "See prices"/"Get exact price"
 *    copy is gone)
 *  - trust pills sit above the grid; deposit % is sourced from DepositCalculator
 *  - each service card shows its short_description and a card-level CTA
 *  - the on-page date field is present
 *  - the sticky-header compact CTA is wired
 *  - reviews render the honest empty state when the vendor has none, and real
 *    quotes when they exist
 *
 * @internal
 */
final class TenantStorefrontLanderTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    private const BASE_DOMAIN = 'partyplanner.test';

    protected $namespace;
    private int $vendorId;
    private int $marqueeId;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('tenant.baseDomain=' . self::BASE_DOMAIN);
        $_ENV['tenant.baseDomain']    = self::BASE_DOMAIN;
        $_SERVER['tenant.baseDomain'] = self::BASE_DOMAIN;

        $this->vendorId = $this->seedVendor();

        // Two active, non-deleted services → mode B (card lander).
        $this->marqueeId = $this->seedService('Marquee Hire', 'Elegant frame marquees for garden weddings', 'Durham & Teesside');
        $this->seedService('Rustic Bar Hire', 'Mobile bar staffed for the evening', 'Durham & Teesside');

        $this->db->table('vendor_sites')->insert([
            'vendor_id'     => $this->vendorId,
            'subdomain'     => 'vendorone',
            'business_name' => 'Vendor One Events',
            'phone'         => '020 7946 0958',
            'about_text'    => 'Family-run events team covering the North East',
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
        $row = [
            'name'     => 'Lander Vendor',
            'username' => 'lander_vendor',
            'email'    => 'lander_vendor@example.test',
            'password' => 'hash',
            'role'     => 'vendor',
        ];
        if ($this->db->fieldExists('vendor_status', 'users')) {
            $row['vendor_status'] = 'approved';
        }
        $this->db->table('users')->insert($row);

        return (int) $this->db->insertID();
    }

    private function seedService(string $title, string $blurb, string $location): int
    {
        $row = [
            'vendor_id'         => $this->vendorId,
            'title'             => $title,
            'short_description' => $blurb,
            'status'            => 'active',
            'deleted_at'        => null,
        ];
        if ($this->db->fieldExists('service_location', 'services')) {
            $row['service_location'] = $location;
        }
        $this->db->table('services')->insert($row);

        return (int) $this->db->insertID();
    }

    private function onHost(string $host): void
    {
        $_SERVER['HTTP_HOST'] = $host;
        service('routes')->resetRoutes();
    }

    private function seedBooking(int $serviceId, string $date, ?string $start = null, ?string $end = null): void
    {
        $this->db->table('events')->insert(['user_id' => 1, 'title' => 'E', 'date' => $date]);
        $eventId = (int) $this->db->insertID();
        $this->db->table('bookings')->insert(['user_id' => 1, 'event_id' => $eventId, 'status' => 'confirmed']);
        $bookingId = (int) $this->db->insertID();
        $this->db->table('booking_items')->insert([
            'booking_id' => $bookingId,
            'service_id' => $serviceId,
            'status'     => 'accepted',
            'start_time' => $start,
            'end_time'   => $end,
        ]);
    }

    private function makeTimeBased(int $serviceId, int $hours): void
    {
        $this->db->table('services_private_event_pricing')->insert([
            'service_id'   => $serviceId,
            'pricing_type' => 'custom_duration_pricing',
        ]);
        $pid = (int) $this->db->insertID();
        $this->db->table('services_custom_duration_pricing')->insert([
            'service_id'               => $serviceId,
            'private_event_pricing_id' => $pid,
            'duration_type'            => 'hour',
            'duration'                 => $hours,
            'price'                    => 250,
        ]);
    }

    public function testLanderRendersHeroTaglineAndSingleQuoteLabel(): void
    {
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $result = $this->get('/');

        $result->assertStatus(200);
        $result->assertSee('Vendor One Events');
        $result->assertSee('Family-run events team covering the North East'); // tagline in hero
        $result->assertSee('Instant quotes');
        $result->assertSee('Durham &amp; Teesside'); // coverage in the hero meta row
        $result->assertSee('sf-hero-lander');

        // One action, one label — everywhere, and the old copy is gone.
        $result->assertSee('Get an instant quote');
        $result->assertDontSee('See prices');
        $result->assertDontSee('Get exact price');
        $result->assertDontSee('Get quote');
    }

    public function testLanderShowsTrustPillsWithDepositPercentAndDateField(): void
    {
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $result = $this->get('/');

        $result->assertSee('deposit holds your date');
        $result->assertSee((string) \App\Libraries\DepositCalculator::percentDisplay() . '%');
        $result->assertSee('Secure card payment');
        $result->assertSee('sf-datebar');                 // on-page date field
        $result->assertSee('instant quote for your date'); // updated section copy
    }

    public function testLanderCardsShowDescriptionAndCta(): void
    {
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $result = $this->get('/');

        $result->assertSee('Marquee Hire');
        $result->assertSee('Elegant frame marquees for garden weddings'); // short_description
        $result->assertSee('Rustic Bar Hire');
        $result->assertSee('sf-svc-cta');
    }

    public function testLanderHasStickyHeaderCtaAndClosingEnquiry(): void
    {
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $result = $this->get('/');

        $result->assertSee('sf-headcta');             // scroll-revealed compact CTA
        $result->assertSee("Can't see what you need?");
        $result->assertSee('Send an enquiry');
    }

    public function testNoGreyOutWithoutADate(): void
    {
        $this->seedBooking($this->marqueeId, '2027-06-05'); // whole-day booking
        $this->onHost('vendorone.' . self::BASE_DOMAIN);

        $this->get('/')->assertDontSee('is-unavailable');
    }

    public function testWholeDayGreyOutForNonTimeBasedService(): void
    {
        $this->seedBooking($this->marqueeId, '2027-06-05'); // no times → whole day
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $result = $this->get('/?date=2027-06-05');

        $result->assertSee('is-unavailable');
        $result->assertSee('Booked on this date');
        $result->assertSee('Rustic Bar Hire'); // the un-booked service still lists
    }

    public function testTimeBasedGreyOutRespectsChosenTime(): void
    {
        // Make the marquee a 3-hour time-based service booked 10:00–12:00.
        $this->makeTimeBased($this->marqueeId, 3);
        $this->seedBooking($this->marqueeId, '2027-06-05', '10:00:00', '12:00:00');
        $this->onHost('vendorone.' . self::BASE_DOMAIN);

        // Starting 11:00 collides with the existing slot → greyed.
        $this->get('/?date=2027-06-05&time=11:00')->assertSee('is-unavailable');

        // Starting 15:00 is clear → not greyed.
        $this->get('/?date=2027-06-05&time=15:00')->assertDontSee('is-unavailable');
    }

    public function testReviewsEmptyStateWhenNoneExist(): void
    {
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $result = $this->get('/');

        $result->assertSee('Reviews');
        $result->assertSee('No written reviews yet');
    }

    public function testReviewsRenderWhenTheyExist(): void
    {
        if (! $this->db->tableExists('reviews')) {
            $this->markTestSkipped('reviews table not present in this migration set');
        }

        $this->db->table('reviews')->insert([
            'vendor_id'   => $this->vendorId,
            'customer_id' => $this->seedVendor(), // any user id; reviewer name joined
            'rating'      => 5,
            'comment'     => 'Absolutely brilliant on the day',
        ]);

        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $result = $this->get('/');

        $result->assertSee('Absolutely brilliant on the day');
        $result->assertDontSee('No written reviews yet');
    }
}
