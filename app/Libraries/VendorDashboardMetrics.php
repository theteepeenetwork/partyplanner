<?php

namespace App\Libraries;

use Config\Database;

class VendorDashboardMetrics
{
    public static function profileViewsCount(int $vendorId): int
    {
        $db = Database::connect();
        if (! $db->tableExists('service_views')) {
            return 0;
        }

        $serviceIds = $db->table('services')
            ->select('id')
            ->where('vendor_id', $vendorId)
            ->where('deleted_at', null)
            ->get()
            ->getResultArray();

        if ($serviceIds === []) {
            return 0;
        }

        return (int) $db->table('service_views')
            ->whereIn('service_id', array_column($serviceIds, 'id'))
            ->countAllResults();
    }

    /**
     * Average hours from a customer message to the vendor's next reply (all rooms).
     */
    public static function averageResponseHours(int $vendorId): ?float
    {
        $db = Database::connect();
        if (! $db->tableExists('chat_messages') || ! $db->tableExists('chat_rooms')) {
            return null;
        }

        $rooms = $db->table('chat_rooms')->where('vendor_id', $vendorId)->get()->getResultArray();
        if ($rooms === []) {
            return null;
        }

        $deltas = [];
        foreach ($rooms as $room) {
            $customerId = (int) $room['customer_id'];
            $roomVendor = (int) $room['vendor_id'];
            $messages   = $db->table('chat_messages')
                ->where('chat_room_id', $room['id'])
                ->orderBy('created_at', 'ASC')
                ->get()
                ->getResultArray();

            $pendingCustomerAt = null;
            foreach ($messages as $msg) {
                $senderId = (int) $msg['sender_id'];
                if ($senderId === $customerId) {
                    $pendingCustomerAt = strtotime((string) $msg['created_at']);
                } elseif ($senderId === $roomVendor && $pendingCustomerAt !== null) {
                    $replyAt = strtotime((string) $msg['created_at']);
                    if ($replyAt >= $pendingCustomerAt) {
                        $deltas[] = $replyAt - $pendingCustomerAt;
                    }
                    $pendingCustomerAt = null;
                }
            }
        }

        if ($deltas === []) {
            return null;
        }

        return round(array_sum($deltas) / count($deltas) / 3600, 1);
    }
}
