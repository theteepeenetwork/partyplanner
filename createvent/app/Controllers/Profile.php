<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ServiceModel;
use App\Models\BookingModel;
use App\Models\BookingItemModel;
use App\Models\EventModel;

class Profile extends BaseController
{
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login')->with('error', 'You must be logged in to view your profile.');
        }

        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);
        $serviceModel = new ServiceModel();

        // Check if the user exists
        if (!$user) {
            return redirect()->to('/')->with('error', 'User not found.');
        }

        $data['user'] = $user;

        // Pass additional data to view based on user role
        if ($user['role'] == 'vendor') {
            // Fetch the vendor's services
            $serviceModel = new ServiceModel();
            $services = $serviceModel->where('vendor_id', $userId)->findAll();

            // Fetch booking items for the vendor's services
            $bookingItemModel = new BookingItemModel();
            $bookingItems = $bookingItemModel
                ->select('
            booking_items.id as booking_item_id,
            booking_items.status as booking_item_status,
            bookings.*, 
            events.title as event_title, 
            events.date as event_date, 
            events.ceremony_type, 
            events.location, 
            services.title as service_title, 
            services.price
        ')
                ->join('bookings', 'bookings.id = booking_items.booking_id')
                ->join('events', 'events.id = bookings.event_id')
                ->join('services', 'services.id = booking_items.service_id')
                ->where('services.vendor_id', $userId)
                ->findAll();

            $data['status'] = $bookingItemModel
                ->select('status')
                ->where('booking_id', $userId)
                ->findAll();

            $data['services'] = $services;
            $data['bookingItems'] = $bookingItems;
            return view('profile_vendor', $data);
        } else {
            $eventModel = new EventModel();
            $data['events'] = $eventModel->where('user_id', $userId)->findAll();
            $bookingItemModel = new BookingItemModel();
            $data['bookingItems'] = $bookingItemModel
                ->select('booking_items.*, bookings.user_id') // Get the user_id from the bookings table
                ->join('bookings', 'bookings.id = booking_items.booking_id')
                ->where('bookings.user_id', $userId) // Filter on bookings.user_id
                ->findAll();

            return view('profile_customer', $data);
        }
    }




    private function vendorProfile($userId, $data)
    {
        $serviceModel = new ServiceModel();
        $data['services'] = $serviceModel->where('vendor_id', $userId)->findAll();
        return view('profile_vendor', $data); // Use separate vendor profile view
    }

    public function event($eventId)
    {
        // ... login check and user fetching logic ...

        // Check if the event exists and belongs to the user
        $eventModel = new BookingModel();
        $event = $eventModel->find($eventId);

        if (!$event || $event['user_id'] != $userId) {
            return redirect()->to('/profile')->with('error', 'Event not found.');
        }

        // Fetch the services booked for this event
        $bookingItemModel = new BookingItemModel();
        $serviceModel = new ServiceModel();

        $data['bookingItems'] = $bookingItemModel
            ->select('booking_items.*, services.title, services.price, services.image')
            ->join('services', 'services.id = booking_items.service_id')
            ->where('booking_id', $eventId) // Filter based on the event ID
            ->findAll();


        $data['event'] = $event;

        return view('event_view', $data); // Use separate customer profile view
    }

    public function edit()
    {
        // Ensure the user is logged in
        if (!session()->has('user_id')) {
            return redirect()->to('/login'); // Redirect to login if not logged in
        }

        $userModel = new UserModel();  // Create the model instance HERE
        $userId = session()->get('user_id');

        // Retrieve user data from the database
        $user = $userModel->find($userId);

        if (!$user) {
            // Handle the case where the user is not found (e.g., display an error message or redirect)
            return redirect()->to('/')->with('error', 'User not found.');
        }

        // ... (login check and user fetching logic) ...

        if ($this->request->is('POST')) {
            $rules = [
                'name' => 'required|min_length[3]|max_length[255]',
                'username' => 'required|min_length[3]|max_length[255]|is_unique[users.username,id,' . $user['id'] . ']', // Check for uniqueness, excluding the current user's username
                'email' => 'required|valid_email|is_unique[users.email,id,' . $user['id'] . ']'  // Check for uniqueness, excluding the current user's email
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $data = [
                'name' => $this->request->getPost('name'),
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
            ];

            if ($userModel->update($userId, $data)) {
                // Update the session data after successful profile update
                session()->set('username', $data['username']); // Update username in session

                session()->setFlashdata('success', 'Profile updated successfully.');
                return redirect()->to('/profile');
            } else {
                return redirect()->back()->withInput()->with('errors', $userModel->errors());
            }
        }

        // Pass the user data to the view
        $data['user'] = $user;
        return view('profile_edit', $data);
    }

    public function updateBookingStatus($bookingItemId)
    {
        // Ensure the user is logged in as a vendor
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to update this booking.');
        }

        // Check if the booking item exists
        $bookingItemModel = new BookingItemModel();
        $bookingItem = $bookingItemModel->find($bookingItemId); // Find by booking_item_id

        if (!$bookingItem) {
            return redirect()->to('/profile')->with('error', 'Booking item not found.');
        }

        // Check if the vendor is authorized to update this booking (i.e., if they own the service)
        $serviceModel = new ServiceModel();
        $service = $serviceModel->find($bookingItem['service_id']);

        if (!$service || $service['vendor_id'] != session()->get('user_id')) {
            return redirect()->to('/profile')->with('error', 'You are not authorized to update this booking.');
        }

        $newStatus = $this->request->getPost('status'); // Get status from form submission
        if (!in_array($newStatus, ['pending', 'accepted', 'rejected'])) {
            return redirect()->to('/profile')->with('error', 'Invalid status update.');
        }

        // Update the status
        if (!$bookingItemModel->update($bookingItemId, ['status' => $newStatus])) { // Update by booking_item_id
            return redirect()->to('/profile')->with('error', 'Failed to update booking status.');
        }

        // Fetch user data
        $userModel = new UserModel();
        $user = $userModel->find(session()->get('user_id'));

        // Pass user data to the view
        $data['user'] = $user;
        $data['services'] = $serviceModel->where('vendor_id', $user['id'])->findAll();
        $bookingItemModel = new BookingItemModel();
        $data['bookingItems'] = $bookingItemModel
            ->select('booking_items.*, bookings.event_id, events.title as event_title, events.date as event_date, events.ceremony_type, events.location')
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('events', 'events.id = bookings.event_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->where('services.vendor_id', session()->get('user_id'))
            ->findAll();

        return view('profile_vendor', $data); // Use separate vendor profile view
    }
}

