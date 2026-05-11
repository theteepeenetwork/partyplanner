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
use App\Models\FavouriteModel;
use DateTime;

class Profile extends BaseController
{
    private function requireLogin()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login')->with('error', 'You must be logged in.');
        }
        return null;
    }

    private function getUser()
    {
        $userId = session()->get('user_id');
        $userModel = new UserModel();
        return $userModel->find($userId);
    }

    // =========================================================
    // MAIN DASHBOARD
    // =========================================================

    public function index()
    {
        if ($r = $this->requireLogin()) return $r;
        $user = $this->getUser();
        if (!$user) return redirect()->to('/')->with('error', 'User not found.');

        if ($user['role'] === 'vendor') {
            return $this->vendorMain($user);
        }
        return $this->customerMain($user);
    }

    private function vendorMain($user)
    {
        $userId = $user['id'];
        $serviceModel = new ServiceModel();
        $serviceImageModel = new ServiceImageModel();
        $bookingItemModel = new BookingItemModel();
        $chatMessageModel = new ChatMessageModel();

        $activeServices = $serviceModel->where('vendor_id', $userId)->where('status', 'active')->where('deleted_at', null)->findAll();
        $vendorServiceIds = array_column($activeServices, 'id');

        $pendingBookings = 0;
        $upcomingBookings = 0;
        $upcomingBookingsList = [];
        $pendingBookingsList = [];

        if (!empty($vendorServiceIds)) {
            $pendingBookings = $bookingItemModel->whereIn('service_id', $vendorServiceIds)->where('status', 'pending')->countAllResults();
            $upcomingBookings = $bookingItemModel->whereIn('service_id', $vendorServiceIds)->where('status', 'accepted')->countAllResults();

            $upcomingBookingsList = $bookingItemModel
                ->select('booking_items.*, bookings.event_id, bookings.user_id, events.title as event_title, events.`date` as event_date, events.location, events.event_type, services.title as service_title, users.name as customer_name', false)
                ->join('bookings', 'bookings.id = booking_items.booking_id')
                ->join('events', 'events.id = bookings.event_id')
                ->join('services', 'services.id = booking_items.service_id')
                ->join('users', 'users.id = bookings.user_id')
                ->whereIn('booking_items.service_id', $vendorServiceIds)
                ->where('booking_items.status', 'accepted')
                ->orderBy('events.`date`', 'ASC', false)->limit(5)->findAll();

            $pendingBookingsList = $bookingItemModel
                ->select('booking_items.*, bookings.event_id, bookings.user_id, events.title as event_title, events.`date` as event_date, events.location, events.event_type, services.title as service_title, users.name as customer_name', false)
                ->join('bookings', 'bookings.id = booking_items.booking_id')
                ->join('events', 'events.id = bookings.event_id')
                ->join('services', 'services.id = booking_items.service_id')
                ->join('users', 'users.id = bookings.user_id')
                ->whereIn('booking_items.service_id', $vendorServiceIds)
                ->where('booking_items.status', 'pending')
                ->orderBy('events.`date`', 'ASC', false)->limit(5)->findAll();
        }

        $unreadMessages = $chatMessageModel->where('receiver_id', $userId)->where('is_read', 0)->countAllResults();

        $servicesMissingImages = 0;
        $serviceHealthItems = [];
        foreach ($activeServices as $svc) {
            $hasImages = $serviceImageModel->where('service_id', $svc['id'])->countAllResults() > 0;
            if (!$hasImages) $servicesMissingImages++;
            $serviceHealthItems[] = [
                'title' => $svc['title'],
                'has_images' => $hasImages,
                'has_price' => !empty($svc['price']),
                'has_description' => !empty($svc['description']),
                'has_cancellation' => !empty($svc['cancellation_policy']),
            ];
        }

        return view('dashboard/vendor_main', [
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
        ]);
    }

    private function customerMain($user)
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

        $events = $eventModel->where('user_id', $userId)->findAll();
        $enrichedEvents = [];
        $totalPendingRequests = 0; $totalAccepted = 0; $totalDeclined = 0;
        $totalConfirmed = 0; $totalAwaitingPayment = 0; $totalSpend = 0; $depositsPaid = 0;

        foreach ($events as $event) {
            $bookings = $bookingModel->where('event_id', $event['id'])->findAll();
            $eventBookingItems = []; $eventCost = 0;

            foreach ($bookings as $booking) {
                $items = $bookingItemModel
                    ->select('booking_items.*, services.title as service_title, services.price as service_price, services.vendor_id, services.category_id')
                    ->join('services', 'services.id = booking_items.service_id')
                    ->where('booking_id', $booking['id'])->findAll();

                foreach ($items as &$item) {
                    $vendor = $userModel->find($item['vendor_id']);
                    $item['vendor_name'] = $vendor ? $vendor['name'] : 'Unknown';
                    $payment = $paymentsModel->where('booking_id', $booking['id'])->first();
                    $item['payment_status'] = $payment ? $payment['payment_status'] : 'not paid';
                    if ($payment && $payment['payment_status'] === 'succeeded') {
                        $depositsPaid += (float)($payment['amount_paid'] ?? 0);
                    }
                    $itemPrice = (float)($item['price'] ?? $item['service_price'] ?? 0);
                    $eventCost += $itemPrice;
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

        $unreadMessages = $chatMessageModel->where('receiver_id', $userId)->where('is_read', 0)->countAllResults();
        $recentMessages = $chatMessageModel
            ->select('chat_messages.*, users.name as sender_name')
            ->join('users', 'users.id = chat_messages.sender_id')
            ->where('chat_messages.receiver_id', $userId)
            ->orderBy('chat_messages.created_at', 'DESC')->limit(5)->findAll();

        return view('dashboard/customer_main', [
            'user' => $user, 'events' => $enrichedEvents,
            'totalPendingRequests' => $totalPendingRequests, 'totalAccepted' => $totalAccepted,
            'totalDeclined' => $totalDeclined, 'totalConfirmed' => $totalConfirmed,
            'totalAwaitingPayment' => $totalAwaitingPayment, 'unreadMessages' => $unreadMessages,
            'recentMessages' => $recentMessages, 'totalSpend' => $totalSpend,
            'depositsPaid' => $depositsPaid, 'categories' => $categoryModel->findAll(),
            'currentTab' => 'main',
        ]);
    }

    // =========================================================
    // VENDOR TABS
    // =========================================================

    public function services()
    {
        if ($r = $this->requireLogin()) return $r;
        $user = $this->getUser();
        $userId = $user['id'];
        $serviceModel = new ServiceModel();
        $serviceImageModel = new ServiceImageModel();
        $categoryModel = new CategoryModel();

        $allServices = $serviceModel->where('vendor_id', $userId)->where('deleted_at', null)->findAll();
        foreach ($allServices as &$svc) {
            $svc['images'] = $serviceImageModel->where('service_id', $svc['id'])->findAll();
            if (!empty($svc['category_id'])) {
                $cat = $categoryModel->find($svc['category_id']);
                $svc['category_name'] = $cat ? $cat['name'] : '';
            } else {
                $svc['category_name'] = '';
            }
        }

        return view('dashboard/vendor_services', [
            'user' => $user,
            'services' => $allServices,
            'currentTab' => 'services',
        ]);
    }

    public function vendorBookings()
    {
        if ($r = $this->requireLogin()) return $r;
        $user = $this->getUser();
        $userId = $user['id'];
        $serviceModel = new ServiceModel();
        $bookingItemModel = new BookingItemModel();

        $vendorServices = $serviceModel->where('vendor_id', $userId)->findAll();
        $vendorServiceIds = array_column($vendorServices, 'id');

        $bookingItems = [];
        if (!empty($vendorServiceIds)) {
            $bookingItems = $bookingItemModel
                ->select('booking_items.*, bookings.user_id, bookings.event_id, bookings.status as booking_status, bookings.created_at as request_date,
                          events.title as event_title, events.`date` as event_date, events.location, events.event_type,
                          services.title as service_title, services.price as service_price,
                          users.name as customer_name,
                          payments.payment_status, payments.amount_paid', false)
                ->join('bookings', 'bookings.id = booking_items.booking_id')
                ->join('events', 'events.id = bookings.event_id')
                ->join('services', 'services.id = booking_items.service_id')
                ->join('users', 'users.id = bookings.user_id')
                ->join('payments', 'payments.booking_id = booking_items.booking_id', 'left')
                ->whereIn('booking_items.service_id', $vendorServiceIds)
                ->orderBy('bookings.created_at', 'DESC')
                ->findAll();
        }

        return view('dashboard/vendor_bookings', [
            'user' => $user,
            'bookingItems' => $bookingItems,
            'currentTab' => 'bookings',
        ]);
    }

    public function vendorCalendar()
    {
        if ($r = $this->requireLogin()) return $r;
        $user = $this->getUser();

        return view('dashboard/vendor_calendar', [
            'user' => $user,
            'currentTab' => 'calendar',
        ]);
    }

    public function calendarData()
    {
        if (!session()->has('user_id')) return $this->response->setJSON([]);
        $userId = session()->get('user_id');
        $serviceModel = new ServiceModel();
        $bookingItemModel = new BookingItemModel();

        $vendorServices = $serviceModel->where('vendor_id', $userId)->findAll();
        $vendorServiceIds = array_column($vendorServices, 'id');
        if (empty($vendorServiceIds)) return $this->response->setJSON([]);

        $items = $bookingItemModel
            ->select('booking_items.*, events.title as event_title, events.`date` as event_date, events.location, events.event_type, services.title as service_title, users.name as customer_name', false)
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('events', 'events.id = bookings.event_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->join('users', 'users.id = bookings.user_id')
            ->whereIn('booking_items.service_id', $vendorServiceIds)
            ->whereIn('booking_items.status', ['pending', 'accepted', 'confirmed'])
            ->findAll();

        $calendarEvents = [];
        foreach ($items as $item) {
            $color = '#ffc107';
            if ($item['status'] === 'accepted' || $item['status'] === 'confirmed') $color = '#198754';
            $calendarEvents[] = [
                'title' => $item['event_title'] . ' - ' . $item['service_title'],
                'start' => $item['event_date'],
                'color' => $color,
                'extendedProps' => [
                    'customer' => $item['customer_name'],
                    'event_type' => $item['event_type'] ?? '',
                    'location' => $item['location'] ?? '',
                    'service' => $item['service_title'],
                    'status' => ucfirst($item['status']),
                ],
            ];
        }

        return $this->response->setJSON($calendarEvents);
    }

    // =========================================================
    // CUSTOMER TABS
    // =========================================================

    public function customerEvents()
    {
        if ($r = $this->requireLogin()) return $r;
        $user = $this->getUser();
        $userId = $user['id'];
        $eventModel = new EventModel();
        $bookingModel = new BookingModel();
        $bookingItemModel = new BookingItemModel();

        $events = $eventModel->where('user_id', $userId)->findAll();
        foreach ($events as &$event) {
            $bookings = $bookingModel->where('event_id', $event['id'])->findAll();
            $serviceCount = 0; $totalCost = 0;
            foreach ($bookings as $booking) {
                $items = $bookingItemModel->where('booking_id', $booking['id'])->findAll();
                $serviceCount += count($items);
                foreach ($items as $it) { $totalCost += (float)($it['price'] ?? 0); }
            }
            $event['servicesBooked'] = $serviceCount;
            $event['totalCost'] = $totalCost;
        }

        return view('dashboard/customer_events', [
            'user' => $user,
            'events' => $events,
            'currentTab' => 'events',
        ]);
    }

    public function customerBookings()
    {
        if ($r = $this->requireLogin()) return $r;
        $user = $this->getUser();
        $userId = $user['id'];
        $bookingModel = new BookingModel();
        $bookingItemModel = new BookingItemModel();
        $paymentsModel = new PaymentsModel();
        $userModel = new UserModel();

        $bookings = $bookingModel->where('user_id', $userId)->findAll();
        $allItems = [];

        foreach ($bookings as $booking) {
            $items = $bookingItemModel
                ->select('booking_items.*, services.title as service_title, services.vendor_id, services.price as service_price,
                          events.title as event_title, events.`date` as event_date, events.location', false)
                ->join('services', 'services.id = booking_items.service_id')
                ->join('bookings', 'bookings.id = booking_items.booking_id')
                ->join('events', 'events.id = bookings.event_id')
                ->where('booking_items.booking_id', $booking['id'])->findAll();

            foreach ($items as &$item) {
                $vendor = $userModel->find($item['vendor_id']);
                $item['vendor_name'] = $vendor ? $vendor['name'] : 'Unknown';
                $payment = $paymentsModel->where('booking_id', $booking['id'])->first();
                $item['payment_status'] = $payment ? $payment['payment_status'] : 'unpaid';
                $item['amount_paid'] = $payment ? $payment['amount_paid'] : 0;
                $itemPrice = (float)($item['price'] ?? $item['service_price'] ?? 0);
                $item['outstanding'] = max(0, $itemPrice - (float)$item['amount_paid']);
                $item['booking_id'] = $booking['id'];
            }
            $allItems = array_merge($allItems, $items);
        }

        return view('dashboard/customer_bookings', [
            'user' => $user,
            'bookingItems' => $allItems,
            'currentTab' => 'bookings',
        ]);
    }

    public function customerMessages()
    {
        if ($r = $this->requireLogin()) return $r;
        $user = $this->getUser();
        $userId = $user['id'];
        $chatRoomModel = new ChatRoomModel();
        $chatMessageModel = new ChatMessageModel();
        $userModel = new UserModel();
        $serviceModel = new ServiceModel();

        $rooms = $chatRoomModel->groupStart()
            ->where('customer_id', $userId)
            ->orWhere('vendor_id', $userId)
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->findAll();

        foreach ($rooms as &$room) {
            $peerId = ((int) $room['customer_id'] === (int) $userId) ? (int) $room['vendor_id'] : (int) $room['customer_id'];
            $peer = $userModel->find($peerId);
            $room['peer_name'] = $peer ? $peer['name'] : 'Unknown';
            $vendor = $userModel->find($room['vendor_id']);
            $room['vendor_name'] = $vendor ? $vendor['name'] : 'Unknown';
            $customer = $userModel->find($room['customer_id']);
            $room['customer_name'] = $customer ? $customer['name'] : 'Unknown';
            $service = $serviceModel->find($room['service_id']);
            $room['service_name'] = $service ? $service['title'] : '';
            $lastMsg = $chatMessageModel->where('chat_room_id', $room['id'])->orderBy('created_at', 'DESC')->first();
            $room['last_message'] = $lastMsg ? $lastMsg['message'] : '';
            $room['last_message_time'] = $lastMsg ? $lastMsg['created_at'] : $room['created_at'];
            $room['unread_count'] = $chatMessageModel->where('chat_room_id', $room['id'])->where('receiver_id', $userId)->where('is_read', 0)->countAllResults();
        }

        return view('dashboard/customer_messages', [
            'user' => $user,
            'rooms' => $rooms,
            'currentTab' => 'messages',
        ]);
    }

    /**
     * Customer: open or create the thread for a listing after an eligible booking exists.
     */
    public function startMessageForService($serviceId)
    {
        if ($r = $this->requireLogin()) return $r;
        $user = $this->getUser();
        $userId = (int) $user['id'];
        $serviceId = (int) $serviceId;

        if ($user['role'] === 'vendor') {
            return redirect()->to('/profile/messages')->with('error', 'Open a conversation from your bookings to message a customer.');
        }

        $serviceModel = new ServiceModel();
        $service = $serviceModel->find($serviceId);
        if (!$service) {
            return redirect()->to('/browse-services')->with('error', 'Service not found.');
        }

        if ((int) $service['vendor_id'] === $userId) {
            return redirect()->back()->with('error', 'You cannot message yourself.');
        }

        $bookingItemModel = new BookingItemModel();
        if (!$bookingItemModel->customerHasEligibleBookingForService($userId, $serviceId)) {
            return redirect()->back()->with('error', 'Messaging is available after you have booked this service.');
        }

        $chatRoomModel = new ChatRoomModel();
        $roomId = $chatRoomModel->ensureRoom((int) $service['vendor_id'], $userId, $serviceId);

        return redirect()->to('/profile/messages/' . $roomId);
    }

    /**
     * Vendor: open the thread for a booking line item (must own the service).
     */
    public function openThreadForBookingItem($bookingItemId)
    {
        if ($r = $this->requireLogin()) return $r;
        $user = $this->getUser();
        if ($user['role'] !== 'vendor') {
            return redirect()->to('/profile/messages')->with('error', 'Only vendors can use this link.');
        }

        $vendorId = (int) $user['id'];
        $bookingItemModel = new BookingItemModel();
        $row = $bookingItemModel
            ->select('booking_items.*, bookings.user_id as customer_user_id, services.vendor_id', false)
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->where('booking_items.id', (int) $bookingItemId)
            ->first();

        if (!$row || (int) $row['vendor_id'] !== $vendorId) {
            return redirect()->to('/profile/bookings')->with('error', 'Booking not found.');
        }

        if (in_array($row['status'], ['rejected', 'cancelled'], true)) {
            return redirect()->to('/profile/bookings')->with('error', 'Messaging is not available for cancelled or declined bookings.');
        }

        $chatRoomModel = new ChatRoomModel();
        $roomId = $chatRoomModel->ensureRoom(
            $vendorId,
            (int) $row['customer_user_id'],
            (int) $row['service_id']
        );

        return redirect()->to('/profile/messages/' . $roomId);
    }

    public function customerMessageThread($roomId)
    {
        if ($r = $this->requireLogin()) return $r;
        $user = $this->getUser();
        $userId = $user['id'];
        $chatRoomModel = new ChatRoomModel();
        $chatMessageModel = new ChatMessageModel();
        $userModel = new UserModel();
        $serviceModel = new ServiceModel();

        $room = $chatRoomModel->find($roomId);
        if (!$room || ((int) $room['customer_id'] !== (int) $userId && (int) $room['vendor_id'] !== (int) $userId)) {
            return redirect()->to('/profile/messages')->with('error', 'Conversation not found.');
        }

        $chatMessageModel->where('chat_room_id', $roomId)->where('receiver_id', $userId)->set('is_read', 1)->update();

        $messages = $chatMessageModel->where('chat_room_id', $roomId)->orderBy('created_at', 'ASC')->findAll();
        $peerId = ((int) $room['customer_id'] === (int) $userId) ? (int) $room['vendor_id'] : (int) $room['customer_id'];
        $peer = $userModel->find($peerId);
        $service = $serviceModel->find($room['service_id']);

        return view('dashboard/customer_message_thread', [
            'user' => $user,
            'room' => $room,
            'messages' => $messages,
            'peer_name' => $peer ? $peer['name'] : 'Unknown',
            'vendor_name' => $peer ? $peer['name'] : 'Unknown',
            'service_name' => $service ? $service['title'] : '',
            'currentTab' => 'messages',
        ]);
    }

    public function sendMessage()
    {
        if ($r = $this->requireLogin()) return $r;
        $userId = (int) session()->get('user_id');
        $chatMessageModel = new ChatMessageModel();
        $chatRoomModel = new ChatRoomModel();

        $roomId = (int) $this->request->getPost('chat_room_id');
        $message = $this->request->getPost('message');
        $room = $chatRoomModel->find($roomId);
        if (!$room) {
            return redirect()->to('/profile/messages');
        }

        if ((int) $room['customer_id'] !== $userId && (int) $room['vendor_id'] !== $userId) {
            return redirect()->to('/profile/messages')->with('error', 'You are not part of this conversation.');
        }

        $receiverId = ((int) $room['customer_id'] === $userId) ? (int) $room['vendor_id'] : (int) $room['customer_id'];

        $chatMessageModel->insert([
            'chat_room_id' => $roomId,
            'sender_id' => $userId,
            'receiver_id' => $receiverId,
            'message' => $message,
            'is_read' => 0,
        ]);

        return redirect()->to('/profile/messages/' . $roomId);
    }

    public function customerPayments()
    {
        if ($r = $this->requireLogin()) return $r;
        $user = $this->getUser();
        $userId = $user['id'];
        $bookingModel = new BookingModel();
        $paymentsModel = new PaymentsModel();
        $bookingItemModel = new BookingItemModel();
        $serviceModel = new ServiceModel();
        $eventModel = new EventModel();

        $bookings = $bookingModel->where('user_id', $userId)->findAll();
        $payments = [];
        $totalPaid = 0; $totalOutstanding = 0;

        foreach ($bookings as $booking) {
            $bookingPayments = $paymentsModel->where('booking_id', $booking['id'])->findAll();
            $event = $eventModel->find($booking['event_id']);
            $items = $bookingItemModel->select('booking_items.*, services.title as service_title, users.name as vendor_name')
                ->join('services', 'services.id = booking_items.service_id')
                ->join('users', 'users.id = services.vendor_id')
                ->where('booking_id', $booking['id'])->findAll();

            foreach ($bookingPayments as &$p) {
                $p['event_name'] = $event ? $event['title'] : '';
                $p['service_name'] = !empty($items) ? $items[0]['service_title'] : '';
                $p['vendor_name'] = !empty($items) ? $items[0]['vendor_name'] : '';
                $totalPaid += (float)($p['amount_paid'] ?? 0);
            }
            $payments = array_merge($payments, $bookingPayments);

            foreach ($items as $item) {
                $itemTotal = (float)($item['price'] ?? 0);
                $paidForBooking = 0;
                foreach ($bookingPayments as $bp) { $paidForBooking += (float)($bp['amount_paid'] ?? 0); }
                $totalOutstanding += max(0, $itemTotal - $paidForBooking);
            }
        }

        return view('dashboard/customer_payments', [
            'user' => $user,
            'payments' => $payments,
            'totalPaid' => $totalPaid,
            'totalOutstanding' => $totalOutstanding,
            'currentTab' => 'payments',
        ]);
    }

    public function customerFavourites()
    {
        if ($r = $this->requireLogin()) return $r;
        $user = $this->getUser();
        $userId = $user['id'];
        $favouriteModel = new FavouriteModel();
        $serviceModel = new ServiceModel();
        $serviceImageModel = new ServiceImageModel();
        $categoryModel = new CategoryModel();
        $userModel = new UserModel();

        $favs = $favouriteModel->where('user_id', $userId)->findAll();
        $favourites = [];

        foreach ($favs as $fav) {
            $service = $serviceModel->find($fav['service_id']);
            if (!$service) continue;
            $vendor = $userModel->find($service['vendor_id']);
            $images = $serviceImageModel->where('service_id', $service['id'])->where('is_primary', 1)->findAll();
            $category = !empty($service['category_id']) ? $categoryModel->find($service['category_id']) : null;

            $favourites[] = [
                'favourite_id' => $fav['id'],
                'service' => $service,
                'vendor_name' => $vendor ? $vendor['name'] : 'Unknown',
                'category_name' => $category ? $category['name'] : '',
                'image' => !empty($images) ? $images[0]['thumbnail_path'] : null,
            ];
        }

        return view('dashboard/customer_favourites', [
            'user' => $user,
            'favourites' => $favourites,
            'currentTab' => 'favourites',
        ]);
    }

    public function removeFavourite($id)
    {
        if ($r = $this->requireLogin()) return $r;
        $favouriteModel = new FavouriteModel();
        $fav = $favouriteModel->find($id);
        if ($fav && $fav['user_id'] == session()->get('user_id')) {
            $favouriteModel->delete($id);
        }
        return redirect()->to('/profile/favourites')->with('success', 'Removed from favourites.');
    }

    // =========================================================
    // SHARED ACTIONS
    // =========================================================

    public function updateBookingStatus($bookingItemId)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'Unauthorized.');
        }
        $bookingItemModel = new BookingItemModel();
        $bookingItem = $bookingItemModel->find($bookingItemId);
        if (!$bookingItem) return redirect()->to('/profile/bookings')->with('error', 'Booking not found.');

        $newStatus = $this->request->getPost('status');
        if (!in_array($newStatus, ['pending', 'accepted', 'rejected', 'confirmed', 'cancelled'])) {
            return redirect()->to('/profile/bookings')->with('error', 'Invalid status.');
        }

        $bookingItemModel->update($bookingItemId, ['status' => $newStatus]);
        return redirect()->to('/profile/bookings')->with('success', 'Booking status updated.');
    }

    public function edit()
    {
        if ($r = $this->requireLogin()) return $r;
        $user = $this->getUser();

        if ($this->request->getMethod() === 'POST') {
            $userModel = new UserModel();
            $userModel->update($user['id'], [
                'name' => $this->request->getVar('name'),
                'username' => $this->request->getVar('username'),
                'email' => $this->request->getVar('email'),
            ]);
            return redirect()->to('/profile')->with('success', 'Profile updated.');
        }

        return view('profile_edit', ['user' => $user]);
    }

    public function main()
    {
        return $this->index();
    }
}
