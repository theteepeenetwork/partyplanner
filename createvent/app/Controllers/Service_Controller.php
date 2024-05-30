<?php namespace App\Controllers;

use App\Models\ServiceModel;
use App\Models\CategoryModel;
use App\Models\SubCategoryModel;
use App\Models\EventModel;
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
    $serviceModel = new ServiceModel(); // Initialize the ServiceModel **here**

    // Pass additional data to view based on user role
    if ($user['role'] == 'vendor') {
        $data['services'] = $serviceModel->where('vendor_id', $userId)->findAll();
        return view('profile_vendor', $data); // Use separate vendor profile view
    } else {
        $bookingModel = new BookingModel();
        $bookingItemModel = new BookingItemModel();
        
        // Get the customer's bookings
        $bookings = $bookingModel->where('user_id', $userId)->findAll();

        // Fetch service details and booking status for each booking item
        $bookingData = [];
        foreach ($bookings as $booking) {
            $bookingItems = $bookingItemModel
                ->select('booking_items.*, services.title, services.price, bookings.status')
                ->join('services', 'services.id = booking_items.service_id')
                ->join('bookings', 'bookings.id = booking_items.booking_id')
                ->where('booking_id', $booking['id'])
                ->findAll();
            
            // Assign services to booking
            $booking['services'] = $bookingItems;
        }
        
        $data['bookings'] = $bookings;
        return view('profile', $data); 
    }
}
   
public function create()
{
    // Ensure the user is authorized
    if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
        return redirect()->to('/')->with('error', 'You are not authorized to add services.');
    }

    $categoryModel = new CategoryModel();
    $data['categories'] = $categoryModel->findAll();

    $subcategoryModel = new SubcategoryModel();
    $data['subcategories'] = $subcategoryModel->findAll();
    $data['subcategoriesJson'] = json_encode($data['subcategories']);

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
            'title'             => 'required|min_length[3]|max_length[255]',
            'description'       => 'required',
            'price'             => 'required|decimal',
            'category_id'       => 'required|is_natural_no_zero',
            'short_description' => 'required',
            'subcategory_id'    => 'required|is_natural_no_zero'
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

             var_dump($id);
 
         if (!$data['service']) {
             return redirect()->to('/service')->with('error', 'Service not found.');
         }
 
         return view('service_view', $data); // View for the service detail page
     }

    public function update($id = null)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to edit services.');
        }

        $serviceModel = new ServiceModel();
        $data['service'] = $serviceModel->find($id);

        if (!$data['service'] || $data['service']['vendor_id'] != session()->get('user_id')) {
            return redirect()->to('/profile')->with('error', 'Service not found or you are not authorized to edit it.');
        }

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'title'       => 'required|min_length[3]|max_length[255]',
                'description' => 'required',
                'price'       => 'required|decimal'
                // ... (add validation rules for image if needed) ...
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $serviceData = [
                'title' => $this->request->getPost('title'),
                'description' => $this->request->getPost('description'),
                'price' => $this->request->getPost('price'),
            ];

            if ($serviceModel->update($id, $serviceData)) {
                session()->setFlashdata('success', 'Service updated successfully.');
                return redirect()->to('/profile');
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to update service.');
            }
        }

        return view('service_edit', $data);
    }

    public function search()
{
    $searchQuery = $this->request->getGet('q');
    $categoryId = $this->request->getGet('cuisine');

    $serviceModel = new ServiceModel();
    $categoryModel = new CategoryModel();

    $builder = $serviceModel->like('title', $searchQuery);

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


    


}
