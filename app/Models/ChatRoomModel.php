<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatRoomModel extends Model
{
    protected $table = 'chat_rooms';
    protected $primaryKey = 'id';
    protected $allowedFields = ['vendor_id', 'customer_id', 'created_at', 'service_id', 'flagged_for_review'];

    /**
     * Find or create a conversation for one listing (service) between vendor and customer.
     */
    public function ensureRoom(int $vendorId, int $customerId, int $serviceId): int
    {
        $existing = $this->where('vendor_id', $vendorId)
            ->where('customer_id', $customerId)
            ->where('service_id', $serviceId)
            ->first();

        if ($existing) {
            return (int) $existing['id'];
        }

        $this->insert([
            'vendor_id'   => $vendorId,
            'customer_id' => $customerId,
            'service_id'  => $serviceId,
        ]);

        return (int) $this->getInsertID();
    }
}
