<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ServiceModel;
use App\Models\ServiceImageModel;
use App\Models\BookingModel;
use App\Models\BookingItemModel;
use App\Models\EventModel;
use App\Models\ChatMessageModel;
use App\Models\ChatRoomModel;
use App\Models\PaymentsModel;
use App\Models\CategoryModel;
use App\Models\CartModel;
use DateTime;

class Profile extends BaseController
{
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login')->with('error', 'You must be logged in to view your profile.');
        }

        $userId = session()->get('user_id');
        $role = session()->get('role');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/')->with('error', 'User not found.');
        }

        if ($role === 'vendor') {
            return $this->vendorDashboard($user);
        }

        return $this->customerDashboard($user);
    }

    private function vendorDashboard($user)
    {
        $userId = $user['id'];
        $serviceModel = new ServiceModel();
        $serviceImageModel = new ServiceImageModel();
        $bookingItemModel = new BookingItemModel();
        $bookingModel = new BookingModel();
        $chatMessageModel = new ChatMessageModel();

        $activeServices = $serviceModel
            ->where('vendor_id', $userId)
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->findAll();

        $vendorServiceIds = array_column($activeServices, 'id');

        // Pending booking requests count
        $pendingBookings = 0;
        $upcomingBookings = 0;
        $upcomingBookingsList = [];
        $pendingBookingsList = [];

        if (!empty($vendorServiceIds)) {
            $pendingBookings = $bookingItemModel
                ->whereIn('service_id', $vendorServiceIds)
                ->where('status', 'pending')
                ->countAllResults();

            $upcomingBookings = $bookingItemModel
                ->whereIn('service_id', $vendorServiceIds)
                ->where('status', 'accepted')
                ->countAllResults();

            // Upcoming bookings list (next 5)
            $upcomingBookingsList = $bookingItemModel
                ->select('booking_items.*, bookings.event_id, bookings.user_id, events.title as event_title, events.date as event_date, events.location, services.title as service_title, users.name as customer_name')
                ->join('bookings', 'bookings.id = booking_items.booking_id')
                ->join('events', 'events.id = bookings.event_id')
                ->join('services', 'services.id = booking_items.service_id')
                ->join('users', 'users.id = bookings.user_id')
                ->whereIn('booking_items.service_id', $vendorServiceIds)
                ->where('booking_items.status', 'accepted')
                ->orderBy('events.date', 'ASC')
                ->limit(5)
                ->findAll();

            $pendingBookingsList = $bookingItemModel
                ->select('booking_items.*, bookings.event_id, bookings.user_id, events.title as event_title, events.date as event_date, events.location, services.title as service_title, users.name as customer_name')
                ->join('bookings', 'bookings.id = booking_items.booking_id')
                ->join('events', 'events.id = bookings.event_id')
                ->join('services', 'services.id = booking_items.service_id')
                ->join('users', 'users.id = bookings.user_id')
                ->whereIn('booking_items.service_id', $vendorServiceIds)
                ->where('booking_items.status', 'pending')
                ->orderBy('events.date', 'ASC')
                ->limit(5)
                ->findAll();
        }

        // Unread messages count
        $unreadMessages = $chatMessageModel
            ->where('receiver_id', $userId)
            ->where('is_read', 0)
            ->countAllResults();

        // Services missing images
        $servicesMissingImages = 0;
        foreach ($activeServices as $svc) {
            $imgCount = $serviceImageModel->where('service_id', $svc['id'])->countAllResults();
            if ($imgCount === 0) {
                $servicesMissingImages++;
            }
        }

        // Service health checks
        $serviceHealthItems = [];
        foreach ($activeServices as $svc) {
            $hasImages = $serviceImageModel->where('service_id', $svc['id'])->countAllResults() > 0;
            $hasPrice = !empty($svc['price']);
            $hasDescription = !empty($svc['description']);
            $hasCancellation = !empty($svc['cancellation_policy']);

            $serviceHealthItems[] = [
                'title' => $svc['title'],
                'has_images' => $hasImages,
                'has_price' => $hasPrice,
                'has_description' => $hasDescription,
                'has_cancellation' => $hasCancellation,
            ];
        }

        $data = [
            'user' => $user,
            'activeServicesCount' => count($activeServices),
            'pendingBookings' => $pendingBookings,
            'upcomingBookings' => $upcomingBookings,
            'upcomingBookingsList' => $upcomingBookingsList,
            'pendingBookingsList' => $pendingBookingsList,
            'unreadMessages' => $unreadMessages,
            'servicesMissingImages' => $servicesMissingImages,
            'serviceHealthItems' => $serviceHealthItems,
            'currentTab' => 'main',
        ];

        return view('dashboard/vendor_main', $data);
    }

    private function customerDashboard($user)
    {
        $userId = $user['id'];
        $eventModel = new EventModel();
        $bookingModel = new BookingModel();
        $bookingItemModel = new BookingItemModel();
        $serviceModel = new ServiceModel();
        $userModel = new UserModel();
        $chatMessageModel = new ChatMessageModel();
        $paymentsModel = new PaymentsModel();
        $categoryModel = new CategoryModel();

        // Fetch user's events
        $events = $eventModel->where('user_id', $userId)->findAll();
        $enrichedEvents = [];

        $totalPendingRequests = 0;
        $totalAccepted = 0;
        $totalDeclined = 0;
        $totalConfirmed = 0;
        $totalAwaitingPayment = 0;
        $totalSpend = 0;
        $depositsPaid = 0;

        foreach ($events as $event) {
            $bookings = $bookingModel->where('event_id', $event['id'])->findAll();
            $eventBookingItems = [];
            $eventCost = 0;

            foreach ($bookings as $booking) {
                $items = $bookingItemModel
                    ->select('booking_items.*, services.title as service_title, services.price, services.vendor_id, services.category_id')
                    ->join('services', 'services.id = booking_items.service_id')
                    ->where('booking_id', $booking['id'])
                    ->findAll();

                foreach ($items as &$item) {
                    $vendor = $userModel->find($item['vendor_id']);
                    $item['vendor_name'] = $vendor ? $vendor['name'] : 'Unknown';

                    $payment = $paymentsModel->where('booking_id', $item['booking_id'])->first();
                    $item['payment_status'] = $payment ? $payment['payment_status'] : 'not paid';

                    if ($item['payment_status'] === 'succeeded') {
                        $depositsPaid += (float)($payment['amount_paid'] ?? 0);
                    }

                    $eventCost += (float)($item['price'] ?? 0);

                    switch ($item['status']) {
                        case 'pending': $totalPendingRequests++; break;
                        case 'accepted': $totalAccepted++; break;
                        case 'rejected': $totalDeclined++; break;
                        case 'confirmed': $totalConfirmed++; break;
                    }
                }

                $eventBookingItems = array_merge($eventBookingItems, $items);
            }

            $event['bookingItems'] = $eventBookingItems;
            $event['totalCost'] = $eventCost;
            $event['servicesBooked'] = count($eventBookingItems);
            $totalSpend += $eventCost;
            $enrichedEvents[] = $event;
        }

        // Unread messages
        $unreadMessages = $chatMessageModel
            ->where('receiver_id', $userId)
            ->where('is_read', 0)
            ->countAllResults();

        // Recent messages preview
        $recentMessages = $chatMessageModel
            ->select('chat_messages.*, users.name as sender_name')
            ->join('users', 'users.id = chat_messages.sender_id')
            ->where('chat_messages.receiver_id', $userId)
            ->orderBy('chat_messages.created_at', 'DESC')
            ->limit(5)
            ->findAll();

        // All categories for planning progress
        $categories = $categoryModel->findAll();

        $data = [
            'user' => $user,
            'events' => $enrichedEvents,
            'totalPendingRequests' => $totalPendingRequests,
            'totalAccepted' => $totalAccepted,
            'totalDeclined' => $totalDeclined,
            'totalConfirmed' => $totalConfirmed,
            'totalAwaitingPayment' => $totalAwaitingPayment,
            'unreadMessages' => $unreadMessages,
            'recentMessages' => $recentMessages,
            'totalSpend' => $totalSpend,
            'depositsPaid' => $depositsPaid,
            'categories' => $categories,
            'currentTab' => 'main',
        ];

        return view('dashboard/customer_main', $data);
    }

    public function main()
    {
        return $this->index();
    }

    public function services()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login')->with('error', 'Unauthorized access.');
        }

        $userId = session()->get('user_id');
        $serviceModel = new ServiceModel();
        $serviceImageModel = new ServiceImageModel();

        $activeServices = $this->fetchServices($serviceModel, $serviceImageModel, $userId, 'active');
        $inactiveServices = $this->fetchServices($serviceModel, $serviceImageModel, $userId, 'inactive');

        return view('profile/services', [
            'activeServices' => $activeServices,
            'inactiveServices' => $inactiveServices,
        ]);
    }

    public function bookings()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }
        $data = $this->loadProfileData($this->getUser());
        return view('profile/bookings', $data);
    }

    public function calendar()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }
        $data = $this->loadProfileData($this->getUser());
    }

    public function edit()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if ($this->request->getMethod() === 'POST') {
            $updateData = [
                'name' => $this->request->getVar('name'),
                'username' => $this->request->getVar('username'),
                'email' => $this->request->getVar('email'),
            ];
            $userModel->update($userId, $updateData);
            return redirect()->to('/profile')->with('success', 'Profile updated successfully.');
        }

        return view('profile_edit', ['user' => $user]);
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

            $data['activeServices'] = $this->fetchServices($serviceModel, $serviceImageModel, $userId, 'active');
            $data['inactiveServices'] = $this->fetchServices($serviceModel, $serviceImageModel, $userId, 'inactive');
            $data['bookingItems'] = $this->fetchBookingItems($bookingItemModel, $chatMessageModel, $userId);
        } else {
            $data['events'] = $this->fetchEvents($userId);
        }

        return $data;
    }

    private function fetchServices($serviceModel, $serviceImageModel, $userId, $status)
    {
        $services = $serviceModel
            ->where('vendor_id', $userId)
            ->where('status', $status)
            ->where('deleted_at', null)
            ->findAll();

        foreach ($services as &$service) {
            $images = $serviceImageModel->where('service_id', $service['id'])->findAll();
            foreach ($images as &$image) {
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
                    ->select('booking_items.*, services.title, users.name as vendor_name')
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

        if (!$bookingItem) {
            return redirect()->to('/profile')->with('error', 'Booking not found.');
        }

        $newStatus = $this->request->getPost('status');
        if (!in_array($newStatus, ['pending', 'accepted', 'rejected'])) {
            return redirect()->to('/profile')->with('error', 'Invalid status.');
        }

        $bookingItemModel->update($bookingItemId, ['status' => $newStatus]);
        return redirect()->to('/profile')->with('success', 'Booking status updated.');
    }
}
