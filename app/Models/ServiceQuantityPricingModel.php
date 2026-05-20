<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceQuantityPricingModel extends Model
{
    protected $table = 'services_quantity_pricing';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'service_id',
        'private_event_pricing_id',
        'unit_price',
        'min_quantity',
        'max_quantity',
        'unit_label',
    ];
}
