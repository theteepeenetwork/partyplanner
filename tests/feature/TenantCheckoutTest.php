<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\DepositCalculator;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;

/**
 * Tenant instant-quote → 10% deposit → confirmation (guest checkout), on the
 * simulated-payment path (Stripe unconfigured — the app must work without
 * keys). Pricing runs through the real EventQuoteBuilder with seeded
 * guest-based tiers; persistence through TenantBookingFlow.
 *
 * Covers:
 *  - POST /quote prices the service and shows total + 10% deposit
 *  - full checkout creates customer/event/booking/booking_item/payments rows
 *    in the marketplace shape, deposit exactly DepositCalculator::forTotal()
 *  - a quote for another vendor's service 404s (assertOwns)
 *  - checkout without a session quote bounces home
 *  - the confirmation page is only visible to the session that booked
 *
 * @internal
 */
final class TenantCheckoutTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    private const BASE_DOMAIN = 'partyplanner.test';

    protected $namespace; // all namespaces: real App migrations + test-support tables
    private int $vendorId;
    private int $serviceId;
    private int $otherVendorId;
    private int $otherServiceId;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('tenant.baseDomain=' . self::BASE_DOMAIN);
        $_ENV['tenant.baseDomain']    = self::BASE_DOMAIN;
        $_SERVER['tenant.baseDomain'] = self::BASE_DOMAIN;

        $this->vendorId  = $this->seedVendor('wl_money');
        $this->serviceId = $this->seedGuestPricedService($this->vendorId, 'Hot Buffet');

        $this->otherVendorId  = $this->seedVendor('wl_rival');
        $this->otherServiceId = $this->seedGuestPricedService($this->otherVendorId, 'Rival Buffet');

        $this->db->table('vendor_sites')->insert([
            'vendor_id'     => $this->vendorId,
            'subdomain'     => 'money',
            'business_name' => 'Money Test Events',
            'phone'         => '020 7946 0958',
            'status'        => 'active',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        putenv('tenant.baseDomain');
        unset($_ENV['tenant.baseDomain'], $_SERVER['tenant.baseDomain'], $_SERVER['HTTP_HOST']);
    }

    private function seedVendor(string $slug): int
    {
        $row = [
            'name'     => 'Vendor ' . $slug,
            'username' => $slug . '_' . uniqid(),
            'email'    => uniqid($slug . '_') . '@example.test',
            'password' => 'hash',
            'role'     => 'vendor',
        ];
        if ($this->db->fieldExists('vendor_status', 'users')) {
            $row['vendor_status'] = 'approved';
        }
        $this->db->table('users')->insert($row);

        return (int) $this->db->insertID();
    }

    /**
     * A service priced £19/guest for 26–50 guests (and £22 for 1–25), with a
     * flat +£120 staffing extra — mirrors the design comp's caterer example.
     */
    private function seedGuestPricedService(int $vendorId, string $title): int
    {
        $this->db->table('services')->insert([
            'vendor_id' => $vendorId,
            'title'     => $title,
            'status'    => 'active',
        ]);
        $serviceId = (int) $this->db->insertID();

        $this->db->table('services_private_event_pricing')->insert([
            'service_id'   => $serviceId,
            'pricing_type' => 'guest_based_pricing',
        ]);
        $pid = (int) $this->db->insertID();

        foreach ([[1, 25, 22.00], [26, 50, 19.00]] as [$min, $max, $price]) {
            $this->db->table('services_guest_based_pricing')->insert([
                'service_id'               => $serviceId,
                'private_event_pricing_id' => $pid,
                'min_guest'                => $min,
                'max_guest'                => $max,
                'guest_price'              => $price,
            ]);
        }

        $this->db->table('services_optional_extras')->insert([
            'service_id'   => $serviceId,
            'name'         => 'Staff to serve & clear',
            'price'        => 120.00,
            'pricing_type' => 'flat',
        ]);

        return $serviceId;
    }

    private function onTenant(): void
    {
        $_SERVER['HTTP_HOST'] = 'money.' . self::BASE_DOMAIN;
        service('routes')->resetRoutes();
    }

    /**
     * The csrf global filter applies to tenant POSTs like everywhere else —
     * feature-test bodies need the token pair.
     *
     * @param array<string,mixed> $data
     *
     * @return array<string,mixed>
     */
    private function withCsrf(array $data): array
    {
        $data[csrf_token()] = csrf_hash();

        return $data;
    }

    private function extraId(): int
    {
        return (int) $this->db->table('services_optional_extras')
            ->where('service_id', $this->serviceId)->get()->getRowArray()['id'];
    }

    /**
     * 45 guests × £19 + £120 staffing = £975.00 → deposit £97.50
     */
    private function postQuote(): TestResponse
    {
        $this->onTenant();

        return $this->post('/quote', $this->withCsrf([
            'service_id'  => $this->serviceId,
            'event_date'  => date('Y-m-d', strtotime('+30 days')),
            'guest_count' => 45,
            'extras'      => [$this->extraId()],
            // no postcode → no geocoding network call in tests
        ]));
    }

    public function testQuotePricesAndGoesStraightToCheckout(): void
    {
        // New funnel (Storefront System board): the itemised quote lives on
        // the service page; "Book this date" stores the server-priced quote
        // and redirects straight to checkout.
        $result = $this->postQuote();

        $result->assertRedirectTo('http://money.' . self::BASE_DOMAIN . '/checkout');
        $quote = session()->get('tenant_quote');
        $this->assertIsArray($quote);
        $this->assertSame(975.00, (float) $quote['total']); // 45 × £19 + £120
        $this->assertSame(97.50, DepositCalculator::forTotal((float) $quote['total']));
    }

    public function testQuoteLiveReturnsItemisedJson(): void
    {
        $this->onTenant();
        $result = $this->get('/quote-live?' . http_build_query([
            'service_id'  => $this->serviceId,
            'event_date'  => date('Y-m-d', strtotime('+30 days')),
            'guest_count' => 45,
            'extras'      => [$this->extraId()],
        ]));

        $result->assertStatus(200);
        $json = json_decode((string) $result->response()->getBody(), true);
        $this->assertTrue($json['ok']);
        $this->assertSame(975.00, (float) $json['total']);
        $this->assertSame(97.50, (float) $json['deposit']);
        $this->assertNotEmpty($json['lines']);
    }

    public function testQuoteForForeignServiceIs404(): void
    {
        $this->onTenant();
        $this->expectException(PageNotFoundException::class);
        $this->post('/quote', $this->withCsrf([
            'service_id' => $this->otherServiceId,
            'event_date' => date('Y-m-d', strtotime('+30 days')),
        ]));
    }

    public function testCheckoutWithoutQuoteRedirectsHome(): void
    {
        $this->onTenant();
        $this->get('/checkout')->assertRedirectTo('http://money.' . self::BASE_DOMAIN . '/');
    }

    public function testFullGuestCheckoutCreatesMarketplaceShapedRecords(): void
    {
        $this->postQuote()->assertRedirectTo('http://money.' . self::BASE_DOMAIN . '/checkout');
        // Feature-test requests reset $_SESSION — carry the quote forward
        // the way a real browser session would.
        $quote = session()->get('tenant_quote');
        $this->assertIsArray($quote);

        // GET checkout renders the simulated-payment form (no Stripe keys).
        $this->onTenant();
        $checkout = $this->withSession(['tenant_quote' => $quote])->get('/checkout');
        $checkout->assertStatus(200);
        $checkout->assertSee('Hold your date');
        $checkout->assertSee('97.50');
        $checkout->assertSee('simulated');

        // POST completes the booking.
        $this->onTenant();
        $done = $this->withSession(['tenant_quote' => $quote])->post('/checkout', $this->withCsrf([
            'guest_name'  => 'Sarah Okafor',
            'guest_email' => 'sarah.okafor@example.test',
            'guest_phone' => '07700 900456',
        ]));
        $done->assertRedirect();
        preg_match('#/booked/(\d+)#', (string) $done->getRedirectUrl(), $m); // path only — baseURL may contain digits (e.g. a port)
        $bookingId = (int) ($m[1] ?? 0);
        $this->assertGreaterThan(0, $bookingId);

        // Customer account created for the guest email.
        $user = $this->db->table('users')->where('email', 'sarah.okafor@example.test')->get()->getRowArray();
        $this->assertNotNull($user);
        $this->assertSame('customer', $user['role']);

        // Event carries the quoted date + guests, owned by the guest account.
        $booking = $this->db->table('bookings')->where('id', $bookingId)->get()->getRowArray();
        $this->assertNotNull($booking);
        $this->assertSame((int) $user['id'], (int) $booking['user_id']);
        $this->assertSame(877.50, (float) $booking['balance_due']);

        $event = $this->db->table('events')->where('id', $booking['event_id'])->get()->getRowArray();
        $this->assertSame(45, (int) $event['guest_count']);

        // Booking item in marketplace shape, quote_breakdown JSON intact.
        $item = $this->db->table('booking_items')->where('booking_id', $bookingId)->get()->getRowArray();
        $this->assertSame($this->serviceId, (int) $item['service_id']);
        $this->assertSame(975.00, (float) $item['price']);
        $breakdown = json_decode((string) $item['quote_breakdown'], true);
        $this->assertIsArray($breakdown['lines']);

        // Simulated payment row for exactly the 10% deposit.
        $payment = $this->db->table('payments')->where('booking_id', $bookingId)->get()->getRowArray();
        $this->assertSame(97.50, (float) $payment['amount_paid']);
        $this->assertSame('simulated', $payment['payment_method']);
        $this->assertSame('deposit', $payment['payment_type']);
        $this->assertSame('succeeded', $payment['payment_status']);

        // Confirmation page renders for this session…
        $mine = session()->get('tenant_bookings');
        $this->onTenant();
        $confirm = $this->withSession(['tenant_bookings' => $mine, 'tenant_guest_name' => 'Sarah'])->get('/booked/' . $bookingId);
        $confirm->assertStatus(200);
        $confirm->assertSee('Date held');
        $confirm->assertSee('PS-' . $bookingId); // platform reference (frame 1k)
        $confirm->assertSee('97.50');
        $confirm->assertSee('877.50');
    }

    public function testConfirmationHiddenFromStrangers(): void
    {
        // Book in this session…
        $this->postQuote();
        $quote = session()->get('tenant_quote');
        $this->onTenant();
        $done = $this->withSession(['tenant_quote' => $quote])->post('/checkout', $this->withCsrf([
            'guest_name'  => 'Emma Wright',
            'guest_email' => 'emma.wright@example.test',
        ]));
        preg_match('#/booked/(\d+)#', (string) $done->getRedirectUrl(), $m); // path only — baseURL may contain digits (e.g. a port)
        $bookingId = (int) ($m[1] ?? 0);

        // …then pretend to be a different visitor (no tenant_bookings in session).
        session()->remove('tenant_bookings');
        $this->onTenant();
        $this->expectException(PageNotFoundException::class);
        $this->get('/booked/' . $bookingId);
    }
}
