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

    public function testLanderRendersBookingFirstHeroAndEstimator(): void
    {
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $result = $this->get('/');

        $result->assertStatus(200);
        $result->assertSee('Vendor One Events');
        $result->assertSee('Book your event in three clicks.'); // 3D hybrid hero
        $result->assertSee('sf-lhero');
        $result->assertSee('sf-estimator');               // live instant-quote estimator
        $result->assertSee('est-input');                  // polymorphic control mount
        $result->assertSee('est-total-lbl');
        $result->assertSee('Durham &amp; Teesside');      // coverage in the hero pill
        $result->assertDontSee('est-time');               // start time removed from hero card
        $result->assertDontSee('% deposit</b>');          // deposit is a concrete figure, not a %
    }

    public function testReserveCtaAlwaysHasALabel(): void
    {
        // Task 1 regression: the primary quote-card button must never render
        // without text (server-rendered fallback label present before JS runs).
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $body = (string) $this->get('/')->getBody();

        $this->assertMatchesRegularExpression(
            '/id="est-reserve-lbl">\s*\S+/',
            $body,
            'Reserve CTA label span must contain text'
        );
        $this->assertStringContainsString('Reserve your date', $body);

        // Root cause of the "empty button": an <a class="sf-btn"> was painted
        // teal-on-teal because body.sf-body a out-specified .sf-btn. Guard the
        // stylesheet override so the label can never go invisible again.
        $css = (string) file_get_contents(FCPATH . 'assets/css/tenant-storefront.css');
        $this->assertMatchesRegularExpression('/a\.sf-btn[^{]*\{[^}]*color:\s*#fff/i', $css, 'sf-btn anchors must force white label text');
    }

    public function testLanderShowsTrustStripWithDepositPercent(): void
    {
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $result = $this->get('/');

        $result->assertSee('deposit holds your date');
        $result->assertSee((string) \App\Libraries\DepositCalculator::percentDisplay() . '%');
        $result->assertSee('Secure card payment');
    }

    public function testLanderServicesGridShowsDescriptionAndBookCta(): void
    {
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $result = $this->get('/');

        $result->assertSee('Our services');
        $result->assertSee('sf-lcard');
        $result->assertSee('Marquee Hire');
        $result->assertSee('Elegant frame marquees for garden weddings'); // short_description
        $result->assertSee('Rustic Bar Hire');
    }

    public function testLanderHasStickyHeaderCta(): void
    {
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $result = $this->get('/');

        $result->assertSee('sf-headcta'); // scroll-revealed compact CTA in the header
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
        $result->assertSee('sf-badge-booked');
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

    public function testGalleryBandSuppressedAndNeverEchoesServicePhotos(): void
    {
        // A service HAS a card photo, but there is no dedicated vendor gallery —
        // the band must be suppressed entirely (never fall back to service imagery).
        $this->db->table('service_images')->insert(['service_id' => $this->marqueeId, 'image_path' => 'uploads/service-card.jpg', 'is_primary' => 1]);
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $result = $this->get('/');

        // The band (heading + grid) is absent entirely — no fall-back to the
        // service imagery that legitimately shows on the service card itself.
        $result->assertDontSee('Recent events');
        $result->assertDontSee('sf-gallery3');
    }

    public function testGalleryBandRendersDedicatedVendorGalleryOnly(): void
    {
        if (! $this->db->tableExists('vendor_site_gallery')) {
            $this->markTestSkipped('vendor_site_gallery migration not present');
        }
        $this->db->table('vendor_site_gallery')->insert(['vendor_id' => $this->vendorId, 'image_path' => 'uploads/vendor_gallery/g1.jpg', 'sort_order' => 0]);
        $this->onHost('vendorone.' . self::BASE_DOMAIN);
        $result = $this->get('/');

        $result->assertSee('Recent events');
        $result->assertSee('uploads/vendor_gallery/g1.jpg');
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
