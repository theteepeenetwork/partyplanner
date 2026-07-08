<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\TenantBookingFlow;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * TenantBookingFlow::resolveWindow — turns a chosen pricing option + submitted
 * start time into the booked clock window:
 *  - fixed time block carries its own start/end;
 *  - hours-duration tier runs from the customer's start for that many hours
 *    (needsStart until a start is given);
 *  - day tiers / no option have no intra-day window (book whole-date).
 *
 * @internal
 */
final class TenantBookingWindowTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace;
    private TenantBookingFlow $flow;
    private int $serviceId = 700;

    protected function setUp(): void
    {
        parent::setUp();
        $this->flow = new TenantBookingFlow();
    }

    private function seedDuration(string $type, int $duration): int
    {
        $this->db->table('services_custom_duration_pricing')->insert([
            'service_id'    => $this->serviceId,
            'duration_type' => $type,
            'duration'      => $duration,
            'price'         => 100,
        ]);

        return (int) $this->db->insertID();
    }

    private function seedBlock(string $start, string $end): int
    {
        $this->db->table('service_time_blocks')->insert([
            'service_id' => $this->serviceId,
            'start_time' => $start,
            'end_time'   => $end,
            'price'      => 100,
        ]);

        return (int) $this->db->insertID();
    }

    public function testNoOptionHasNoWindow(): void
    {
        $w = $this->flow->resolveWindow($this->serviceId, null, null);
        $this->assertNull($w['start']);
        $this->assertNull($w['end']);
        $this->assertFalse($w['needsStart']);
    }

    public function testHourDurationNeedsStartWhenNoneGiven(): void
    {
        $id = $this->seedDuration('hour', 3);
        $w  = $this->flow->resolveWindow($this->serviceId, 'duration_' . $id, null);
        $this->assertTrue($w['needsStart']);
        $this->assertNull($w['start']);
    }

    public function testHourDurationComputesEndFromStart(): void
    {
        $id = $this->seedDuration('hour', 3);
        $w  = $this->flow->resolveWindow($this->serviceId, 'duration_' . $id, '14:00');
        $this->assertSame('14:00:00', $w['start']);
        $this->assertSame('17:00:00', $w['end']);
        $this->assertFalse($w['needsStart']);
    }

    public function testHourDurationClampsEndAtMidnight(): void
    {
        $id = $this->seedDuration('hour', 3);
        $w  = $this->flow->resolveWindow($this->serviceId, 'duration_' . $id, '23:00');
        $this->assertSame('24:00:00', $w['end']);
    }

    public function testDayDurationBooksWholeDate(): void
    {
        $id = $this->seedDuration('day', 2);
        $w  = $this->flow->resolveWindow($this->serviceId, 'duration_' . $id, '14:00');
        $this->assertNull($w['start']);
        $this->assertFalse($w['needsStart']);
    }

    public function testTimeBlockUsesItsOwnWindow(): void
    {
        $id = $this->seedBlock('18:00:00', '23:00:00');
        $w  = $this->flow->resolveWindow($this->serviceId, 'timeblock_' . $id, null);
        $this->assertSame('18:00:00', $w['start']);
        $this->assertSame('23:00:00', $w['end']);
        $this->assertFalse($w['needsStart']);
    }

    public function testWindowIsScopedToTheService(): void
    {
        $id = $this->seedDuration('hour', 3); // belongs to serviceId 700
        $w  = $this->flow->resolveWindow($this->serviceId + 1, 'duration_' . $id, '14:00');
        $this->assertNull($w['start']); // wrong service → no tier found → no window
    }
}
