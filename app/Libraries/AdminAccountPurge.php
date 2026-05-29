<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Transactional cascade deletes for admin moderation.
 */
class AdminAccountPurge
{
    public function __construct(protected BaseConnection $db)
    {
    }

    /**
     * Remove an event and dependent rows (basket lines, bookings, payments).
     */
    public function purgeEvent(int $eventId): void
    {
        $this->db->table('event_basket_items')->where('event_id', $eventId)->delete();

        $bookingIds = $this->db->table('bookings')->select('id')->where('event_id', $eventId)->get()->getResultArray();
        $ids        = array_column($bookingIds, 'id');
        if ($ids !== []) {
            $this->purgeBookingsByIds($ids);
        }

        $this->db->table('events')->where('id', $eventId)->delete();
    }

    /**
     * @param list<int> $bookingIds
     */
    public function purgeBookingsByIds(array $bookingIds): void
    {
        $bookingIds = array_values(array_unique(array_map('intval', $bookingIds)));
        if ($bookingIds === []) {
            return;
        }

        $this->db->table('payments')->whereIn('booking_id', $bookingIds)->delete();
        $this->db->table('booking_items')->whereIn('booking_id', $bookingIds)->delete();
        $this->db->table('bookings')->whereIn('id', $bookingIds)->delete();
    }

    /**
     * After deleting booking_items, remove bookings that have no items left.
     *
     * @param list<int> $bookingIds
     */
    public function purgeEmptyBookings(array $bookingIds): void
    {
        foreach (array_unique(array_map('intval', $bookingIds)) as $bid) {
            $n = $this->db->table('booking_items')->where('booking_id', $bid)->countAllResults();
            if ($n === 0) {
                $this->purgeBookingsByIds([$bid]);
            }
        }
    }

    /**
     * Delete all chat messages and rooms returned by the given query callable.
     *
     * @param callable $roomIdQuery Callable that returns a query result with an `id` column.
     * @return void
     */
    public function deleteChatRoomsByQuery(callable $roomIdQuery): void
    {
        $rooms = $roomIdQuery()->getResultArray();
        $rids  = array_column($rooms, 'id');
        if ($rids === []) {
            return;
        }
        $this->db->table('chat_messages')->whereIn('chat_room_id', $rids)->delete();
        $this->db->table('chat_rooms')->whereIn('id', $rids)->delete();
    }

    /**
     * Remove filesystem images for a service then DB rows for service_images.
     */
    public function deleteServiceImageFiles(int $serviceId): void
    {
        $rows = $this->db->table('service_images')->where('service_id', $serviceId)->get()->getResultArray();
        foreach ($rows as $row) {
            if (! empty($row['image_path'])) {
                @unlink(ROOTPATH . 'public/' . $row['image_path']);
            }
            if (! empty($row['thumbnail_path'])) {
                @unlink(ROOTPATH . 'public/' . $row['thumbnail_path']);
            }
        }
        $this->db->table('service_images')->where('service_id', $serviceId)->delete();
    }

    /**
     * Delete dependent rows for a service (not the services row itself).
     *
     * @return list<int> Booking IDs touched by deleted booking_items
     */
    public function detachServiceFromBookingsAndRelated(int $serviceId): array
    {
        $bItems = $this->db->table('booking_items')->select('booking_id')->where('service_id', $serviceId)->get()->getResultArray();
        $bids   = array_column($bItems, 'booking_id');
        $this->db->table('booking_items')->where('service_id', $serviceId)->delete();

        $this->db->table('favourites')->where('service_id', $serviceId)->delete();
        $this->db->table('carts')->where('service_id', $serviceId)->delete();
        $this->db->table('event_basket_items')->where('service_id', $serviceId)->delete();

        $this->deleteChatRoomsByQuery(fn () => $this->db->table('chat_rooms')->select('id')->where('service_id', $serviceId)->get());

        return array_values(array_unique(array_map('intval', $bids)));
    }

    /**
     * Delete all pricing, availability, and metadata rows belonging to a service, excluding the service row itself.
     *
     * @param int $serviceId The service primary key.
     * @return void
     */
    public function purgeServiceChildren(int $serviceId): void
    {
        $this->db->table('services_service_tags')->where('service_id', $serviceId)->delete();
        $this->db->table('services_event_types')->where('service_id', $serviceId)->delete();
        $this->db->table('services_optional_extras')->where('service_id', $serviceId)->delete();
        $this->db->table('services_locations')->where('service_id', $serviceId)->delete();
        $this->db->table('service_availability')->where('service_id', $serviceId)->delete();
        $this->db->table('service_time_blocks')->where('service_id', $serviceId)->delete();
        $this->db->table('service_public_event_data')->where('service_id', $serviceId)->delete();
        $this->db->table('services_public_event_pricing')->where('service_id', $serviceId)->delete();
        $this->db->table('services_corporate_event_pricing')->where('service_id', $serviceId)->delete();
        $this->db->table('services_cancellation_policies')->where('service_id', $serviceId)->delete();

        $this->db->table('services_guest_based_pricing')->where('service_id', $serviceId)->delete();
        $this->db->table('services_custom_duration_pricing')->where('service_id', $serviceId)->delete();
        $this->db->table('services_tiered_packages_pricing')->where('service_id', $serviceId)->delete();
        $this->db->table('services_quantity_pricing')->where('service_id', $serviceId)->delete();
        $this->db->table('services_private_event_pricing')->where('service_id', $serviceId)->delete();

        $this->db->table('unavailable_dates')->where('service_id', $serviceId)->delete();

        $this->deleteServiceImageFiles($serviceId);
    }

    /**
     * Completely remove a service and all dependent data including bookings, images, and pricing.
     *
     * @param int $serviceId The service primary key.
     * @return void
     */
    public function purgeServiceFully(int $serviceId): void
    {
        $touchedBookings = $this->detachServiceFromBookingsAndRelated($serviceId);
        $this->purgeEmptyBookings($touchedBookings);
        $this->purgeServiceChildren($serviceId);

        $row = $this->db->table('services')->select('image')->where('id', $serviceId)->get()->getRowArray();
        if ($row && ! empty($row['image'])) {
            @unlink(ROOTPATH . 'public/' . $row['image']);
        }

        $this->db->table('services')->where('id', $serviceId)->delete();
    }

    /**
     * Delete a customer account and all associated events, bookings, basket items, and chat rooms.
     *
     * @param int $userId The customer user primary key.
     * @return void
     */
    public function purgeCustomer(int $userId): void
    {
        $eventRows = $this->db->table('events')->select('id')->where('user_id', $userId)->get()->getResultArray();
        foreach ($eventRows as $er) {
            $this->purgeEvent((int) $er['id']);
        }

        $custBookings = $this->db->table('bookings')->select('id')->where('user_id', $userId)->get()->getResultArray();
        $this->purgeBookingsByIds(array_column($custBookings, 'id'));

        $this->db->table('event_basket_items')->where('user_id', $userId)->delete();
        $this->db->table('carts')->where('user_id', $userId)->delete();
        $this->db->table('favourites')->where('user_id', $userId)->delete();

        $this->deleteChatRoomsByQuery(fn () => $this->db->table('chat_rooms')->select('id')->where('customer_id', $userId)->get());

        $this->db->table('users')->where('id', $userId)->delete();
    }

    /**
     * Delete a vendor account and all associated services, chat rooms, and unavailability records.
     *
     * @param int $userId The vendor user primary key.
     * @return void
     */
    public function purgeVendor(int $userId): void
    {
        $svcRows = $this->db->table('services')->select('id')->where('vendor_id', $userId)->get()->getResultArray();
        foreach ($svcRows as $s) {
            $this->purgeServiceFully((int) $s['id']);
        }

        $this->deleteChatRoomsByQuery(fn () => $this->db->table('chat_rooms')->select('id')->where('vendor_id', $userId)->get());

        $this->db->table('unavailable_dates')->where('vendor_id', $userId)->delete();

        $evRows = $this->db->table('events')->select('id')->where('vendor_id', $userId)->get()->getResultArray();
        foreach ($evRows as $er) {
            $this->purgeEvent((int) $er['id']);
        }

        $this->db->table('event_basket_items')->where('vendor_id', $userId)->delete();

        $this->db->table('users')->where('id', $userId)->delete();
    }
}
