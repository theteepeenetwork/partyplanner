<?php namespace App\Models;

use CodeIgniter\Model;

class CartModel extends Model
{
    protected $table            = 'carts';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['user_id', 'service_id', 'event_id', 'quantity', 'start_time', 'end_time']; 
}