<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Libraries\EventBookingQuote;
use App\Libraries\EventQuoteBuilder;
use App\Models\ServiceCustomDurationPricingModel;
use App\Models\ServiceGuestBasedPricingModel;
use App\Models\ServiceLocationModel;
use App\Models\ServiceModel;
use App\Models\ServiceOptionalExtrasModel;
use App\Models\ServicePrivatePricingModel;
use App\Models\ServicePublicEventPricingModel;
use App\Models\ServiceQuantityPricingModel;
use App\Models\ServiceTieredPackagesPricingModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Covers EventQuoteBuilder::build() end-to-end: DB-row loading, dispatch to each
 * pricing model, missing-pricing-row error handling, travel-fee integration, and
 * guest/quantity boundary behaviour. EventBookingQuote's internal math (near-edge
 * detection, warning_codes parallelism, clamp arithmetic) is already covered by
 * EventBookingQuoteTest and is not re-tested here.
 *
 * @internal
 */
final class EventQuoteBuilderTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    private int $serviceId;

    protected function setUp(): void
    {
        parent::setUp();

        $serviceModel = new ServiceModel();
        $serviceModel->insert([
            'vendor_id' => 5001,
            'title' => 'Test Service',
        ]);
        $this->serviceId = (int) $serviceModel->getInsertID();
    }

    public function testMergeServiceLocationIncludesStrictTravel(): void
    {
        $builder = new EventQuoteBuilder();
        $merged = $builder->mergeServiceLocation(['price' => 10], [
            'strict_travel_radius' => 1,
            'fulfillment_type' => 'postal',
            'postal_fee' => 5.0,
        ]);
        $this->assertSame(1, (int) $merged['strict_travel_radius']);
        $this->assertSame('postal', $merged['fulfillment_type']);
    }

    /**
     * @return array<string,mixed>
     */
    private function baseService(): array
    {
        return ['id' => $this->serviceId, 'vendor_id' => 5001, 'price' => 0.0, 'title' => 'Test Service'];
    }

    private function insertPrivatePricing(string $pricingType): int
    {
        $model = new ServicePrivatePricingModel();
        $model->insert(['service_id' => $this->serviceId, 'pricing_type' => $pricingType]);

        return (int) $model->getInsertID();
    }

    // ------------------------------------------------------------------
    // build() across each pricing model
    // ------------------------------------------------------------------

    public function testBuildGuestBasedPricingProducesConcreteLineAndTotal(): void
    {
        $privateId = $this->insertPrivatePricing('guest_based_pricing');
        (new ServiceGuestBasedPricingModel())->insert([
            'service_id' => $this->serviceId,
            'private_event_pricing_id' => $privateId,
            'min_guest' => 1,
            'max_guest' => 100,
            'guest_price' => 10.0,
        ]);

        $builder = new EventQuoteBuilder();
        $result = $builder->build(
            $this->baseService(),
            ['guest_count' => 50, 'event_setting' => 'private']
        );

        $this->assertSame([], $result['errors']);
        $this->assertCount(1, $result['lines']);
        $this->assertSame('guest_based', $result['lines'][0]['code']);
        $this->assertEqualsWithDelta(500.0, $result['lines'][0]['amount'], 0.01);
        $this->assertEqualsWithDelta(500.0, $result['total'], 0.01);
    }

    public function testBuildCustomDurationPricingProducesConcreteLineAndTotal(): void
    {
        $privateId = $this->insertPrivatePricing('custom_duration_pricing');
        (new ServiceCustomDurationPricingModel())->insert([
            'service_id' => $this->serviceId,
            'private_event_pricing_id' => $privateId,
            'duration_type' => 'hour',
            'duration' => 3,
            'price' => 150.0,
        ]);

        $builder = new EventQuoteBuilder();
        $result = $builder->build(
            $this->baseService(),
            ['guest_count' => 20, 'event_setting' => 'private']
        );

        $this->assertSame([], $result['errors']);
        $this->assertCount(1, $result['lines']);
        $this->assertSame('duration', $result['lines'][0]['code']);
        $this->assertEqualsWithDelta(150.0, $result['lines'][0]['amount'], 0.01);
        $this->assertEqualsWithDelta(150.0, $result['total'], 0.01);
    }

    public function testBuildTieredPackagesPricingProducesConcreteLineAndTotal(): void
    {
        $privateId = $this->insertPrivatePricing('tiered_packages_pricing');
        (new ServiceTieredPackagesPricingModel())->insert([
            'service_id' => $this->serviceId,
            'private_event_pricing_id' => $privateId,
            'package_name' => 'Gold',
            'package_description' => 'Everything',
            'package_price' => 400.0,
        ]);

        $builder = new EventQuoteBuilder();
        $result = $builder->build(
            $this->baseService(),
            ['guest_count' => 20, 'event_setting' => 'private']
        );

        $this->assertSame([], $result['errors']);
        $this->assertCount(1, $result['lines']);
        $this->assertSame('package', $result['lines'][0]['code']);
        $this->assertSame('Package: Gold', $result['lines'][0]['label']);
        $this->assertEqualsWithDelta(400.0, $result['lines'][0]['amount'], 0.01);
        $this->assertEqualsWithDelta(400.0, $result['total'], 0.01);
    }

    public function testBuildQuantityBasedPricingProducesConcreteLineAndTotal(): void
    {
        $privateId = $this->insertPrivatePricing('quantity_based_pricing');
        (new ServiceQuantityPricingModel())->insert([
            'service_id' => $this->serviceId,
            'private_event_pricing_id' => $privateId,
            'unit_price' => 5.0,
            'min_quantity' => 1,
            'max_quantity' => 500,
            'unit_label' => 'chairs',
        ]);

        $builder = new EventQuoteBuilder();
        $result = $builder->build(
            $this->baseService(),
            ['guest_count' => 20, 'event_setting' => 'private'],
            null,
            [],
            [],
            30
        );

        $this->assertSame([], $result['errors']);
        $this->assertCount(1, $result['lines']);
        $this->assertSame('quantity_based', $result['lines'][0]['code']);
        $this->assertEqualsWithDelta(150.0, $result['lines'][0]['amount'], 0.01);
        $this->assertEqualsWithDelta(150.0, $result['total'], 0.01);
    }

    public function testBuildCustomQuotePricingReturnsBespokeWarningNoLinesNoErrors(): void
    {
        $this->insertPrivatePricing('custom_quote');

        $builder = new EventQuoteBuilder();
        $result = $builder->build(
            $this->baseService(),
            ['guest_count' => 20, 'event_setting' => 'private']
        );

        $this->assertSame([], $result['errors']);
        $this->assertSame([], $result['lines']);
        $this->assertEqualsWithDelta(0.0, $result['total'], 0.01);
        $this->assertContains(EventBookingQuote::WARNING_CUSTOM_QUOTE, $result['warning_codes']);
        $this->assertNotSame([], $result['warnings']);
    }

    public function testBuildPublicEventPitchFeePathProducesConcreteLinesAndTotal(): void
    {
        (new ServicePublicEventPricingModel())->insert([
            'service_id' => $this->serviceId,
            'commission_percentage' => 0,
            'min_attendance' => 100,
            'max_attendance' => 1000,
            'max_pitch_fee' => 150.0,
        ]);

        $service = $this->baseService();
        $service['price'] = 200.0;

        $builder = new EventQuoteBuilder();
        $result = $builder->build(
            $service,
            [
                'guest_count' => 500,
                'event_setting' => 'public',
                'organiser_pitch_fee' => 120.0,
            ]
        );

        $this->assertSame([], $result['errors']);
        $codes = array_column($result['lines'], 'code');
        $this->assertContains('public_base', $codes);
        $this->assertContains('pitch_fee', $codes);
        $this->assertEqualsWithDelta(320.0, $result['total'], 0.01);
    }

    // ------------------------------------------------------------------
    // Missing-pricing-rows handling
    // ------------------------------------------------------------------

    /**
     * Service is configured for guest_based_pricing but has zero guest tiers.
     * Current behaviour: build() surfaces the EventBookingQuote error and returns
     * a zero total with no line items — it does NOT silently emit a $0 quote with
     * no error. Documenting this as the current, correct-looking behaviour.
     */
    public function testBuildGuestBasedPricingWithNoTiersReturnsErrorNotSilentZero(): void
    {
        $this->insertPrivatePricing('guest_based_pricing');
        // Deliberately no services_guest_based_pricing rows inserted.

        $builder = new EventQuoteBuilder();
        $result = $builder->build(
            $this->baseService(),
            ['guest_count' => 50, 'event_setting' => 'private']
        );

        $this->assertNotSame([], $result['errors']);
        $this->assertSame(
            'Guest count does not match any guest-based price band for this service.',
            $result['errors'][0]
        );
        $this->assertSame([], $result['lines']);
        $this->assertEqualsWithDelta(0.0, $result['total'], 0.01);
    }

    /**
     * Service is configured for tiered_packages_pricing but has zero packages.
     * Same shape as the guest-based case: an explicit error, not a silent zero-total.
     */
    public function testBuildTieredPackagesPricingWithNoPackagesReturnsErrorNotSilentZero(): void
    {
        $this->insertPrivatePricing('tiered_packages_pricing');
        // Deliberately no services_tiered_packages_pricing rows inserted.

        $builder = new EventQuoteBuilder();
        $result = $builder->build(
            $this->baseService(),
            ['guest_count' => 20, 'event_setting' => 'private']
        );

        $this->assertSame(['Please choose a package for this service.'], $result['errors']);
        $this->assertSame([], $result['lines']);
        $this->assertEqualsWithDelta(0.0, $result['total'], 0.01);
    }

    public function testBuildQuantityBasedPricingWithNoTiersReturnsExplicitError(): void
    {
        $this->insertPrivatePricing('quantity_based_pricing');
        // Deliberately no services_quantity_pricing rows inserted.

        $builder = new EventQuoteBuilder();
        $result = $builder->build(
            $this->baseService(),
            ['guest_count' => 20, 'event_setting' => 'private'],
            null,
            [],
            [],
            10
        );

        $this->assertSame(['This service does not have quantity-based pricing configured.'], $result['errors']);
        $this->assertSame([], $result['lines']);
        $this->assertEqualsWithDelta(0.0, $result['total'], 0.01);
    }

    public function testBuildPublicEventWithNoBandsReturnsExplicitError(): void
    {
        // Deliberately no services_public_event_pricing rows inserted.
        $service = $this->baseService();
        $service['price'] = 200.0;

        $builder = new EventQuoteBuilder();
        $result = $builder->build(
            $service,
            [
                'guest_count' => 500,
                'event_setting' => 'public',
                'organiser_pitch_fee' => 100.0,
            ]
        );

        $this->assertSame(
            ['Expected attendance does not match any band configured for this vendor’s public event pricing.'],
            $result['errors']
        );
        // The base service-fee line is still emitted before the band lookup fails.
        $this->assertSame('public_base', $result['lines'][0]['code']);
    }

    // ------------------------------------------------------------------
    // Travel-fee integration
    // ------------------------------------------------------------------

    public function testBuildTravelInRadiusAddsNoFeeLine(): void
    {
        $privateId = $this->insertPrivatePricing('guest_based_pricing');
        (new ServiceGuestBasedPricingModel())->insert([
            'service_id' => $this->serviceId,
            'private_event_pricing_id' => $privateId,
            'min_guest' => 1,
            'max_guest' => 100,
            'guest_price' => 10.0,
        ]);
        (new ServiceLocationModel())->insert([
            'service_id' => $this->serviceId,
            'latitude' => 51.5074,
            'longitude' => -0.1278,
            'all_travel_included' => 0,
            'no_travel_limit' => 0,
            'free_coverage_radius' => 50,
            'paid_coverage_radius' => 0,
            'travel_fee_per_km' => 2.0,
        ]);

        $service = $this->baseService();
        // Event ~1km from the vendor: comfortably inside the 50km free radius.
        $event = [
            'guest_count' => 50,
            'event_setting' => 'private',
            'latitude' => 51.5150,
            'longitude' => -0.1278,
        ];

        $builder = new EventQuoteBuilder();
        $result = $builder->build($service, $event);

        $this->assertSame([], $result['errors']);
        $codes = array_column($result['lines'], 'code');
        $this->assertNotContains('travel', $codes);
        $this->assertNotNull($result['distance_km']);
        $this->assertLessThan(50.0, $result['distance_km']);
        $this->assertEqualsWithDelta(500.0, $result['total'], 0.01);
    }

    public function testBuildTravelOutOfRadiusAddsFeeLineWithWarningCode(): void
    {
        $privateId = $this->insertPrivatePricing('guest_based_pricing');
        (new ServiceGuestBasedPricingModel())->insert([
            'service_id' => $this->serviceId,
            'private_event_pricing_id' => $privateId,
            'min_guest' => 1,
            'max_guest' => 100,
            'guest_price' => 10.0,
        ]);
        (new ServiceLocationModel())->insert([
            'service_id' => $this->serviceId,
            // London.
            'latitude' => 51.5074,
            'longitude' => -0.1278,
            'all_travel_included' => 0,
            'no_travel_limit' => 1,
            'free_coverage_radius' => 40,
            'travel_fee_per_km' => 2.0,
        ]);

        $service = $this->baseService();
        $event = [
            'guest_count' => 50,
            'event_setting' => 'private',
            // South of London; known ~70-95km separation (see EventBookingQuoteTest::testHaversineKnownSeparation).
            'latitude' => 50.8225,
            'longitude' => -0.1372,
        ];

        $builder = new EventQuoteBuilder();
        $result = $builder->build($service, $event);

        $this->assertSame([], $result['errors']);
        $travelLines = array_values(array_filter($result['lines'], static fn (array $l): bool => $l['code'] === 'travel'));
        $this->assertCount(1, $travelLines);
        $this->assertGreaterThan(0.0, $travelLines[0]['amount']);
        $this->assertNotNull($result['distance_km']);
        $this->assertGreaterThan(40.0, $result['distance_km']);
        $this->assertEqualsWithDelta(500.0 + $travelLines[0]['amount'], $result['total'], 0.01);
    }

    // ------------------------------------------------------------------
    // Boundaries
    // ------------------------------------------------------------------

    public function testBuildGuestCountAtUpperTierBoundaryMatchesTier(): void
    {
        $privateId = $this->insertPrivatePricing('guest_based_pricing');
        $guestModel = new ServiceGuestBasedPricingModel();
        $guestModel->insert([
            'service_id' => $this->serviceId,
            'private_event_pricing_id' => $privateId,
            'min_guest' => 1,
            'max_guest' => 25,
            'guest_price' => 10.0,
        ]);
        $guestModel->insert([
            'service_id' => $this->serviceId,
            'private_event_pricing_id' => $privateId,
            'min_guest' => 26,
            'max_guest' => 50,
            'guest_price' => 6.0,
        ]);

        $builder = new EventQuoteBuilder();
        $result = $builder->build(
            $this->baseService(),
            ['guest_count' => 50, 'event_setting' => 'private']
        );

        $this->assertSame([], $result['errors']);
        $this->assertCount(1, $result['lines']);
        $this->assertEqualsWithDelta(300.0, $result['total'], 0.01);
    }

    /**
     * guest_count=51 falls one guest above a configured 26-50 tier with no further tier
     * defined above it. Per EventBookingQuote::countFallsInRangeGap(), a count above the
     * highest range's max with no next range is NOT treated as an in-between "gap" (that
     * warning only fires between two configured ranges) — it simply fails to match any
     * band and resolveGuestTier() returns null, producing a hard error rather than a
     * gap warning.
     */
    public function testBuildGuestCountOneAboveTopTierReturnsNoMatchError(): void
    {
        $privateId = $this->insertPrivatePricing('guest_based_pricing');
        $guestModel = new ServiceGuestBasedPricingModel();
        $guestModel->insert([
            'service_id' => $this->serviceId,
            'private_event_pricing_id' => $privateId,
            'min_guest' => 1,
            'max_guest' => 25,
            'guest_price' => 10.0,
        ]);
        $guestModel->insert([
            'service_id' => $this->serviceId,
            'private_event_pricing_id' => $privateId,
            'min_guest' => 26,
            'max_guest' => 50,
            'guest_price' => 6.0,
        ]);

        $builder = new EventQuoteBuilder();
        $result = $builder->build(
            $this->baseService(),
            ['guest_count' => 51, 'event_setting' => 'private']
        );

        $this->assertSame([], $result['lines']);
        $this->assertEqualsWithDelta(0.0, $result['total'], 0.01);
        $this->assertNotSame([], $result['errors']);
        $this->assertStringContainsString(
            'Guest count (51) does not match any guest-based price band for this service.',
            $result['errors'][0]
        );
        // No "falls between configured bands" gap warning: 51 is beyond the top of the
        // highest configured range, not between two ranges.
        $this->assertNotContains(EventBookingQuote::WARNING_GUEST_TIER_GAP, $result['warning_codes']);
    }

    public function testBuildQuantityClampedToVendorMinimumThroughBuild(): void
    {
        $privateId = $this->insertPrivatePricing('quantity_based_pricing');
        (new ServiceQuantityPricingModel())->insert([
            'service_id' => $this->serviceId,
            'private_event_pricing_id' => $privateId,
            'unit_price' => 4.0,
            'min_quantity' => 10,
            'max_quantity' => 100,
            'unit_label' => 'units',
        ]);

        $builder = new EventQuoteBuilder();
        // Requested quantity (2) is below the vendor minimum (10); build() clamps up.
        $result = $builder->build(
            $this->baseService(),
            ['guest_count' => 5, 'event_setting' => 'private'],
            null,
            [],
            [],
            2
        );

        $this->assertSame([], $result['errors']);
        $this->assertCount(1, $result['lines']);
        $this->assertEqualsWithDelta(40.0, $result['lines'][0]['amount'], 0.01);
        $this->assertEqualsWithDelta(40.0, $result['total'], 0.01);
    }

    public function testBuildQuantityClampedToVendorMaximumThroughBuild(): void
    {
        $privateId = $this->insertPrivatePricing('quantity_based_pricing');
        (new ServiceQuantityPricingModel())->insert([
            'service_id' => $this->serviceId,
            'private_event_pricing_id' => $privateId,
            'unit_price' => 4.0,
            'min_quantity' => 10,
            'max_quantity' => 100,
            'unit_label' => 'units',
        ]);

        $builder = new EventQuoteBuilder();
        // Requested quantity (500) exceeds the vendor maximum (100); build() clamps down.
        $result = $builder->build(
            $this->baseService(),
            ['guest_count' => 5, 'event_setting' => 'private'],
            null,
            [],
            [],
            500
        );

        $this->assertSame([], $result['errors']);
        $this->assertCount(1, $result['lines']);
        $this->assertEqualsWithDelta(400.0, $result['lines'][0]['amount'], 0.01);
        $this->assertEqualsWithDelta(400.0, $result['total'], 0.01);
    }
}
