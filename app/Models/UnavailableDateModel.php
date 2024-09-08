<?php namespace App\Models;

use CodeIgniter\Model;

class UnavailableDateModel extends Model
{
    protected $table            = 'unavailable_dates';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['vendor_id', 'date'];
}
