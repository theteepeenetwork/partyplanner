<?php

namespace App\Models;

use CodeIgniter\Model;

class EventBasketItemModel extends Model
{
    protected $table = 'event_basket_items';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'event_id', 'user_id', 'service_id', 'vendor_id',
        'package_name', 'extras', 'quantity',
        'unit_price', 'deposit_amount', 'estimated_total',
        'notes', 'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
