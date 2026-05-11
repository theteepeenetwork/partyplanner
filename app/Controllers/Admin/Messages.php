<?php

namespace App\Controllers\Admin;

use App\Models\ChatMessageModel;
use App\Models\ChatRoomModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Messages extends BaseAdminController
{
    public function index()
    {
        $db = db_connect();

        $keyword    = trim((string) $this->request->getGet('q'));
        $userId     = (int) $this->request->getGet('user_id');
        $vendorId   = (int) $this->request->getGet('vendor_id');
        $customerId = (int) $this->request->getGet('customer_id');
        $bookingId  = (int) $this->request->getGet('booking_id');

        $builder = $db->table('chat_rooms')
            ->select('chat_rooms.*, v.name as vendor_name, c.name as customer_name, s.title as service_title')
            ->join('users v', 'v.id = chat_rooms.vendor_id', 'left')
            ->join('users c', 'c.id = chat_rooms.customer_id', 'left')
            ->join('services s', 's.id = chat_rooms.service_id', 'left');

        if ($vendorId > 0) {
            $builder->where('chat_rooms.vendor_id', $vendorId);
        }
        if ($customerId > 0) {
            $builder->where('chat_rooms.customer_id', $customerId);
        }
        if ($userId > 0) {
            $builder->groupStart()
                ->where('chat_rooms.vendor_id', $userId)
                ->orWhere('chat_rooms.customer_id', $userId)
                ->groupEnd();
        }
        if ($bookingId > 0) {
            $b = $db->table('bookings')->where('id', $bookingId)->get()->getRowArray();
            if ($b) {
                $vendors = $db->table('booking_items')
                    ->select('services.vendor_id')
                    ->join('services', 'services.id = booking_items.service_id')
                    ->where('booking_items.booking_id', $bookingId)
                    ->get()
                    ->getResultArray();
                $vids = array_values(array_unique(array_column($vendors, 'vendor_id')));
                $builder->where('chat_rooms.customer_id', $b['user_id']);
                if ($vids !== []) {
                    $builder->whereIn('chat_rooms.vendor_id', $vids);
                }
            } else {
                $builder->where('1=0', null, false);
            }
        }
        if ($keyword !== '') {
            $roomIds = $db->table('chat_messages')
                ->select('chat_room_id')
                ->like('message', $keyword)
                ->groupBy('chat_room_id')
                ->get()
                ->getResultArray();
            $rids = array_column($roomIds, 'chat_room_id');
            if ($rids === []) {
                $builder->where('1=0', null, false);
            } else {
                $builder->whereIn('chat_rooms.id', $rids);
            }
        }

        $rooms = $builder->orderBy('chat_rooms.id', 'DESC')->get()->getResultArray();

        return $this->layout('admin/messages/index', [
            'title'       => 'Messages',
            'activeNav'   => 'messages',
            'rooms'       => $rooms,
            'q'           => $keyword,
            'user_id'     => $userId,
            'vendor_id'   => $vendorId,
            'customer_id' => $customerId,
            'booking_id'  => $bookingId,
        ]);
    }

    public function thread(int $roomId)
    {
        $db = db_connect();
        $room = $db->table('chat_rooms')
            ->select('chat_rooms.*, v.name as vendor_name, c.name as customer_name, s.title as service_title')
            ->join('users v', 'v.id = chat_rooms.vendor_id', 'left')
            ->join('users c', 'c.id = chat_rooms.customer_id', 'left')
            ->join('services s', 's.id = chat_rooms.service_id', 'left')
            ->where('chat_rooms.id', $roomId)
            ->get()
            ->getRowArray();

        if (! $room) {
            throw PageNotFoundException::forPageNotFound();
        }

        $messages = $db->table('chat_messages')
            ->where('chat_room_id', $roomId)
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResultArray();

        $context = [];
        $bk = $db->table('bookings')
            ->where('user_id', $room['customer_id'])
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
        $context['recent_bookings'] = $bk;

        return $this->layout('admin/messages/thread', [
            'title'     => 'Conversation #' . $roomId,
            'activeNav' => 'messages',
            'room'      => $room,
            'messages'  => $messages,
            'context'   => $context,
        ]);
    }

    public function deleteMessage(int $messageId)
    {
        $msgModel = new ChatMessageModel();
        $msg      = $msgModel->find($messageId);
        if (! $msg) {
            return redirect()->back()->with('error', 'Message not found.');
        }

        $msgModel->delete($messageId);

        return redirect()->back()->with('success', 'Message removed.');
    }

    public function flagRoom(int $roomId)
    {
        $roomModel = new ChatRoomModel();
        if (! $roomModel->find($roomId)) {
            return redirect()->back()->with('error', 'Room not found.');
        }
        $roomModel->update($roomId, ['flagged_for_review' => 1]);

        return redirect()->back()->with('success', 'Conversation flagged for review.');
    }

    public function unflagRoom(int $roomId)
    {
        $roomModel = new ChatRoomModel();
        if (! $roomModel->find($roomId)) {
            return redirect()->back()->with('error', 'Room not found.');
        }
        $roomModel->update($roomId, ['flagged_for_review' => 0]);

        return redirect()->back()->with('success', 'Flag cleared.');
    }
}
