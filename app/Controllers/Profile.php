<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ServiceModel;
use App\Models\ServiceImageModel;
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

        if (!$user) {
            return redirect()->to('/')->with('error', 'User not found.');
        }

        return view('profile_vendor', ['user' => $user]);
    }

    public function main()
    {
        return view('profile/main'); // Replace with the actual view for the Main tab
    }


    public function services()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login')->with('error', 'Unauthorized access.');
        }

        $userId = session()->get('user_id');
        $serviceModel = new ServiceModel();
        $serviceImageModel = new ServiceImageModel();

        // Use the fetchServices method to retrieve services and their images
        $activeServices = $this->fetchServices($serviceModel, $serviceImageModel, $userId, 'active');
        $inactiveServices = $this->fetchServices($serviceModel, $serviceImageModel, $userId, 'inactive');

        // Pass the retrieved data to the view
        return view('profile/services', [
            'activeServices' => $activeServices,
            'inactiveServices' => $inactiveServices,
        ]);
    }


    public function bookings()
    {
        $data = $this->loadProfileData($this->getUser());
        return view('profile/bookings', $data);
    }

    public function calendar()
    {
        $data = $this->loadProfileData($this->getUser());
        //return view('profile/calendar', $data);
    }

    private function getUser()
    {
        $userId = session()->get('user_id');
        $userModel = new UserModel();
        return $userModel->find($userId);
    }






    private function loadProfileData($user)
    {
        $userId = $user['id'];
        $data['user'] = $user;

        if ($user['role'] === 'vendor') {
            $serviceModel = new ServiceModel();
            $serviceImageModel = new ServiceImageModel();
            $bookingItemModel = new BookingItemModel();
            $chatMessageModel = new ChatMessageModel();

            // Fetch active and inactive services
            $data['activeServices'] = $this->fetchServices($serviceModel, $serviceImageModel, $userId, 'active');
            $data['inactiveServices'] = $this->fetchServices($serviceModel, $serviceImageModel, $userId, 'inactive');

            // Fetch bookings for vendor
            $data['bookingItems'] = $this->fetchBookingItems($bookingItemModel, $chatMessageModel, $userId);

        } else {
            $data['events'] = $this->fetchEvents($userId);
        }

        return $data;
    }

    private function fetchServices($serviceModel, $serviceImageModel, $userId, $status)
    {
        // Fetch services based on status
        $services = $serviceModel
            ->where('vendor_id', $userId)
            ->where('status', $status)
            ->where('deleted_at', null) // Ensure `deleted_at` is NULL
            ->findAll();

        // Load images for each service
        foreach ($services as &$service) {
            //Die('1');
            $images = $serviceImageModel->where('service_id', $service['id'])->findAll();


            // Ensure paths are constructed correctly
            foreach ($images as &$image) {
                //Die('2');
                $image['thumbnail_path'] = base_url($image['thumbnail_path']);
                $image['image_path'] = base_url($image['image_path']);
            }

            $service['images'] = $images;
        }

        return $services;
    }

    private function fetchBookingItems($bookingItemModel, $chatMessageModel, $userId)
    {
        $bookingItems = $bookingItemModel
            ->select('
                booking_items.id as booking_item_id,
                booking_items.booking_id,
                booking_items.status as booking_item_status,
                booking_items.start_time,
                booking_items.end_time,
                bookings.*, 
                events.title as event_title, 
                events.date as event_date, 
                events.location, 
                services.title as service_title, 
                users.name as customer_name
            ')
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('events', 'events.id = bookings.event_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->join('users', 'users.id = bookings.user_id')
            ->where('services.vendor_id', $userId)
            ->findAll();

        foreach ($bookingItems as &$item) {
            $item['start_time'] = $this->formatTime($item['start_time']);
            $item['end_time'] = $this->formatTime($item['end_time']);
            $item['has_new_messages'] = $this->hasUnreadMessages($chatMessageModel, $item['booking_id'], $userId);
        }

        return $bookingItems;
    }

    private function fetchEvents($userId)
    {
        $eventModel = new EventModel();
        $bookingModel = new BookingModel();
        $bookingItemModel = new BookingItemModel();
        $userModel = new UserModel();

        $events = $eventModel->where('user_id', $userId)->findAll();

        foreach ($events as &$event) {
            $event['bookingItems'] = [];

            $bookings = $bookingModel->where('event_id', $event['id'])->findAll();
            foreach ($bookings as $booking) {
                $bookingItems = $bookingItemModel
                    ->select('
                        booking_items.*, 
                        services.title, 
                        users.name as vendor_name
                    ')
                    ->join('services', 'services.id = booking_items.service_id')
                    ->join('users', 'users.id = services.vendor_id')
                    ->where('booking_id', $booking['id'])
                    ->findAll();

                $event['bookingItems'] = array_merge($event['bookingItems'], $bookingItems);
            }
        }

        return $events;
    }

    private function formatTime($time)
    {
        return $time ? (new DateTime($time))->format('H:i') : null;
    }

    private function hasUnreadMessages($chatMessageModel, $bookingId, $userId)
    {
        return $chatMessageModel
            ->where('chat_room_id', $bookingId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->countAllResults() > 0;
    }

    public function updateBookingStatus($bookingItemId)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to update this booking.');
        }

        $userId = session()->get('user_id');
        $bookingItemModel = new BookingItemModel();
        $bookingItem = $bookingItemModel->find($bookingItemId);

        if (!$bookingItem || $bookingItem['service_id'] !== $userId) {
            return redirect()->to('/profile')->with('error', 'Unauthorized booking update.');
        }

        $newStatus = $this->request->getPost('status');
        if (!in_array($newStatus, ['pending', 'accepted', 'rejected'])) {
            return redirect()->to('/profile')->with('error', 'Invalid status.');
        }

        $bookingItemModel->update($bookingItemId, ['status' => $newStatus]);
        return $this->index();
    }

    /*public function calendar($year = null, $month = null)
    {
        $year = $year ?? date('Y');
        $month = $month ?? date('m');

        // Fetch bookings by date and any other data required
        $bookingsByDate = $this->getBookingsByDate($year, $month);

        return view('vendor_calendar', [
            'year' => $year,
            'month' => $month,
            'bookingsByDate' => $bookingsByDate,
        ]);
    }*/

    private function getBookingsByDate($year, $month)
    {
        // Example: Logic to fetch bookings grouped by date
        $bookingsModel = new BookingModel();
        $bookings = $bookingsModel
            ->select('date, start_time, end_time, service_title')
            ->where('YEAR(date)', $year)
            ->where('MONTH(date)', $month)
            ->findAll();

        $groupedBookings = [];
        foreach ($bookings as $booking) {
            $groupedBookings[$booking['date']][] = $booking;
        }

        return $groupedBookings;
    }

}
