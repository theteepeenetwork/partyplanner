<?php 

namespace App\Models;

use CodeIgniter\Model;

class ChatMessageModel extends Model
{
    protected $table = 'chat_messages';
    protected $primaryKey = 'id';
    protected $allowedFields = ['chat_room_id', 'sender_id', 'message', 'is_read', 'created_at', 'receiver_id'];
}