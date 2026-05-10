<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceCustomDurationPricingModel extends Model
{
    protected $table = 'services_custom_duration_pricing';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'service_id',
        'private_event_pricing_id',
        'duration_type',
        'duration',
        'price',
    ];
}
