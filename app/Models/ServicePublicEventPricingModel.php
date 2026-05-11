<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicePublicEventPricingModel extends Model
{
    protected $table            = 'services_public_event_pricing';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'service_id',
        'commission_percentage',
        'min_attendance',
        'max_attendance',
        'max_pitch_fee',
    ];
}
