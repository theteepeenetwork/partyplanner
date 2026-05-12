<?php
namespace App\Controllers;

use App\Models\ChatRoomModel;
use App\Models\ChatMessageModel;
use App\Models\UserModel;
use App\Models\ServiceModel;
use App\Models\BookingItemModel;
use App\Libraries\ChatModeration;
use CodeIgniter\Controller;

class ChatController extends Controller
{
    public function startChat($userId, $serviceId)
    {
        $currentUserId = (int) session()->get('user_id');
        if (!$currentUserId) {
            return redirect()->to('/login')->with('error', 'Please log in to continue.');
        }

        $serviceModel = new ServiceModel();
        $service = $serviceModel->find((int) $serviceId);
        if (!$service) {
            return redirect()->back()->with('error', 'Service not found.');
        }

        $bookingItemModel = new BookingItemModel();

        // Determine roles and IDs
        if (session()->get('role') === 'vendor') {
            $vendorId = $currentUserId;
            $customerId = (int) $userId;
            if ((int) $service['vendor_id'] !== $vendorId) {
                return redirect()->back()->with('error', 'You can only start chats for your own services.');
            }
            if (!$bookingItemModel->customerHasEligibleBookingForService($customerId, (int) $serviceId)) {
                return redirect()->back()->with('error', 'Messaging is only available once the customer has booked this service.');
            }
        } else {
            $vendorId = (int) $userId;
            $customerId = $currentUserId;
            if ((int) $service['vendor_id'] !== $vendorId) {
                return redirect()->back()->with('error', 'Invalid vendor for this listing.');
            }
            if (!$bookingItemModel->customerHasEligibleBookingForService($customerId, (int) $serviceId)) {
                return redirect()->back()->with('error', 'Messaging is available after you have booked this service.');
            }
        }

        // Check if a chat room already exists between the vendor, customer, and service
        $chatRoomModel = new ChatRoomModel();
        $chatRoomId = $chatRoomModel->ensureRoom($vendorId, $customerId, (int) $serviceId);

        // Same inbox UI as the dashboard (avoids a separate legacy chat page).
        return redirect()->to('/profile/messages/' . $chatRoomId);
    }

    public function viewChat($chatRoomId)
    {
        $uid = (int) session()->get('user_id');
        if (!$uid) {
            return redirect()->to('/login');
        }

        $chatRoomModel = new ChatRoomModel();
        $room = $chatRoomModel->find((int) $chatRoomId);
        if (!$room || ((int) $room['customer_id'] !== $uid && (int) $room['vendor_id'] !== $uid)) {
            return redirect()->to('/profile/messages')->with('error', 'Conversation not found.');
        }

        return redirect()->to('/profile/messages/' . (int) $chatRoomId);
    }

    public function sendMessage()
    {
        $senderId = (int) session()->get('user_id');
        if (!$senderId) {
            return redirect()->to('/login')->with('error', 'Please log in to continue.');
        }

        $chatRoomId = (int) $this->request->getPost('chat_room_id');
        $message = $this->request->getPost('message');

        // Fetch chat room details to determine the receiver
        $chatRoomModel = new ChatRoomModel();
        $chatRoom = $chatRoomModel->find($chatRoomId);

        if (!$chatRoom) {
            return redirect()->back()->with('error', 'Chat room not found.');
        }

        if ((int) $chatRoom['customer_id'] !== $senderId && (int) $chatRoom['vendor_id'] !== $senderId) {
            return redirect()->back()->with('error', 'You are not part of this conversation.');
        }

        // Determine the receiver based on the sender and chat room participants
        if ($chatRoom['vendor_id'] == $senderId) {
            $receiverId = $chatRoom['customer_id'];
        } else {
            $receiverId = $chatRoom['vendor_id'];
        }


        // Ensure the receiver_id exists in the users table
        $userModel = new UserModel();
        $receiver = $userModel->find($receiverId);
        if (!$receiver) {
            return redirect()->back()->with('error', 'Receiver not found.');
        }

        $moderation = new ChatModeration();
        $row        = array_merge([
            'chat_room_id' => $chatRoomId,
            'sender_id'    => $senderId,
            'receiver_id'  => $receiverId,
            'is_read'      => false,
        ], $moderation->moderationFieldsForInsert((string) $message));

        $chatMessageModel = new ChatMessageModel();
        $chatMessageModel->insert($row);
        ChatModeration::refreshRoomModerationFlag($chatRoomId);

        $response = redirect()->to('/profile/messages/' . $chatRoomId);
        if (($row['moderation_status'] ?? '') === ChatModeration::STATUS_PENDING) {
            $response = $response->with(
                'moderation_warning',
                'Inappropriate language was detected. Your message was partially masked and flagged for admin review.'
            );
        }

        return $response;
    }

    public function checkNewMessages()
    {
        $userId = session()->get('user_id');
        $chatMessageModel = new ChatMessageModel();
        $newMessages = $chatMessageModel->where('receiver_id', $userId)->where('is_read', false)->countAllResults();

        return $this->response->setJSON(['newMessages' => $newMessages]);
    }
}