<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\EventBasketItemModel;
use App\Models\ServiceModel;
use App\Models\UserModel;
use App\Models\BookingModel;
use App\Models\BookingItemModel;
use App\Models\PaymentsModel;
use App\Models\ChatRoomModel;
use App\Models\ServiceEventTypeModel;
use App\Models\ServicePublicEventPricingModel;
use App\Models\ServicePrivatePricingModel;
use App\Models\ServiceGuestBasedPricingModel;
use App\Models\ServiceCustomDurationPricingModel;
use App\Models\ServiceLocationModel;
use App\Models\ServiceOptionalExtrasModel;
use App\Models\ServiceImageModel;
use App\Libraries\EventBookingQuote;
use App\Libraries\EventQuoteBuilder;
use App\Libraries\QuoteAnalyticsRecorder;
use App\Libraries\QuoteNotifier;
use App\Libraries\ServiceAvailabilityChecker;
use App\Libraries\StripeCheckoutHelper;
use App\Libraries\VendorQuoteAutomation;
use App\Libraries\UKAddressGeocoder;
use App\Models\PaymentScheduleModel;
use App\Models\ServiceTieredPackagesPricingModel;

/**
 * Manages multi-step event creation, the service basket, and event checkout flow.
 */
class EventController extends BaseController
{
    // =========================================================
    // MULTI-STEP EVENT CREATION
    // =========================================================

    /**
     * Entry point for event creation — redirects to step 1.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function create()
    {
        if ($r = $this->requireCustomerAccount('/event/create')) {
            return $r;
        }

        return redirect()->to('/event/create/step1');
    }

    /**
     * Display or process step 1 of event creation (title, type, setting, date, guest count).
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
     */
    public function createStep1()
    {
        if ($r = $this->requireCustomerAccount('/event/create/step1')) {
            return $r;
        }

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'title' => 'required|min_length[3]|max_length[255]',
                'event_type' => 'required',
                'event_setting' => 'required|in_list[private,public]',
                'date' => 'required|valid_date',
                'guest_count' => 'required|is_natural_no_zero',
            ];

            if (!$this->validate($rules)) {
                return view('event/create_step1', [
                    'errors' => $this->validator->getErrors(),
                    'old' => $this->request->getPost(),
                ]);
            }

            session()->set('event_step1', [
                'title' => $this->request->getPost('title'),
                'event_type' => $this->request->getPost('event_type'),
                'event_setting' => $this->request->getPost('event_setting'),
                'date' => $this->request->getPost('date'),
                'guest_count' => $this->request->getPost('guest_count'),
            ]);

            return redirect()->to('/event/create/step2');
        }

        $data = session()->get('event_step1') ?? [];
        return view('event/create_step1', ['old' => $data, 'errors' => []]);
    }

    /**
     * Display or process step 2 of event creation (venue, postcode, indoor/outdoor, pitch fee).
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
     */
    public function createStep2()
    {
        if ($r = $this->requireCustomerAccount('/event/create/step2')) {
            return $r;
        }
        if (!session()->has('event_step1')) return redirect()->to('/event/create/step1');

        if ($this->request->getMethod() === 'POST') {
            session()->set('event_step2', [
                'venue_name' => $this->request->getPost('venue_name'),
                'postcode' => $this->request->getPost('postcode'),
                'town_city' => $this->request->getPost('town_city'),
                'indoor_outdoor' => $this->request->getPost('indoor_outdoor'),
                'organiser_pitch_fee' => $this->request->getPost('organiser_pitch_fee'),
            ]);

            return redirect()->to('/event/create/step3');
        }

        $step1 = session()->get('event_step1') ?? [];
        $data = session()->get('event_step2') ?? [];
        return view('event/create_step2', [
            'old' => $data,
            'eventSetting' => $step1['event_setting'] ?? 'private',
        ]);
    }

    /**
     * Display or process step 3 of event creation (budget, style/theme, notes).
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
     */
    public function createStep3()
    {
        if ($r = $this->requireCustomerAccount('/event/create/step3')) {
            return $r;
        }
        if (!session()->has('event_step1')) return redirect()->to('/event/create/step1');

        if ($this->request->getMethod() === 'POST') {
            session()->set('event_step3', [
                'budget_min' => $this->request->getPost('budget_min'),
                'budget_max' => $this->request->getPost('budget_max'),
                'style_theme' => $this->request->getPost('style_theme'),
                'notes' => $this->request->getPost('notes'),
            ]);

            return redirect()->to('/event/create/review');
        }

        $data = session()->get('event_step3') ?? [];
        return view('event/create_step3', ['old' => $data]);
    }

    /**
     * Display the review page summarising all event creation steps before final submission.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
     */
    public function createReview()
    {
        if ($r = $this->requireCustomerAccount('/event/create/review')) {
            return $r;
        }
        if (!session()->has('event_step1')) return redirect()->to('/event/create/step1');

        $step1 = session()->get('event_step1');
        $step2 = session()->get('event_step2') ?? [];
        $step3 = session()->get('event_step3') ?? [];

        return view('event/create_review', [
            'step1' => $step1,
            'step2' => $step2,
            'step3' => $step3,
        ]);
    }

    /**
     * Persist the new event from session-stored wizard data and redirect to the events list or pending basket action.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function store()
    {
        if ($r = $this->requireCustomerAccount('/event/create/review')) {
            return $r;
        }
        if (!session()->has('event_step1')) return redirect()->to('/event/create/step1');

        $step1 = session()->get('event_step1');
        $step2 = session()->get('event_step2') ?? [];
        $step3 = session()->get('event_step3') ?? [];

        $eventModel = new EventModel();

        $location = trim(($step2['town_city'] ?? '') . (!empty($step2['postcode']) ? ', ' . $step2['postcode'] : ''));

        $eventSetting = $step1['event_setting'] ?? 'private';
        $pitchFee = null;
        if ($eventSetting === 'public' && isset($step2['organiser_pitch_fee']) && $step2['organiser_pitch_fee'] !== '') {
            $pitchFee = (float) $step2['organiser_pitch_fee'];
        }

        $geo = (new UKAddressGeocoder())->geocode(
            $step2['postcode'] ?? null,
            $step2['town_city'] ?? null
        );

        $eventData = [
            'user_id' => session()->get('user_id'),
            'title' => $step1['title'],
            'event_type' => $step1['event_type'],
            'date' => $step1['date'],
            'guest_count' => $step1['guest_count'],
            'event_setting' => $eventSetting,
            'organiser_pitch_fee' => $pitchFee,
            'latitude' => $geo['latitude'] ?? null,
            'longitude' => $geo['longitude'] ?? null,
            'location' => $location ?: null,
            'venue_name' => $step2['venue_name'] ?? null,
            'postcode' => $step2['postcode'] ?? null,
            'town_city' => $step2['town_city'] ?? null,
            'indoor_outdoor' => $step2['indoor_outdoor'] ?? null,
            'budget_min' => !empty($step3['budget_min']) ? $step3['budget_min'] : null,
            'budget_max' => !empty($step3['budget_max']) ? $step3['budget_max'] : null,
            'style_theme' => $step3['style_theme'] ?? null,
            'notes' => $step3['notes'] ?? null,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $eventModel->insert($eventData);
        $newEventId = $eventModel->getInsertID();

        // Clear event creation session data
        session()->remove('event_step1');
        session()->remove('event_step2');
        session()->remove('event_step3');

        // Check if there's a pending add-to-event action
        $pendingAdd = session()->get('pending_add_to_event');
        if ($pendingAdd) {
            session()->remove('pending_add_to_event');
            return redirect()->to('/event/add-to-basket/' . $pendingAdd['service_id'] . '?event_id=' . $newEventId);
        }

        return redirect()->to('/profile/events')->with('success', 'Event "' . $step1['title'] . '" created successfully!');
    }

    // =========================================================
    // ADD TO EVENT FLOW
    // =========================================================

    /**
     * Display the event-selection page so the customer can choose which event to add a service to.
     *
     * @param int|string $serviceId The service primary key.
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
     */
    public function addToEvent($serviceId)
    {
        $serviceModel = new ServiceModel();
        $service = $serviceModel->find($serviceId);
        if (!$service) {
            return redirect()->to('/browse-services')->with('error', 'Service not found.');
        }

        // Capture selected options from the service view form
        $pricingOption = $this->request->getGet('pricing_option') ?? $this->request->getPost('pricing_option');
        $selectedOptions = [
            'service_id' => $serviceId,
            'pricing_option' => $pricingOption,
            'extras' => $this->request->getGet('extras') ?? $this->request->getPost('extras'),
            'extra_qty' => $this->normalizeExtraQtyMap($this->request->getPost('extra_qty')),
            'order_quantity' => $this->resolveOrderQuantity(
                $pricingOption,
                $this->request->getPost('order_quantity')
            ),
        ];

        if (! session()->has('user_id')) {
            session()->set('pending_add_to_event', $selectedOptions);
            session()->set('redirect_after_login', '/event/add-to-event/' . $serviceId);

            return redirect()->to('/login')->with('error', 'Please login to add services to your event.');
        }

        if ($r = $this->requireCustomerAccount()) {
            return $r;
        }

        // Check: does user have events?
        $eventModel = new EventModel();
        $userId = session()->get('user_id');
        $events = $eventModel->where('user_id', $userId)->where('status', 'active')->findAll();

        if (empty($events)) {
            session()->set('pending_add_to_event', $selectedOptions);
            return redirect()->to('/event/create')->with('info', 'Create an event first, then we\'ll add this service to it.');
        }

        // If the inline service-view selector already chose an event, skip the selection page.
        $postedEventId = (int) $this->request->getPost('event_id');
        if ($postedEventId > 0) {
            foreach ($events as $ev) {
                if ((int) $ev['id'] === $postedEventId) {
                    session()->set('preferred_basket_event_id', $postedEventId);
                    session()->set('pending_add_to_event', $selectedOptions);
                    return redirect()->to('/event/add-to-basket/' . $serviceId . '?event_id=' . $postedEventId);
                }
            }
        }

        $preferredEventId = session()->get('preferred_basket_event_id');
        if ($preferredEventId !== null) {
            foreach ($events as $ev) {
                if ((int) $ev['id'] === (int) $preferredEventId) {
                    session()->remove('preferred_basket_event_id');
                    session()->set('pending_add_to_event', $selectedOptions);

                    return redirect()->to('/event/add-to-basket/' . $serviceId . '?event_id=' . (int) $preferredEventId);
                }
            }
            session()->remove('preferred_basket_event_id');
        }

        // Which of the user's events already contain this exact service?
        $basketModel = new EventBasketItemModel();
        $existingRows = $basketModel
            ->where('user_id', $userId)
            ->where('service_id', $serviceId)
            ->findAll();
        $eventsWithService = array_map('intval', array_column($existingRows, 'event_id'));

        // Which events already have ANY service from this vendor? (vendor-level lock)
        $vendorRows = $basketModel
            ->where('user_id', $userId)
            ->where('vendor_id', $service['vendor_id'])
            ->findAll();
        $eventsWithVendor = array_map('intval', array_column($vendorRows, 'event_id'));

        // Service thumbnail for the summary card.
        $imageModel = new ServiceImageModel();
        $serviceThumbnail = $this->primaryThumbnailPath($imageModel, (int) $serviceId);

        // User has events — show event selection
        return view('event/select_event', [
            'service'          => $service,
            'events'           => $events,
            'selectedOptions'  => $selectedOptions,
            'eventsWithService' => $eventsWithService,
            'eventsWithVendor' => $eventsWithVendor,
            'serviceThumbnail' => $serviceThumbnail,
        ]);
    }

    /**
     * Build a quote for the service/event pair and save it as a basket item.
     *
     * @param int|string $serviceId The service primary key.
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function addToBasket($serviceId)
    {
        if ($r = $this->requireCustomerAccount('/event/add-to-basket/' . $serviceId)) {
            return $r;
        }

        $eventId = $this->request->getGet('event_id') ?? $this->request->getPost('event_id');
        if (!$eventId) return redirect()->to('/browse-services')->with('error', 'Please select an event.');

        $serviceModel = new ServiceModel();
        $eventModel = new EventModel();
        $basketModel = new EventBasketItemModel();
        $eventTypeModel = new ServiceEventTypeModel();
        $packageModel = new ServiceTieredPackagesPricingModel();

        $service = $serviceModel->find($serviceId);
        $event = $eventModel->find($eventId);
        $userId = session()->get('user_id');

        if (!$service || !$event || $event['user_id'] != $userId) {
            return redirect()->to('/browse-services')->with('error', 'Invalid service or event.');
        }

        // Prevent adding the same service to the same event more than once.
        $alreadyInBasket = $basketModel
            ->where('event_id', $eventId)
            ->where('service_id', $serviceId)
            ->where('user_id', $userId)
            ->first();
        if ($alreadyInBasket) {
            session()->remove('pending_add_to_event');
            return redirect()->to('/event/basket/' . $eventId)
                ->with('info', '"' . $service['title'] . '" is already in this event\'s basket.');
        }

        // Prevent adding a second service from the same vendor to the same event.
        $vendorAlreadyBooked = $basketModel
            ->where('event_id', $eventId)
            ->where('vendor_id', $service['vendor_id'])
            ->where('user_id', $userId)
            ->first();
        if ($vendorAlreadyBooked) {
            session()->remove('pending_add_to_event');
            return redirect()->to('/event/basket/' . $eventId)
                ->with('info', 'This vendor already has a service booked for this event. Remove it first to add a different one.');
        }

        $pendingAdd = session()->get('pending_add_to_event') ?? [];
        $pricingOption = $this->request->getPost('pricing_option') ?? ($pendingAdd['pricing_option'] ?? null);
        $orderQuantity = $this->resolveOrderQuantity(
            $pricingOption,
            $this->request->getPost('order_quantity') ?? ($pendingAdd['order_quantity'] ?? null)
        );
        $extrasRaw = $this->request->getPost('extras') ?? ($pendingAdd['extras'] ?? null);
        $extraQtyMap = array_replace(
            $this->normalizeExtraQtyMap($pendingAdd['extra_qty'] ?? []),
            $this->normalizeExtraQtyMap($this->request->getPost('extra_qty'))
        );
        session()->remove('pending_add_to_event');

        if (is_string($extrasRaw)) {
            $decoded = json_decode($extrasRaw, true);
            $extrasRaw = is_array($decoded) ? $decoded : null;
        }
        $selectedExtras = [];
        if (is_array($extrasRaw)) {
            foreach ($extrasRaw as $x) {
                $selectedExtras[] = (int) $x;
            }
        } elseif ($extrasRaw !== null && $extrasRaw !== '') {
            $selectedExtras[] = (int) $extrasRaw;
        }

        $svcTypes = $eventTypeModel->where('service_id', $serviceId)->findAll();
        $typeSlugs = array_column($svcTypes, 'event_type');
        $eventSetting = $event['event_setting'] ?? 'private';

        if ($eventSetting === 'public' && !in_array('public', $typeSlugs, true)) {
            return redirect()->to('/service/view/' . $serviceId)
                ->with('error', 'This service is not offered for public / pitch events. Create a private-format event or choose another vendor.');
        }
        if ($eventSetting === 'private' && !in_array('private', $typeSlugs, true)) {
            return redirect()->to('/service/view/' . $serviceId)
                ->with('error', 'This service is not offered for private events. Switch your event to public format or choose another vendor.');
        }

        $privatePricingModel = new ServicePrivatePricingModel();
        $privatePricing = $privatePricingModel->where('service_id', $serviceId)->first();
        $privateId = $privatePricing['id'] ?? null;
        $packages = $privateId ? $packageModel->where('private_event_pricing_id', $privateId)->findAll() : [];

        if (($privatePricing['pricing_type'] ?? '') === 'guest_based_pricing') {
            $pricingOption = null;
        }

        $quoteBuilder = new EventQuoteBuilder();
        $quote = $quoteBuilder->build($service, $event, $pricingOption, $selectedExtras, $extraQtyMap, $orderQuantity);

        if (!empty($quote['errors'])) {
            return redirect()->to('/service/view/' . $serviceId)
                ->with('error', implode(' ', $quote['errors']));
        }

        $estimated = $quote['total'];
        $depositPercent = 0.15;
        $depositAmount = round($estimated * $depositPercent, 2);

        // Store a human-readable label for the chosen pricing option (e.g.
        // "Duration (1 day(s))" rather than the raw "duration_1" token). The
        // quote already produces friendly labels for every pricing type.
        $isCustomQuote = !empty($quote['custom_quote']);
        $packageLabel = $isCustomQuote
            ? 'Price on request'
            : ($this->pricingOptionLabelFromLines($quote['lines']) ?? $pricingOption);

        $breakdownPayload = json_encode([
            'lines' => $quote['lines'],
            'warnings' => $quote['warnings'],
            'distance_km' => $quote['distance_km'],
        ], JSON_UNESCAPED_UNICODE);

        $basketModel->insert([
            'event_id' => $eventId,
            'user_id' => $userId,
            'service_id' => $serviceId,
            'vendor_id' => $service['vendor_id'],
            'package_name' => $packageLabel,
            'extras' => json_encode($selectedExtras),
            'quantity' => max(1, (int) ($orderQuantity ?? 1)),
            'unit_price' => round($estimated, 2),
            'deposit_amount' => $depositAmount,
            'estimated_total' => round($estimated, 2),
            'quote_breakdown' => $breakdownPayload,
        ]);

        $flashSuccess = $isCustomQuote
            ? '"' . $service['title'] . '" added to your event. This supplier prices on request — submit your event to ask them for a quote.'
            : '"' . $service['title'] . '" added to your event basket. Estimated total: £' . number_format($estimated, 2);
        session()->setFlashdata('success', $flashSuccess);
        if (!empty($quote['warnings'])) {
            session()->setFlashdata('info', implode(' ', $quote['warnings']));
        }

        return redirect()->to('/event/basket/' . $eventId);
    }

    /**
     * Extract the human-readable label for the chosen pricing option from a
     * quote's line items (e.g. "Duration (1 day(s))", "Package: Gold").
     *
     * @param list<array<string,mixed>> $lines
     */
    private function pricingOptionLabelFromLines(array $lines): ?string
    {
        $optionCodes = ['guest_based', 'quantity_based', 'time_block', 'duration', 'package'];
        foreach ($lines as $line) {
            if (in_array($line['code'] ?? '', $optionCodes, true)) {
                $label = trim((string) ($line['label'] ?? ''));
                if ($label !== '') {
                    return $label;
                }
            }
        }

        return null;
    }

    /**
     * Resolve a friendly pricing-option label for a basket line. New items
     * store a clean label in package_name, but legacy rows may hold a raw
     * token such as "duration_1"; in that case fall back to the quote
     * breakdown lines so the user sees something meaningful.
     *
     * @param array<string,mixed> $item
     */
    private function basketOptionLabel(array $item): ?string
    {
        $raw = trim((string) ($item['package_name'] ?? ''));

        // Already a human-readable label — use as-is.
        if ($raw !== '' && !preg_match('/^(duration|timeblock|package|qty|guest)_\d+$/', $raw)) {
            return $raw;
        }

        $lines = $item['quote_detail']['lines'] ?? [];

        return is_array($lines) ? $this->pricingOptionLabelFromLines($lines) : null;
    }

    /**
     * Fetch the primary image thumbnail path for a service, falling back to
     * the first available image. Returns null when the service has no images.
     */
    private function primaryThumbnailPath(ServiceImageModel $imageModel, int $serviceId): ?string
    {
        $image = $imageModel->where('service_id', $serviceId)->where('is_primary', 1)->first();
        if (!$image) {
            $image = $imageModel->where('service_id', $serviceId)->first();
        }

        $path = $image['thumbnail_path'] ?? $image['image_path'] ?? null;

        return ($path !== null && $path !== '') ? $path : null;
    }

    /**
     * @param array<string,mixed>|null $locationRow
     * @return array<string,mixed>
     */
    private function mergeServiceLocation(array $service, ?array $locationRow): array
    {
        return (new EventQuoteBuilder())->mergeServiceLocation($service, $locationRow);
    }

    /**
     * Live quote preview (JSON) for service page AJAX.
     */
    /**
     * Return a live quote breakdown as JSON for the service-page AJAX preview panel.
     *
     * @param int|string $serviceId The service primary key.
     * @param int|string $eventId   The event primary key.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function quotePreview($serviceId, $eventId)
    {
        if (!session()->has('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Login required']);
        }

        $serviceModel = new ServiceModel();
        $eventModel = new EventModel();
        $service = $serviceModel->find((int) $serviceId);
        $event = $eventModel->find((int) $eventId);
        $userId = (int) session()->get('user_id');

        if (!$service || !$event || (int) $event['user_id'] !== $userId) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        $pricingOption = $this->request->getGet('pricing_option');
        $orderQuantity = $this->resolveOrderQuantity(
            $pricingOption,
            $this->request->getGet('order_quantity')
        );
        $extrasRaw = $this->request->getGet('extras');
        $selectedExtras = [];
        if (is_string($extrasRaw) && $extrasRaw !== '') {
            foreach (explode(',', $extrasRaw) as $x) {
                $selectedExtras[] = (int) $x;
            }
        }
        $extraQtyMap = $this->normalizeExtraQtyMap($this->request->getGet('extra_qty'));

        $quote = (new EventQuoteBuilder())->build($service, $event, $pricingOption, $selectedExtras, $extraQtyMap, $orderQuantity);
        $depositPercent = 0.15;

        return $this->response->setJSON([
            'lines' => $quote['lines'],
            'total' => $quote['total'],
            'warnings' => $quote['warnings'],
            'errors' => $quote['errors'],
            'distance_km' => $quote['distance_km'],
            'deposit' => round($quote['total'] * $depositPercent, 2),
        ]);
    }

    /**
     * @param mixed $raw
     * @return array<int, int>
     */
    private function normalizeExtraQtyMap($raw): array
    {
        if (!is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $key => $val) {
            $id = (int) $key;
            if ($id <= 0) {
                continue;
            }
            if (is_string($val)) {
                $val = trim($val);
            }
            if ($val === '' || $val === null) {
                continue;
            }
            $n = (int) $val;
            if ($n <= 0) {
                continue;
            }
            $out[$id] = $n;
        }

        return $out;
    }

    /**
     * Normalise order quantity from form input and qty_* pricing options.
     *
     * @param mixed $rawQty
     */
    private function resolveOrderQuantity(?string $pricingOption, $rawQty): ?int
    {
        $qty = null;

        if ($rawQty !== null && $rawQty !== '') {
            $parsed = (int) $rawQty;
            if ($parsed > 0) {
                $qty = $parsed;
            }
        }

        if ($qty === null && $pricingOption !== null && preg_match('/^qty_(\d+)$/', $pricingOption, $m)) {
            $qty = (int) $m[1];
        }

        return ($qty !== null && $qty > 0) ? $qty : null;
    }

    // =========================================================
    // EVENT BASKET
    // =========================================================

    /**
     * Display the event basket page showing all queued services and deposit totals.
     *
     * @param int|string $eventId The event primary key.
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
     */
    public function basket($eventId)
    {
        if ($r = $this->requireCustomerAccount('/event/basket/' . $eventId)) {
            return $r;
        }

        $userId = session()->get('user_id');
        $eventModel = new EventModel();
        $basketModel = new EventBasketItemModel();
        $serviceModel = new ServiceModel();
        $userModel = new UserModel();
        $imageModel = new ServiceImageModel();

        $event = $eventModel->find($eventId);
        if (!$event || $event['user_id'] != $userId) {
            return redirect()->to('/profile/events')->with('error', 'Event not found.');
        }

        $items = $basketModel->where('event_id', $eventId)->where('user_id', $userId)->findAll();
        $basketItems = [];
        $totalDeposit = 0;
        $totalEstimated = 0;

        foreach ($items as $item) {
            $service = $serviceModel->find($item['service_id']);
            $vendor = $userModel->find($item['vendor_id']);
            $item['service_title'] = $service ? $service['title'] : 'Unknown Service';
            $item['service_description'] = $service ? ($service['short_description'] ?? '') : '';
            $item['vendor_name'] = $vendor ? $vendor['name'] : 'Unknown Vendor';
            $item['thumbnail_path'] = $this->primaryThumbnailPath($imageModel, (int) $item['service_id']);
            $qd = json_decode($item['quote_breakdown'] ?? '', true);
            $item['quote_detail'] = is_array($qd) ? $qd : null;
            $item['option_label'] = $this->basketOptionLabel($item);
            $totalDeposit += (float)$item['deposit_amount'];
            $totalEstimated += (float)$item['estimated_total'];
            $basketItems[] = $item;
        }

        return view('event/basket', [
            'event' => $event,
            'basketItems' => $basketItems,
            'totalDeposit' => $totalDeposit,
            'totalEstimated' => $totalEstimated,
        ]);
    }

    /**
     * Remove a single item from the event basket and redirect back to it.
     *
     * @param int|string $itemId The basket item primary key.
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function removeFromBasket($itemId)
    {
        if ($r = $this->requireCustomerAccount()) {
            return $r;
        }

        $basketModel = new EventBasketItemModel();
        $item = $basketModel->find($itemId);

        if ($item && $item['user_id'] == session()->get('user_id')) {
            $basketModel->delete($itemId);
            return redirect()->to('/event/basket/' . $item['event_id'])->with('success', 'Service removed from basket.');
        }

        return redirect()->to('/profile/events')->with('error', 'Item not found.');
    }

    // =========================================================
    // CHECKOUT
    // =========================================================

    /**
     * Display the checkout page with deposit summary and, when Stripe is configured, a PaymentIntent client secret.
     *
     * @param int|string $eventId The event primary key.
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
     */
    public function checkout($eventId)
    {
        if ($r = $this->requireCustomerAccount('/event/checkout/' . $eventId)) {
            return $r;
        }

        $userId = session()->get('user_id');
        $eventModel = new EventModel();
        $basketModel = new EventBasketItemModel();
        $serviceModel = new ServiceModel();

        $event = $eventModel->find($eventId);
        if (!$event || $event['user_id'] != $userId) {
            return redirect()->to('/profile/events')->with('error', 'Event not found.');
        }

        $items = $basketModel->where('event_id', $eventId)->where('user_id', $userId)->findAll();
        if (empty($items)) {
            return redirect()->to('/event/basket/' . $eventId)->with('error', 'Your basket is empty.');
        }

        $basketItems = [];
        $totalDeposit = 0;

        foreach ($items as $item) {
            $service = $serviceModel->find($item['service_id']);
            $item['service_title'] = $service ? $service['title'] : 'Unknown';
            $totalDeposit += (float)$item['deposit_amount'];
            $basketItems[] = $item;
        }

        $stripe = new StripeCheckoutHelper();
        $clientSecret = null;
        $stripeEnabled = $stripe->isConfigured();
        if ($stripeEnabled && $totalDeposit > 0) {
            $pi = $stripe->createPaymentIntent((int) round($totalDeposit * 100), [
                'event_id' => (string) $eventId,
                'user_id' => (string) $userId,
            ]);
            if ($pi['success']) {
                $clientSecret = $pi['client_secret'];
            } else {
                $stripeEnabled = false;
            }
        }

        return view('event/checkout', [
            'event' => $event,
            'basketItems' => $basketItems,
            'totalDeposit' => $totalDeposit,
            'stripeEnabled' => $stripeEnabled,
            'stripePublishableKey' => getenv('STRIPE_PUBLISHABLE_KEY') ?: '',
            'stripeClientSecret' => $clientSecret,
        ]);
    }

    /**
     * Verify the Stripe payment, create booking records for all basket items, trigger automation and notifications, then clear the basket.
     *
     * @param int|string $eventId The event primary key.
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function processCheckout($eventId)
    {
        if ($r = $this->requireCustomerAccount('/event/checkout/' . $eventId)) {
            return $r;
        }

        $userId = session()->get('user_id');
        $eventModel = new EventModel();
        $basketModel = new EventBasketItemModel();
        $bookingModel = new BookingModel();
        $bookingItemModel = new BookingItemModel();
        $paymentsModel = new PaymentsModel();
        $chatRoomModel = new ChatRoomModel();
        $serviceModel = new ServiceModel();

        $event = $eventModel->find($eventId);
        if (!$event || $event['user_id'] != $userId) {
            return redirect()->to('/profile/events');
        }

        $items = $basketModel->where('event_id', $eventId)->where('user_id', $userId)->findAll();
        if (empty($items)) {
            return redirect()->to('/event/basket/' . $eventId)->with('error', 'Basket is empty.');
        }

        $totalEstimated = 0.0;
        foreach ($items as $item) {
            $totalEstimated += (float) $item['estimated_total'];
        }
        $totalDeposit = 0.0;
        foreach ($items as $item) {
            $totalDeposit += (float) $item['deposit_amount'];
        }

        $stripe = new StripeCheckoutHelper();
        $paymentIntentId = $this->request->getPost('payment_intent_id');
        $paymentPlan = $this->request->getPost('payment_plan') === 'instalments' ? 'instalments' : 'single';
        $balanceDue = max(0, round($totalEstimated - $totalDeposit, 2));

        if ($stripe->isConfigured()) {
            if (!$paymentIntentId) {
                return redirect()->to('/event/checkout/' . $eventId)->with('error', 'Payment was not completed.');
            }
            $verified = $stripe->verifyPaymentIntent($paymentIntentId);
            if (!$verified['success']) {
                return redirect()->to('/event/checkout/' . $eventId)->with('error', $verified['error'] ?? 'Payment verification failed.');
            }
        }

        $bookingModel->insert([
            'user_id' => $userId,
            'event_id' => $eventId,
            'status' => 'pending',
            'payment_intent_id' => $paymentIntentId ?: null,
            'balance_due' => $balanceDue,
            'payment_plan' => $paymentPlan,
        ]);
        $bookingId = $bookingModel->getInsertID();

        $automation = new VendorQuoteAutomation();
        $notifier = new QuoteNotifier();
        $analytics = new QuoteAnalyticsRecorder();

        foreach ($items as $item) {
            $qd = json_decode($item['quote_breakdown'] ?? '', true);
            $quoteDetail = is_array($qd) ? $qd : ['lines' => [], 'warnings' => []];

            $bookingItemModel->insert([
                'booking_id' => $bookingId,
                'service_id' => $item['service_id'],
                'quantity' => $item['quantity'],
                'package_name' => $item['package_name'],
                'guest_count' => $event['guest_count'] ?? null,
                'price' => $item['estimated_total'],
                'status' => 'pending',
                'quote_breakdown' => $item['quote_breakdown'],
                'quote_warnings' => json_encode($quoteDetail['warnings'] ?? [], JSON_UNESCAPED_UNICODE),
                'extras_snapshot' => $item['extras'] ?? null,
            ]);
            $bookingItemId = (int) $bookingItemModel->getInsertID();

            $svc = $serviceModel->find($item['service_id']);
            if ($svc) {
                $chatRoomModel->ensureRoom((int) $svc['vendor_id'], (int) $userId, (int) $item['service_id']);
                $analytics->recordQuoteGenerated((int) $svc['vendor_id'], (int) $item['service_id'], (float) $item['estimated_total']);

                $joinedItem = array_merge($item, [
                    'id' => $bookingItemId,
                    'event_title' => $event['title'],
                    'event_date' => $event['date'] ?? null,
                    'event_setting' => $event['event_setting'] ?? 'private',
                ]);
                $quoteForAutomation = array_merge($quoteDetail, ['total' => (float) $item['estimated_total']]);
                $autoResult = $automation->evaluateAfterCheckout(
                    $joinedItem,
                    $quoteForAutomation,
                    (int) $svc['vendor_id'],
                    (int) $item['service_id']
                );
                if ($autoResult['auto_accepted']) {
                    $analytics->recordAccepted((int) $svc['vendor_id'], (int) $item['service_id'], true);
                }

                $notifier->sendVendorNewQuoteNotification(
                    (int) $svc['vendor_id'],
                    (int) $userId,
                    (int) $item['service_id'],
                    $joinedItem,
                    $quoteDetail
                );
            }
        }

        $firstSvc = $serviceModel->find($items[0]['service_id'] ?? 0);
        $firstQd = json_decode($items[0]['quote_breakdown'] ?? '', true);
        if ($firstSvc) {
            $notifier->sendCustomerQuoteConfirmed(
                (int) $userId,
                (int) $firstSvc['vendor_id'],
                (int) $items[0]['service_id'],
                is_array($firstQd) ? $firstQd : []
            );
        }

        if ($paymentPlan === 'instalments' && $balanceDue > 0) {
            $schedule = new PaymentScheduleModel();
            $schedule->insert([
                'booking_id' => $bookingId,
                'due_date' => date('Y-m-d', strtotime('+30 days')),
                'amount' => round($balanceDue / 2, 2),
                'status' => 'pending',
            ]);
            $schedule->insert([
                'booking_id' => $bookingId,
                'due_date' => date('Y-m-d', strtotime('+60 days')),
                'amount' => round($balanceDue - round($balanceDue / 2, 2), 2),
                'status' => 'pending',
            ]);
        }

        $paymentsModel->insert([
            'booking_id' => $bookingId,
            'payment_intent_id' => $paymentIntentId ?: null,
            'payment_status' => 'succeeded',
            'amount_paid' => $totalDeposit,
            'currency' => 'gbp',
            'payment_method' => $stripe->isConfigured() ? 'stripe' : 'simulated',
            'payment_type' => 'deposit',
            'description' => 'Deposit for ' . $event['title'],
        ]);

        $basketModel->where('event_id', $eventId)->where('user_id', $userId)->delete();

        return redirect()->to('/event/checkout/success/' . $bookingId);
    }

    /**
     * Display the post-checkout confirmation page listing all booked services.
     *
     * @param int|string $bookingId The booking primary key.
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
     */
    public function checkoutSuccess($bookingId)
    {
        if ($r = $this->requireCustomerAccount()) {
            return $r;
        }

        $bookingModel = new BookingModel();
        $bookingItemModel = new BookingItemModel();
        $eventModel = new EventModel();
        $serviceModel = new ServiceModel();

        $booking = $bookingModel->find($bookingId);
        if (!$booking || $booking['user_id'] != session()->get('user_id')) {
            return redirect()->to('/profile/events');
        }

        $event = $eventModel->find($booking['event_id']);
        $items = $bookingItemModel
            ->select('booking_items.*, services.title as service_title, users.name as vendor_name')
            ->join('services', 'services.id = booking_items.service_id')
            ->join('users', 'users.id = services.vendor_id')
            ->where('booking_id', $bookingId)->findAll();

        return view('event/checkout_success', [
            'booking' => $booking,
            'event' => $event,
            'items' => $items,
        ]);
    }
}
