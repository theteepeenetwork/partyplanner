<?php

namespace App\Controllers;

use App\Models\ServiceModel;
use App\Models\EventModel;
use App\Models\CategoryModel;
use App\Models\ServiceImageModel;
use App\Models\UserModel;
use App\Models\BookingModel;
use App\Models\ServiceTimeBlockModel;  // Add this line
use App\Models\ServiceAvailabilityModel;
use App\Models\BookingItemModel;
use App\Models\CartModel;
use CodeIgniter\Controller;
use Config\Services;
use DateTime;


class Service_Controller extends BaseController
{
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
            $serviceModel = new ServiceModel();
            $services = $serviceModel->where('vendor_id', $userId)->findAll();

            $data['services'] = $services;
            return view('profile_vendor', $data);
        } else {
            return redirect()->to('/')->with('error', 'You are not authorized to view this page.');
        }
    }

    public function create()
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to add services.');
        }

        $data['categories'] = $this->buildCategoryTree();

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'title' => 'required|min_length[3]|max_length[255]',
                'description' => 'required',
                'price' => 'required|decimal',
                'category_id' => 'required|is_natural_no_zero',
                'images' => 'uploaded[images]|max_size[images,10000]|is_image[images]',
                'hire_durations' => 'required' // Ensure hire durations are required
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
                ];

                if ($serviceModel->save($serviceData)) {
                    $serviceId = $serviceModel->insertID();
                    $this->handleImages($serviceId);

                    // Save hire durations
                    $hireDurations = $this->request->getPost('hire_durations');
                    $this->saveTimeBlocks($serviceId, $hireDurations);

                    return redirect()->to('/service/create')->with('success', 'Service created successfully!');
                } else {
                    return redirect()->back()->withInput()->with('error', 'Failed to add service to the database.');
                }
            }
        }

        return view('service_create', $data);
    }



    private function handleImages($serviceId)
    {
        $imageFiles = $this->request->getFiles();
        $serviceImageModel = new ServiceImageModel();

        foreach ($imageFiles['images'] as $imageFile) {
            if ($imageFile->isValid() && !$imageFile->hasMoved()) {
                // Move the original image
                $newName = $imageFile->getRandomName();
                $imageFile->move(ROOTPATH . 'public/uploads/services/', $newName);
                $imagePath = 'uploads/services/' . $newName;

                // Create a thumbnail
                $thumbnailPath = $this->createThumbnail($imagePath);

                // Save image paths to the database
                $serviceImageModel->save([
                    'service_id' => $serviceId,
                    'image_path' => $imagePath,
                    'thumbnail_path' => $thumbnailPath
                ]);
            }
        }
    }

    private function createThumbnail($imagePath)
    {
        $imageService = Services::image();
        $thumbnailName = 'thumb_' . basename($imagePath);
        $thumbnailPath = 'uploads/services/thumbnails/' . $thumbnailName;

        $imageService->withFile(ROOTPATH . 'public/' . $imagePath)
            ->fit(200, 200, 'center')
            ->save(ROOTPATH . 'public/' . $thumbnailPath);

        return $thumbnailPath;
    }

    public function update($id = null)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to edit services.');
        }

        $serviceModel = new ServiceModel();
        $service = $serviceModel->getServiceWithImages($id);

        if (!$service || $service['vendor_id'] != session()->get('user_id')) {
            return redirect()->to('/profile')->with('error', 'Service not found or you are not authorized to edit it.');
        }

        // Retrieve the time blocks associated with the service
        $serviceTimeBlockModel = new ServiceTimeBlockModel();
        $timeBlocks = $serviceTimeBlockModel->where('service_id', $id)->findAll();

        $data['categories'] = $this->buildCategoryTree();
        $data['service'] = $service;
        $data['timeBlocks'] = $timeBlocks;  // Pass time blocks to the view

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'title' => 'required|min_length[3]|max_length[255]',
                'short_description' => 'required',
                'description' => 'required',
                'price' => 'required|decimal',
                'category_id' => 'required|is_natural_no_zero',
                'subcategory_id' => 'required|is_natural_no_zero',
                'images.*' => 'max_size[images,10240]|is_image[images]',
                'time_blocks' => 'required' // Time blocks are required
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            } else {
                $serviceModel->transStart();

                $serviceData = [
                    'title' => $this->request->getPost('title'),
                    'description' => $this->request->getPost('description'),
                    'short_description' => $this->request->getPost('short_description'),
                    'price' => $this->request->getPost('price'),
                    'category_id' => $this->request->getPost('category_id'),
                    'subcategory_id' => $this->request->getPost('subcategory_id'),
                ];

                if ($serviceModel->update($id, $serviceData)) {
                    $this->handleImages($id);

                    // Update time blocks
                    $timeBlocks = $this->request->getPost('time_blocks');
                    $this->saveTimeBlocks($id, $timeBlocks);

                    $serviceModel->transComplete();
                    return redirect()->to('/service/edit/' . $id)->with('success', 'Service updated successfully.');
                } else {
                    $serviceModel->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Failed to update service in the database.');
                }
            }
        }

        return view('service_edit', $data);
    }





    private function buildCategoryTree($parentId = null, $level = 0)
    {
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->where('parent_id', $parentId)->findAll();
        $result = [];

        foreach ($categories as $category) {
            $category['level'] = $level;
            $result[] = $category;
            $children = $this->buildCategoryTree($category['id'], $level + 1);
            $result = array_merge($result, $children);
        }

        return $result;
    }

    public function view($id)
    {
        // Initialize necessary models
        $serviceModel = new ServiceModel();
        $categoryModel = new CategoryModel();
        $serviceImageModel = new ServiceImageModel();
        $bookingItemModel = new BookingItemModel();
        $eventModel = new EventModel();
        $serviceTimeBlockModel = new ServiceTimeBlockModel();

        // Fetch the service details
        $data['service'] = $serviceModel
            ->select('services.*, categories.name as category_name')
            ->join('categories', 'categories.id = services.category_id')
            ->find($id);

        if (!$data['service']) {
            return redirect()->to('/service')->with('error', 'Service not found.');
        }

        // Fetch associated images
        $data['images'] = $serviceImageModel->where('service_id', $id)->findAll();

        // Fetch the service's time blocks
        $data['service']['time_blocks'] = $serviceTimeBlockModel->where('service_id', $id)->findAll();

        // Get the logged-in user ID and role
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role == 'vendor' && $data['service']['vendor_id'] == $userId) {
            // Vendor-specific view logic
            $bookings = $bookingItemModel
                ->select('booking_items.*, bookings.*, events.title as event_title, events.date as event_date')
                ->join('bookings', 'bookings.id = booking_items.booking_id')
                ->join('events', 'events.id = bookings.event_id')
                ->where('booking_items.service_id', $id)
                ->whereIn('bookings.status', ['accepted', 'pending']) // Only accepted or pending bookings
                ->findAll();

            $data['bookings'] = $bookings;

            // Use the same view but conditionally render vendor-specific information
            return view('service_view', $data);
        } else {
            // Customer-specific view logic
            $events = $eventModel->where('user_id', $userId)->findAll();

            if (empty($events)) {
                $data['no_events'] = true;
                return view('service_view', $data);
            }

            $availabilityStatuses = [];

            foreach ($events as $event) {
                if (isset($event['date'])) {
                    $eventDate = $event['date'];

                    // Check existing bookings for the selected event's date
                    $existingBookings = $bookingItemModel
                        ->select('booking_items.*')
                        ->join('bookings', 'bookings.id = booking_items.booking_id')
                        ->join('events', 'events.id = bookings.event_id')
                        ->where('booking_items.service_id', $id)
                        ->where('events.date', $eventDate)
                        ->whereIn('bookings.status', ['accepted', 'pending']) // Consider only accepted and pending bookings
                        ->findAll();

                    if (count($existingBookings) > 0) {
                        // If there are existing bookings, mark as "Limited Availability"
                        $availabilityStatuses[] = [
                            'event_id' => $event['id'],
                            'event_title' => $event['title'],
                            'date' => $eventDate,
                            'status' => 'Limited Availability',
                        ];
                    } else {
                        // If there are no bookings, mark as "Available"
                        $availabilityStatuses[] = [
                            'event_id' => $event['id'],
                            'event_title' => $event['title'],
                            'date' => $eventDate,
                            'status' => 'Available',
                        ];
                    }
                } else {
                    $availabilityStatuses[] = [
                        'event_id' => $event['id'],
                        'event_title' => $event['title'],
                        'date' => 'N/A',
                        'status' => 'Date not found',
                    ];
                }
            }

            $data['availability_statuses'] = $availabilityStatuses;

            return view('service_view', $data);
        }
    }











    public function deactivate($id = null)
{
    try {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'You are not authorized to deactivate services.']);
        }

        $serviceModel = new ServiceModel();
        $service = $serviceModel->find($id);

        if (!$service || $service['vendor_id'] != session()->get('user_id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Service not found or you are not authorized to deactivate it.']);
        }

        if ($serviceModel->update($id, ['status' => 'inactive'])) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Service deactivated successfully.']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to deactivate the service.']);
        }
    } catch (\Exception $e) {
        log_message('error', $e->getMessage());
        return $this->response->setJSON(['status' => 'error', 'message' => 'An error occurred while deactivating the service.']);
    }
}

    public function reactivate($id = null)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'You are not authorized to reactivate services.']);
        }

        $serviceModel = new ServiceModel();
        $service = $serviceModel->find($id);

        if (!$service || $service['vendor_id'] != session()->get('user_id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Service not found or you are not authorized to reactivate it.']);
        }

        if ($serviceModel->update($id, ['status' => 'active'])) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Service reactivated successfully.']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to reactivate the service.']);
        }
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
        if ($serviceModel->update($id, ['deleted_at' => date('Y-m-d H:i:s')])) {
            // Remove associated images from the database and filesystem
            $this->deleteServiceImages($id);

            return redirect()->to('/profile')->with('success', 'Service deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to delete the service.');
        }
    }

    public function deleteImage($imageId)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized.']);
        }

        $serviceImageModel = new \App\Models\ServiceImageModel();
        $image = $serviceImageModel->find($imageId);

        if (!$image) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Image not found.']);
        }

        $serviceId = $image['service_id'];
        $serviceModel = new \App\Models\ServiceModel();
        $service = $serviceModel->find($serviceId);

        if ($service['vendor_id'] != session()->get('user_id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized to delete this image.']);
        }

        // Delete the image files
        unlink(ROOTPATH . 'public/' . $image['image_path']);
        unlink(ROOTPATH . 'public/' . $image['thumbnail_path']);

        // Delete the image record from the database
        $serviceImageModel->delete($imageId);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Image deleted successfully.']);
    }

    public function setPrimaryImage($imageId)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $serviceImageModel = new ServiceImageModel();
        $image = $serviceImageModel->find($imageId);

        if (!$image) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Image not found']);
        }

        // Unset previous primary image
        $serviceImageModel->where('service_id', $image['service_id'])->set(['is_primary' => 0])->update();

        // Set the selected image as primary
        if ($serviceImageModel->update($imageId, ['is_primary' => 1])) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Primary image set successfully']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to set primary image']);
        }
    }



    private function deleteServiceImages($serviceId)
    {
        $serviceImageModel = new ServiceImageModel();
        $images = $serviceImageModel->where('service_id', $serviceId)->findAll();

        foreach ($images as $image) {
            // Delete images from filesystem
            @unlink(ROOTPATH . 'public/' . $image['image_path']);
            @unlink(ROOTPATH . 'public/' . $image['thumbnail_path']);

            // Remove images from the database
            $serviceImageModel->delete($image['id']);
        }
    }

    public function getAvailableSlots($serviceId, $date)
    {
        $serviceAvailabilityModel = new ServiceAvailabilityModel();

        // Fetch all availability for the service on the given date
        $availability = $serviceAvailabilityModel->where('service_id', $serviceId)
            ->where('date', $date)
            ->where('is_booked', 0)
            ->findAll();

        // Calculate available time slots


        $availableSlots = [];
        foreach ($availability as $slot) {
            // Format the start_time and end_time
            $formattedStartTime = (new DateTime($slot['start_time']))->format('H:i');
            $formattedEndTime = (new DateTime($booking['end_time']))->format('H:i');
            $availableSlots[] = [
                'start_time' => $formattedStartTime,
                'end_time' => $formattedEndTime
            ];
        }

        return $availableSlots;
    }

    public function bookService()
    {
        $bookingModel = new BookingModel();
        $serviceAvailabilityModel = new ServiceAvailabilityModel();

        // Get the necessary data from the POST request
        $serviceId = $this->request->getPost('service_id');
        $eventId = $this->request->getPost('event_id');
        $date = $this->request->getPost('date');
        $startTime = $this->request->getPost('start_time');
        $duration = $this->request->getPost('duration');

        // Convert the duration to minutes
        $durationMinutes = $this->convertToMinutes($duration);

        // Calculate the end time based on the start time and duration
        $startDateTime = new \DateTime("$date $startTime");
        $endDateTime = clone $startDateTime;
        $endDateTime->modify("+$durationMinutes minutes");

        // Check if the time slot is still available
        $availability = $serviceAvailabilityModel->where('service_id', $serviceId)
            ->where('date', $date)
            ->where('start_time <=', $startTime)
            ->where('end_time >=', $endDateTime->format('H:i:s'))
            ->where('is_booked', 0)
            ->first();

        if (!$availability) {
            return redirect()->back()->with('error', 'The selected time slot is no longer available.');
        }

        // Check for booking conflicts
        $conflict = $bookingModel->where('service_id', $serviceId)
            ->where('date', $date)
            ->where('start_time <', $endDateTime->format('H:i:s'))
            ->where('end_time >', $startTime)
            ->first();

        if ($conflict) {
            return redirect()->back()->with('error', 'The selected time slot conflicts with an existing booking.');
        }

        // Book the service
        $bookingModel->save([
            'user_id' => session()->get('user_id'),
            'event_id' => $eventId,
            'service_id' => $serviceId,
            'date' => $date,
            'start_time' => $startDateTime->format('H:i:s'),
            'end_time' => $endDateTime->format('H:i:s'),
            'status' => 'pending',
        ]);

        // Mark the time slot as booked
        $serviceAvailabilityModel->update($availability['id'], ['is_booked' => 1]);

        return redirect()->to('/service/view/' . $serviceId)->with('success', 'Service booked successfully!');
    }

    private function convertToMinutes($timeLength)
    {
        // Parse the time length (e.g., "1h", "2h30m") into total minutes
        $hours = 0;
        $minutes = 0;

        if (preg_match('/(\d+)h/', $timeLength, $matches)) {
            $hours = (int)$matches[1];
        }

        if (preg_match('/(\d+)m/', $timeLength, $matches)) {
            $minutes = (int)$matches[1];
        }

        return ($hours * 60) + $minutes;
    }

    private function saveTimeBlocks($serviceId, $timeBlocks)
    {
        $serviceTimeBlockModel = new ServiceTimeBlockModel();

        // Clear existing time blocks before saving new ones
        $serviceTimeBlockModel->where('service_id', $serviceId)->delete();

        foreach ($timeBlocks as $timeBlock) {
            $serviceTimeBlockModel->save([
                'service_id' => $serviceId,
                'time_length' => trim($timeBlock)
            ]);
        }
    }


    public function addAvailability($serviceId)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to add availability.');
        }

        $serviceModel = new ServiceModel();
        $service = $serviceModel->find($serviceId);

        if (!$service || $service['vendor_id'] != session()->get('user_id')) {
            return redirect()->to('/profile')->with('error', 'Service not found or you are not authorized to add availability.');
        }

        if ($this->request->getMethod() === 'POST') {
            $availabilityModel = new ServiceAvailabilityModel();
            $date = $this->request->getPost('date');
            $startTime = $this->request->getPost('start_time');
            $endTime = $this->request->getPost('end_time');

            $availabilityData = [
                'service_id' => $serviceId,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_booked' => 0
            ];

            if ($availabilityModel->save($availabilityData)) {
                return redirect()->to('/service/edit/' . $serviceId)->with('success', 'Availability added successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to add availability.');
            }
        }

        return view('add_availability', ['service' => $service]);
    }

    private function checkAvailability($serviceId, $date)
    {
        $bookingModel = new BookingModel();

        // Fetch all bookings for the service on the selected date
        $existingBookings = $bookingModel->where('service_id', $serviceId)
            ->where('DATE(start_time)', $date)
            ->findAll();

        // If no bookings are found, the service is fully available
        if (empty($existingBookings)) {
            return 'Available';
        }

        // Calculate if the service has some availability
        // You can add logic here to check the time slots that are already booked and compare them to the service's availability.

        // For now, if there are existing bookings but not fully booked, we consider it 'Limited Availability'
        $totalSlots = $this->calculateAvailableSlots($serviceId, $date);

        if (!empty($totalSlots)) {
            return 'Limited Availability';
        }

        // If all slots are booked, mark the service as not available
        return 'Not Available';
    }

    private function calculateAvailableSlots($service, $existingBookings)
    {
        // Assuming the service has time blocks defined
        $availableSlots = [];

        // Convert the service's time blocks into usable slots
        foreach ($service['time_blocks'] as $timeBlock) {
            $slotStart = new DateTime($timeBlock['start_time']);
            $slotEnd = new DateTime($timeBlock['end_time']);

            // Check each slot against the existing bookings
            $isAvailable = true;
            foreach ($existingBookings as $booking) {
                $bookingStart = new DateTime($booking['start_time']);
                $bookingEnd = new DateTime($booking['end_time']);

                // If the time block overlaps with an existing booking, mark it as unavailable
                if ($slotStart < $bookingEnd && $slotEnd > $bookingStart) {
                    $isAvailable = false;
                    break;
                }
            }

            // If the slot is available, add it to the available slots
            if ($isAvailable) {
                $availableSlots[] = [
                    'start_time' => $slotStart->format('H:i:s'),
                    'end_time' => $slotEnd->format('H:i:s')
                ];
            }
        }

        return $availableSlots;
    }

    private function timeOverlap($block, $booking)
    {
        $blockStart = strtotime($block['start_time']);
        $blockEnd = strtotime($block['end_time']);
        $bookingStart = strtotime($booking['start_time']);
        $bookingEnd = strtotime($booking['end_time']);

        // Check if time blocks overlap
        return ($blockStart < $bookingEnd && $blockEnd > $bookingStart);
    }
}
