<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceLocationModel extends Model
{
    protected $table = 'services_locations';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'service_id',
        'service_location',
        'latitude',
        'longitude',
        'all_travel_included',
        'no_travel_limit',
        'free_coverage_radius',
        'paid_coverage_radius',
        'travel_fee_per_km',
    ];
}
