<?php
namespace App\Controllers;

use App\Models\ChatRoomModel;
use App\Models\ChatMessageModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class ChatController extends Controller
{
    public function startChat($userId, $serviceId)
    {
        $currentUserId = session()->get('user_id');

        // Determine roles and IDs
        if (session()->get('role') == 'vendor') {
            $vendorId = $currentUserId;
            $customerId = $userId;
        } else {
            $vendorId = $userId;
            $customerId = $currentUserId;
        }

        // Check if a chat room already exists between the vendor, customer, and service
        $chatRoomModel = new ChatRoomModel();
        $chatRoom = $chatRoomModel->where('vendor_id', $vendorId)
                                   ->where('customer_id', $customerId)
                                   ->where('service_id', $serviceId)
                                   ->first();

        // If the chat room does not exist, create a new one
        if (!$chatRoom) {
            $chatRoomModel->insert([
                'vendor_id' => $vendorId,
                'customer_id' => $customerId,
                'service_id' => $serviceId,
            ]);
            $chatRoomId = $chatRoomModel->getInsertID();
        } else {
            // Use the existing chat room
            $chatRoomId = $chatRoom['id'];
        }

        // Redirect to the chat view with the appropriate chat room ID
        return redirect()->to("/chat/view/$chatRoomId");
    }

    public function viewChat($chatRoomId)
    {
        $chatMessageModel = new ChatMessageModel();
        $messages = $chatMessageModel->where('chat_room_id', $chatRoomId)
                                     ->orderBy('created_at', 'ASC')
                                     ->findAll();

        return view('chat_view', ['messages' => $messages, 'chatRoomId' => $chatRoomId]);
    }

    public function sendMessage()
    {
        $chatRoomId = $this->request->getPost('chat_room_id');
        $message = $this->request->getPost('message');
        $senderId = session()->get('user_id');

        // Fetch chat room details to determine the receiver
        $chatRoomModel = new ChatRoomModel();
        $chatRoom = $chatRoomModel->find($chatRoomId);

        if (!$chatRoom) {
            return redirect()->back()->with('error', 'Chat room not found.');
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

        // Insert the message into the chat_messages table
        $chatMessageModel = new ChatMessageModel();
        $chatMessageModel->insert([
            'chat_room_id' => $chatRoomId,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $message,
            'is_read' => false,
        ]);

        return redirect()->to("/chat/view/$chatRoomId");
    }

    public function checkNewMessages()
    {
        $userId = session()->get('user_id');
        $chatMessageModel = new ChatMessageModel();
        $newMessages = $chatMessageModel->where('receiver_id', $userId)->where('is_read', false)->countAllResults();

        return $this->response->setJSON(['newMessages' => $newMessages]);
    }
}