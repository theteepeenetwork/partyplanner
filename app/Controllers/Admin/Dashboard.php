<?php

namespace App\Controllers\Admin;

use App\Models\BookingModel;
use App\Models\ServiceModel;
use App\Models\UserModel;

class Dashboard extends BaseAdminController
{
    public function index()
    {
        $db = db_connect();

        $userModel    = new UserModel();
        $serviceModel = new ServiceModel();
        $bookingModel = new BookingModel();

        $stats = [
            'customers' => $userModel->where('role', 'customer')->countAllResults(),
            'vendors'   => $userModel->where('role', 'vendor')->countAllResults(),
            'services'  => $serviceModel->where('deleted_at', null)->countAllResults(),
            'bookings'  => $bookingModel->countAllResults(),
        ];

        $recentBookings = $db->table('bookings')
            ->select('bookings.*, users.name as customer_name, users.email as customer_email, events.title as event_title')
            ->join('users', 'users.id = bookings.user_id', 'left')
            ->join('events', 'events.id = bookings.event_id', 'left')
            ->orderBy('bookings.created_at', 'DESC')
            ->limit(12)
            ->get()
            ->getResultArray();

        $recentMessages = $db->table('chat_messages')
            ->select('chat_messages.*, chat_rooms.id as room_id, chat_rooms.flagged_for_review')
            ->join('chat_rooms', 'chat_rooms.id = chat_messages.chat_room_id')
            ->orderBy('chat_messages.created_at', 'DESC')
            ->limit(15)
            ->get()
            ->getResultArray();

        $pendingBookings = $bookingModel->where('status', 'pending')->countAllResults();
        $inactiveServices = $serviceModel->where('deleted_at', null)->whereNotIn('status', ['active'])->countAllResults();
        $flaggedRooms     = $db->table('chat_rooms')->where('flagged_for_review', 1)->countAllResults();

        return $this->layout('admin/dashboard_home', [
            'title'            => 'Admin dashboard',
            'activeNav'        => 'dashboard',
            'stats'            => $stats,
            'recentBookings'   => $recentBookings,
            'recentMessages'   => $recentMessages,
            'pendingBookings'  => $pendingBookings,
            'inactiveServices' => $inactiveServices,
            'flaggedRooms'     => $flaggedRooms,
        ]);
    }
}
