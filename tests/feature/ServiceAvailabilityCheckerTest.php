<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\ServiceAvailabilityChecker;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Time-aware availability engine. Whole-date mode (no times) blocks the day on
 * any live booking; slot mode books a window padded by the service's setup +
 * breakdown minutes, so only genuinely overlapping slots clash. A live booking
 * with no recorded time is treated as taking the whole day (fail closed).
 *
 * @internal
 */
final class ServiceAvailabilityCheckerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace;
    private ServiceAvailabilityChecker $checker;
    private int $vendorId = 900;
    private int $serviceId;
    private string $date = '2026-09-01';

    protected function setUp(): void
    {
        parent::setUp();
        $this->checker   = new ServiceAvailabilityChecker();
        $this->serviceId = $this->seedService(0, 0);
    }

    private function seedService(int $setup, int $breakdown): int
    {
        $this->db->table('services')->insert([
            'vendor_id'         => $this->vendorId,
            'title'             => 'Photo Booth',
            'status'            => 'active',
            'setup_minutes'     => $setup,
            'breakdown_minutes' => $breakdown,
        ]);

        return (int) $this->db->insertID();
    }

    private function seedBooking(?string $start, ?string $end, string $status = 'accepted', ?string $date = null): void
    {
        $this->db->table('events')->insert(['user_id' => 1, 'title' => 'E', 'date' => $date ?? $this->date]);
        $eventId = (int) $this->db->insertID();
        $this->db->table('bookings')->insert(['user_id' => 1, 'event_id' => $eventId, 'status' => 'confirmed']);
        $bookingId = (int) $this->db->insertID();
        $this->db->table('booking_items')->insert([
            'booking_id' => $bookingId,
            'service_id' => $this->serviceId,
            'status'     => $status,
            'start_time' => $start,
            'end_time'   => $end,
        ]);
    }

    public function testFreeWhenNoBookings(): void
    {
        $this->assertSame([], $this->checker->check($this->serviceId, $this->vendorId, $this->date));
        $this->assertSame([], $this->checker->check($this->serviceId, $this->vendorId, $this->date, '14:00', '16:00'));
    }

    public function testWholeDateModeBlocksOnAnyBooking(): void
    {
        $this->seedBooking('10:00:00', '12:00:00');
        $this->assertNotSame([], $this->checker->check($this->serviceId, $this->vendorId, $this->date));
    }

    public function testNonOverlappingSlotIsFree(): void
    {
        $this->seedBooking('10:00:00', '12:00:00');
        $this->assertSame([], $this->checker->check($this->serviceId, $this->vendorId, $this->date, '14:00', '16:00'));
    }

    public function testOverlappingSlotClashes(): void
    {
        $this->seedBooking('10:00:00', '12:00:00');
        $this->assertNotSame([], $this->checker->check($this->serviceId, $this->vendorId, $this->date, '11:00', '13:00'));
    }

    public function testBufferPaddingClashesNearAdjacentSlots(): void
    {
        // 30/30 buffers: existing 10:00–12:00 really occupies 09:30–12:30, so a
        // 12:15 start (minus its own 30m setup = 11:45) still collides.
        $this->serviceId = $this->seedService(30, 30);
        $this->seedBooking('10:00:00', '12:00:00');
        $this->assertNotSame([], $this->checker->check($this->serviceId, $this->vendorId, $this->date, '12:15', '13:15'));
    }

    public function testBufferedSlotsWithEnoughGapAreFree(): void
    {
        $this->serviceId = $this->seedService(30, 30);
        $this->seedBooking('10:00:00', '12:00:00');
        // Starts at 13:30: padded window 13:00–14:30 clears the 12:30 pack-down.
        $this->assertSame([], $this->checker->check($this->serviceId, $this->vendorId, $this->date, '13:30', '14:30'));
    }

    public function testExistingBookingWithNoTimesBlocksWholeDaySlot(): void
    {
        $this->seedBooking(null, null); // legacy whole-day booking
        $this->assertNotSame([], $this->checker->check($this->serviceId, $this->vendorId, $this->date, '14:00', '16:00'));
    }

    public function testCancelledBookingIsIgnored(): void
    {
        $this->seedBooking('10:00:00', '12:00:00', 'cancelled');
        $this->assertSame([], $this->checker->check($this->serviceId, $this->vendorId, $this->date, '11:00', '13:00'));
        $this->assertSame([], $this->checker->check($this->serviceId, $this->vendorId, $this->date));
    }

    public function testVendorDayOffBlocksWholeDay(): void
    {
        $this->db->table('unavailable_dates')->insert(['vendor_id' => $this->vendorId, 'date' => $this->date]);
        $this->assertNotSame([], $this->checker->check($this->serviceId, $this->vendorId, $this->date, '14:00', '16:00'));
    }
}
