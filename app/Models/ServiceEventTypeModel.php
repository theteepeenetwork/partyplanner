<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceEventTypeModel extends Model
{
    protected $table = 'services_event_types';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'service_id',
        'event_type',
    ];

    /**
     * Retrieve all event types for a given service ID.
     *
     * @param int $serviceId
     * @return array
     */
    public function getEventTypesByServiceId(int $serviceId): array
    {
        return $this->where('service_id', $serviceId)->findAll();
    }
}
