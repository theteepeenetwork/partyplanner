<?php
namespace App\Models;

use CodeIgniter\Model;

class BookingItemModel extends Model
{
    protected $table            = 'booking_items';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['booking_id', 'service_id', 'quantity'];
}

