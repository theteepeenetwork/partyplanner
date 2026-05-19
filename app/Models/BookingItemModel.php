<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingItemModel extends Model
{
    protected $table            = 'booking_items';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'booking_id',
        'service_id',
        'quantity',
        'package_name',
        'guest_count',
        'price',
        'status',
        'start_time',
        'end_time',
        'quote_breakdown',
        'quote_warnings',
        'extras_snapshot',
    ];

    /**
     * Whether the customer has at least one non-cancelled booking line for this service
     * (so vendor–customer messaging is allowed).
     */
    public function customerHasEligibleBookingForService(int $customerUserId, int $serviceId): bool
    {
        return $this->db->table($this->table)
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->where('bookings.user_id', $customerUserId)
            ->where('booking_items.service_id', $serviceId)
            ->whereNotIn('booking_items.status', ['rejected', 'cancelled'])
            ->countAllResults() > 0;
    }

    /**
     * @param list<int> $serviceIds
     * @return list<int> service ids the customer has an eligible booking for
     */
    public function eligibleServiceIdsForCustomer(int $customerUserId, array $serviceIds): array
    {
        if ($serviceIds === []) {
            return [];
        }

        $rows = $this->db->table($this->table)
            ->select('booking_items.service_id')
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->where('bookings.user_id', $customerUserId)
            ->whereIn('booking_items.service_id', $serviceIds)
            ->whereNotIn('booking_items.status', ['rejected', 'cancelled'])
            ->groupBy('booking_items.service_id')
            ->get()
            ->getResultArray();

        return array_map(static fn ($r) => (int) $r['service_id'], $rows);
    }
}