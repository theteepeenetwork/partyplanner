<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceGuestBasedPricingModel extends Model
{
    protected $table = 'services_guest_based_pricing'; // Correct table name
    protected $primaryKey = 'id'; // Assuming the primary key is 'id'

    protected $allowedFields = [
        'private_event_pricing_id',
        'min_guest',
        'max_guest',
        'guest_price'
    ];
}
