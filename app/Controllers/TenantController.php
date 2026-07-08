<?php

namespace App\Controllers;

use App\Libraries\DepositCalculator;
use App\Libraries\EventQuoteBuilder;
use App\Libraries\ServiceAvailabilityChecker;
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
 * White-label storefront — the customer-facing funnel on a vendor's tenant
 * subdomain (Storefront System handoff, frames 1a–1n):
 *
 *   Landing (mode A: single service IS the homepage / mode B: service cards)
 *   → Service + live itemised quote → Checkout (10% deposit, guest)
 *   → Confirmation.
 *
 * Every query is pinned to the tenant vendor resolved by the VendorTenant
 * filter; ownership checks go through TenantContext::assertOwns(). Pricing
 * is exclusively EventQuoteBuilder / DepositCalculator — no maths here.
 */
class TenantController extends BaseController
{
    /**
     * "Most booked" badge threshold: the leading service must have at least
     * this many CONFIRMED bookings before the badge is shown. Without a floor
     * the badge would appear on near-empty vendors and mean nothing (report
     * §3). Only accepted/confirmed bookings count — the same states that make
     * a booking a "verified booking" elsewhere.
     */
    private const MOST_BOOKED_MIN = 3;

    public function home()
    {
        $tenant = $this->requireTenant();

        $services = (new ServiceModel())->publicCatalogue()
            ->where('vendor_id', $tenant->vendorId())
            ->orderBy('id', 'DESC')
            ->findAll();

        // Landing mode A: a single-service vendor's service IS the homepage.
        if (count($services) === 1) {
            return $this->landingSingle($tenant, $services[0]);
        }

        $flow          = new TenantBookingFlow();
        $imageModel    = new ServiceImageModel();
        $categoryModel = new CategoryModel();

        foreach ($services as &$service) {
            $service['images']        = $imageModel->where(['service_id' => $service['id'], 'is_primary' => 1])->findAll();
            $service['category_name'] = $categoryModel->getServiceCategoryLabel($service);
            $service['from']          = $flow->fromPrice($service);
        }
        unset($service);

        // "Most booked" badge: the service with the most confirmed bookings.
        $mostBookedId = $this->mostBookedServiceId($tenant->vendorId());

        return view('tenant/home', [
            'site'         => $tenant->site(),
            'vendor'       => $tenant->vendor(),
            'services'     => $services,
            'trust'        => $this->vendorTrust($tenant->vendorId()),
            'aboutLine'    => $this->aboutLine($tenant, $services),
            'coverage'     => $this->coverageArea($services),
            'reviews'      => $this->recentReviews($tenant->vendorId(), 3),
            'mostBookedId' => $mostBookedId,
            'heroImage'    => $this->firstImageUrl($services),
        ]);
    }

    /**
     * Landing mode A (frames 1b/1c): hero photo + availability checker +
     * what's-included + review, with a sticky book bar on mobile. The
     * checker submits to the service page, which runs the real quote.
     */
    private function landingSingle(TenantContext $tenant, array $service)
    {
        $flow    = new TenantBookingFlow();
        $service = (new ServiceModel())->getServiceWithImages((int) $service['id']);

        return view('tenant/home_single', [
            'site'         => $tenant->site(),
            'vendor'       => $tenant->vendor(),
            'service'      => $service,
            'categoryName' => (new CategoryModel())->getServiceCategoryLabel($service),
            'from'         => $flow->fromPrice($service),
            'trust'        => $this->vendorTrust($tenant->vendorId(), (int) $service['id']),
            'reviews'      => $this->recentReviews($tenant->vendorId(), 1),
            'photos'       => $this->photoContext($service),
            'newVendor'    => $this->newVendorContext($tenant),
            'aboutLine'    => $this->aboutLine($tenant, [$service]),
            'hasStickyBar' => true,
        ]);
    }

    /**
     * The core screen (frames 1f/1g/1h): gallery, option cards, live
     * itemised quote, availability chip / unavailable panel, sticky bar.
     * Accepts ?date=&postcode=&guests= from the landing checker.
     *
     * @param mixed $id
     */
    public function service($id)
    {
        $tenant = $this->requireTenant();

        $service = (new ServiceModel())->getServiceWithImages((int) $id);
        $service = $tenant->assertOwns($service);
        if (($service['status'] ?? 'active') !== 'active' || ($service['deleted_at'] ?? null) !== null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $flow = new TenantBookingFlow();

        $date = trim((string) $this->request->getGet('date'));
        if ($date !== '' && (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < date('Y-m-d'))) {
            $date = '';
        }
        $postcode = strtoupper(trim((string) $this->request->getGet('postcode')));
        if (! preg_match('/^[A-Z0-9][A-Z0-9 ]{1,9}$/', $postcode)) {
            $postcode = '';
        }
        $guests = max(0, (int) $this->request->getGet('guests'));

        $available    = null;
        $nearestDates = [];
        if ($date !== '') {
            $available = (new ServiceAvailabilityChecker())->check((int) $service['id'], $tenant->vendorId(), $date) === [];
            if (! $available) {
                $nearestDates = $flow->nearestFreeDates((int) $service['id'], $tenant->vendorId(), $date);
            }
        }

        return view('tenant/service', [
            'site'           => $tenant->site(),
            'vendor'         => $tenant->vendor(),
            'service'        => $service,
            'categoryName'   => (new CategoryModel())->getServiceCategoryLabel($service),
            'extras'         => $flow->extrasForForm((int) $service['id']),
            'pricing'        => $flow->pricingContext((int) $service['id']),
            'trust'          => $this->vendorTrust($tenant->vendorId(), (int) $service['id']),
            'reviews'        => $this->recentReviews($tenant->vendorId(), 2),
            'photos'         => $this->photoContext($service),
            'depositPercent' => DepositCalculator::percentDisplay(),
            'ctxDate'        => $date,
            'ctxPostcode'    => $postcode,
            'ctxGuests'      => $guests,
            'available'      => $available,
            'nearestDates'   => $nearestDates,
            'isMultiService' => $this->activeServiceCount($tenant->vendorId()) > 1,
            'pageTitle'      => $service['title'],
            'hasStickyBar'   => $available !== false,
        ]);
    }

    /**
     * Live itemised quote (JSON, GET so the CSRF token isn't consumed on
     * every option change). Recomputes through EventQuoteBuilder each time —
     * base, extras, travel to the postcode — and returns the same figures
     * the sticky bar and price card render. "Never lies": these are the
     * numbers checkout will charge against.
     */
    public function quoteLive()
    {
        $tenant = $this->requireTenant();
        $parsed = $this->parseQuoteRequest($tenant, 'get');
        if (isset($parsed['error'])) {
            return $this->response->setJSON(['ok' => false, 'error' => $parsed['error']]);
        }

        $quote = $parsed['quote'];
        if (! empty($quote['errors'])) {
            return $this->response->setJSON(['ok' => false, 'error' => implode(' ', $quote['errors'])]);
        }

        $total = round((float) $quote['total'], 2);

        return $this->response->setJSON([
            'ok'          => true,
            'lines'       => $quote['lines'],
            'warnings'    => $quote['warnings'],
            'distance_km' => $quote['distance_km'],
            'total'       => $total,
            'deposit'     => DepositCalculator::forTotal($total),
        ]);
    }

    /**
     * Availability check for a date (frame 1h): available, or the nearest
     * free dates so the "no" always comes with alternatives.
     */
    public function availability()
    {
        $tenant  = $this->requireTenant();
        $service = (new ServiceModel())->find((int) $this->request->getGet('service_id'));
        $service = $tenant->assertOwns($service);

        $date = trim((string) $this->request->getGet('date'));
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < date('Y-m-d')) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Pick an upcoming date.']);
        }

        $available = (new ServiceAvailabilityChecker())->check((int) $service['id'], $tenant->vendorId(), $date) === [];

        return $this->response->setJSON([
            'ok'        => true,
            'available' => $available,
            'date'      => $date,
            'dateLabel' => date('D j M', strtotime($date)),
            'nearest'   => $available ? [] : (new TenantBookingFlow())->nearestFreeDates((int) $service['id'], $tenant->vendorId(), $date),
        ]);
    }

    /**
     * "Book this date": final server-side quote for the chosen options,
     * stored in the session, then straight to checkout — the itemised quote
     * already lives on the service page (no interim quote page).
     */
    public function quote()
    {
        $tenant = $this->requireTenant();
        $parsed = $this->parseQuoteRequest($tenant, 'post');
        if (isset($parsed['error'])) {
            return redirect()->to($tenant->url($parsed['backTo'] ?? '/'))->with('error', $parsed['error']);
        }

        $service = $parsed['service'];
        $quote   = $parsed['quote'];
        $back    = redirect()->to($tenant->url('/service/' . (int) $service['id'] . '?' . http_build_query(array_filter([
            'date'     => $parsed['event']['date'],
            'postcode' => $parsed['event']['postcode'],
            'guests'   => $parsed['event']['guest_count'],
        ]))));

        if (! empty($quote['errors'])) {
            return $back->with('error', implode(' ', $quote['errors']));
        }

        $total = round((float) $quote['total'], 2);
        if (! empty($quote['custom_quote']) || $total <= 0) {
            return $back->with('info', 'This service is priced on request — call us and we\'ll sort your date.');
        }

        session()->set('tenant_quote', [
            'subdomain'      => $tenant->subdomain(),
            'service_id'     => (int) $service['id'],
            'event'          => $parsed['event'],
            'pricing_option' => $parsed['pricingOption'],
            'pricing_label'  => $quote['lines'][0]['label'] ?? null,
            'order_quantity' => $parsed['orderQuantity'],
            'extras'         => $parsed['extras'],
            'extra_qty'      => $parsed['extraQty'],
            'lines'          => $quote['lines'],
            'warnings'       => $quote['warnings'],
            'warning_codes'  => $quote['warning_codes'] ?? [],
            'distance_km'    => $quote['distance_km'],
            'total'          => $total,
            'expires_at'     => time() + 48 * 3600,
        ]);

        return redirect()->to($tenant->url('/checkout'));
    }

    /**
     * Checkout (frames 1i/1j): hold-your-date framing, 10% deposit only,
     * guest checkout mandatory. GET renders; POST verifies payment and books.
     */
    public function checkout()
    {
        $tenant = $this->requireTenant();
        $q      = $this->validSessionQuote($tenant);
        if ($q === null) {
            return redirect()->to($tenant->url('/'))->with('error', 'Your quote has expired — grab a fresh price, it only takes a second.');
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

        $thumb = (new ServiceImageModel())
            ->where(['service_id' => (int) $service['id'], 'is_primary' => 1])
            ->first();

        return view('tenant/checkout', [
            'site'                 => $tenant->site(),
            'service'              => $service,
            'quote'                => $q,
            'deposit'              => $deposit,
            'depositPercent'       => DepositCalculator::percentDisplay(),
            'thumbUrl'             => $thumb ? '/' . ltrim((string) ($thumb['thumbnail_path'] ?? $thumb['image_path'] ?? ''), '/') : '',
            'stripeEnabled'        => $stripeEnabled,
            'stripePublishableKey' => getenv('STRIPE_PUBLISHABLE_KEY') ?: '',
            'stripeClientSecret'   => $clientSecret,
            'pageTitle'            => 'Hold your date',
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
            return redirect()->to($tenant->url('/checkout'))->with('error', 'Please tell us your name.');
        }
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to($tenant->url('/checkout'))->with('error', 'Please enter a valid email — your confirmation goes there.');
        }

        $stripe          = new StripeCheckoutHelper();
        $paymentIntentId = (string) $this->request->getPost('payment_intent_id') ?: null;
        $paidNow         = true;

        if ($stripe->isConfigured()) {
            if (! $paymentIntentId) {
                return redirect()->to($tenant->url('/checkout'))->with('error', 'Payment was not completed.');
            }
            $verified = $stripe->verifyPaymentIntent($paymentIntentId);
            if (! $verified['success']) {
                return redirect()->to($tenant->url('/checkout'))->with('error', $verified['error'] ?? 'Payment verification failed.');
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
        session()->set('tenant_guest_name', strtok($name, ' '));
        session()->set('tenant_guest_email', $email);
        session()->remove('tenant_quote');

        return redirect()->to($tenant->url('/booked/' . $result['bookingId']));
    }

    /**
     * Confirmation (frames 1k/1l): "date held" framing, PS reference,
     * calendar/contact actions, what-happens-next timeline, optional
     * account offer.
     *
     * @param mixed $bookingId
     */
    public function booked($bookingId)
    {
        $tenant                              = $this->requireTenant();
        [$booking, $items, $event, $payment] = $this->ownedBooking($tenant, (int) $bookingId);

        return view('tenant/booked', [
            'site'       => $tenant->site(),
            'booking'    => $booking,
            'items'      => $items,
            'event'      => $event,
            'payment'    => $payment,
            'reference'  => 'PS-' . (int) $booking['id'],
            'guestEmail' => (string) session()->get('tenant_guest_email'),
            'pageTitle'  => 'Date held',
        ]);
    }

    /**
     * "Add to calendar" (frame 1k): an ICS file for the booked date. Same
     * session-ownership gate as the confirmation page itself.
     *
     * @param mixed $bookingId
     */
    public function calendarIcs($bookingId)
    {
        $tenant                    = $this->requireTenant();
        [$booking, $items, $event] = $this->ownedBooking($tenant, (int) $bookingId);

        $date  = ! empty($event['date']) ? date('Ymd', strtotime($event['date'])) : date('Ymd');
        $title = ($items[0]['service_title'] ?? 'Booking') . ' — ' . ($tenant->site()['business_name'] ?? 'PartySmith');

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//PartySmith//Storefront//EN',
            'BEGIN:VEVENT',
            'UID:partysmith-booking-' . (int) $booking['id'] . '@' . $tenant->subdomain(),
            'DTSTAMP:' . gmdate('Ymd\THis\Z'),
            'DTSTART;VALUE=DATE:' . $date,
            'SUMMARY:' . str_replace([',', ';'], ['\,', '\;'], $title),
            'DESCRIPTION:Booking ref PS-' . (int) $booking['id'],
            'END:VEVENT',
            'END:VCALENDAR',
        ]) . "\r\n";

        return $this->response
            ->setHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="booking-ps-' . (int) $booking['id'] . '.ics"')
            ->setBody($ics);
    }

    // =====================================================================
    // Shared internals
    // =====================================================================

    /**
     * Parse + price one quote request (GET for the live quote, POST for the
     * final book action). Geocoding results are cached in the session per
     * postcode so live re-quotes don't re-hit the geocoder on every option
     * change.
     *
     * @return array{error: string, backTo?: string}|array{service: array, event: array, pricingOption: ?string, orderQuantity: ?int, extras: list<int>, extraQty: array<int,int>, quote: array}
     */
    private function parseQuoteRequest(TenantContext $tenant, string $method): array
    {
        $in = fn (string $key) => $method === 'get' ? $this->request->getGet($key) : $this->request->getPost($key);

        $service = (new ServiceModel())->find((int) $in('service_id'));
        $service = $tenant->assertOwns($service);
        if (($service['status'] ?? 'active') !== 'active' || ($service['deleted_at'] ?? null) !== null) {
            throw PageNotFoundException::forPageNotFound();
        }
        $backTo = '/service/' . (int) $service['id'];

        $date = trim((string) $in('event_date'));
        if ($date === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return ['error' => 'Please pick your date.', 'backTo' => $backTo];
        }
        if ($date < date('Y-m-d')) {
            return ['error' => 'That date has already passed — pick an upcoming date.', 'backTo' => $backTo];
        }

        $postcode = strtoupper(trim((string) $in('postcode')));
        if (mb_strlen($postcode) > 10) {
            return ['error' => 'Please check the postcode.', 'backTo' => $backTo];
        }

        $pricing    = (new TenantBookingFlow())->pricingContext((int) $service['id']);
        $guestCount = (int) $in('guest_count');
        if ($pricing['needsGuests'] && $guestCount < 1) {
            return ['error' => 'How many guests are you expecting?', 'backTo' => $backTo];
        }

        $orderQuantity = (int) $in('order_quantity') ?: null;
        if ($pricing['needsQuantity'] && ($orderQuantity === null || $orderQuantity < 1)) {
            return ['error' => 'How many do you need?', 'backTo' => $backTo];
        }

        $pricingOption = trim((string) $in('pricing_option')) ?: null;
        if ($pricingOption !== null && ! preg_match('/^(guest|duration|package|timeblock|qty)_\d+$/', $pricingOption)) {
            $pricingOption = null;
        }

        $extras = [];

        foreach ((array) $in('extras') as $x) {
            if ((int) $x > 0) {
                $extras[] = (int) $x;
            }
        }
        $extraQty = [];

        foreach ((array) $in('extra_qty') as $id => $qty) {
            if ((int) $id > 0 && (int) $qty > 0) {
                $extraQty[(int) $id] = (int) $qty;
            }
        }

        // Geocode (session-cached per postcode) so travel prices correctly;
        // the engine degrades to its travel warning without coordinates.
        $geo = null;
        if ($postcode !== '') {
            $cache = (array) session()->get('sf_geo');
            if (array_key_exists($postcode, $cache)) {
                $geo = $cache[$postcode];
            } else {
                $geo              = (new UKAddressGeocoder())->geocode($postcode, null);
                $cache[$postcode] = $geo;
                session()->set('sf_geo', array_slice($cache, -8, null, true));
            }
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
            'town_city'     => null,
            'location'      => $postcode ?: null,
        ];

        return [
            'service'       => $service,
            'event'         => $event,
            'pricingOption' => $pricingOption,
            'orderQuantity' => $orderQuantity,
            'extras'        => $extras,
            'extraQty'      => $extraQty,
            'quote'         => (new EventQuoteBuilder())->build($service, $event, $pricingOption, $extras, $extraQty, $orderQuantity),
        ];
    }

    /**
     * Load a booking for the confirmation surfaces, enforcing both gates:
     * the booking was made by THIS session, and every line belongs to this
     * tenant.
     *
     * @return array{0: array, 1: list<array>, 2: array|null, 3: array|null}
     */
    private function ownedBooking(TenantContext $tenant, int $bookingId): array
    {
        $booking = (new BookingModel())->find($bookingId);
        if ($booking === null || ! in_array($bookingId, array_map('intval', (array) session()->get('tenant_bookings')), true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $items = (new BookingItemModel())
            ->select('booking_items.*, services.title AS service_title, services.vendor_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->where('booking_id', $bookingId)
            ->findAll();
        if ($items === []) {
            throw PageNotFoundException::forPageNotFound();
        }

        foreach ($items as $item) {
            $tenant->assertOwns($item);
        }

        $event   = (new EventModel())->find((int) $booking['event_id']);
        $payment = (new PaymentsModel())->where('booking_id', $bookingId)->first();

        return [$booking, $items, $event, $payment];
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
     * review rating and a confirmed-bookings count. Stars render only from
     * real verified bookings — never seeded (handoff 1m/1n).
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

    /**
     * Recent review cards (text, event context, reviewer) for the vendor.
     *
     * @return list<array<string,mixed>>
     */
    private function recentReviews(int $vendorId, int $limit): array
    {
        $db = Database::connect();
        if (! $db->tableExists('reviews')) {
            return [];
        }

        return $db->table('reviews')
            ->select('reviews.rating, reviews.comment, reviews.created_at, users.name AS reviewer')
            ->join('users', 'users.id = reviews.customer_id', 'left')
            ->where('reviews.vendor_id', $vendorId)
            ->orderBy('reviews.created_at', 'DESC')
            ->get($limit)->getResultArray();
    }

    /**
     * Photo fallback rules, per the design FRAMES (1b/1c/1f/1g/1m — the
     * authoritative source; the 1n text's "filmstrip" line contradicts them
     * and was dropped): 0 → nothing; 1 → framed-on-tint hero; 2+ → gallery
     * (mobile swipe strip / laptop 2fr-1fr mosaic). "No sparse grid" is
     * honoured by the views: at 2 photos both mosaic tiles run full height,
     * so the grid never shows an empty cell.
     *
     * @return array{mode: string, urls: list<string>, extra: int}
     */
    private function photoContext(array $service): array
    {
        $urls = [];

        foreach ((array) ($service['images'] ?? []) as $img) {
            $p = trim((string) ($img['image_path'] ?? ''));
            if ($p !== '') {
                $urls[] = '/' . ltrim($p, '/');
            }
        }

        $mode = match (true) {
            $urls === []       => 'none',
            count($urls) === 1 => 'framed',
            default            => 'gallery',
        };

        return ['mode' => $mode, 'urls' => $urls, 'extra' => max(0, count($urls) - 3)];
    }

    /**
     * "New on PartySmith" context — shown only when the vendor has zero
     * reviews. Never fake stars; explain the platform protection instead.
     *
     * @return array{isNew: bool, joined: string}
     */
    private function newVendorContext(TenantContext $tenant): array
    {
        $trust  = $this->vendorTrust($tenant->vendorId());
        $vendor = $tenant->vendor() ?? [];
        $joined = ! empty($vendor['created_at']) ? date('F Y', strtotime($vendor['created_at'])) : date('F Y');

        return ['isNew' => $trust['reviews'] === 0, 'joined' => $joined];
    }

    /**
     * Auto-generated about line when the vendor hasn't written one (1n):
     * "<Category> covering <area>, on PartySmith since <year>."
     */
    private function aboutLine(TenantContext $tenant, array $services): string
    {
        $site  = $tenant->site() ?? [];
        $about = trim((string) ($site['about_text'] ?? ''));
        if ($about !== '') {
            return $about;
        }

        $category = '';
        if ($services !== []) {
            $category = (new CategoryModel())->getServiceCategoryLabel($services[0]);
            $category = trim(explode('·', $category)[0] ?? '');
        }
        $area   = trim((string) ($services[0]['service_location'] ?? '')) ?: 'your area';
        $vendor = $tenant->vendor() ?? [];
        $since  = ! empty($vendor['created_at']) ? date('Y', strtotime($vendor['created_at'])) : date('Y');

        return ($category !== '' ? $category : 'Event services') . ' covering ' . $area . ', on PartySmith since ' . $since . '.';
    }

    /**
     * Coverage area for the hero meta row — the first service's stated
     * service_location. Returns null when no service carries one, so the view
     * omits the coverage pill rather than showing an empty location.
     */
    private function coverageArea(array $services): ?string
    {
        foreach ($services as $service) {
            $area = trim((string) ($service['service_location'] ?? ''));
            if ($area !== '') {
                return $area;
            }
        }

        return null;
    }

    private function activeServiceCount(int $vendorId): int
    {
        return (new ServiceModel())->publicCatalogue()->where('vendor_id', $vendorId)->countAllResults();
    }

    private function mostBookedServiceId(int $vendorId): ?int
    {
        $db = Database::connect();
        if (! $db->tableExists('booking_items')) {
            return null;
        }

        $row = $db->table('booking_items')
            ->select('booking_items.service_id, COUNT(*) AS cnt')
            ->join('services', 'services.id = booking_items.service_id')
            ->where('services.vendor_id', $vendorId)
            ->whereIn('booking_items.status', ['accepted', 'confirmed'])
            ->groupBy('booking_items.service_id')
            ->orderBy('cnt', 'DESC')
            ->get(1)->getRowArray();

        return ($row && (int) $row['cnt'] >= self::MOST_BOOKED_MIN) ? (int) $row['service_id'] : null;
    }

    /**
     * First primary-image URL across a service list (mode B hero).
     */
    private function firstImageUrl(array $services): string
    {
        foreach ($services as $service) {
            foreach ((array) ($service['images'] ?? []) as $img) {
                $p = trim((string) ($img['image_path'] ?? ''));
                if ($p !== '') {
                    return '/' . ltrim($p, '/');
                }
            }
        }

        return '';
    }
}
