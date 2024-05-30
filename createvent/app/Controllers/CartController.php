<?php namespace App\Controllers;

use App\Models\CartModel;
use App\Models\ServiceModel;
use App\Models\BookingModel;
use App\Models\BookingItemModel;
use App\Models\EventModel;


class CartController extends BaseController
{
   public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        
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
    
        // Check if the service exists and get its details
        $serviceModel = new ServiceModel();
        $service = $serviceModel->find($serviceId);
    
        if (!$service) {
            return redirect()->back()->with('error', 'Service not found.');
        }
    
        // Check if the user has at least one event (updated query)
        $eventModel = new EventModel();
        $hasEvent = $eventModel->where('user_id', $userId)->countAllResults() > 0;
    
        // Redirect if no event exists
        if (!$hasEvent) {
            // Store the current URL for redirection after creating an event
            session()->setFlashdata('redirect_after_event_create', current_url());
            return redirect()->to(site_url('event/create'))->with('error', 'You must create an event before adding services.');
        }
    
        // Get the event_id, prioritizing the value from the POST request
        $event_id = $this->request->getPost('event_id');
        if (empty($event_id)) {
            $event = $eventModel->where('user_id', $userId)->first(); // Use the first event found
            $event_id = $event['id']; // Use the first event found
        }

        $cartModel = new CartModel();
        $existingItem = $cartModel->where('user_id', $userId)->where('service_id', $serviceId)->where('event_id', $event_id)->first();

        if (!$existingItem) {
            // Add new item to the cart
            $cartModel->save([
                'user_id' => $userId,
                'service_id' => $serviceId,
                'event_id' => $event_id,
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

        // Check if the cart has items for the selected event
        $cartModel = new CartModel();
        $cartItems = $cartModel->where('user_id', $userId)->where('event_id', $eventId)->findAll();
        if (empty($cartItems)) {
            return redirect()->to('/cart')->with('error', 'Your cart is empty for this event.');
        }

        // Create a new booking
        $bookingModel = new BookingModel();
        $bookingId = $bookingModel->insert([
            'user_id' => $userId,
            'event_id' => $eventId,
            'status' => 'pending', // You can set the default status here
        ]);

        if ($bookingId) {
            $bookingItemModel = new BookingItemModel();
            foreach ($cartItems as $item) {
                $bookingItemModel->insert([
                    'booking_id' => $bookingId,
                    'service_id' => $item['service_id'],
                ]);
            }

            // Clear the cart for the specific event
            $cartModel->where('user_id', $userId)->where('event_id', $eventId)->delete();

            // Update cart item count in session
            $this->updateCartCount();

            return redirect()->to('/profile')->with('success', 'Your booking request has been submitted!');
        } else {
            return redirect()->to('/cart')->with('error', 'Failed to create a booking.');
        }
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
