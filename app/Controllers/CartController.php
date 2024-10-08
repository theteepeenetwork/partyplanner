<?php

namespace App\Controllers;

use App\Models\CartModel;
use App\Models\ServiceModel;
use App\Models\BookingModel;
use App\Models\BookingItemModel;
use App\Models\EventModel;
use App\Models\ChatRoomModel;
use App\Models\PaymentsModel;
use DateTime;
use Stripe\Stripe;
use Stripe\PaymentIntent;



class CartController extends BaseController
{
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');

        // Fetch cart items for the user
        $cartModel = new CartModel();
        $cartItems = $cartModel->where('user_id', $userId)->findAll();

        if (empty($cartItems)) {
            return redirect()->to('/cart')->with('error', 'Your cart is empty.');
        }

        // Fetch service details for each cart item and organize them by event
        $serviceModel = new ServiceModel();
        $eventModel = new EventModel();
        $data['events'] = [];

        foreach ($cartItems as $item) {
            $service = $serviceModel->find($item['service_id']);
            $event = $eventModel->find($item['event_id']);

            // Ensure the event and service exist
            if (!$event || !$service) {
                continue; // Skip if no valid event or service found
            }

            // Organize the services under their respective events
            if (!isset($data['events'][$event['id']])) {
                $data['events'][$event['id']] = [
                    'title' => $event['title'],
                    'date' => $event['date'],
                    'services' => []
                ];
            }

            $data['events'][$event['id']]['services'][] = [
                'id' => $item['id'],
                'title' => $service['title'],
                'price' => $service['price'],
                'start_time' => $item['start_time'],
                'end_time' => $item['end_time']
            ];
        }



        // Load the cart view with the organized events and their respective services
        return view('cart_view', $data);
    }



    public function submitToVendors()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $cartModel = new CartModel();

        // Get all cart items grouped by event
        $events = $this->getCartItemsGroupedByEvent($userId);

        if (empty($events)) {
            return redirect()->to('/cart')->with('error', 'Your cart is empty.');
        }

        $totalDeposit = 0;
        foreach ($events as $event_id => $event) {
            $eventTotal = 0;
            foreach ($event['services'] as $service) {
                $eventTotal += $service['price'];
            }
            $eventDeposit = $eventTotal * 0.10; // 10% deposit per event
            $totalDeposit += $eventDeposit;
        }

        // Create Stripe PaymentIntent for the total deposit
        $paymentResult = $this->createStripePaymentIntent($totalDeposit * 100); // Amount in pence
        if (!$paymentResult['success']) {
            return redirect()->to('/cart')->with('error', 'Error creating payment intent: ' . $paymentResult['error']);
        }

        // Pass data to the cart_submit view
        return view('cart_submit', [
            'events' => $events,
            'totalDeposit' => $totalDeposit,
            'client_secret' => $paymentResult['client_secret'],
        ]);
    }

    private function getCartItemsGroupedByEvent($userId)
    {
        $cartModel = new CartModel();
        $serviceModel = new ServiceModel();
        $eventModel = new EventModel();

        $cartItems = $cartModel->where('user_id', $userId)->findAll();
        $events = [];

        foreach ($cartItems as $item) {
            $service = $serviceModel->find($item['service_id']);
            $event = $eventModel->find($item['event_id']);

            if (!isset($events[$item['event_id']])) {
                $events[$item['event_id']] = [
                    'title' => $event['title'],
                    'date' => $event['date'],
                    'services' => []
                ];
            }

            $events[$item['event_id']]['services'][] = [
                'id' => $service['id'],
                'title' => $service['title'],
                'price' => $service['price'],
                'start_time' => $item['start_time'],
                'end_time' => $item['end_time']
            ];
        }

        return $events;
    }

    public function add($serviceId)
    {
        if (!session()->has('user_id')) {
            session()->setFlashdata('redirect_after_login', current_url());
            return redirect()->to('/login')->with('error', 'You must be logged in to add services.');
        }

        $userId = session()->get('user_id');
        $startTime = $this->request->getPost('start_time');
        $duration = $this->request->getPost('duration');
        $eventId = $this->request->getPost('event_id');

        if (!$startTime || !$duration || !$eventId) {
            return redirect()->back()->with('error', 'Please select a valid start time, duration, and event.');
        }

        // Calculate the end time based on start time and duration
        $startTimeObj = new DateTime($startTime);
        $durationObj = new \DateInterval('PT' . (int) $duration . 'H');
        $endTimeObj = clone $startTimeObj;
        $endTimeObj->add($durationObj);
        $endTime = $endTimeObj->format('H:i:s');

        // Check if the service exists
        $serviceModel = new ServiceModel();
        $service = $serviceModel->find($serviceId);

        if (!$service) {
            return redirect()->back()->with('error', 'Service not found.');
        }

        $cartModel = new CartModel();
        $existingItem = $cartModel->where('user_id', $userId)
            ->where('service_id', $serviceId)
            ->where('event_id', $eventId)
            ->first();

        if (!$existingItem) {
            // Add new item to the cart
            $cartModel->save([
                'user_id' => $userId,
                'service_id' => $serviceId,
                'event_id' => $eventId,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]);
        }

        // Update cart item count in session
        //$this->updateCartCount();

        return redirect()->back()->with('success', 'Service added to cart!');
    }

    public function submit()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $eventId = $this->request->getPost('event_id');

        if (empty($eventId)) {
            return redirect()->to('/cart')->with('error', 'Please select an event.');
        }

        $cartModel = new CartModel();
        $cartItems = $cartModel
            ->select('carts.id, carts.service_id, carts.start_time, carts.end_time')  // Added start_time and end_time
            ->where('user_id', $userId)
            ->where('event_id', $eventId)
            ->findAll();

        if (empty($cartItems)) {
            return redirect()->to('/cart')->with('error', 'Your cart is empty for this event.');
        }

        //all stripe functionality
        $totalAmount = $this->calculateCartTotal($cartItems);  // Calculate the total in cents/pence
        $paymentResult = $this->processStripePayment($totalAmount);

        if (!$paymentResult['success']) {
            return redirect()->to('/cart')->with('error', 'Payment failed: ' . $paymentResult['error']);
        }

        //other

        $serviceIds = array_column($cartItems, 'service_id');

        $bookingModel = new BookingModel();
        $bookingId = $bookingModel->insert([
            'user_id' => $userId,
            'event_id' => $eventId,
            'status' => 'pending',
            'payment_intent_id' => $paymentResult['payment_intent_id'],
        ]);

        if ($bookingId) {
            $bookingItemModel = new BookingItemModel();
            foreach ($cartItems as $item) {
                $bookingItemModel->insert([
                    'booking_id' => $bookingId,
                    'service_id' => $item['service_id'],
                    'start_time' => $item['start_time'],  // Save start time from cart
                    'end_time' => $item['end_time'],      // Save end time from cart
                    'status' => 'pending',
                ]);
            }

            // Assume getVendorIdFromServices is a helper function to get the vendor ID
            $vendorId = $this->getVendorIdFromServices($serviceIds);

            $chatRoomModel = new ChatRoomModel();
            $chatRoomModel->insert([
                'vendor_id' => $vendorId,
                'customer_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $cartModel->where('user_id', $userId)->where('event_id', $eventId)->delete();

            $this->updateCartCount();

            return redirect()->to('/profile')->with('success', 'Your booking request has been submitted!');
        } else {
            return redirect()->to('/cart')->with('error', 'Failed to create a booking.');
        }
    }

    //*******Stripe Functions ****************
    private function processStripePayment($amount)
    {
        \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));  // Set your secret key

        try {
            // Create a Payment Intent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,  // The amount in cents/pence
                'currency' => 'usd',  // Set currency
                'payment_method_types' => ['card'],
            ]);

            // Return success and payment intent details
            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,  // Send this to the client side
            ];

        } catch (\Exception $e) {
            // Return error details in case of failure
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function calculateCartTotal($cartItems)
    {
        $total = 15;
        foreach ($cartItems as $item) {
            $total += $item[''];  // Assume price is in dollars or major currency unit
        }

        return $total * 100;  // Convert to cents or pence (Stripe requires amounts in the smallest currency unit)
    }

    private function createStripePaymentIntent($amount)
    {
        \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));  // Set your Stripe secret key

        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount, // Amount should be in cents/pence
                'currency' => 'GBP',  // Or any other currency
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'paymentIntent' => $paymentIntent,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function processPayment()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $eventIds = $this->request->getPost('event_ids'); // Retrieve multiple event IDs
        $paymentIntentId = $this->request->getPost('payment_intent_id');

        if (empty($eventIds) || empty($paymentIntentId)) {
            return redirect()->to('/cart')->with('error', 'Invalid payment or events.');
        }

        // Set Stripe API key
        \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

        try {
            // Retrieve the PaymentIntent from Stripe
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            if ($paymentIntent->status == 'succeeded') {
                // Payment succeeded, process each event separately
                $cartModel = new CartModel();
                $serviceModel = new ServiceModel();
                $bookingModel = new BookingModel();
                $bookingItemModel = new BookingItemModel();
                $paymentsModel = new PaymentsModel();

                $totalDeposit = 0;

                foreach ($eventIds as $eventId) {
                    // Fetch the cart items for each event
                    $cartItems = $cartModel
                        ->where('user_id', $userId)
                        ->where('event_id', $eventId)
                        ->findAll();

                    if (empty($cartItems)) {
                        continue; // Skip if no items are found for the event
                    }

                    $eventTotal = 0;

                    // Calculate the total amount for this event
                    foreach ($cartItems as $item) {
                        $service = $serviceModel->find($item['service_id']);
                        $eventTotal += $service['price'];
                    }

                    // Calculate the 10% deposit for this event
                    $eventDeposit = $eventTotal * 0.10;
                    $totalDeposit += $eventDeposit;

                    // Insert booking for the event
                    $bookingId = $bookingModel->insert([
                        'user_id' => $userId,
                        'event_id' => $eventId,
                        'status' => 'pending',
                        'payment_intent_id' => $paymentIntentId, // Save payment intent ID
                    ]);

                    if ($bookingId) {
                        // Insert booking items for each service in the event
                        foreach ($cartItems as $item) {
                            $bookingItemModel->insert([
                                'booking_id' => $bookingId,
                                'service_id' => $item['service_id'],
                                'start_time' => $item['start_time'],
                                'end_time' => $item['end_time'],
                                'status' => 'pending',
                            ]);
                        }

                        // Notify vendors and create chat room
                        $serviceIds = array_column($cartItems, 'service_id');
                        $vendorId = $this->getVendorIdFromServices($serviceIds);
                        $chatRoomModel = new ChatRoomModel();
                        $chatRoomModel->insert([
                            'vendor_id' => $vendorId,
                            'customer_id' => $userId,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);

                        // Insert payment data into the payments table
                        $paymentData = [
                            'booking_id' => $bookingId,
                            'payment_intent_id' => $paymentIntentId,
                            'payment_status' => $paymentIntent->status,
                            'amount_paid' => $eventDeposit,
                            'currency' => 'GBP',
                            'payment_method' => 'card',
                            'created_at' => date('Y-m-d H:i:s'),
                        ];
                        $paymentsModel->insert($paymentData);

                        // Clear the cart for this event
                        $cartModel->where('user_id', $userId)->where('event_id', $eventId)->delete();
                    }
                }

                // Update the cart count
                $this->updateCartCount();

                return redirect()->to('/profile')->with('success', 'Your booking request has been submitted and payment was successful for all events!');
            } else {
                return redirect()->to('/cart')->with('error', 'Payment was not successful.');
            }
        } catch (\Exception $e) {
            return redirect()->to('/cart')->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }


    //*******End Stripe Functions ****************



    private function getVendorIdFromServices($serviceIds)
    {
        $serviceModel = new \App\Models\ServiceModel();
        $service = $serviceModel->find($serviceIds[0]);
        return $service['vendor_id'];
    }


    private function updateCartCount()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return; // User not logged in
        }

        $cartModel = new CartModel();
        $cartCount = $cartModel->where('user_id', $userId)->countAllResults();
        session()->set('cart_count', $cartCount);
    }

    private function calculateTotal($userId)
    {
        $cartModel = new CartModel();
        $totalQuery = $cartModel->select('services.price as total')
            ->join('services', 'services.id = carts.service_id')
            ->where('user_id', $userId)
            ->get();

        $result = $totalQuery->getRow();
        return $result ? $result->total : 0; // Return total or 0 if null
    }

    public function remove($cartItemId)
    {
        // Ensure user is logged in
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $cartModel = new CartModel();
        $cartItem = $cartModel->find($cartItemId);

        // Ensure cart item exists and belongs to the current user
        if (!$cartItem || $cartItem['user_id'] != session()->get('user_id')) {
            return redirect()->to('/cart')->with('error', 'Cart item not found or not authorized.');
        }

        // Delete the cart item
        $cartModel->delete($cartItemId);

        // Update the cart count in the session
        $this->updateCartCount();

        return redirect()->to('/cart')->with('success', 'Item removed from cart.');
    }
}