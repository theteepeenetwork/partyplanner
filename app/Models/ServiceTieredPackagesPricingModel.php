<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceTieredPackagesPricingModel extends Model
{
    protected $table = 'services_tiered_packages_pricing';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'service_id',
        'private_event_pricing_id',
        'package_name',
        'package_description',
        'package_price',
    ];
}
