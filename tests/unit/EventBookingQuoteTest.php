<?php

namespace Tests\Unit;

use App\Libraries\EventBookingQuote;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class EventBookingQuoteTest extends CIUnitTestCase
{
    public function testHaversineKnownSeparation(): void
    {
        $km = EventBookingQuote::haversineKm(51.5074, -0.1278, 50.8225, -0.1372);
        $this->assertGreaterThan(70.0, $km);
        $this->assertLessThan(95.0, $km);
    }

    public function testTravelAllIncludedNationwide(): void
    {
        $calc = new EventBookingQuote();
        $r = $calc->computeTravel(500.0, [
            'all_travel_included' => 1,
            'no_travel_limit'     => 1,
        ]);
        $this->assertSame([], $r['lines']);
        $this->assertSame([], $r['warnings']);
    }

    public function testTravelNationalPerKmNoFreeBand(): void
    {
        $calc = new EventBookingQuote();
        $r = $calc->computeTravel(40.0, [
            'all_travel_included' => 0,
            'no_travel_limit'     => 1,
            'free_coverage_radius' => 0,
            'travel_fee_per_km' => 2.5,
        ]);
        $this->assertCount(1, $r['lines']);
        $this->assertEqualsWithDelta(100.0, $r['lines'][0]['amount'], 0.01);
    }

    public function testTravelFreeThenPerKmBeyond(): void
    {
        $calc = new EventBookingQuote();
        $r = $calc->computeTravel(60.0, [
            'all_travel_included' => 0,
            'no_travel_limit'     => 1,
            'free_coverage_radius' => 40,
            'travel_fee_per_km' => 2.0,
        ]);
        $this->assertCount(1, $r['lines']);
        $this->assertEqualsWithDelta(40.0, $r['lines'][0]['amount'], 0.01);
    }

    public function testGuestBasedPrivateSubtotal(): void
    {
        $calc = new EventBookingQuote();
        $service = ['price' => 0.0];
        $event = [
            'guest_count' => 50,
            'event_setting' => 'private',
        ];
        $guestTiers = [
            ['id' => 1, 'min_guest' => 1, 'max_guest' => 100, 'guest_price' => 10.0],
        ];
        $result = $calc->calculate(
            $service,
            $event,
            ['all_travel_included' => 1, 'no_travel_limit' => 1],
            [],
            ['pricing_type' => 'guest_based_pricing', 'id' => 9],
            $guestTiers,
            [],
            [],
            [],
            [],
            'guest_1'
        );
        $this->assertSame([], $result['errors']);
        $this->assertEqualsWithDelta(500.0, $result['total'], 0.01);
    }

    public function testPublicPitchUsesOrganiserValue(): void
    {
        $calc = new EventBookingQuote();
        $service = ['price' => 200.0];
        $event = [
            'guest_count' => 8000,
            'event_setting' => 'public',
            'organiser_pitch_fee' => 100.0,
            'latitude' => 51.5,
            'longitude' => -0.12,
        ];
        $bands = [
            ['min_attendance' => 5000, 'max_attendance' => 10000, 'max_pitch_fee' => 150.0],
        ];
        $loc = [
            'latitude' => 51.51,
            'longitude' => -0.13,
            'all_travel_included' => 1,
            'no_travel_limit' => 1,
        ];
        $result = $calc->calculate($service, $event, $loc, $bands, null, [], [], [], [], [], null);
        $this->assertSame([], $result['errors']);
        $this->assertEqualsWithDelta(300.0, $result['total'], 0.01);
    }
}
