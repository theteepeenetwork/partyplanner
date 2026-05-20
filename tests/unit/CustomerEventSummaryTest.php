<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Libraries\CustomerEventSummary;
use App\Models\BookingItemModel;
use App\Models\BookingModel;
use App\Models\EventBasketItemModel;
use App\Models\EventModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class CustomerEventSummaryTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    public function testEnrichManyReturnsBasketAndBookingCountsForTwoEvents(): void
    {
        $userId = 9001;
        $now = date('Y-m-d H:i:s');

        $eventModel = new EventModel();
        $eventModel->insert([
            'user_id' => $userId,
            'title' => 'Event Alpha',
            'date' => date('Y-m-d', strtotime('+30 days')),
            'guest_count' => 50,
            'event_setting' => 'private',
            'status' => 'active',
            'created_at' => $now,
        ]);
        $eventModel->insert([
            'user_id' => $userId,
            'title' => 'Event Beta',
            'date' => date('Y-m-d', strtotime('+60 days')),
            'guest_count' => 80,
            'event_setting' => 'private',
            'status' => 'active',
            'created_at' => $now,
        ]);

        $events = $eventModel->where('user_id', $userId)->orderBy('id', 'ASC')->findAll();
        $this->assertCount(2, $events);
        $eventA = (int) $events[0]['id'];
        $eventB = (int) $events[1]['id'];

        $basketModel = new EventBasketItemModel();
        foreach ([
            ['event_id' => $eventA, 'service_id' => 1, 'vendor_id' => 10, 'estimated_total' => 100.0, 'deposit_amount' => 20.0],
            ['event_id' => $eventA, 'service_id' => 2, 'vendor_id' => 11, 'estimated_total' => 150.0, 'deposit_amount' => 30.0],
            ['event_id' => $eventB, 'service_id' => 3, 'vendor_id' => 12, 'estimated_total' => 200.0, 'deposit_amount' => 40.0],
        ] as $row) {
            $basketModel->insert(array_merge($row, [
                'user_id' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $bookingModel = new BookingModel();
        $bookingModel->insert([
            'user_id' => $userId,
            'event_id' => $eventB,
            'status' => 'pending',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $bookingId = (int) $bookingModel->getInsertID();

        $itemModel = new BookingItemModel();
        foreach ([
            ['service_id' => 4, 'price' => 80.0, 'status' => 'pending'],
            ['service_id' => 5, 'price' => 120.0, 'status' => 'accepted'],
            ['service_id' => 6, 'price' => 90.0, 'status' => 'confirmed'],
        ] as $item) {
            $itemModel->insert(array_merge($item, [
                'booking_id' => $bookingId,
                'created_at' => $now,
            ]));
        }

        $summary = new CustomerEventSummary();
        $enriched = $summary->enrichMany($userId, $events);

        $this->assertCount(2, $enriched);
        $byId = [];
        foreach ($enriched as $e) {
            $byId[(int) $e['id']] = $e;
        }

        $this->assertSame(2, (int) $byId[$eventA]['basket_count']);
        $this->assertSame(0, (int) $byId[$eventA]['services_booked']);
        $this->assertEqualsWithDelta(250.0, (float) $byId[$eventA]['basket_estimated'], 0.01);
        $this->assertEqualsWithDelta(50.0, (float) $byId[$eventA]['basket_deposit'], 0.01);

        $this->assertSame(1, (int) $byId[$eventB]['basket_count']);
        $this->assertSame(3, (int) $byId[$eventB]['services_booked']);
        $this->assertEqualsWithDelta(200.0, (float) $byId[$eventB]['basket_estimated'], 0.01);
        $this->assertEqualsWithDelta(290.0, (float) $byId[$eventB]['total_cost'], 0.01);
        $this->assertSame(1, (int) $byId[$eventB]['pending_count']);
        $this->assertSame(2, (int) $byId[$eventB]['accepted_count']);
    }
}
