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
use App\Libraries\ChatModeration;
use App\Libraries\CustomerEventSummary;
use App\Libraries\QuoteAnalyticsRecorder;
use App\Models\EventBasketItemModel;
use App\Models\VendorQuoteModel;
use App\Models\VendorQuoteSettingsModel;
use App\Models\VendorMessageTemplateModel;
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

    /**
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    private function requireCustomer()
    {
        if ($r = $this->requireLogin()) {
            return $r;
        }
        $user = $this->getUser();
        if (!$user) {
            return redirect()->to('/')->with('error', 'User not found.');
        }
        if (($user['role'] ?? '') === 'admin') {
            return redirect()->to('/admin');
        }
        if (($user['role'] ?? '') !== 'customer') {
            return redirect()->to('/profile')->with('error', 'This area is only available to customer accounts. Vendors should use the Services and Bookings tabs in the dashboard.');
        }

        return null;
    }

    /**
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    private function requireVendor()
    {
        if ($r = $this->requireLogin()) {
            return $r;
        }
        $user = $this->getUser();
        if (!$user) {
            return redirect()->to('/')->with('error', 'User not found.');
        }
        if (($user['role'] ?? '') === 'admin') {
            return redirect()->to('/admin');
        }
        if (($user['role'] ?? '') !== 'vendor') {
            return redirect()->to('/profile')->with('error', 'This area is only available to vendor accounts.');
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

        if (($user['role'] ?? '') === 'admin') {
            return redirect()->to('/admin');
        }

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
        $paymentsModel = new PaymentsModel();

        $activeServices = $serviceModel->where('vendor_id', $userId)->where('status', 'active')->where('deleted_at', null)->findAll();
        $vendorServiceIds = array_column($activeServices, 'id');

        $pendingBookings = 0;
        $upcomingBookings = 0;
        $upcomingBookingsList = [];
        $pendingBookingsList = [];
        $earningsThisMonth = 0.0;

        if (!empty($vendorServiceIds)) {
            $pendingBookings = $bookingItemModel->whereIn('service_id', $vendorServiceIds)->where('status', 'pending')->countAllResults();
            $upcomingBookings = $bookingItemModel->whereIn('service_id', $vendorServiceIds)->where('status', 'accepted')->countAllResults();

            $upcomingBookingsList = $bookingItemModel
                ->select('booking_items.*, bookings.event_id, bookings.user_id, events.title as event_title, events.`date` as event_date, events.location, events.event_type, services.title as service_title, users.name as customer_name, booking_items.created_at as item_created_at', false)
                ->join('bookings', 'bookings.id = booking_items.booking_id')
                ->join('events', 'events.id = bookings.event_id')
                ->join('services', 'services.id = booking_items.service_id')
                ->join('users', 'users.id = bookings.user_id')
                ->whereIn('booking_items.service_id', $vendorServiceIds)
                ->where('booking_items.status', 'accepted')
                ->orderBy('events.`date`', 'ASC', false)->limit(5)->findAll();

            $pendingBookingsList = $bookingItemModel
                ->select('booking_items.*, bookings.event_id, bookings.user_id, events.title as event_title, events.`date` as event_date, events.location, events.event_type, services.title as service_title, users.name as customer_name, booking_items.created_at as item_created_at', false)
                ->join('bookings', 'bookings.id = booking_items.booking_id')
                ->join('events', 'events.id = bookings.event_id')
                ->join('services', 'services.id = booking_items.service_id')
                ->join('users', 'users.id = bookings.user_id')
                ->whereIn('booking_items.service_id', $vendorServiceIds)
                ->where('booking_items.status', 'pending')
                ->orderBy('events.`date`', 'ASC', false)->limit(5)->findAll();

            // Calculate earnings this month: payments received for bookings of vendor's services
            $monthStart = date('Y-m-01 00:00:00');
            $monthEnd   = date('Y-m-t 23:59:59');
            $db = \Config\Database::connect();
            $earningsRow = $db->table('payments')
                ->select('SUM(payments.amount_paid) as total')
                ->join('booking_items', 'booking_items.booking_id = payments.booking_id')
                ->whereIn('booking_items.service_id', $vendorServiceIds)
                ->where('payments.payment_status', 'succeeded')
                ->where('payments.created_at >=', $monthStart)
                ->where('payments.created_at <=', $monthEnd)
                ->get()->getRowArray();
            $earningsThisMonth = (float) ($earningsRow['total'] ?? 0);
        }

        $unreadMessages = $chatMessageModel->where('receiver_id', $userId)->where('is_read', 0)->countAllResults();

        $servicesMissingImages = 0;
        $serviceHealthItems = [];
        foreach ($activeServices as $svc) {
            $hasImages = $serviceImageModel->where('service_id', $svc['id'])->countAllResults() > 0;
            if (!$hasImages) $servicesMissingImages++;
            $bookingsCount = $bookingItemModel->where('service_id', $svc['id'])->countAllResults();
            $serviceHealthItems[] = [
                'title'           => $svc['title'],
                'has_images'      => $hasImages,
                'has_price'       => !empty($svc['price']),
                'has_description' => !empty($svc['description']),
                'has_cancellation'=> !empty($svc['cancellation_policy']),
                'bookings_count'  => $bookingsCount,
            ];
        }

        // Add guest_count to pending and upcoming lists.
        foreach ($pendingBookingsList as &$row) {
            if (!isset($row['guest_count']) && !empty($row['event_id'])) {
                $ev = (new \App\Models\EventModel())->find($row['event_id']);
                $row['guest_count'] = $ev['guest_count'] ?? null;
            }
        }
        unset($row);

        // Payout summary — use this month's earnings as settled, outstanding accepted as pending.
        $pendingPayoutAmount = 0.0;
        if (!empty($vendorServiceIds)) {
            $acceptedItems = $bookingItemModel
                ->select('booking_items.price', false)
                ->whereIn('service_id', $vendorServiceIds)
                ->whereIn('status', ['accepted', 'confirmed'])
                ->findAll();
            foreach ($acceptedItems as $ai) {
                $pendingPayoutAmount += (float) ($ai['price'] ?? 0);
            }
        }
        $payouts = [
            'settled' => $earningsThisMonth,
            'pending' => $pendingPayoutAmount,
            'next'    => null,
        ];

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
            'earningsThisMonth' => $earningsThisMonth,
            'payouts' => $payouts,
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

        $events = $eventModel->where('user_id', $userId)->orderBy('date', 'ASC')->findAll();
        $summaryLib = new CustomerEventSummary();
        $enrichedEvents = $summaryLib->enrichMany($userId, $events);
        $totalPendingRequests = 0;
        $totalAccepted = 0;
        $totalDeclined = 0;
        $totalConfirmed = 0;
        $totalAwaitingPayment = 0;
        $totalSpend = 0.0;
        $depositsPaid = 0.0;

        foreach ($enrichedEvents as &$event) {
            $event['servicesBooked'] = $event['services_booked'] ?? 0;
            $event['totalCost'] = $event['total_cost'] ?? 0;
            $totalSpend += (float) $event['total_cost'];

            $bookings = $bookingModel->where('event_id', $event['id'])->findAll();
            $eventBookingItems = [];

            foreach ($bookings as $booking) {
                $items = $bookingItemModel
                    ->select('booking_items.*, services.title as service_title, services.price as service_price, services.vendor_id, services.category_id')
                    ->join('services', 'services.id = booking_items.service_id')
                    ->where('booking_id', $booking['id'])->findAll();

                $payment = $paymentsModel->where('booking_id', $booking['id'])->first();
                if ($payment && $payment['payment_status'] === 'succeeded') {
                    $depositsPaid += (float) ($payment['amount_paid'] ?? 0);
                }

                foreach ($items as &$item) {
                    $vendor = $userModel->find($item['vendor_id']);
                    $item['vendor_name'] = $vendor ? $vendor['name'] : 'Unknown';
                    $item['payment_status'] = $payment ? $payment['payment_status'] : 'not paid';
                    if (!$payment && in_array($item['status'], ['accepted', 'confirmed'], true)) {
                        $totalAwaitingPayment++;
                    }
                    switch ($item['status']) {
                        case 'pending':
                            $totalPendingRequests++;
                            break;
                        case 'accepted':
                            $totalAccepted++;
                            break;
                        case 'rejected':
                        case 'cancelled':
                            $totalDeclined++;
                            break;
                        case 'confirmed':
                            $totalConfirmed++;
                            break;
                    }
                }
                $eventBookingItems = array_merge($eventBookingItems, $items);
            }

            $event['bookingItems'] = $eventBookingItems;
        }
        unset($event);

        $unreadMessages = $chatMessageModel->where('receiver_id', $userId)->where('is_read', 0)->countAllResults();
        $recentMessages = $chatMessageModel
            ->select('chat_messages.*, users.name as sender_name')
            ->join('users', 'users.id = chat_messages.sender_id')
            ->where('chat_messages.receiver_id', $userId)
            ->orderBy('chat_messages.created_at', 'DESC')->limit(5)->findAll();

        // Compute days until each event and sort ascending (soonest first) for countdown cards.
        $today = new \DateTime('today');
        foreach ($enrichedEvents as &$ev) {
            if (!empty($ev['date'])) {
                $d = new \DateTime($ev['date']);
                $ev['days'] = (int) $today->diff($d)->days * ($d >= $today ? 1 : -1);
            } else {
                $ev['days'] = null;
            }
        }
        unset($ev);
        usort($enrichedEvents, static function ($a, $b) {
            $da = $a['days'] ?? PHP_INT_MAX;
            $db = $b['days'] ?? PHP_INT_MAX;
            return $da <=> $db;
        });

        return view('dashboard/customer_main', [
            'user' => $user, 'events' => $enrichedEvents,
            'totalPendingRequests' => $totalPendingRequests, 'totalAccepted' => $totalAccepted,
            'totalDeclined' => $totalDeclined, 'totalConfirmed' => $totalConfirmed,
            'totalAwaitingPayment' => $totalAwaitingPayment, 'unreadMessages' => $unreadMessages,
            'recentMessages' => $recentMessages, 'totalSpend' => $totalSpend,
            'depositsPaid' => $depositsPaid, 'categories' => $categoryModel->getRootCategories(),
            'recommendedCategories' => \App\Libraries\CustomerDashboardRecommendations::forUser($userId, $categoryModel),
            'currentTab' => 'main',
        ]);
    }

    // =========================================================
    // VENDOR TABS
    // =========================================================

    public function services()
    {
        if ($r = $this->requireVendor()) return $r;
        $user = $this->getUser();
        $userId = $user['id'];
        $serviceModel = new ServiceModel();
        $serviceImageModel = new ServiceImageModel();
        $categoryModel = new CategoryModel();

        $bookingItemModel = new BookingItemModel();
        $allServices = $serviceModel->where('vendor_id', $userId)->where('deleted_at', null)->findAll();
        foreach ($allServices as &$svc) {
            $svc['images']         = $serviceImageModel->where(['service_id' => $svc['id'], 'is_primary' => 1])->findAll();
            $svc['category_name']  = $categoryModel->getServiceCategoryLabel($svc);
            $svc['bookings_count'] = $bookingItemModel->where('service_id', $svc['id'])->countAllResults();
        }
        unset($svc);

        return view('dashboard/vendor_services', [
            'user'       => $user,
            'services'   => $allServices,
            'currentTab' => 'services',
        ]);
    }

    public function vendorBookings()
    {
        if ($r = $this->requireVendor()) return $r;
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
                          events.guest_count, events.event_setting, events.organiser_pitch_fee,
                          services.title as service_title, services.price as service_price, services.vendor_id,
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

            foreach ($bookingItems as &$bi) {
                $qd = json_decode($bi['quote_breakdown'] ?? '', true);
                $bi['quote_detail'] = is_array($qd) ? $qd : null;
                if ($bi['quote_detail'] === null && !empty($bi['quote_warnings'])) {
                    $w = json_decode($bi['quote_warnings'], true);
                    $bi['quote_detail'] = ['lines' => [], 'warnings' => is_array($w) ? $w : []];
                }
            }
            unset($bi);
        }

        return view('dashboard/vendor_bookings', [
            'user' => $user,
            'bookingItems' => $bookingItems,
            'currentTab' => 'bookings',
        ]);
    }

    public function vendorCalendar()
    {
        if ($r = $this->requireVendor()) return $r;
        $user = $this->getUser();

        return view('dashboard/vendor_calendar', [
            'user' => $user,
            'currentTab' => 'calendar',
        ]);
    }

    public function vendorEarnings()
    {
        if ($r = $this->requireVendor()) return $r;
        $user   = $this->getUser();
        $userId = (int) $user['id'];

        $serviceModel     = new ServiceModel();
        $bookingItemModel = new BookingItemModel();
        $paymentsModel    = new PaymentsModel();

        $services       = $serviceModel->where('vendor_id', $userId)->where('status', 'active')->findAll();
        $serviceIds     = array_column($services, 'id');

        $earningsThisMonth = 0.0;
        $settledTotal      = 0.0;
        $pendingTotal      = 0.0;
        $monthlyEarnings   = [];
        $payoutHistory     = [];

        if (! empty($serviceIds)) {
            $db = \Config\Database::connect();

            // This month
            $monthStart = date('Y-m-01 00:00:00');
            $monthEnd   = date('Y-m-t 23:59:59');
            $row = $db->table('payments')
                ->select('SUM(payments.amount_paid) as total')
                ->join('booking_items', 'booking_items.booking_id = payments.booking_id')
                ->whereIn('booking_items.service_id', $serviceIds)
                ->where('payments.payment_status', 'succeeded')
                ->where('payments.created_at >=', $monthStart)
                ->where('payments.created_at <=', $monthEnd)
                ->get()->getRowArray();
            $earningsThisMonth = (float) ($row['total'] ?? 0);

            // Last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $ts    = strtotime("-{$i} months");
                $mStart = date('Y-m-01 00:00:00', $ts);
                $mEnd   = date('Y-m-t 23:59:59', $ts);
                $mRow   = $db->table('payments')
                    ->select('SUM(payments.amount_paid) as total')
                    ->join('booking_items', 'booking_items.booking_id = payments.booking_id')
                    ->whereIn('booking_items.service_id', $serviceIds)
                    ->where('payments.payment_status', 'succeeded')
                    ->where('payments.created_at >=', $mStart)
                    ->where('payments.created_at <=', $mEnd)
                    ->get()->getRowArray();
                $monthlyEarnings[] = [
                    'month'  => date('M', $ts),
                    'amount' => (float) ($mRow['total'] ?? 0),
                ];
            }
            $settledTotal = array_sum(array_column($monthlyEarnings, 'amount'));

            // Pending = accepted booking items not yet paid
            $pendingItems = $bookingItemModel
                ->join('payments', 'payments.booking_id = booking_items.booking_id', 'left')
                ->whereIn('booking_items.service_id', $serviceIds)
                ->where('booking_items.status', 'accepted')
                ->where('(payments.id IS NULL OR payments.payment_status != \'succeeded\')', null, false)
                ->findAll();
            foreach ($pendingItems as $pi) {
                $pendingTotal += (float) ($pi['price'] ?? 0) * 0.15;
            }

            // Recent payments as payout history
            $payoutHistory = $db->table('payments')
                ->select('payments.amount_paid as amount, payments.payment_status as status, payments.created_at as date, payments.id as ref')
                ->join('booking_items', 'booking_items.booking_id = payments.booking_id')
                ->whereIn('booking_items.service_id', $serviceIds)
                ->orderBy('payments.created_at', 'DESC')
                ->limit(8)
                ->get()->getResultArray();
            foreach ($payoutHistory as &$ph) {
                $ph['status']    = $ph['status'] === 'succeeded' ? 'settled' : 'pending';
                $ph['date']      = date('d M Y', strtotime($ph['date']));
                $ph['reference'] = 'PAY-' . str_pad($ph['ref'], 6, '0', STR_PAD_LEFT);
            }
            unset($ph);
        }

        $allMonths = array_column($monthlyEarnings, 'amount');
        $avgMonthly = count($allMonths) > 0 ? array_sum($allMonths) / count($allMonths) : 0;

        return view('dashboard/vendor_earnings', [
            'user'              => $user,
            'earningsThisMonth' => $earningsThisMonth,
            'settledTotal'      => $settledTotal,
            'pendingTotal'      => $pendingTotal,
            'avgMonthly'        => $avgMonthly,
            'monthlyEarnings'   => $monthlyEarnings,
            'payoutHistory'     => $payoutHistory,
            'currentTab'        => 'earnings',
        ]);
    }

    public function vendorRequestDetail($bookingItemId)
    {
        if ($r = $this->requireVendor()) return $r;
        $user   = $this->getUser();
        $userId = (int) $user['id'];

        $bookingItemModel = new BookingItemModel();
        $serviceModel     = new ServiceModel();

        $item = $bookingItemModel
            ->select('booking_items.*, bookings.event_id, bookings.id as booking_id,
                      services.title as service_title, services.vendor_id,
                      events.title as event_title, events.date as event_date, events.location, events.guest_count,
                      users.name as customer_name,
                      payments.payment_status, payments.amount_paid', false)
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('events', 'events.id = bookings.event_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->join('users', 'users.id = bookings.user_id')
            ->join('payments', 'payments.booking_id = booking_items.booking_id', 'left')
            ->where('booking_items.id', (int) $bookingItemId)
            ->where('services.vendor_id', $userId)
            ->first();

        if (! $item) {
            return redirect()->to('/profile/bookings')->with('error', 'Request not found.');
        }

        $qd = json_decode($item['quote_breakdown'] ?? '', true);
        $item['quote_detail'] = is_array($qd) ? $qd : null;

        return view('dashboard/vendor_request_detail', [
            'user'       => $user,
            'item'       => $item,
            'currentTab' => 'bookings',
        ]);
    }

    public function hostProfile()
    {
        if ($r = $this->requireVendor()) {
            return $r;
        }
        $user = $this->getUser();

        if ($this->request->getMethod() === 'POST') {
            $userModel = new UserModel();

            $playsRaw = $this->request->getPost('host_plays') ?? '';
            $plays = array_values(array_filter(array_map('trim', explode(',', $playsRaw))));

            $updateData = [
                'host_bio'     => $this->request->getPost('host_bio'),
                'host_tagline' => $this->request->getPost('host_tagline'),
                'host_quote'   => $this->request->getPost('host_quote'),
                'host_plays'   => json_encode($plays),
            ];

            $photo = $this->request->getFile('host_photo');
            if ($photo && $photo->isValid() && ! $photo->hasMoved()) {
                if (! in_array(strtolower($photo->getExtension()), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                    return redirect()->to('/profile/host-profile')->with('error', 'Photo must be jpg, png, webp or gif.');
                }
                if ($photo->getSize() > 5 * 1024 * 1024) {
                    return redirect()->to('/profile/host-profile')->with('error', 'Photo must be under 5 MB.');
                }
                $newName = 'vendor_' . $user['id'] . '_' . time() . '.' . $photo->getExtension();
                $photo->move(ROOTPATH . 'public/uploads/vendor_photos/', $newName);
                $updateData['host_photo_path'] = 'uploads/vendor_photos/' . $newName;
            }

            $userModel->update($user['id'], $updateData);

            return redirect()->to('/profile/host-profile')->with('success', 'Host profile saved.');
        }

        $serviceModel      = new ServiceModel();
        $serviceImageModel = new ServiceImageModel();
        $hostServices      = $serviceModel->where('vendor_id', $user['id'])->where('status', 'active')->where('deleted_at', null)->findAll();
        foreach ($hostServices as &$svc) {
            $svc['images'] = $serviceImageModel->where(['service_id' => $svc['id'], 'is_primary' => 1])->findAll();
        }
        unset($svc);

        return view('dashboard/vendor_host_profile', [
            'user'       => $user,
            'services'   => $hostServices,
            'currentTab' => 'host-profile',
        ]);
    }

    public function calendarData()
    {
        if (!session()->has('user_id')) return $this->response->setJSON([]);
        $userModel = new UserModel();
        $user = $userModel->find((int) session()->get('user_id'));
        if (!$user || ($user['role'] ?? '') !== 'vendor') {
            return $this->response->setJSON([]);
        }
        $userId = session()->get('user_id');
        $serviceModel = new ServiceModel();
        $bookingItemModel = new BookingItemModel();

        $vendorServices = $serviceModel->where('vendor_id', $userId)->findAll();
        $vendorServiceIds = array_column($vendorServices, 'id');

        $calendarEvents = [];

        // Vendor-level blocked (unavailable) dates — rendered so they can be toggled off.
        $blockedDates = (new \App\Models\UnavailableDateModel())->where('vendor_id', $userId)->findAll();
        foreach ($blockedDates as $bd) {
            $calendarEvents[] = [
                'title'         => 'Blocked',
                'start'         => $bd['date'],
                'allDay'        => true,
                'color'         => '#6f4a6f',
                'extendedProps' => ['status' => 'Blocked', 'blocked' => true],
            ];
        }

        $items = empty($vendorServiceIds) ? [] : $bookingItemModel
            ->select('booking_items.*, events.title as event_title, events.`date` as event_date, events.location, events.event_type, services.title as service_title, users.name as customer_name', false)
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('events', 'events.id = bookings.event_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->join('users', 'users.id = bookings.user_id')
            ->whereIn('booking_items.service_id', $vendorServiceIds)
            ->whereIn('booking_items.status', ['pending', 'accepted', 'confirmed'])
            ->findAll();

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

    /**
     * Toggle a vendor-level blocked (unavailable) date from the calendar.
     * Inserts the date if free, removes it if already blocked. AJAX/JSON.
     * Returns the rotated CSRF hash so the SPA-style caller can chain requests.
     */
    public function toggleBlockDate()
    {
        if (!session()->has('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'error' => 'Not signed in']);
        }
        $user = (new UserModel())->find((int) session()->get('user_id'));
        if (!$user || ($user['role'] ?? '') !== 'vendor') {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'Vendors only']);
        }

        $date = trim((string) $this->request->getPost('date'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'error' => 'Invalid date', 'csrfHash' => csrf_hash()]);
        }

        $vendorId = (int) session()->get('user_id');
        $model    = new \App\Models\UnavailableDateModel();
        $existing = $model->where('vendor_id', $vendorId)->where('date', $date)->first();

        if ($existing) {
            $model->where('vendor_id', $vendorId)->where('date', $date)->delete();
            $blocked = false;
        } else {
            $model->insert(['vendor_id' => $vendorId, 'date' => $date]);
            $blocked = true;
        }

        return $this->response->setJSON([
            'ok'       => true,
            'blocked'  => $blocked,
            'date'     => $date,
            'csrfHash' => csrf_hash(),
        ]);
    }

    // =========================================================
    // CUSTOMER TABS
    // =========================================================

    public function customerEvents()
    {
        if ($r = $this->requireCustomer()) return $r;
        $user = $this->getUser();
        $userId = (int) $user['id'];
        $eventModel = new EventModel();
        $summary = new CustomerEventSummary();

        $events = $eventModel->where('user_id', $userId)->orderBy('date', 'ASC')->findAll();
        $events = $summary->enrichMany($userId, $events);
        $today  = new \DateTime('today');
        foreach ($events as &$event) {
            $event['servicesBooked'] = $event['services_booked'] ?? 0;
            $event['totalCost']      = $event['total_cost'] ?? 0;
            if (! empty($event['date'])) {
                $d             = new \DateTime($event['date']);
                $event['days'] = (int) $today->diff($d)->days * ($d >= $today ? 1 : -1);
            } else {
                $event['days'] = null;
            }
        }
        unset($event);
        usort($events, static fn ($a, $b) => ($a['days'] ?? PHP_INT_MAX) <=> ($b['days'] ?? PHP_INT_MAX));

        return view('dashboard/customer_events', [
            'user'       => $user,
            'events'     => $events,
            'currentTab' => 'events',
        ]);
    }

    public function customerEventDetail($eventId)
    {
        if ($r = $this->requireCustomer()) return $r;
        $user   = $this->getUser();
        $userId = (int) $user['id'];

        $eventModel       = new EventModel();
        $bookingModel     = new BookingModel();
        $bookingItemModel = new BookingItemModel();
        $paymentsModel    = new PaymentsModel();
        $userModel        = new UserModel();

        $event = $eventModel->find((int) $eventId);
        if (! $event || (int) ($event['user_id'] ?? 0) !== $userId) {
            return redirect()->to('/profile/events')->with('error', 'Event not found.');
        }

        $today = new \DateTime('today');
        if (! empty($event['date'])) {
            $d              = new \DateTime($event['date']);
            $event['days']  = (int) $today->diff($d)->days * ($d >= $today ? 1 : -1);
        }

        $bookings     = $bookingModel->where('event_id', $event['id'])->findAll();
        $liveBookings = [];
        foreach ($bookings as $booking) {
            $items = $bookingItemModel
                ->select('booking_items.*, services.title as service_title, services.category_id, users.name as vendor_name, payments.payment_status, payments.amount_paid', false)
                ->join('services', 'services.id = booking_items.service_id')
                ->join('users', 'users.id = services.vendor_id')
                ->join('payments', 'payments.booking_id = booking_items.booking_id', 'left')
                ->where('booking_items.booking_id', $booking['id'])
                ->findAll();
            foreach ($items as $item) {
                if (! in_array($item['status'] ?? '', ['rejected', 'cancelled'], true)) {
                    $liveBookings[] = array_merge($item, [
                        'event_id'       => $event['id'],
                        'event_title'    => $event['title'],
                        'event_date'     => $event['date'] ?? null,
                        'event_location' => $event['location'] ?? '',
                    ]);
                }
            }
        }

        $categoryModel      = new \App\Models\CategoryModel();
        $planningCategories = ['Venue', 'Catering', 'Photography', 'Entertainment', 'Flowers', 'Cake', 'Transport', 'Hair & beauty'];

        return view('dashboard/customer_event_detail', [
            'user'               => $user,
            'event'              => $event,
            'liveBookings'       => $liveBookings,
            'planningCategories' => $planningCategories,
            'currentTab'         => 'events',
        ]);
    }

    public function customerBookingDetail($bookingItemId)
    {
        if ($r = $this->requireCustomer()) return $r;
        $user   = $this->getUser();
        $userId = (int) $user['id'];

        $bookingItemModel = new BookingItemModel();
        $vqModel          = new VendorQuoteModel();

        $item = $bookingItemModel
            ->select('booking_items.*, bookings.event_id, bookings.id as booking_id, bookings.user_id,
                      services.title as service_title, services.vendor_id, services.price as service_price, services.id as service_id,
                      events.title as event_title, events.date as event_date, events.location as event_location,
                      users.name as vendor_name,
                      payments.payment_status, payments.amount_paid', false)
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('events', 'events.id = bookings.event_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->join('users', 'users.id = services.vendor_id')
            ->join('payments', 'payments.booking_id = booking_items.booking_id', 'left')
            ->where('booking_items.id', (int) $bookingItemId)
            ->where('bookings.user_id', $userId)
            ->first();

        if (! $item) {
            return redirect()->to('/profile/my-bookings')->with('error', 'Booking not found.');
        }

        $qd = json_decode($item['quote_breakdown'] ?? '', true);
        $item['quote_detail'] = is_array($qd) ? $qd : null;
        $item['pending_vendor_quote'] = $vqModel
            ->where('booking_item_id', (int) $item['id'])
            ->where('status', 'sent')
            ->orderBy('id', 'DESC')
            ->first();

        $reviewableIds = array_map(
            static fn ($r) => (int) $r['booking_item_id'],
            $bookingItemModel->reviewableItemsForCustomer($userId)
        );

        return view('dashboard/customer_booking_detail', [
            'user'          => $user,
            'item'          => $item,
            'reviewableIds' => $reviewableIds,
            'currentTab'    => 'bookings',
        ]);
    }

    public function setActiveEvent($eventId)
    {
        if ($r = $this->requireCustomer()) {
            return $r;
        }
        $userId = (int) session()->get('user_id');
        $eventModel = new EventModel();
        $event = $eventModel->find((int) $eventId);
        if (!$event || (int) ($event['user_id'] ?? 0) !== $userId) {
            return redirect()->to('/profile/events')->with('error', 'Event not found.');
        }

        session()->set('preferred_basket_event_id', (int) $eventId);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => true]);
        }

        $redirect = $this->request->getGet('redirect');
        if (is_string($redirect) && $redirect !== '' && str_starts_with($redirect, '/')) {
            return redirect()->to($redirect);
        }

        return redirect()->to('/browse-services?event_id=' . (int) $eventId);
    }

    public function customerBookings()
    {
        if ($r = $this->requireCustomer()) return $r;
        $user = $this->getUser();
        $userId = $user['id'];
        $bookingModel = new BookingModel();
        $bookingItemModel = new BookingItemModel();
        $paymentsModel = new PaymentsModel();
        $userModel = new UserModel();

        $filterEventId = (int) ($this->request->getGet('event_id') ?? 0);

        $builder = $bookingItemModel
            ->select('booking_items.*, bookings.event_id, bookings.id as booking_id, services.title as service_title, services.vendor_id, services.price as service_price,
                      events.title as event_title, events.`date` as event_date, events.location', false)
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('events', 'events.id = bookings.event_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->where('bookings.user_id', $userId)
            ->orderBy('events.date', 'ASC')
            ->orderBy('booking_items.created_at', 'DESC');

        if ($filterEventId > 0) {
            $builder->where('bookings.event_id', $filterEventId);
        }

        $allItems = $builder->findAll();
        $vqModel = new VendorQuoteModel();

        // Pre-calculate total price per booking so deposit can be split proportionally.
        $bookingTotals = [];
        foreach ($allItems as $bItem) {
            $bid = (int) $bItem['booking_id'];
            $bookingTotals[$bid] = ($bookingTotals[$bid] ?? 0.0) + (float) ($bItem['price'] ?? $bItem['service_price'] ?? 0);
        }

        // Cache payments by booking_id to avoid repeated queries.
        $paymentsCache = [];

        foreach ($allItems as &$item) {
            $vendor = $userModel->find($item['vendor_id']);
            $item['vendor_name'] = $vendor ? $vendor['name'] : 'Unknown';
            $bid = (int) $item['booking_id'];
            if (! array_key_exists($bid, $paymentsCache)) {
                $paymentsCache[$bid] = $paymentsModel->where('booking_id', $bid)->first();
            }
            $payment = $paymentsCache[$bid];
            $item['payment_status'] = $payment ? $payment['payment_status'] : 'unpaid';
            $itemPrice = (float) ($item['price'] ?? $item['service_price'] ?? 0);
            $bookingTotal = $bookingTotals[$bid] ?: $itemPrice;
            $totalPaid    = $payment ? (float) ($payment['amount_paid'] ?? 0) : 0.0;
            $item['amount_paid'] = ($bookingTotal > 0 && $totalPaid > 0)
                ? round($itemPrice / $bookingTotal * $totalPaid, 2)
                : 0.0;
            $item['outstanding'] = max(0.0, $itemPrice - $item['amount_paid']);
            $qd = json_decode($item['quote_breakdown'] ?? '', true);
            $item['quote_detail'] = is_array($qd) ? $qd : null;
            $item['pending_vendor_quote'] = $vqModel->where('booking_item_id', (int) $item['id'])
                ->where('status', 'sent')->orderBy('id', 'DESC')->first();
        }
        unset($item);

        $groupedByEvent = [];
        foreach ($allItems as $item) {
            $eid = (int) ($item['event_id'] ?? 0);
            if (!isset($groupedByEvent[$eid])) {
                $groupedByEvent[$eid] = [
                    'event_title' => $item['event_title'] ?? '',
                    'event_date' => $item['event_date'] ?? '',
                    'location' => $item['location'] ?? '',
                    'items' => [],
                ];
            }
            $groupedByEvent[$eid]['items'][] = $item;
        }

        $reviewableIds = array_map(
            static fn ($r) => (int) $r['booking_item_id'],
            $bookingItemModel->reviewableItemsForCustomer($userId)
        );

        return view('dashboard/customer_bookings', [
            'user' => $user,
            'bookingItems' => $allItems,
            'groupedByEvent' => $groupedByEvent,
            'filterEventId' => $filterEventId,
            'reviewableIds' => $reviewableIds,
            'currentTab' => 'bookings',
        ]);
    }

    public function customerMessages()
    {
        if ($r = $this->requireLogin()) return $r;
        $user = $this->getUser();
        if (!$user) {
            return redirect()->to('/')->with('error', 'User not found.');
        }
        if (($user['role'] ?? '') === 'admin') {
            return redirect()->to('/admin');
        }
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

        // Open the first room by default (or the one from the URL if called via customerMessageThread)
        $activeRoom = ! empty($rooms) ? $rooms[0] : null;
        $messages   = [];
        if ($activeRoom) {
            $chatMessageModel->where('chat_room_id', $activeRoom['id'])->where('receiver_id', $userId)->where('is_read', 0)->set(['is_read' => 1])->update();
            $messages = $chatMessageModel->where('chat_room_id', $activeRoom['id'])->orderBy('created_at', 'ASC')->findAll();
        }

        return view('dashboard/customer_messages', [
            'user'       => $user,
            'rooms'      => $rooms,
            'activeRoom' => $activeRoom,
            'messages'   => $messages,
            'currentTab' => 'messages',
        ]);
    }

    /**
     * Customer: open or create the thread for a listing after an eligible booking exists.
     */
    public function startMessageForService($serviceId)
    {
        if ($r = $this->requireCustomer()) return $r;
        $user = $this->getUser();
        $userId = (int) $user['id'];
        $serviceId = (int) $serviceId;

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
        if ($r = $this->requireVendor()) return $r;
        $user = $this->getUser();

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
        if (!$user) {
            return redirect()->to('/')->with('error', 'User not found.');
        }
        if (($user['role'] ?? '') === 'admin') {
            return redirect()->to('/admin');
        }
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
        $user = $this->getUser();
        if (!$user) {
            return redirect()->to('/')->with('error', 'User not found.');
        }
        if (($user['role'] ?? '') === 'admin') {
            return redirect()->to('/admin');
        }
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

        $moderation = new ChatModeration();
        $row        = array_merge([
            'chat_room_id' => $roomId,
            'sender_id'    => $userId,
            'receiver_id'  => $receiverId,
            'is_read'      => 0,
        ], $moderation->moderationFieldsForInsert((string) $message));

        $chatMessageModel->insert($row);
        ChatModeration::refreshRoomModerationFlag($roomId);

        $response = redirect()->to('/profile/messages/' . $roomId);
        if (($row['moderation_status'] ?? '') === ChatModeration::STATUS_PENDING) {
            $response = $response->with(
                'moderation_warning',
                'Inappropriate language was detected. Your message was partially masked and flagged for admin review.'
            );
        }

        return $response;
    }

    public function customerPayments()
    {
        if ($r = $this->requireCustomer()) return $r;
        $user = $this->getUser();
        $userId = $user['id'];
        $bookingModel = new BookingModel();
        $paymentsModel = new PaymentsModel();
        $bookingItemModel = new BookingItemModel();
        $serviceModel = new ServiceModel();
        $eventModel = new EventModel();

        $bookings = $bookingModel->where('user_id', $userId)->findAll();
        $payments = [];
        $paymentsByEvent = [];
        $totalPaid = 0;
        $totalOutstanding = 0;

        foreach ($bookings as $booking) {
            $bookingPayments = $paymentsModel->where('booking_id', $booking['id'])->findAll();
            $event = $eventModel->find($booking['event_id']);
            $eid = (int) ($booking['event_id'] ?? 0);
            $items = $bookingItemModel->select('booking_items.*, services.title as service_title, users.name as vendor_name')
                ->join('services', 'services.id = booking_items.service_id')
                ->join('users', 'users.id = services.vendor_id')
                ->where('booking_id', $booking['id'])->findAll();

            $itemsTotal = 0.0;
            foreach ($items as $item) {
                $itemsTotal += (float) ($item['price'] ?? 0);
            }
            $paidForBooking = 0.0;
            foreach ($bookingPayments as $bp) {
                $paidForBooking += (float) ($bp['amount_paid'] ?? 0);
            }
            $bookingOutstanding = max(0.0, $itemsTotal - $paidForBooking);
            $totalOutstanding += $bookingOutstanding;

            if (!isset($paymentsByEvent[$eid])) {
                $paymentsByEvent[$eid] = [
                    'event_title' => $event ? $event['title'] : 'Event',
                    'event_date' => $event['date'] ?? null,
                    'payments' => [],
                    'outstanding' => 0.0,
                    'paid' => 0.0,
                ];
            }
            $paymentsByEvent[$eid]['outstanding'] += $bookingOutstanding;

            foreach ($bookingPayments as &$p) {
                $p['event_name'] = $event ? $event['title'] : '';
                $p['service_name'] = !empty($items) ? $items[0]['service_title'] : '';
                $p['vendor_name'] = !empty($items) ? $items[0]['vendor_name'] : '';
                $amt = (float) ($p['amount_paid'] ?? 0);
                $totalPaid += $amt;
                $paymentsByEvent[$eid]['paid'] += $amt;
                $paymentsByEvent[$eid]['payments'][] = $p;
            }
            $payments = array_merge($payments, $bookingPayments);
        }

        return view('dashboard/customer_payments', [
            'user' => $user,
            'payments' => $payments,
            'paymentsByEvent' => $paymentsByEvent,
            'totalPaid' => $totalPaid,
            'totalOutstanding' => $totalOutstanding,
            'currentTab' => 'payments',
        ]);
    }

    public function customerFavourites()
    {
        if ($r = $this->requireCustomer()) return $r;
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
            $favourites[] = [
                'favourite_id' => $fav['id'],
                'service' => $service,
                'vendor_name' => $vendor ? $vendor['name'] : 'Unknown',
                'category_name' => $categoryModel->getServiceCategoryLabel($service),
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
        if ($r = $this->requireCustomer()) return $r;
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
        if ($r = $this->requireVendor()) return $r;
        $user = $this->getUser();
        $vendorId = (int) $user['id'];

        $bookingItemModel = new BookingItemModel();
        $bookingItem = $bookingItemModel
            ->select('booking_items.*, services.vendor_id', false)
            ->join('services', 'services.id = booking_items.service_id')
            ->where('booking_items.id', (int) $bookingItemId)
            ->first();

        if (!$bookingItem || (int) ($bookingItem['vendor_id'] ?? 0) !== $vendorId) {
            return redirect()->to('/profile/bookings')->with('error', 'Booking not found.');
        }

        $newStatus = $this->request->getPost('status');
        if (!in_array($newStatus, ['pending', 'accepted', 'rejected', 'confirmed', 'cancelled'])) {
            return redirect()->to('/profile/bookings')->with('error', 'Invalid status.');
        }

        $bookingItemModel->update($bookingItemId, ['status' => $newStatus]);
        if ($newStatus === 'accepted') {
            (new QuoteAnalyticsRecorder())->recordAccepted($vendorId, (int) $bookingItem['service_id'], false);
        }

        return redirect()->to('/profile/bookings')->with('success', 'Booking status updated.');
    }

    public function bulkUpdateBookingStatus()
    {
        if ($r = $this->requireVendor()) {
            return $r;
        }
        $user = $this->getUser();
        $ids = $this->request->getPost('booking_item_ids') ?? [];
        $status = $this->request->getPost('status');
        if (!is_array($ids) || !in_array($status, ['accepted', 'rejected'], true)) {
            return redirect()->to('/profile/bookings')->with('error', 'Invalid bulk action.');
        }

        $bookingItemModel = new BookingItemModel();
        $serviceModel = new ServiceModel();
        $updated = 0;
        foreach ($ids as $rawId) {
            $id = (int) $rawId;
            $row = $bookingItemModel
                ->select('booking_items.*, services.vendor_id')
                ->join('services', 'services.id = booking_items.service_id')
                ->where('booking_items.id', $id)
                ->first();
            if (!$row || (int) ($row['vendor_id'] ?? 0) !== (int) $user['id']) {
                continue;
            }
            $bookingItemModel->update($id, ['status' => $status]);
            $updated++;
        }

        return redirect()->to('/profile/bookings')->with('success', "Updated {$updated} booking(s).");
    }

    public function quoteSettings()
    {
        if ($r = $this->requireVendor()) {
            return $r;
        }
        $user = $this->getUser();
        $model = new VendorQuoteSettingsModel();
        $existing = $model->where('vendor_id', (int) $user['id'])->where('service_id', null)->first()
            ?: $model->where('vendor_id', (int) $user['id'])->where('service_id', 0)->first();

        if ($this->request->getMethod() === 'POST') {
            $allowed = $this->request->getPost('allowed_event_settings') ?? [];
            if (!is_array($allowed)) {
                $allowed = [];
            }
            $payload = [
                'vendor_id' => (int) $user['id'],
                'service_id' => null,
                'auto_accept_enabled' => $this->request->getPost('auto_accept_enabled') ? 1 : 0,
                'max_auto_accept_amount' => $this->request->getPost('max_auto_accept_amount') ?: null,
                'require_within_travel_radius' => $this->request->getPost('require_within_travel_radius') ? 1 : 0,
                'min_lead_days' => (int) ($this->request->getPost('min_lead_days') ?? 0),
                'allowed_event_settings' => json_encode(array_values($allowed)),
                'blackout_respect' => $this->request->getPost('blackout_respect') ? 1 : 0,
            ];
            if ($existing) {
                $model->update($existing['id'], $payload);
            } else {
                $model->insert($payload);
            }

            return redirect()->to('/profile/quote-settings')->with('success', 'Quote automation settings saved.');
        }

        return view('dashboard/vendor_quote_settings', [
            'user' => $user,
            'settings' => $existing,
            'currentTab' => 'bookings',
        ]);
    }

    public function quoteAnalytics()
    {
        if ($r = $this->requireVendor()) {
            return $r;
        }
        $user = $this->getUser();
        $db = \Config\Database::connect();
        $rows = [];
        if ($db->tableExists('quote_analytics_daily')) {
            $rows = $db->table('quote_analytics_daily')
                ->where('vendor_id', (int) $user['id'])
                ->orderBy('metric_date', 'DESC')
                ->limit(30)
                ->get()
                ->getResultArray();
        }

        return view('dashboard/vendor_quote_analytics', [
            'user' => $user,
            'metrics' => $rows,
            'currentTab' => 'bookings',
        ]);
    }

    public function vendorQuote($bookingItemId)
    {
        if ($r = $this->requireVendor()) {
            return $r;
        }
        $user = $this->getUser();
        $bookingItemModel = new BookingItemModel();
        $item = $bookingItemModel
            ->select('booking_items.*, services.vendor_id, services.title as service_title, events.title as event_title', false)
            ->join('services', 'services.id = booking_items.service_id')
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('events', 'events.id = bookings.event_id')
            ->where('booking_items.id', (int) $bookingItemId)
            ->first();

        if (!$item || (int) ($item['vendor_id'] ?? 0) !== (int) $user['id']) {
            return redirect()->to('/profile/bookings')->with('error', 'Booking not found.');
        }

        $vqModel = new VendorQuoteModel();
        $draft = $vqModel->where('booking_item_id', (int) $bookingItemId)
            ->whereIn('status', ['draft', 'sent'])
            ->orderBy('id', 'DESC')
            ->first();

        $templates = (new VendorMessageTemplateModel())->where('vendor_id', (int) $user['id'])->findAll();
        $original = json_decode($item['quote_breakdown'] ?? '', true);

        if ($this->request->getMethod() === 'POST') {
            $linesJson = $this->request->getPost('lines_json');
            $lines = is_string($linesJson) ? json_decode($linesJson, true) : [];
            if (!is_array($lines)) {
                $lines = [];
            }
            $total = 0.0;
            foreach ($lines as $ln) {
                $total += (float) ($ln['amount'] ?? 0);
            }
            $payload = [
                'booking_item_id' => (int) $bookingItemId,
                'vendor_id' => (int) $user['id'],
                'status' => 'draft',
                'lines' => json_encode($lines, JSON_UNESCAPED_UNICODE),
                'total' => round($total, 2),
                'vendor_notes' => $this->request->getPost('vendor_notes'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
            ];
            if ($draft) {
                $vqModel->update($draft['id'], $payload);
            } else {
                $vqModel->insert($payload);
            }

            return redirect()->to('/profile/vendor-quote/' . $bookingItemId)->with('success', 'Draft quote saved.');
        }

        return view('dashboard/vendor_quote_edit', [
            'user' => $user,
            'item' => $item,
            'draft' => $draft,
            'original' => is_array($original) ? $original : null,
            'templates' => $templates,
            'currentTab' => 'bookings',
        ]);
    }

    public function sendVendorQuote($bookingItemId)
    {
        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to('/profile/bookings');
        }
        if ($r = $this->requireVendor()) {
            return $r;
        }
        $vqModel = new VendorQuoteModel();
        $draft = $vqModel->where('booking_item_id', (int) $bookingItemId)
            ->where('status', 'draft')
            ->orderBy('id', 'DESC')
            ->first();
        if (!$draft) {
            return redirect()->to('/profile/vendor-quote/' . $bookingItemId)->with('error', 'No draft quote found.');
        }
        $vqModel->update($draft['id'], ['status' => 'sent']);

        return redirect()->to('/profile/bookings')->with('success', 'Revised quote sent to customer.');
    }

    public function acceptVendorQuote($bookingItemId)
    {
        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to('/profile/my-bookings');
        }
        if ($r = $this->requireCustomer()) {
            return $r;
        }
        $userId = (int) session()->get('user_id');
        $bookingItemModel = new BookingItemModel();
        $item = $bookingItemModel
            ->select('booking_items.*, bookings.user_id')
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->where('booking_items.id', (int) $bookingItemId)
            ->first();
        if (!$item || (int) ($item['user_id'] ?? 0) !== $userId) {
            return redirect()->to('/profile/my-bookings')->with('error', 'Booking not found.');
        }

        $vqModel = new VendorQuoteModel();
        $vq = $vqModel->where('booking_item_id', (int) $bookingItemId)
            ->where('status', 'sent')
            ->orderBy('id', 'DESC')
            ->first();
        if (!$vq) {
            return redirect()->to('/profile/my-bookings')->with('error', 'No revised quote to accept.');
        }

        $bookingItemModel->update((int) $bookingItemId, [
            'price' => $vq['total'],
            'quote_breakdown' => $vq['lines'],
            'status' => 'accepted',
        ]);
        $vqModel->update($vq['id'], ['status' => 'accepted']);

        return redirect()->to('/profile/my-bookings')->with('success', 'Revised quote accepted.');
    }

    public function edit()
    {
        if ($r = $this->requireLogin()) return $r;
        $user = $this->getUser();
        if (!$user) {
            return redirect()->to('/')->with('error', 'User not found.');
        }
        if (($user['role'] ?? '') === 'admin') {
            return redirect()->to('/admin');
        }

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
