<?php

namespace App\Libraries;

use App\Models\BookingItemModel;
use App\Models\UnavailableDateModel;
use Config\Database;

/**
 * Checks vendor/service availability for an event date — and, for time-based
 * (duration) services, for a specific time window within that date.
 *
 * Whole-date mode (no start/end passed) is unchanged: a vendor day-off or any
 * live booking on the date blocks it. Time-window mode books a *slot* instead:
 * two bookings for the same service only clash when their occupied windows
 * overlap, where each window is padded by the service's setup + breakdown
 * minutes (services.setup_minutes / breakdown_minutes, vendor-set in the
 * wizard) so back-to-back jobs leave room to pack down and set up. A live
 * booking with no recorded time is treated as taking the whole day (fail
 * closed — legacy bookings predate slot times).
 */
class ServiceAvailabilityChecker
{
    /**
     * @param int         $serviceId ID of the service to check.
     * @param int         $vendorId  ID of the vendor who owns the service.
     * @param string|null $eventDate Date string (any strtotime format), or null/'' to skip.
     * @param string|null $startTime Event start 'HH:MM'(:SS) — with $endTime, switches to slot mode.
     * @param string|null $endTime   Event end 'HH:MM'(:SS), setup/breakdown applied on top.
     *
     * @return list<string> Error messages (empty if available)
     */
    public function check(int $serviceId, int $vendorId, ?string $eventDate, ?string $startTime = null, ?string $endTime = null): array
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

        $reqStart = self::toMinutes($startTime);
        $reqEnd   = self::toMinutes($endTime);
        $slotMode = $reqStart !== null && $reqEnd !== null && $reqEnd > $reqStart;

        $rows = (new BookingItemModel())
            ->select('booking_items.start_time, booking_items.end_time')
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('events', 'events.id = bookings.event_id')
            ->where('booking_items.service_id', $serviceId)
            ->where('events.date', $dateOnly)
            ->whereNotIn('booking_items.status', ['rejected', 'cancelled'])
            ->findAll();

        if ($rows === []) {
            return $errors;
        }

        // Whole-date mode: any live booking on the date blocks it.
        if (! $slotMode) {
            $errors[] = 'This service already has a booking on your event date.';

            return $errors;
        }

        [$setup, $breakdown] = $this->buffers($serviceId);
        $winStart            = $reqStart - $setup;
        $winEnd              = $reqEnd + $breakdown;

        foreach ($rows as $row) {
            $bs = self::toMinutes($row['start_time'] ?? null);
            $be = self::toMinutes($row['end_time'] ?? null);

            // A live booking with no recorded slot takes the whole day.
            if ($bs === null || $be === null || $be <= $bs) {
                $errors[] = 'This service already has a booking on your event date.';

                return $errors;
            }

            if ($winStart < $be + $breakdown && $bs - $setup < $winEnd) {
                $errors[] = 'This service is already booked over your chosen time.';

                return $errors;
            }
        }

        return $errors;
    }

    /**
     * Service setup + breakdown minutes (0 when unset/absent). Guarded on the
     * column existing so slim test schemas without the logistics columns fall
     * back to no padding.
     *
     * @return array{0: int, 1: int}
     */
    private function buffers(int $serviceId): array
    {
        $db = Database::connect();
        if (! $db->fieldExists('setup_minutes', 'services')) {
            return [0, 0];
        }

        $row = $db->table('services')
            ->select('setup_minutes, breakdown_minutes')
            ->where('id', $serviceId)
            ->get()->getRowArray();

        return [
            max(0, (int) ($row['setup_minutes'] ?? 0)),
            max(0, (int) ($row['breakdown_minutes'] ?? 0)),
        ];
    }

    /**
     * 'HH:MM' or 'HH:MM:SS' → minutes since midnight, or null if not a time.
     */
    private static function toMinutes(?string $time): ?int
    {
        $time = trim((string) $time);
        if (! preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $time, $m)) {
            return null;
        }
        $h = (int) $m[1];
        $min = (int) $m[2];
        if ($h > 23 || $min > 59) {
            return null;
        }

        return $h * 60 + $min;
    }
}
