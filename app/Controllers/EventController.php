<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\EventBasketItemModel;
use App\Models\ServiceModel;
use App\Models\UserModel;
use App\Models\BookingModel;
use App\Models\BookingItemModel;
use App\Models\PaymentsModel;

class EventController extends BaseController
{
    // =========================================================
    // MULTI-STEP EVENT CREATION
    // =========================================================

    public function create()
    {
        if (!session()->has('user_id')) {
            session()->set('redirect_after_login', '/event/create');
            return redirect()->to('/login')->with('error', 'Please login to create an event.');
        }

        return redirect()->to('/event/create/step1');
    }

    public function createStep1()
    {
        if (!session()->has('user_id')) {
            session()->set('redirect_after_login', '/event/create');
            return redirect()->to('/login');
        }

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'title' => 'required|min_length[3]|max_length[255]',
                'event_type' => 'required',
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
        if (!session()->has('user_id')) return redirect()->to('/login');
        if (!session()->has('event_step1')) return redirect()->to('/event/create/step1');

        if ($this->request->getMethod() === 'POST') {
            session()->set('event_step2', [
                'venue_name' => $this->request->getPost('venue_name'),
                'postcode' => $this->request->getPost('postcode'),
                'town_city' => $this->request->getPost('town_city'),
                'indoor_outdoor' => $this->request->getPost('indoor_outdoor'),
            ]);

            return redirect()->to('/event/create/step3');
        }

        $data = session()->get('event_step2') ?? [];
        return view('event/create_step2', ['old' => $data]);
    }

    public function createStep3()
    {
        if (!session()->has('user_id')) return redirect()->to('/login');
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
        if (!session()->has('user_id')) return redirect()->to('/login');
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
        if (!session()->has('user_id')) return redirect()->to('/login');
        if (!session()->has('event_step1')) return redirect()->to('/event/create/step1');

        $step1 = session()->get('event_step1');
        $step2 = session()->get('event_step2') ?? [];
        $step3 = session()->get('event_step3') ?? [];

        $eventModel = new EventModel();

        $location = trim(($step2['town_city'] ?? '') . (!empty($step2['postcode']) ? ', ' . $step2['postcode'] : ''));

        $eventData = [
            'user_id' => session()->get('user_id'),
            'title' => $step1['title'],
            'event_type' => $step1['event_type'],
            'date' => $step1['date'],
            'guest_count' => $step1['guest_count'],
            'category' => $step1['event_type'],
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
        if (!$service) return redirect()->to('/browse-services')->with('error', 'Service not found.');

        // Capture selected options from the service view form
        $selectedOptions = [
            'service_id' => $serviceId,
            'pricing_option' => $this->request->getGet('pricing_option') ?? $this->request->getPost('pricing_option'),
            'extras' => $this->request->getGet('extras') ?? $this->request->getPost('extras'),
        ];

        // Check: is user logged in?
        if (!session()->has('user_id')) {
            session()->set('pending_add_to_event', $selectedOptions);
            session()->set('redirect_after_login', '/event/add-to-event/' . $serviceId);
            return redirect()->to('/login')->with('error', 'Please login to add services to your event.');
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
        if (!session()->has('user_id')) return redirect()->to('/login');

        $eventId = $this->request->getGet('event_id') ?? $this->request->getPost('event_id');
        if (!$eventId) return redirect()->to('/browse-services')->with('error', 'Please select an event.');

        $serviceModel = new ServiceModel();
        $eventModel = new EventModel();
        $basketModel = new EventBasketItemModel();

        $service = $serviceModel->find($serviceId);
        $event = $eventModel->find($eventId);
        $userId = session()->get('user_id');

        if (!$service || !$event || $event['user_id'] != $userId) {
            return redirect()->to('/browse-services')->with('error', 'Invalid service or event.');
        }

        // Get options from session or request
        $pendingAdd = session()->get('pending_add_to_event');
        $pricingOption = $this->request->getPost('pricing_option') ?? ($pendingAdd['pricing_option'] ?? null);
        $extras = $this->request->getPost('extras') ?? ($pendingAdd['extras'] ?? null);
        session()->remove('pending_add_to_event');

        $depositPercent = 0.15;
        $servicePrice = (float)$service['price'];
        $depositAmount = round($servicePrice * $depositPercent, 2);

        $basketModel->insert([
            'event_id' => $eventId,
            'user_id' => $userId,
            'service_id' => $serviceId,
            'vendor_id' => $service['vendor_id'],
            'package_name' => $pricingOption,
            'extras' => is_array($extras) ? json_encode($extras) : $extras,
            'quantity' => 1,
            'unit_price' => $servicePrice,
            'deposit_amount' => $depositAmount,
            'estimated_total' => $servicePrice,
        ]);

        return redirect()->to('/event/basket/' . $eventId)->with('success', '"' . $service['title'] . '" added to your event basket!');
    }

    // =========================================================
    // EVENT BASKET
    // =========================================================

    public function basket($eventId)
    {
        if (!session()->has('user_id')) return redirect()->to('/login');

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
        if (!session()->has('user_id')) return redirect()->to('/login');

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
        if (!session()->has('user_id')) return redirect()->to('/login');

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
        if (!session()->has('user_id')) return redirect()->to('/login');

        $userId = session()->get('user_id');
        $eventModel = new EventModel();
        $basketModel = new EventBasketItemModel();
        $bookingModel = new BookingModel();
        $bookingItemModel = new BookingItemModel();
        $paymentsModel = new PaymentsModel();

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
        if (!session()->has('user_id')) return redirect()->to('/login');

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
