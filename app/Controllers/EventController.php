<?php namespace App\Controllers;

use App\Models\EventModel;
use Config\Services;

class EventController extends BaseController
{
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
    $hasEvent = $eventModel->where('user_id', $userId)->countAllResults() > 0; // Use countAllResults()

    if (!$hasEvent) {
        // Store the current URL for redirection after creating an event
        session()->setFlashdata('redirect_after_event_create', current_url());

        // Redirect to event creation with error message
        return redirect()->to('/event/create')->with('error', 'You must create an event before adding services.');
    }
    
    //Check if the user already has this service in their cart
    $cartModel = new CartModel();
    $existingItem = $cartModel->where('user_id', $userId)->where('service_id', $serviceId)->first();

    if ($existingItem) {
        // Update existing item quantity
        $cartModel->update($existingItem['id'], ['quantity' => $existingItem['quantity'] + 1]);
    } else {
        // Add new item to the cart
        $cartModel->save([
            'user_id' => $userId,
            'service_id' => $serviceId,
            'quantity' => 1
        ]);
    }

    // Update cart item count in session
    $this->updateCartCount();

    return redirect()->back()->with('success', 'Service added to cart!');
}

public function create()
    {
        if (!session()->has('user_id')) {
            session()->setFlashdata('redirect_after_login', current_url());
            return redirect()->to('/login')->with('error', 'You must be logged in to create an event.');
        }

        $data = [];
        
        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'title'         => 'required|min_length[3]|max_length[255]',
                'ceremony_type' => 'required|in_list[wedding,party,corporate,other]',
                'location'      => 'required',
                'date'          => 'required|valid_date',
            ];

            // Using the validation service directly
            $validation = Services::validation();

            if (!$validation->withRequest($this->request)->run(null, 'eventCreateRules')) { // Validate against eventCreateRules
                $data['validation'] = $validation;
            } else {
                $eventModel = new EventModel();
                $eventData = [
                    'user_id'       => session()->get('user_id'),
                    'title'         => $this->request->getPost('title'),
                    'ceremony_type' => $this->request->getPost('ceremony_type'),
                    'location'      => $this->request->getPost('location'),
                    'date'          => $this->request->getPost('date'),
                ];

                if ($eventModel->save($eventData)) {
                    $redirectUrl = session()->getFlashdata('redirect_after_event_create');
                    
                    // Check if there's a redirect URL stored in the session
                    if ($redirectUrl) {
                        return redirect()->to($redirectUrl);
                    } 

                    // If no redirect URL, go to the profile
                    return redirect()->to('/profile')->with('success', 'Event created successfully!');
                } else {
                    log_message('error', 'Failed to create event: ' . json_encode($eventModel->errors())); // Log the error
                    return redirect()->back()->withInput()->with('error', 'Failed to create event.'); // Error message
                }
            }
        }

        return view('event_create', $data); // If not a POST request, show the form again
    }

}

