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
            null,
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
        $result = $calc->calculate($service, $event, $loc, $bands, null, [], [], [], null, [], [], null);
        $this->assertSame([], $result['errors']);
        $this->assertEqualsWithDelta(300.0, $result['total'], 0.01);
    }

    public function testPerItemOptionalExtraDefaultsToGuestCount(): void
    {
        $calc = new EventBookingQuote();
        $service = ['price' => 0.0];
        $event = [
            'guest_count' => 80,
            'event_setting' => 'private',
        ];
        $guestTiers = [
            ['id' => 1, 'min_guest' => 1, 'max_guest' => 100, 'guest_price' => 5.0],
        ];
        $extrasById = [
            7 => [
                'price' => 2.0,
                'name' => 'Favours',
                'pricing_type' => 'per_item',
                'min_quantity' => 1,
                'max_quantity' => 200,
                'unit_label' => 'favours',
            ],
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
            null,
            $extrasById,
            [7],
            'guest_1',
            []
        );
        $this->assertSame([], $result['errors']);
        $this->assertEqualsWithDelta(560.0, $result['total'], 0.01);
    }

    public function testPerItemOptionalExtraUsesExplicitQuantity(): void
    {
        $calc = new EventBookingQuote();
        $service = ['price' => 0.0];
        $event = [
            'guest_count' => 80,
            'event_setting' => 'private',
        ];
        $guestTiers = [
            ['id' => 1, 'min_guest' => 1, 'max_guest' => 100, 'guest_price' => 5.0],
        ];
        $extrasById = [
            7 => [
                'price' => 2.0,
                'name' => 'Favours',
                'pricing_type' => 'per_item',
                'min_quantity' => 1,
                'max_quantity' => 200,
                'unit_label' => 'favours',
            ],
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
            null,
            $extrasById,
            [7],
            'guest_1',
            [7 => 10]
        );
        $this->assertSame([], $result['errors']);
        $this->assertEqualsWithDelta(420.0, $result['total'], 0.01);
    }

    public function testPerItemOptionalExtraClampedToVendorMax(): void
    {
        $calc = new EventBookingQuote();
        $service = ['price' => 0.0];
        $event = [
            'guest_count' => 200,
            'event_setting' => 'private',
        ];
        $guestTiers = [
            ['id' => 1, 'min_guest' => 1, 'max_guest' => 300, 'guest_price' => 1.0],
        ];
        $extrasById = [
            7 => [
                'price' => 3.0,
                'name' => 'Favours',
                'pricing_type' => 'per_item',
                'min_quantity' => 1,
                'max_quantity' => 100,
                'unit_label' => 'favours',
            ],
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
            null,
            $extrasById,
            [7],
            'guest_1',
            []
        );
        $this->assertSame([], $result['errors']);
        $this->assertEqualsWithDelta(500.0, $result['total'], 0.01);
    }

    /**
     * A stale guest_<id> from an old booking form must not override the tier when guest count is outside that band.
     */
    public function testGuestTierIgnoresPricingOptionOutsideGuestCount(): void
    {
        $calc = new EventBookingQuote();
        $service = ['price' => 0.0];
        $event = [
            'guest_count' => 30,
            'event_setting' => 'private',
        ];
        $guestTiers = [
            ['id' => 1, 'min_guest' => 1, 'max_guest' => 25, 'guest_price' => 10.0],
            ['id' => 2, 'min_guest' => 26, 'max_guest' => 50, 'guest_price' => 6.25],
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
            null,
            [],
            [],
            'guest_1',
            []
        );
        $this->assertSame([], $result['errors']);
        $this->assertEqualsWithDelta(187.5, $result['total'], 0.01);
    }

    public function testCorporateMinSpendAdjustment(): void
    {
        $calc = new EventBookingQuote();
        $service = ['price' => 100.0];
        $event = [
            'guest_count' => 10,
            'event_setting' => 'private',
            'event_type' => 'corporate',
        ];
        $corporate = [
            'corporate_enabled' => 1,
            'corporate_min_spend' => 500,
            'corporate_surcharge_type' => 'none',
        ];
        $result = $calc->calculate(
            $service,
            $event,
            ['all_travel_included' => 1, 'no_travel_limit' => 1, 'fulfillment_type' => 'in_person'],
            [],
            null,
            [],
            [],
            [],
            [],
            [],
            null,
            [],
            $corporate
        );
        $this->assertSame([], $result['errors']);
        $this->assertEqualsWithDelta(500.0, $result['total'], 0.01);
    }

    public function testPostalFeeForPostalFulfillment(): void
    {
        $calc = new EventBookingQuote();
        $service = ['price' => 50.0];
        $event = ['guest_count' => 5, 'event_setting' => 'private'];
        $result = $calc->calculate(
            $service,
            $event,
            [
                'all_travel_included' => 1,
                'no_travel_limit' => 1,
                'fulfillment_type' => 'postal',
                'postal_fee' => 12.5,
            ],
            [],
            null,
            [],
            [],
            [],
            [],
            [],
            null
        );
        $this->assertEqualsWithDelta(62.5, $result['total'], 0.01);
    }

    public function testBudgetMaxWarning(): void
    {
        $calc = new EventBookingQuote();
        $service = ['price' => 200.0];
        $event = [
            'guest_count' => 5,
            'event_setting' => 'private',
            'budget_max' => 100,
        ];
        $result = $calc->calculate(
            $service,
            $event,
            ['all_travel_included' => 1, 'no_travel_limit' => 1],
            [],
            null,
            [],
            [],
            [],
            [],
            [],
            null
        );
        $joined = strtolower(implode(' ', $result['warnings']));
        $this->assertStringContainsString('budget', $joined);
    }

    public function testFreePostageAboveWaivesFee(): void
    {
        $calc = new EventBookingQuote();
        $service = ['price' => 100.0];
        $event = ['guest_count' => 5, 'event_setting' => 'private'];
        $result = $calc->calculate(
            $service,
            $event,
            [
                'fulfillment_type' => 'postal',
                'postal_fee' => 15.0,
                'free_postage_above' => 50.0,
            ],
            [],
            null,
            [],
            [],
            [],
            [],
            [],
            null
        );
        $codes = array_column($result['lines'], 'code');
        $this->assertNotContains('postal_fee', $codes);
        $this->assertEqualsWithDelta(100.0, $result['total'], 0.01);
        $joined = implode(' ', $result['warnings']);
        $this->assertStringContainsString('Free postage applied', $joined);
        $this->assertStringContainsString('50.00', $joined);
    }

    public function testPostalOnlySkipsTravel(): void
    {
        $calc = new EventBookingQuote();
        $service = ['price' => 100.0, 'latitude' => 51.5, 'longitude' => -0.12];
        $event = [
            'guest_count' => 5,
            'event_setting' => 'private',
            'latitude' => 52.5,
            'longitude' => -1.0,
        ];
        $loc = [
            'fulfillment_type' => 'postal',
            'postal_fee' => 10.0,
            'all_travel_included' => 0,
            'no_travel_limit' => 0,
            'free_coverage_radius' => 10,
            'paid_coverage_radius' => 20,
            'travel_fee_per_km' => 5.0,
            'strict_travel_radius' => 1,
        ];
        $result = $calc->calculate($service, $event, $loc, [], null, [], [], [], [], [], null);
        $codes = array_column($result['lines'], 'code');
        $this->assertNotContains('travel', $codes);
        $this->assertNull($result['distance_km']);
        $this->assertSame([], $result['errors']);
        $travelWarnings = array_filter(
            $result['warnings'],
            static fn (string $w): bool => stripos($w, 'travel') !== false
        );
        $this->assertSame([], array_values($travelWarnings));
        $this->assertEqualsWithDelta(110.0, $result['total'], 0.01);
    }

    public function testStrictTravelBlocksOutOfRadius(): void
    {
        $calc = new EventBookingQuote();
        $service = ['price' => 100.0, 'latitude' => 51.5, 'longitude' => -0.12];
        $event = [
            'guest_count' => 5,
            'event_setting' => 'private',
            'latitude' => 52.5,
            'longitude' => -1.0,
        ];
        $loc = [
            'latitude' => 51.5,
            'longitude' => -0.12,
            'all_travel_included' => 0,
            'no_travel_limit' => 0,
            'free_coverage_radius' => 10,
            'paid_coverage_radius' => 20,
            'travel_fee_per_km' => 1.0,
            'strict_travel_radius' => 1,
        ];
        $result = $calc->calculate($service, $event, $loc, [], null, [], [], [], [], [], null);
        $this->assertNotEmpty($result['errors']);
    }
}
