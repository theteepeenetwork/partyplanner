<?php namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ServiceModel;
use App\Models\BookingModel;
use App\Models\BookingItemModel;
use App\Models\EventModel;

class Profile extends BaseController
{
    public function index()
    {
        // Check if the user is logged in
        if (!session()->has('user_id')) {
            return redirect()->to('/login')->with('error', 'You must be logged in to view your profile.');
        }
    
        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);
    
        // Check if the user exists
        if (!$user) {
            return redirect()->to('/')->with('error', 'User not found.');
        }
    
        $data['user'] = $user; 
    
        // Check user role and load the appropriate view
        if ($user['role'] == 'vendor') {
            $serviceModel = new ServiceModel();
            $data['services'] = $serviceModel->where('vendor_id', $userId)->findAll();
            return view('profile_vendor', $data);
        } else {
            $eventModel = new EventModel();
            $data['events'] = $eventModel->where('user_id', $userId)->findAll();
    
            return view('profile_customer', $data); 
        }
    }



    private function vendorProfile($userId, $data) {
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
}

