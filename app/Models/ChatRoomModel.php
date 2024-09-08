<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatRoomModel extends Model
{
    protected $table = 'chat_rooms';
    protected $primaryKey = 'id';
    protected $allowedFields = ['vendor_id', 'customer_id', 'created_at', 'service_id'];
}

 