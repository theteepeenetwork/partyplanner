<?php

namespace App\Controllers;

use App\Libraries\DepositCalculator;
use App\Libraries\EventQuoteBuilder;
use App\Libraries\StripeCheckoutHelper;
use App\Libraries\TenantBookingFlow;
use App\Libraries\TenantContext;
use App\Libraries\UKAddressGeocoder;
use App\Models\BookingItemModel;
use App\Models\BookingModel;
use App\Models\CategoryModel;
use App\Models\EventModel;
use App\Models\PaymentsModel;
use App\Models\ServiceImageModel;
use App\Models\ServiceModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

/**
 * White-label storefront (T3): the customer-facing pages served on a vendor's
 * tenant subdomain. Every query is pinned to the tenant vendor resolved by
 * the VendorTenant filter; ownership checks go through
 * TenantContext::assertOwns() rather than inline vendor_id comparisons.
 */
class TenantController extends BaseController
{
    public function home()
    {
        $tenant = $this->requireTenant();

        $serviceModel = new ServiceModel();
        $services     = $serviceModel->publicCatalogue()
            ->where('vendor_id', $tenant->vendorId())
            ->orderBy('id', 'DESC')
            ->findAll();

        $imageModel    = new ServiceImageModel();
        $categoryModel = new CategoryModel();

        foreach ($services as &$service) {
            $service['images'] = $imageModel
                ->where(['service_id' => $service['id'], 'is_primary' => 1])
                ->findAll();
            $service['category_name'] = $categoryModel->getServiceCategoryLabel($service);
        }
        unset($service);

        return view('tenant/home', [
            'site'     => $tenant->site(),
            'vendor'   => $tenant->vendor(),
            'services' => $services,
            'trust'    => $this->vendorTrust($tenant->vendorId()),
        ]);
    }

    public function service($id)
    {
        $tenant = $this->requireTenant();

        $service = (new ServiceModel())->getServiceWithImages((int) $id);
        $service = $tenant->assertOwns($service);

        // Only publicly listable services are reachable on the storefront —
        // the same closed rule as ServiceModel::publicCatalogue().
        if (($service['status'] ?? 'active') !== 'active' || ($service['deleted_at'] ?? null) !== null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $flow = new TenantBookingFlow();

        return view('tenant/service', [
            'site'           => $tenant->site(),
            'vendor'         => $tenant->vendor(),
            'service'        => $service,
            'categoryName'   => (new CategoryModel())->getServiceCategoryLabel($service),
            'extras'         => $flow->extrasForForm((int) $service['id']),
            'pricing'        => $flow->pricingContext((int) $service['id']),
            'trust'          => $this->vendorTrust($tenant->vendorId(), (int) $service['id']),
            'depositPercent' => DepositCalculator::percentDisplay(),
            'pageTitle'      => $service['title'],
        ]);
    }

    /**
     * Instant quote (storefront screen 03): price the tenant's service for a
     * guest-entered date/location via EventQuoteBuilder — the same engine the
     * marketplace basket uses; no pricing logic lives here.
     */
    public function quote()
    {
        $tenant  = $this->requireTenant();
        $service = (new ServiceModel())->find((int) $this->request->getPost('service_id'));
        $service = $tenant->assertOwns($service);
        if (($service['status'] ?? 'active') !== 'active' || ($service['deleted_at'] ?? null) !== null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $back = redirect()->to('/service/' . (int) $service['id']);

        $date = trim((string) $this->request->getPost('event_date'));
        if ($date === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $back->with('error', 'Please pick your party date.');
        }
        if ($date < date('Y-m-d')) {
            return $back->with('error', 'That date has already passed — pick an upcoming date.');
        }

        $postcode = strtoupper(trim((string) $this->request->getPost('postcode')));
        $townCity = trim((string) $this->request->getPost('town_city'));
        if (mb_strlen($postcode) > 10 || mb_strlen($townCity) > 100) {
            return $back->with('error', 'Please check the location details.');
        }

        $pricing    = (new TenantBookingFlow())->pricingContext((int) $service['id']);
        $guestCount = (int) $this->request->getPost('guest_count');
        if ($pricing['needsGuests'] && $guestCount < 1) {
            return $back->with('error', 'How many guests are you expecting?');
        }

        $orderQuantity = (int) $this->request->getPost('order_quantity') ?: null;
        if ($pricing['needsQuantity'] && ($orderQuantity === null || $orderQuantity < 1)) {
            return $back->with('error', 'How many do you need?');
        }

        $pricingOption = trim((string) $this->request->getPost('pricing_option')) ?: null;
        if ($pricingOption !== null && ! preg_match('/^(guest|duration|package|timeblock|qty)_\d+$/', $pricingOption)) {
            $pricingOption = null;
        }

        $extras = [];

        foreach ((array) $this->request->getPost('extras') as $x) {
            if ((int) $x > 0) {
                $extras[] = (int) $x;
            }
        }
        $extraQty = [];

        foreach ((array) $this->request->getPost('extra_qty') as $id => $qty) {
            if ((int) $id > 0 && (int) $qty > 0) {
                $extraQty[(int) $id] = (int) $qty;
            }
        }

        // Best-effort geocode so travel fees price correctly; the engine
        // degrades to a warning when coordinates are missing.
        $geo = null;
        if ($postcode !== '' || $townCity !== '') {
            $geo = (new UKAddressGeocoder())->geocode($postcode ?: null, $townCity ?: null);
        }

        $event = [
            'title'         => 'Your event',
            'event_type'    => 'Private party',
            'event_setting' => 'private',
            'date'          => $date,
            'guest_count'   => $guestCount > 0 ? $guestCount : null,
            'latitude'      => $geo['latitude'] ?? null,
            'longitude'     => $geo['longitude'] ?? null,
            'postcode'      => $postcode ?: null,
            'town_city'     => $townCity ?: null,
            'location'      => trim($townCity . ($postcode !== '' ? ', ' . $postcode : ''), ', ') ?: null,
        ];

        $quote = (new EventQuoteBuilder())->build($service, $event, $pricingOption, $extras, $extraQty, $orderQuantity);

        if (! empty($quote['errors'])) {
            return $back->with('error', implode(' ', $quote['errors']));
        }

        $total = round((float) $quote['total'], 2);
        if (! empty($quote['custom_quote']) || $total <= 0) {
            return $back->with('info', 'This service is priced on request — call us and we\'ll sort your date.');
        }

        $payload = [
            'subdomain'      => $tenant->subdomain(),
            'service_id'     => (int) $service['id'],
            'event'          => $event,
            'pricing_option' => $pricingOption,
            'pricing_label'  => $quote['lines'][0]['label'] ?? null,
            'order_quantity' => $orderQuantity,
            'extras'         => $extras,
            'extra_qty'      => $extraQty,
            'lines'          => $quote['lines'],
            'warnings'       => $quote['warnings'],
            'warning_codes'  => $quote['warning_codes'] ?? [],
            'distance_km'    => $quote['distance_km'],
            'total'          => $total,
            'expires_at'     => time() + 48 * 3600, // "saved for 48 hours"
        ];
        session()->set('tenant_quote', $payload);

        return view('tenant/quote', [
            'site'           => $tenant->site(),
            'service'        => $service,
            'quote'          => $payload,
            'deposit'        => DepositCalculator::forTotal($total),
            'depositPercent' => DepositCalculator::percentDisplay(),
            'pageTitle'      => 'Your quote',
        ]);
    }

    /**
     * Deposit checkout (storefront screen 04). GET renders guest details +
     * card form (Stripe PaymentElement when configured, simulated otherwise);
     * POST verifies payment and books via TenantBookingFlow.
     */
    public function checkout()
    {
        $tenant = $this->requireTenant();
        $q      = $this->validSessionQuote($tenant);
        if ($q === null) {
            return redirect()->to('/')->with('error', 'Your quote has expired — grab a fresh price, it only takes a second.');
        }

        $service = (new ServiceModel())->find((int) $q['service_id']);
        $service = $tenant->assertOwns($service);

        $total   = (float) $q['total'];
        $deposit = DepositCalculator::forTotal($total);

        if (strtoupper($this->request->getMethod()) === 'POST') {
            return $this->checkoutSubmit($tenant, $service, $q, $deposit);
        }

        $stripe        = new StripeCheckoutHelper();
        $stripeEnabled = $stripe->isConfigured();
        $clientSecret  = null;
        if ($stripeEnabled && $deposit > 0) {
            $pi = $stripe->createPaymentIntent((int) round($deposit * 100), [
                'tenant'         => '1',
                'vendor_site_id' => (string) ($tenant->site()['id'] ?? ''),
                'subdomain'      => (string) $tenant->subdomain(),
                'service_id'     => (string) $q['service_id'],
            ]);
            if ($pi['success']) {
                $clientSecret = $pi['client_secret'];
            } else {
                $stripeEnabled = false;
            }
        }

        return view('tenant/checkout', [
            'site'                 => $tenant->site(),
            'service'              => $service,
            'quote'                => $q,
            'deposit'              => $deposit,
            'depositPercent'       => DepositCalculator::percentDisplay(),
            'stripeEnabled'        => $stripeEnabled,
            'stripePublishableKey' => getenv('STRIPE_PUBLISHABLE_KEY') ?: '',
            'stripeClientSecret'   => $clientSecret,
            'pageTitle'            => 'Pay your deposit',
        ]);
    }

    /**
     * POST leg of checkout(): mirrors EventController::processCheckout()'s
     * payment semantics (verify PI when Stripe is configured, simulated
     * otherwise), then persists the booking.
     */
    private function checkoutSubmit(TenantContext $tenant, array $service, array $q, float $deposit)
    {
        $name  = trim((string) $this->request->getPost('guest_name'));
        $email = trim((string) $this->request->getPost('guest_email'));
        $phone = trim((string) $this->request->getPost('guest_phone'));
        if ($name === '' || mb_strlen($name) > 100) {
            return redirect()->to('/checkout')->with('error', 'Please tell us your name.');
        }
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to('/checkout')->with('error', 'Please enter a valid email — your confirmation goes there.');
        }

        $stripe          = new StripeCheckoutHelper();
        $paymentIntentId = (string) $this->request->getPost('payment_intent_id') ?: null;
        $paidNow         = true;

        if ($stripe->isConfigured()) {
            if (! $paymentIntentId) {
                return redirect()->to('/checkout')->with('error', 'Payment was not completed.');
            }
            $verified = $stripe->verifyPaymentIntent($paymentIntentId);
            if (! $verified['success']) {
                return redirect()->to('/checkout')->with('error', $verified['error'] ?? 'Payment verification failed.');
            }
            $paidNow = ($verified['status'] ?? '') === 'succeeded';
        }

        $result = (new TenantBookingFlow())->createGuestBooking(
            $tenant->site(),
            $service,
            $q,
            ['name' => $name, 'email' => $email, 'phone' => $phone],
            $paymentIntentId,
            $paidNow,
            $stripe->isConfigured(),
        );

        // The guest has no login on the storefront: remember which bookings
        // this session made so only they can view the confirmation page.
        $mine   = (array) session()->get('tenant_bookings');
        $mine[] = $result['bookingId'];
        session()->set('tenant_bookings', $mine);
        session()->set('tenant_guest_name', strtok($name, ' ')); // first name, for the confirmation greeting
        session()->remove('tenant_quote');

        return redirect()->to('/booked/' . $result['bookingId']);
    }

    /**
     * Booking confirmed (storefront screen 05).
     *
     * @param mixed $bookingId
     */
    public function booked($bookingId)
    {
        $tenant  = $this->requireTenant();
        $booking = (new BookingModel())->find((int) $bookingId);
        if ($booking === null || ! in_array((int) $bookingId, array_map('intval', (array) session()->get('tenant_bookings')), true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $items = (new BookingItemModel())
            ->select('booking_items.*, services.title AS service_title, services.vendor_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->where('booking_id', (int) $bookingId)
            ->findAll();
        if ($items === []) {
            throw PageNotFoundException::forPageNotFound();
        }

        foreach ($items as $item) {
            $tenant->assertOwns($item); // every line must belong to this tenant
        }

        $event   = (new EventModel())->find((int) $booking['event_id']);
        $payment = (new PaymentsModel())->where('booking_id', (int) $bookingId)->first();

        return view('tenant/booked', [
            'site'      => $tenant->site(),
            'booking'   => $booking,
            'items'     => $items,
            'event'     => $event,
            'payment'   => $payment,
            'pageTitle' => 'Booking confirmed',
        ]);
    }

    /**
     * The session quote, if it exists, hasn't expired, and belongs to THIS
     * tenant host (a quote priced on one storefront can't be checked out on
     * another).
     *
     * @return array<string,mixed>|null
     */
    private function validSessionQuote(TenantContext $tenant): ?array
    {
        $q = session()->get('tenant_quote');
        if (! is_array($q)
            || ($q['subdomain'] ?? null) !== $tenant->subdomain()
            || (int) ($q['expires_at'] ?? 0) < time()
            || (float) ($q['total'] ?? 0) <= 0
        ) {
            return null;
        }

        return $q;
    }

    /**
     * Tenant pages must never render without a tenant resolved by the
     * VendorTenant filter (e.g. a route misconfiguration) — fail closed.
     */
    private function requireTenant(): TenantContext
    {
        $tenant = service('tenant');
        if (! $tenant->isActive()) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $tenant;
    }

    /**
     * Social-proof figures for the storefront hero: the vendor's average
     * review rating and a confirmed-bookings count. Read-only aggregates —
     * the same reviews/booking_items tables the marketplace already reads.
     * When $serviceId is given, the booking count is for that service only.
     *
     * @return array{rating: float|null, reviews: int, bookings: int}
     */
    private function vendorTrust(int $vendorId, ?int $serviceId = null): array
    {
        $db = Database::connect();

        $rating  = null;
        $reviews = 0;
        if ($db->tableExists('reviews')) {
            $row = $db->table('reviews')
                ->select('AVG(rating) AS avg_rating, COUNT(*) AS cnt')
                ->where('vendor_id', $vendorId)
                ->get()->getRowArray();
            if ($row && $row['cnt'] > 0) {
                $rating  = round((float) $row['avg_rating'], 1);
                $reviews = (int) $row['cnt'];
            }
        }

        $bookings = 0;
        if ($db->tableExists('booking_items') && $db->tableExists('services')) {
            $builder = $db->table('booking_items')
                ->join('services', 'services.id = booking_items.service_id')
                ->where('services.vendor_id', $vendorId)
                ->whereIn('booking_items.status', ['accepted', 'confirmed']);
            if ($serviceId !== null) {
                $builder->where('booking_items.service_id', $serviceId);
            }
            $bookings = (int) $builder->countAllResults();
        }

        return ['rating' => $rating, 'reviews' => $reviews, 'bookings' => $bookings];
    }
}
