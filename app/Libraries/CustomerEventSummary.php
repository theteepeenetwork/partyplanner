<?php

namespace App\Libraries;

use App\Models\BookingItemModel;
use App\Models\BookingModel;
use App\Models\EventBasketItemModel;

/**
 * Aggregates per-event planning stats for customer dashboards.
 */
class CustomerEventSummary
{
    /**
     * @param list<array<string,mixed>> $events
     * @return list<array<string,mixed>>
     */
    public function enrichMany(int $userId, array $events): array
    {
        if ($events === []) {
            return [];
        }

        $eventIds = array_map(static fn ($e) => (int) $e['id'], $events);
        $basketByEvent = $this->basketTotalsByEvent($userId, $eventIds);
        $bookingByEvent = $this->bookingTotalsByEvent($userId, $eventIds);

        $out = [];
        foreach ($events as $event) {
            $eid = (int) $event['id'];
            $event = array_merge($event, $basketByEvent[$eid] ?? $this->emptyBasketStats());
            $event = array_merge($event, $bookingByEvent[$eid] ?? $this->emptyBookingStats());
            $out[] = $event;
        }

        return $out;
    }

    /**
     * @param list<int> $eventIds
     * @return array<int, array<string,mixed>>
     */
    private function basketTotalsByEvent(int $userId, array $eventIds): array
    {
        $model = new EventBasketItemModel();
        $rows = $model->where('user_id', $userId)->whereIn('event_id', $eventIds)->findAll();
        $map = [];
        foreach ($rows as $row) {
            $eid = (int) $row['event_id'];
            if (!isset($map[$eid])) {
                $map[$eid] = $this->emptyBasketStats();
            }
            $map[$eid]['basket_count']++;
            $map[$eid]['basket_estimated'] += (float) ($row['estimated_total'] ?? 0);
            $map[$eid]['basket_deposit'] += (float) ($row['deposit_amount'] ?? 0);
        }

        return $map;
    }

    /**
     * @param list<int> $eventIds
     * @return array<int, array<string,mixed>>
     */
    private function bookingTotalsByEvent(int $userId, array $eventIds): array
    {
        $bookingModel = new BookingModel();
        $itemModel = new BookingItemModel();
        $bookings = $bookingModel->where('user_id', $userId)->whereIn('event_id', $eventIds)->findAll();

        $map = [];
        foreach ($bookings as $booking) {
            $eid = (int) $booking['event_id'];
            if (!isset($map[$eid])) {
                $map[$eid] = $this->emptyBookingStats();
            }
            $items = $itemModel->where('booking_id', $booking['id'])->findAll();
            foreach ($items as $it) {
                $map[$eid]['services_booked']++;
                $map[$eid]['total_cost'] += (float) ($it['price'] ?? 0);
                $status = $it['status'] ?? 'pending';
                if ($status === 'pending') {
                    $map[$eid]['pending_count']++;
                } elseif (in_array($status, ['accepted', 'confirmed'], true)) {
                    $map[$eid]['accepted_count']++;
                }
            }
        }

        return $map;
    }

    /**
     * @return array<string,mixed>
     */
    private function emptyBasketStats(): array
    {
        return [
            'basket_count' => 0,
            'basket_estimated' => 0.0,
            'basket_deposit' => 0.0,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function emptyBookingStats(): array
    {
        return [
            'services_booked' => 0,
            'total_cost' => 0.0,
            'pending_count' => 0,
            'accepted_count' => 0,
        ];
    }

    /**
     * @param list<array<string,mixed>> $events
     * @return array<string,mixed>|null
     */
    public function resolveActiveEvent(array $events, ?int $preferredId): ?array
    {
        if ($events === []) {
            return null;
        }
        if ($preferredId !== null) {
            foreach ($events as $e) {
                if ((int) $e['id'] === $preferredId) {
                    return $e;
                }
            }
        }

        return $events[0];
    }
}
