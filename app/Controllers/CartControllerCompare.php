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
        $depositAmount = 15.00;

        // Fetch cart items for the current user
        $cartModel = new CartModel();
        $cartItems = $cartModel->where('user_id', $userId)->findAll();

        // Fetch service details and group them by event
        $serviceModel = new ServiceModel();
        $eventModel = new EventModel();
        $events = [];
        $data['deposit'] = $depositAmount;

        foreach ($cartItems as $item) {
            // Fetch service and event details
            $service = $serviceModel->find($item['service_id']);
            $event = $eventModel->find($item['event_id']);

            // Group services under their respective events
            $events[$item['event_id']]['title'] = $event['title'];
            $events[$item['event_id']]['date'] = $event['date'];
            $events[$item['event_id']]['services'][] = [
                'id' => $service['id'],
                'title' => $service['title'],
                'price' => $service['price'],
                'start_time' => $item['start_time'],
                'end_time' => $item['end_time']
            ];
        }

        // Set the default selected event (optional)
        $selectedEventId = session()->getFlashdata('selected_event_id');

        // Pass events and cart data to the view
        return view('cart_view', [
            'events' => $events,
            'selectedEventId' => $selectedEventId,
            'depositAmount' => $depositAmount
        ]);
    }


    public function submitToVendors()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');

        // Fetch all cart items for the current user
        $cartModel = new CartModel();
        $cartItems = $cartModel->where('user_id', $userId)->findAll();

        if (empty($cartItems)) {
            return redirect()->to('/cart')->with('error', 'Your cart is empty.');
        }

        // Fetch service and event details
        $serviceModel = new ServiceModel();
        $eventModel = new EventModel();

        $events = [];
        foreach ($cartItems as $item) {
            $service = $serviceModel->find($item['service_id']);
            $event = $eventModel->find($item['event_id']);

            // Group services under their respective events
            $events[$item['event_id']]['title'] = $event['title'];
            $events[$item['event_id']]['date'] = $event['date'];
            $events[$item['event_id']]['services'][] = [
                'id' => $service['id'],
                'title' => $service['title'],
                'price' => $service['price'],
                'start_time' => $item['start_time'],
                'end_time' => $item['end_time']
            ];
        }

        // Process each event and its services
        foreach ($events as $eventId => $eventData) {
            // Your logic to create a booking and handle payments for each event
            $bookingModel = new BookingModel();
            $bookingId = $bookingModel->insert([
                'user_id' => $userId,
                'event_id' => $eventId,
                'status' => 'pending',
            ]);

            if ($bookingId) {
                $bookingItemModel = new BookingItemModel();
                foreach ($eventData['services'] as $service) {
                    $bookingItemModel->insert([
                        'booking_id' => $bookingId,
                        'service_id' => $service['id'],
                        'start_time' => $service['start_time'],
                        'end_time' => $service['end_time'],
                        'status' => 'pending',
                    ]);
                }

                // You can handle payments here for each event or as a total sum
                $totalAmount = array_sum(array_column($eventData['services'], 'price')) * 100; // Assuming Stripe amount in cents/pence
                $paymentResult = $this->createStripePaymentIntent($totalAmount);

                if (!$paymentResult['success']) {
                    return redirect()->to('/cart')->with('error', 'Error creating payment intent: ' . $paymentResult['error']);
                }

                // Insert payment details into the payments table
                $paymentsModel = new PaymentsModel();
                $paymentsModel->insert([
                    'booking_id' => $bookingId,
                    'payment_intent_id' => $paymentResult['paymentIntent']->id,
                    'payment_status' => 'pending',  // Initial status is pending
                    'amount_paid' => $totalAmount / 100,  // Amount in major currency unit
                    'currency' => 'GBP',
                    'payment_method' => 'card',
                ]);

                // Clear cart for this event
                $cartModel->where('user_id', $userId)->where('event_id', $eventId)->delete();
            }
        }

        // Redirect after processing all events
        return redirect()->to('/profile')->with('success', 'Your booking requests have been submitted!');
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
        $eventId = $this->request->getPost('event_id');
        $paymentIntentId = $this->request->getPost('payment_intent_id');

        if (empty($eventId) || empty($paymentIntentId)) {
            return redirect()->to('/cart')->with('error', 'Invalid payment or event.');
        }

        // Here you can check the status of the payment intent via Stripe API
        \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

        try {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            if ($paymentIntent->status == 'succeeded') {
                // Payment was successful, proceed with booking logic
                $cartModel = new CartModel();
                $cartItems = $cartModel
                    ->where('user_id', $userId)
                    ->where('event_id', $eventId)
                    ->findAll();

                $serviceIds = array_column($cartItems, 'service_id');
                $bookingModel = new BookingModel();
                $bookingId = $bookingModel->insert([
                    'user_id' => $userId,
                    'event_id' => $eventId,
                    'status' => 'pending',
                    'payment_intent_id' => $paymentIntentId,  // Save payment intent ID
                ]);

                if ($bookingId) {
                    $bookingItemModel = new BookingItemModel();
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
                    $vendorId = $this->getVendorIdFromServices($serviceIds);
                    $chatRoomModel = new ChatRoomModel();
                    $chatRoomModel->insert([
                        'vendor_id' => $vendorId,
                        'customer_id' => $userId,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);

                    $paymentIntentId = $paymentIntent->id;
                    $paymentStatus = $paymentIntent->status;
                    $depositAmount = 15.00;

                    // Insert payment data into the payments table with the initial status
                    $paymentsModel = new PaymentsModel();
                    $paymentData = [
                        'booking_id' => $bookingId,
                        'payment_intent_id' => $paymentIntentId,
                        'payment_status' => $paymentStatus, // Store the initial status from Stripe
                        'amount_paid' => $depositAmount,
                        'currency' => 'GBP',
                        'payment_method' => 'card',
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                    $paymentsModel->insert($paymentData);

                    // Clear the cart
                    $cartModel->where('user_id', $userId)->where('event_id', $eventId)->delete();
                    $this->updateCartCount();

                    return redirect()->to('/profile')->with('success', 'Your booking request has been submitted and payment was successful!');
                }
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