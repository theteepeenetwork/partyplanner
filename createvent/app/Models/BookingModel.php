<?php
namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table = 'bookings';
    protected $allowedFields = ['user_id', 'event_id', 'status']; 
}