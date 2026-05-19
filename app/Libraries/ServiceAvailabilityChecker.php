<?php

namespace App\Libraries;

use App\Models\BookingItemModel;
use App\Models\UnavailableDateModel;

/**
 * Checks vendor/service availability for an event date.
 */
class ServiceAvailabilityChecker
{
    /**
     * @return list<string> Error messages (empty if available)
     */
    public function check(int $serviceId, int $vendorId, ?string $eventDate): array
    {
        $errors = [];
        if ($eventDate === null || $eventDate === '') {
            return $errors;
        }

        $dateOnly = date('Y-m-d', strtotime($eventDate));

        $unavail = new UnavailableDateModel();
        $blocked = $unavail->where('vendor_id', $vendorId)->where('date', $dateOnly)->first();
        if ($blocked) {
            $errors[] = 'The vendor is unavailable on your event date.';
        }

        $bookingItemModel = new BookingItemModel();
        $conflict = $bookingItemModel
            ->select('booking_items.id')
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('events', 'events.id = bookings.event_id')
            ->where('booking_items.service_id', $serviceId)
            ->where('events.date', $dateOnly)
            ->whereNotIn('booking_items.status', ['rejected', 'cancelled'])
            ->first();

        if ($conflict) {
            $errors[] = 'This service already has a booking on your event date.';
        }

        return $errors;
    }
}
