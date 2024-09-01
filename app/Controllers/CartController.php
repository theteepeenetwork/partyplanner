<?php

namespace App\Controllers;

use App\Models\CartModel;
use App\Models\ServiceModel;
use App\Models\BookingModel;
use App\Models\BookingItemModel;
use App\Models\EventModel;
use App\Models\ChatRoomModel;
use DateTime;


class CartController extends BaseController
{
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id'); // hello I'm adding some code

        $cartModel = new CartModel();
        $data['cartItems'] = $cartModel->where('user_id', $userId)->findAll();

        // Fetch service details for each cart item
        $serviceModel = new ServiceModel();
        foreach ($data['cartItems'] as &$item) {
            $item['service'] = $serviceModel->find($item['service_id']);
        }

        // Update the total in the session
        $this->updateCartCount();
        $data['total'] = $this->calculateTotal($userId);

        $eventModel = new EventModel();
        $data['events'] = $eventModel->where('user_id', $userId)->findAll();

        // Set a default selected event (optional, but recommended for better UX)
        $data['event_id'] = session()->getFlashdata('selected_event_id');

        // Load the cart view
        return view('cart_view', $data);
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
    $durationObj = new \DateInterval('PT' . (int)$duration . 'H');
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
    $this->updateCartCount();

    return redirect()->back()->with('success', 'Service added to cart!');
}




    // ... other methods

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

    $serviceIds = array_column($cartItems, 'service_id');

    $bookingModel = new BookingModel();
    $bookingId = $bookingModel->insert([
        'user_id' => $userId,
        'event_id' => $eventId,
        'status' => 'pending',
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
