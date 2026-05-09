<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceCancellationPolicyModel extends Model
{
    protected $table = 'services_cancellation_policies';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'service_id',
        'cancellation_policy',
    ];
}
