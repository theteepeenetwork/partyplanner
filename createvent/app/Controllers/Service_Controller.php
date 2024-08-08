<?php
namespace App\Controllers;

use App\Models\ServiceModel;
use App\Models\UserModel;
use App\Models\CategoryModel;
use App\Models\CartModel;
use App\Models\SubCategoryModel;
use App\Models\EventModel;
use App\Models\BookingItemModel;
use App\Models\UnavailableDateModel; // Import for unavailable dates
use Config\Database;
use CodeIgniter\I18n\Time;

class Service_Controller extends BaseController
{
    // Remove or comment out the index() method 
    /*
     public function index()
     {
         return view('service_create');
     } 
     */

    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login')->with('error', 'You must be logged in to view your profile.');
        }

        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/')->with('error', 'User not found.');
        }

        $data['user'] = $user;

        if ($user['role'] == 'vendor') {
            // Fetch the vendor's services
            $serviceModel = new ServiceModel();
            $services = $serviceModel->where('vendor_id', $userId)->findAll();

            // Fetch booking items for the vendor's services
            $bookingItemModel = new BookingItemModel();
            $bookingItems = $bookingItemModel
                ->select('booking_items.*, bookings.event_id, events.title as event_title, events.date, events.ceremony_type, events.location')
                ->join('services', 'services.id = booking_items.service_id')
                ->join('bookings', 'bookings.id = booking_items.booking_id')
                ->join('events', 'events.id = bookings.event_id')
                ->where('services.vendor_id', $userId)
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

    public function create()
    {
        // Ensure the user is authorized
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to add services.');
        }
        
        $data = [];
        $userId = session()->get('user_id');

        // Fetch categories and subcategories
        $categoryModel = new CategoryModel();
        $subcategoryModel = new SubcategoryModel();
        $data['categories'] = $categoryModel->findAll();
        $data['subcategories'] = $subcategoryModel->findAll();
        $data['subcategoriesJson'] = json_encode($data['subcategories']); 

         if ($this->request->getMethod() === 'POST') {
             $rules = [
                'title'             => 'required|min_length[3]|max_length[255]',
                'description'       => 'required',
                'price'             => 'required|decimal',
                'category_id'       => 'required|is_natural_no_zero',
                'short_description' => 'required',
                'subcategory_id'    => 'required|is_natural_no_zero',
                'image'             => 'uploaded[image]|max_size[image,1024]|is_image[image]'
             ]; 

             if (!$this->validate($rules)) {
                 return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
             } else {
                 $serviceModel = new ServiceModel();
                 $imageData = $this->request->getFile('image');

                // Image Upload and Processing
                if ($imageData && $imageData->isValid() && !$imageData->hasMoved()) {
                    $newName = $imageData->getRandomName();
                    $imageData->move(ROOTPATH . 'public/uploads', $newName); // Move uploaded image
                } else {
                    $newName = null; // Set to null if no image was uploaded or there was an error
                }

                 $serviceData = [
                     'vendor_id' => session()->get('user_id'),
                     'title' => $this->request->getPost('title'),
                     'description' => $this->request->getPost('description'),
                     'price' => $this->request->getPost('price'),
                     'category_id' => $this->request->getPost('category_id'), // Get category from form
                     'subcategory_id' => $this->request->getPost('subcategory_id'),
                     'short_description' => $this->request->getPost('short_description'),
                     'image'             => $newName, // Store the image filename
                 ];
                 
                 if ($serviceModel->save($serviceData)) {
                     return redirect()->to('/service/create/')->with('success', 'Service created successfully!');
                 } else {
                     log_message('error', 'Database Error: Failed to add service: ' . json_encode($serviceModel->errors())); 
                     return redirect()->back()->withInput()->with('error', 'Failed to add service to the database.');
                 }
             }
         }

        // If no POST request (form hasn't been submitted), load the create view
         return view('service_create', $data); 
     }


    public function store()
    {
        // Ensure the user is authorized
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to add services.');
        }

        $data = [];

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'title' => 'required|min_length[3]|max_length[255]',
                'description' => 'required',
                'price' => 'required|decimal',
                'category_id' => 'required|is_natural_no_zero',
                'short_description' => 'required',
                'subcategory_id' => 'required|is_natural_no_zero'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            } else {
                $serviceModel = new ServiceModel();

                $serviceData = [
                    'vendor_id' => session()->get('user_id'),
                    'title' => $this->request->getPost('title'),
                    'description' => $this->request->getPost('description'),
                    'price' => $this->request->getPost('price'),
                    'category_id' => $this->request->getPost('category_id'),
                    'subcategory_id' => $this->request->getPost('subcategory_id'),
                    'short_description' => $this->request->getPost('short_description'),
                ];

                if ($serviceModel->save($serviceData)) {
                    return redirect()->to('/service')->with('success', 'Service created successfully!');
                } else {
                    log_message('error', 'Database Error: Failed to add service: ' . json_encode($serviceModel->errors()));
                    return redirect()->back()->withInput()->with('error', 'Failed to add service to the database.');
                }
            }
        }
    }
    public function view($id)
    {
        $serviceModel = new ServiceModel();
        $categoryModel = new CategoryModel();
        $subcategoryModel = new SubCategoryModel();

        $data['service'] = $serviceModel
            ->select('services.*, categories.name as category_name, subcategories.name as subcategory_name') // Select necessary fields from services table
            ->join('categories', 'categories.id = services.category_id')
            ->join('subcategories', 'subcategories.id = services.subcategory_id', 'left') // Left join in case there is no subcategory
            ->find($id);

        if (!$data['service']) {
            return redirect()->to('/service')->with('error', 'Service not found.');
        }

        return view('service_view', $data); // View for the service detail page
    }

    public function update($id = null)
    {
        // Ensure the user is authorized
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to edit services.');
        }
    
        $serviceModel = new ServiceModel();
        $data['service'] = $serviceModel->find($id);
        $userId = session()->get('user_id');
    
        if (!$data['service'] || $data['service']['vendor_id'] != $userId) {
            return redirect()->to('/profile')->with('error', 'Service not found or you are not authorized to edit it.');
        }
    
        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'title'             => 'required|min_length[3]|max_length[255]',
                'description'       => 'required',
                'price'             => 'required|decimal',
                'category_id'       => 'required|is_natural_no_zero',
                'short_description' => 'required',
                'subcategory_id'    => 'required|is_natural_no_zero'
            ];
    
            if (!$this->validate($rules)) {
                // Validation failed, return to form with errors
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            } else {
                $imageData = $this->request->getFile('image');
                // Image Upload and Processing
                if ($imageData && $imageData->isValid() && !$imageData->hasMoved()) {
                    $newName = $imageData->getRandomName();
                    $imageData->move(ROOTPATH . 'public/uploads', $newName); // Move uploaded image
                    // Remove old image if it exists
                    if ($data['service']['image']) {
                        unlink(ROOTPATH . 'public/uploads/' . $data['service']['image']);
                    }
                } else {
                    $newName = $data['service']['image'];  // Keep the existing image filename if no new image is uploaded
                }
    
                $serviceData = [
                    'vendor_id'         => session()->get('user_id'),
                    'title'             => $this->request->getPost('title'),
                    'description'       => $this->request->getPost('description'),
                    'price'             => $this->request->getPost('price'),
                    'category_id'       => $this->request->getPost('category_id'), 
                    'subcategory_id'    => $this->request->getPost('subcategory_id'),
                    'short_description' => $this->request->getPost('short_description'),
                    'image'             => $newName, // Use the updated or existing image filename
                ];
    
               /* if ($serviceModel->update($id, $serviceData)) { // Check for update errors
                    // Service updated successfully
                    return redirect()->to('/service/edit/'.$id)->with('success', 'Service updated successfully.');
                } else {
                    // Handle errors here (e.g., log, flash message)
                    log_message('error', 'Database Error: Failed to update service: ' . json_encode($serviceModel->errors())); 
                    return redirect()->back()->withInput()->with('errors', $serviceModel->errors());
                }*/
            }
    
        }
    
        // Fetch categories and subcategories for populating the edit form
        $categoryModel = new CategoryModel();
        $subcategoryModel = new SubcategoryModel();
        $data['categories'] = $categoryModel->findAll();
        $data['subcategories'] = $subcategoryModel->findAll(); 
        $data['subcategoriesJson'] = json_encode($data['subcategories']);
        $data['userId'] = $userId;
    
        // Get unavailable dates for this service
        //$unavailableDateModel = new UnavailableDateModel();
        //$unavailableDates = $unavailableDateModel->where('service_id', $id)->findAll();
    
       // $data['unavailableDates'] = array_column($unavailableDates, 'date');
        return view('service_edit', $data);
    }
    
    

    public function search()
{
    $searchQuery = $this->request->getGet('q');
    $categoryId = $this->request->getGet('cuisine');

    $serviceModel = new ServiceModel();
    $categoryModel = new CategoryModel();

    $builder = $serviceModel
        ->like('title', $searchQuery)
        ->where('deleted_at', null); // Add this line

    // Filter by category if selected
    if (!empty($categoryId)) {
        $builder->where('category_id', $categoryId);
    }

    $services = $builder->findAll();

    // Updated section to set both $categories and $categoryId
    $data['services'] = $services;
    $data['categories'] = $categoryModel->findAll(); // Fetch all categories
    $data['searchQuery'] = $searchQuery;
    $data['cuisine'] = $categoryId; // Pass the categoryId as $cuisine 

    return view('service_search_results', $data);
}

    public function updateBookingStatus($bookingItemId)
    {
        // Ensure the user is logged in as a vendor
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to update this booking.');
        }

        // Check if the booking item exists
        $bookingItemModel = new BookingItemModel();
        $bookingItem = $bookingItemModel->find($bookingItemId);

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
        $bookingItemModel->update($bookingItemId, ['status' => $newStatus]);

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

    public function delete($id = null)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to delete services.');
        }

        $serviceModel = new ServiceModel();
        $service = $serviceModel->find($id);

        if (!$service || $service['vendor_id'] != session()->get('user_id')) {
            return redirect()->to('/profile')->with('error', 'Service not found or you are not authorized to delete it.');
        }

        // Soft delete the service by setting deleted_at
        $serviceModel->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);

        // Find carts that contain this service
        $cartModel = new CartModel();
        $affectedCarts = $cartModel->where('service_id', $id)->findAll();

        // Update the affected carts and remove the service
        foreach ($affectedCarts as $cart) {
            // Remove the service from the cart
            $cartModel->delete($cart['id']);

            // Get the user ID associated with the cart
            $userId = $cart['user_id'];

            // Update the cart count for the user (in base controller)
            $this->updateCartCount($userId); 

            // Add a flash message to the user's session
            session()->setFlashdata('cart_message_' . $userId, 'Some services you were interested in are no longer available and have been removed from your cart.');
        }

        return redirect()->to('/profile')->with('success', 'Service deleted successfully.');
    }

    protected function updateCartCount()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return; // User not logged in
        }
    
        $cartModel = new CartModel();
        $cartCount = $cartModel->where('user_id', $userId)->countAllResults();
        session()->set('cart_count', $cartCount);
    
        // Get the updated cart count
        $updatedCartCount = session()->get('cart_count');
    
        // Send JSON response for AJAX update (if needed)
        $this->response->setContentType('application/json');
        return $this->response->setJSON(['cart_count' => $updatedCartCount]);
    }




}
