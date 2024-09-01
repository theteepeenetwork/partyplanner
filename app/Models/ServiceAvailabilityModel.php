<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceAvailabilityModel extends Model
{
    protected $table = 'service_availability';
    protected $primaryKey = 'id';
    protected $allowedFields = ['service_id', 'date', 'start_time', 'end_time', 'is_booked'];
}
