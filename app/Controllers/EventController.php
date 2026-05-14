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
use App\Models\ServiceTieredPackagesPricingModel;
use App\Models\ServiceLocationModel;
use App\Models\ServiceOptionalExtrasModel;
use App\Libraries\EventBookingQuote;
use App\Libraries\UKAddressGeocoder;

class EventController extends BaseController
{
    // =========================================================
    // MULTI-STEP EVENT CREATION
    // =========================================================

    public function create()
    {
        if ($r = $this->requireCustomerAccount('/event/create')) {
            return $r;
        }

        return redirect()->to('/event/create/step1');
    }

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

    public function addToEvent($serviceId)
    {
        $serviceModel = new ServiceModel();
        $service = $serviceModel->find($serviceId);
        if (!$service) {
            return redirect()->to('/browse-services')->with('error', 'Service not found.');
        }

        // Capture selected options from the service view form
        $selectedOptions = [
            'service_id' => $serviceId,
            'pricing_option' => $this->request->getGet('pricing_option') ?? $this->request->getPost('pricing_option'),
            'extras' => $this->request->getGet('extras') ?? $this->request->getPost('extras'),
            'extra_qty' => $this->normalizeExtraQtyMap($this->request->getPost('extra_qty')),
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

        // User has events — show event selection
        return view('event/select_event', [
            'service' => $service,
            'events' => $events,
            'selectedOptions' => $selectedOptions,
        ]);
    }

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
        $publicPricingModel = new ServicePublicEventPricingModel();
        $privatePricingModel = new ServicePrivatePricingModel();
        $guestModel = new ServiceGuestBasedPricingModel();
        $durationModel = new ServiceCustomDurationPricingModel();
        $packageModel = new ServiceTieredPackagesPricingModel();
        $locationModel = new ServiceLocationModel();
        $extrasModel = new ServiceOptionalExtrasModel();

        $service = $serviceModel->find($serviceId);
        $event = $eventModel->find($eventId);
        $userId = session()->get('user_id');

        if (!$service || !$event || $event['user_id'] != $userId) {
            return redirect()->to('/browse-services')->with('error', 'Invalid service or event.');
        }

        $pendingAdd = session()->get('pending_add_to_event') ?? [];
        $pricingOption = $this->request->getPost('pricing_option') ?? ($pendingAdd['pricing_option'] ?? null);
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

        $locationRow = $locationModel->where('service_id', $serviceId)->first();
        $locMerged = $this->mergeServiceLocation($service, $locationRow);

        $publicBands = $publicPricingModel->where('service_id', $serviceId)->orderBy('min_attendance', 'ASC')->findAll();
        $privatePricing = $privatePricingModel->where('service_id', $serviceId)->first();
        $privateId = $privatePricing['id'] ?? null;
        $guestTiers = $privateId ? $guestModel->where('private_event_pricing_id', $privateId)->findAll() : [];
        $durationTiers = $privateId ? $durationModel->where('private_event_pricing_id', $privateId)->findAll() : [];
        $packages = $privateId ? $packageModel->where('private_event_pricing_id', $privateId)->findAll() : [];

        $extraRows = $extrasModel->where('service_id', $serviceId)->findAll();
        $extrasById = [];
        foreach ($extraRows as $er) {
            $ptype = strtolower(trim((string) ($er['pricing_type'] ?? 'flat')));
            $extrasById[(int) $er['id']] = [
                'price' => (float) ($er['price'] ?? 0),
                'name' => (string) ($er['name'] ?? 'Extra'),
                'pricing_type' => $ptype === 'per_item' ? 'per_item' : 'flat',
                'min_quantity' => isset($er['min_quantity']) && $er['min_quantity'] !== '' && $er['min_quantity'] !== null
                    ? (int) $er['min_quantity'] : null,
                'max_quantity' => isset($er['max_quantity']) && $er['max_quantity'] !== '' && $er['max_quantity'] !== null
                    ? (int) $er['max_quantity'] : null,
                'unit_label' => isset($er['unit_label']) && $er['unit_label'] !== '' ? (string) $er['unit_label'] : null,
            ];
        }

        if (($privatePricing['pricing_type'] ?? '') === 'guest_based_pricing') {
            $pricingOption = null;
        }

        $quoteCalc = new EventBookingQuote();
        $quote = $quoteCalc->calculate(
            $service,
            $event,
            $locMerged,
            $publicBands,
            $privatePricing,
            $guestTiers,
            $durationTiers,
            $packages,
            $extrasById,
            $selectedExtras,
            $pricingOption,
            $extraQtyMap
        );

        if (!empty($quote['errors'])) {
            return redirect()->to('/service/view/' . $serviceId)
                ->with('error', implode(' ', $quote['errors']));
        }

        $estimated = $quote['total'];
        $depositPercent = 0.15;
        $depositAmount = round($estimated * $depositPercent, 2);

        $packageLabel = $pricingOption;
        if ($privatePricing && !empty($privatePricing['pricing_type']) && $privatePricing['pricing_type'] === 'tiered_packages_pricing'
            && is_string($pricingOption) && preg_match('/^package_(\d+)$/', $pricingOption, $m)) {
            foreach ($packages as $p) {
                if ((int) $p['id'] === (int) $m[1]) {
                    $packageLabel = $p['package_name'] ?? $pricingOption;
                    break;
                }
            }
        }

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
            'quantity' => 1,
            'unit_price' => round($estimated, 2),
            'deposit_amount' => $depositAmount,
            'estimated_total' => round($estimated, 2),
            'quote_breakdown' => $breakdownPayload,
        ]);

        $flashSuccess = '"' . $service['title'] . '" added to your event basket. Estimated total: £' . number_format($estimated, 2);
        session()->setFlashdata('success', $flashSuccess);
        if (!empty($quote['warnings'])) {
            session()->setFlashdata('info', implode(' ', $quote['warnings']));
        }

        return redirect()->to('/event/basket/' . $eventId);
    }

    /**
     * @param array<string,mixed>|null $locationRow
     * @return array<string,mixed>
     */
    private function mergeServiceLocation(array $service, ?array $locationRow): array
    {
        $base = [
            'latitude' => null,
            'longitude' => null,
            'all_travel_included' => 0,
            'no_travel_limit' => 0,
            'free_coverage_radius' => null,
            'paid_coverage_radius' => null,
            'travel_fee_per_km' => null,
        ];
        $row = $locationRow ?? [];
        $out = array_merge($base, $row);
        $keys = ['latitude', 'longitude', 'all_travel_included', 'no_travel_limit', 'free_coverage_radius', 'paid_coverage_radius', 'travel_fee_per_km'];
        foreach ($keys as $k) {
            if (!isset($out[$k]) || $out[$k] === null || $out[$k] === '') {
                if (array_key_exists($k, $service) && $service[$k] !== null && $service[$k] !== '') {
                    $out[$k] = $service[$k];
                }
            }
        }

        return $out;
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

    // =========================================================
    // EVENT BASKET
    // =========================================================

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
            $qd = json_decode($item['quote_breakdown'] ?? '', true);
            $item['quote_detail'] = is_array($qd) ? $qd : null;
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

        return view('event/checkout', [
            'event' => $event,
            'basketItems' => $basketItems,
            'totalDeposit' => $totalDeposit,
        ]);
    }

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

        // Create a booking for this event
        $bookingModel->insert([
            'user_id' => $userId,
            'event_id' => $eventId,
            'status' => 'pending',
        ]);
        $bookingId = $bookingModel->getInsertID();

        $totalDeposit = 0;

        foreach ($items as $item) {
            // Create booking items
            $bookingItemModel->insert([
                'booking_id' => $bookingId,
                'service_id' => $item['service_id'],
                'quantity' => $item['quantity'],
                'package_name' => $item['package_name'],
                'price' => $item['estimated_total'],
                'status' => 'pending',
            ]);

            $svc = $serviceModel->find($item['service_id']);
            if ($svc) {
                $chatRoomModel->ensureRoom((int) $svc['vendor_id'], (int) $userId, (int) $item['service_id']);
            }

            $totalDeposit += (float)$item['deposit_amount'];
        }

        // Record deposit payment (simulated — no real gateway yet)
        $paymentsModel->insert([
            'booking_id' => $bookingId,
            'payment_status' => 'succeeded',
            'amount_paid' => $totalDeposit,
            'currency' => 'gbp',
            'payment_method' => 'card',
            'payment_type' => 'deposit',
            'description' => 'Deposit for ' . $event['title'],
        ]);

        // Clear basket items after successful checkout
        $basketModel->where('event_id', $eventId)->where('user_id', $userId)->delete();

        return redirect()->to('/event/checkout/success/' . $bookingId);
    }

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
