<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicePrivatePricingModel extends Model
{
    protected $table = 'services_private_event_pricing';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'service_id',
        'pricing_type',
    ];

    /**
     * Retrieve the pricing type for a given service ID.
     *
     * @param int $serviceId
     * @return string|null
     */
    public function getPricingTypeByServiceId(int $serviceId): ?string
    {
        $record = $this->where('service_id', $serviceId)->first();
        return $record ? $record['pricing_type'] : null;
    }
}
