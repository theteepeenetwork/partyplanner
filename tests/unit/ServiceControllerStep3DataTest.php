<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\Service_Controller;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * Covers Service_Controller::buildStep3Data(), in particular the
 * 'custom_quote' pricing-type branch (price-on-request), which must clear
 * every other pricing model's stale keys and must not reintroduce them.
 *
 * @internal
 */
final class ServiceControllerStep3DataTest extends CIUnitTestCase
{
    /**
     * Stale session step3_data seeded with keys from every private pricing
     * model, so we can prove a given branch clears the ones it doesn't own.
     */
    private function staleStep3Data(): array
    {
        return [
            // guest_based_pricing
            'min_guest' => [1],
            'max_guest' => [10],
            'guest_price' => [50],
            // custom_duration_pricing
            'enableHours' => 1,
            'enableDays' => 0,
            'hour_number' => [2],
            'hour_price' => [25],
            'day_number' => [],
            'day_price' => [],
            // tiered_packages_pricing
            'package_name' => ['Gold'],
            'package_description' => ['desc'],
            'package_price' => [100],
            // quantity_based_pricing
            'unit_price' => 5,
            'min_quantity' => 1,
            'max_quantity' => 20,
            'unit_label' => 'items',
        ];
    }

    private function invokeBuildStep3Data(array $post, array $selectedEventTypes, ?string $pricingType): array
    {
        $reflectionClass = new ReflectionClass(Service_Controller::class);
        $controller = $reflectionClass->newInstanceWithoutConstructor();

        $method = new ReflectionMethod(Service_Controller::class, 'buildStep3Data');
        $method->setAccessible(true);

        return $method->invoke($controller, $post, $selectedEventTypes, $pricingType);
    }

    public function testCustomQuoteWithPrivateEventClearsAllOtherPricingModelKeys(): void
    {
        session()->set('step3_data', $this->staleStep3Data());

        $result = $this->invokeBuildStep3Data([], ['private'], 'custom_quote');

        $staleKeys = [
            'min_guest', 'max_guest', 'guest_price',
            'enableHours', 'enableDays', 'hour_number', 'hour_price', 'day_number', 'day_price',
            'package_name', 'package_description', 'package_price',
            'unit_price', 'min_quantity', 'max_quantity', 'unit_label',
        ];

        foreach ($staleKeys as $key) {
            $this->assertArrayNotHasKey($key, $result, "Expected '{$key}' to be cleared for custom_quote pricing type.");
        }
    }

    public function testGuestBasedPricingWithPrivateKeepsSubmittedGuestKeysAndClearsOthers(): void
    {
        session()->set('step3_data', $this->staleStep3Data());

        $post = [
            'min_guest' => [10],
            'max_guest' => [50],
            'guest_price' => [200],
        ];

        $result = $this->invokeBuildStep3Data($post, ['private'], 'guest_based_pricing');

        // Submitted guest keys survive and reflect $post, not the stale session values.
        $this->assertSame([10], $result['min_guest']);
        $this->assertSame([50], $result['max_guest']);
        $this->assertSame([200], $result['guest_price']);

        // Sibling pricing models' keys must be cleared, protecting against regression.
        $clearedKeys = [
            'enableHours', 'enableDays', 'hour_number', 'hour_price', 'day_number', 'day_price',
            'package_name', 'package_description', 'package_price',
            'unit_price', 'min_quantity', 'max_quantity', 'unit_label',
        ];

        foreach ($clearedKeys as $key) {
            $this->assertArrayNotHasKey($key, $result, "Expected '{$key}' to be cleared for guest_based_pricing pricing type.");
        }
    }

    public function testPublicOnlySelectionWithNullPricingTypeReturnsArrayWithoutThrowing(): void
    {
        session()->set('step3_data', []);

        $post = [
            'commission_percentage' => 10,
            'min_attendance' => [20],
            'max_attendance' => [100],
            'max_pitch_fee' => [15],
        ];

        $result = $this->invokeBuildStep3Data($post, ['public'], null);

        $this->assertIsArray($result);
        $this->assertSame(10, $result['commission_percentage']);
        $this->assertSame([20], $result['min_attendance']);
        $this->assertSame([100], $result['max_attendance']);
        $this->assertSame([15], $result['max_pitch_fee']);

        // No private-only pricing keys should appear since 'private' wasn't selected.
        $privateKeys = [
            'min_guest', 'max_guest', 'guest_price',
            'enableHours', 'enableDays', 'hour_number', 'hour_price', 'day_number', 'day_price',
            'package_name', 'package_description', 'package_price',
            'unit_price', 'min_quantity', 'max_quantity', 'unit_label',
        ];

        foreach ($privateKeys as $key) {
            $this->assertArrayNotHasKey($key, $result);
        }
    }
}
