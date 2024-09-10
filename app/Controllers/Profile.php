<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ServiceModel;
use App\Models\ServiceImageModel; // Make sure this model is included
use App\Models\BookingModel;
use App\Models\BookingItemModel;
use App\Models\EventModel;
use App\Models\ChatMessageModel;
use App\Models\PaymentsModel;
use DateTime;
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

        // Check if the user exists
        if (!$user) {
            return redirect()->to('/')->with('error', 'User not found.');
        }

        $data = $this->loadProfileData($user);

        return $user['role'] == 'vendor'
            ? view('profile_vendor', $data)
            : view('profile_customer', $data);
    }
    public function profile($tab = 'main')
    {
        // Load your models and data here
        $userId = session()->get('user_id');

        // Fetch active and inactive services, bookings, etc.
        // This is just an example; adjust based on your actual data fetching logic.
        $activeServices = $this->serviceModel->where('vendor_id', $userId)->where('status', 'active')->findAll();
        $inactiveServices = $this->serviceModel->where('vendor_id', $userId)->where('status', 'inactive')->findAll();
        $bookingItems = $this->bookingModel->getBookingsForVendor($userId);

        // Set the active tab based on the tab parameter or default to 'main'
        $data = [
            'activeTab' => $tab,
            'user' => $this->userModel->find($userId),
            'activeServices' => $activeServices,
            'inactiveServices' => $inactiveServices,
            'bookingItems' => $bookingItems,
            'month' => date('m'), // Default to the current month
            'year' => date('Y'),  // Default to the current year
            'bookingsByDate' => [], // Adjust with actual data
        ];

        return view('profile_vendor', $data);
    }


    private function loadProfileData($user)
    {
        $userId = $user['id'];
        $data['user'] = $user;

        if ($user['role'] == 'vendor') {
            $serviceModel = new ServiceModel();
            $serviceImageModel = new ServiceImageModel();
            $bookingItemModel = new BookingItemModel();
            $chatMessageModel = new ChatMessageModel();

            // Fetch active services
            $activeServices = $serviceModel->where('vendor_id', $userId)->where('status', 'active')->findAll();

            // Fetch inactive services
            $inactiveServices = $serviceModel->where('vendor_id', $userId)->where('status !=', 'active')->findAll();

            // Load images for each service
            foreach ($activeServices as &$service) {
                $service['images'] = $serviceImageModel->where('service_id', $service['id'])->findAll();
            }

            foreach ($inactiveServices as &$service) {
                $service['images'] = $serviceImageModel->where('service_id', $service['id'])->findAll();
            }

            $bookingItems = $bookingItemModel
                ->select('
                booking_items.id as booking_item_id,
                booking_items.booking_id,
                booking_items.status as booking_item_status,
                booking_items.start_time as start_time,
                booking_items.end_time as end_time,
                bookings.*, 
                events.title as event_title, 
                events.date as event_date, 
                events.ceremony_type, 
                events.location, 
                services.title as service_title, 
                services.id as service_id,
                services.price,
                users.name as customer_name,
                users.id as customer_id
            ')
                ->join('bookings', 'bookings.id = booking_items.booking_id')
                ->join('events', 'events.id = bookings.event_id')
                ->join('services', 'services.id = booking_items.service_id')
                ->join('users', 'users.id = bookings.user_id')
                ->where('services.vendor_id', $userId)
                ->findAll();

            foreach ($bookingItems as &$item) {
                if (isset($item['start_time'])) {
                    $item['start_time'] = (new DateTime($item['start_time']))->format('H:i');
                }
                if (isset($item['end_time'])) {
                    $item['end_time'] = (new DateTime($item['end_time']))->format('H:i');
                }
            }

            foreach ($bookingItems as &$item) {
                if (isset($item['booking_id'])) {
                    $unreadMessages = $chatMessageModel
                        ->where('chat_room_id', $item['booking_id'])
                        ->where('receiver_id', $userId)
                        ->where('is_read', false)
                        ->countAllResults();
                    $item['has_new_messages'] = $unreadMessages > 0;
                } else {
                    $item['has_new_messages'] = false;
                }
            }

            // Fetch calendar data
            $bookingController = new \App\Controllers\BookingController();
            $calendarData = $bookingController->calendarView();

            $data['activeServices'] = $activeServices;
            $data['inactiveServices'] = $inactiveServices;
            $data['bookingItems'] = $bookingItems;
        } else {
            $eventModel = new EventModel();
            $bookingModel = new BookingModel();
            $bookingItemModel = new BookingItemModel();
            $serviceModel = new ServiceModel();
            $userModel = new UserModel();
            $chatMessageModel = new ChatMessageModel();
            $paymentsModel = new PaymentsModel(); // Add the PaymentsModel to fetch payment statuses
            $events = $eventModel->where('user_id', $userId)->findAll();

            $data['events'] = [];

            foreach ($events as $event) {
                $bookings = $bookingModel->where('event_id', $event['id'])->findAll();
                $eventBookingItems = [];

                foreach ($bookings as $booking) {
                    $bookingItems = $bookingItemModel
                        ->select('
                            booking_items.*, 
                            services.title, 
                            services.price, 
                            services.id as service_id,
                            booking_items.status,
                            services.vendor_id,
                            booking_items.booking_id
                        ')
                        ->join('services', 'services.id = booking_items.service_id')
                        ->where('booking_id', $booking['id'])
                        ->findAll();

                    foreach ($bookingItems as &$item) {
                        $vendor = $userModel->find($item['vendor_id']);
                        $item['vendor_name'] = $vendor['name'];

                        // Retrieve payment status for each booking
                        $payment = $paymentsModel->where('booking_id', $item['booking_id'])->first();
                        $item['payment_status'] = $payment ? $payment['payment_status'] : 'not paid';

                        // Check for unread messages

                        if (isset($item['booking_id'])) {
                            $unreadMessages = $chatMessageModel
                                ->where('chat_room_id', $item['booking_id'])
                                ->where('receiver_id', $userId)
                                ->where('is_read', false)
                                ->countAllResults();
                            $item['has_new_messages'] = $unreadMessages > 0;
                        } else {
                            $item['has_new_messages'] = false;
                        }
                    }

                    $eventBookingItems = array_merge($eventBookingItems, $bookingItems);
                }

                $event['bookingItems'] = $eventBookingItems;
                $data['events'][] = $event;
            }
        }

        return $data;
    }

    public function updateBookingStatus($bookingItemId)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to update this booking.');
        }

        $userId = session()->get('user_id');
        $bookingItemModel = new BookingItemModel();
        $bookingItem = $bookingItemModel->find($bookingItemId);

        if (!$bookingItem) {
            return redirect()->to('/profile')->with('error', 'Booking item not found.');
        }

        $serviceModel = new ServiceModel();
        $service = $serviceModel->find($bookingItem['service_id']);

        if (!$service || $service['vendor_id'] != $userId) {
            return redirect()->to('/profile')->with('error', 'You are not authorized to update this booking.');
        }

        $newStatus = $this->request->getPost('status');
        if (!in_array($newStatus, ['pending', 'accepted', 'rejected'])) {
            return redirect()->to('/profile')->with('error', 'Invalid status update.');
        }

        if (!$bookingItemModel->update($bookingItemId, ['status' => $newStatus])) {
            return redirect()->to('/profile')->with('error', 'Failed to update booking item status.');
        }

        $bookingId = $bookingItem['booking_id'];
        $allItems = $bookingItemModel->where('booking_id', $bookingId)->findAll();
        $allSameStatus = array_reduce($allItems, function ($carry, $item) use ($newStatus) {
            return $carry && ($item['status'] == $newStatus);
        }, true);

        if ($allSameStatus) {
            $bookingModel = new BookingModel();
            if (!$bookingModel->update($bookingId, ['status' => $newStatus])) {
                return redirect()->to('/profile')->with('error', 'Failed to update booking status.');
            }
        }

        return $this->index(); // Reuse the index method to load the updated profile
    }

    public function calendarData($year, $month)
    {
        // Validate and ensure $year and $month are correct
        if (!is_numeric($year) || !is_numeric($month)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid year or month']);
        }

        // Fetch bookings, calculate days, etc.
        // Same logic as before to ensure the correct data structure is returned

        return $this->response->setJSON([
            'month' => $month,
            'year' => $year,
            'month_name' => date('F', mktime(0, 0, 0, $month, 10)),
            'first_day_of_month' => date('N', strtotime("$year-$month-01")) - 1,
            'days' => $days, // calculated days with bookings
        ]);
    }
}
