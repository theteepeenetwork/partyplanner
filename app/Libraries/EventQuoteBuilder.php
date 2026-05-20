<?php

namespace App\Libraries;

use App\Models\ServiceCustomDurationPricingModel;
use App\Models\ServiceGuestBasedPricingModel;
use App\Models\ServiceOptionalExtrasModel;
use App\Models\ServicePrivatePricingModel;
use App\Models\ServicePublicEventPricingModel;
use App\Models\ServiceQuantityPricingModel;
use App\Models\ServiceTieredPackagesPricingModel;
use App\Models\ServiceLocationModel;
use App\Models\ServiceModel;

/**
 * Loads pricing data and runs EventBookingQuote for a service + event pair.
 */
class EventQuoteBuilder
{
    /**
     * @param array<string,mixed> $service
     * @param array<string,mixed> $event
     * @param list<int|string> $selectedExtraIds
     * @param array<int, int> $extraQuantitiesById
     * @return array{lines: list<array{code:string,label:string,amount:float}>, total: float, warnings: list<string>, errors: list<string>, distance_km: ?float}
     */
    public function build(
        array $service,
        array $event,
        ?string $pricingOption = null,
        array $selectedExtraIds = [],
        array $extraQuantitiesById = [],
        ?int $orderQuantity = null
    ): array {
        $serviceId = (int) ($service['id'] ?? 0);
        $locationModel = new ServiceLocationModel();
        $publicPricingModel = new ServicePublicEventPricingModel();
        $privatePricingModel = new ServicePrivatePricingModel();
        $guestModel = new ServiceGuestBasedPricingModel();
        $durationModel = new ServiceCustomDurationPricingModel();
        $packageModel = new ServiceTieredPackagesPricingModel();
        $quantityModel = new ServiceQuantityPricingModel();
        $extrasModel = new ServiceOptionalExtrasModel();

        $locationRow = $locationModel->where('service_id', $serviceId)->first();
        $locMerged = $this->mergeServiceLocation($service, $locationRow);

        $publicBands = $publicPricingModel->where('service_id', $serviceId)->orderBy('min_attendance', 'ASC')->findAll();
        $privatePricing = $privatePricingModel->where('service_id', $serviceId)->first();
        $privateId = $privatePricing['id'] ?? null;
        $guestTiers = $privateId ? $guestModel->where('private_event_pricing_id', $privateId)->findAll() : [];
        $durationTiers = $privateId ? $durationModel->where('private_event_pricing_id', $privateId)->findAll() : [];
        $packages = $privateId ? $packageModel->where('private_event_pricing_id', $privateId)->findAll() : [];
        $quantityPricing = $privateId
            ? $quantityModel->where('private_event_pricing_id', $privateId)->first()
            : null;

        $extraRows = $extrasModel->where('service_id', $serviceId)->findAll();
        $extrasById = [];
        foreach ($extraRows as $er) {
            $ptype = strtolower(trim((string) ($er['pricing_type'] ?? 'flat')));
            $extrasById[(int) $er['id']] = [
                'price' => (float) ($er['price'] ?? 0),
                'name' => (string) ($er['name'] ?? 'Extra'),
                'pricing_type' => $ptype === 'per_item' ? 'per_item' : 'flat',
                'min_quantity' => isset($er['min_quantity']) && $er['min_quantity'] !== '' && $er['min_quantity'] !== null
                    ? (int) $er['min_quantity'] : null,
                'max_quantity' => isset($er['max_quantity']) && $er['max_quantity'] !== '' && $er['max_quantity'] !== null
                    ? (int) $er['max_quantity'] : null,
                'unit_label' => isset($er['unit_label']) && $er['unit_label'] !== '' ? (string) $er['unit_label'] : null,
            ];
        }

        if (($privatePricing['pricing_type'] ?? '') === 'guest_based_pricing') {
            $pricingOption = null;
        }

        $corporatePricing = $this->loadCorporatePricing($serviceId);
        $quantityPricing = null;
        if (($privatePricing['pricing_type'] ?? '') === 'quantity_based_pricing') {
            $quantityPricing = $this->loadQuantityPricing($serviceId, $privateId);
        }

        $availability = new ServiceAvailabilityChecker();
        $availErrors = $availability->check(
            $serviceId,
            (int) ($service['vendor_id'] ?? 0),
            $event['date'] ?? null
        );

        $calc = new EventBookingQuote();

        $quote = $calc->calculate(
            $service,
            $event,
            $locMerged,
            $publicBands,
            $privatePricing,
            $guestTiers,
            $durationTiers,
            $packages,
            $quantityPricing,
            $extrasById,
            $selectedExtraIds,
            $pricingOption,
            $extraQuantitiesById,
            $corporatePricing,
            $orderQuantity,
            $quantityPricing
        );

        if ($availErrors !== []) {
            $quote['errors'] = array_merge($quote['errors'], $availErrors);
        }

        return $quote;
    }

    /**
     * @return array<string,mixed>|null
     */
    /**
     * @return array<string,mixed>|null
     */
    public function loadQuantityPricing(int $serviceId, ?int $privatePricingId = null): ?array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('services_quantity_based_pricing')) {
            return null;
        }

        $builder = $db->table('services_quantity_based_pricing')->where('service_id', $serviceId);
        if ($privatePricingId !== null) {
            $builder->where('private_event_pricing_id', $privatePricingId);
        }

        $row = $builder->get()->getRowArray();

        return is_array($row) ? $row : null;
    }

    public function loadCorporatePricing(int $serviceId): ?array
    {
        $db = \Config\Database::connect();
        $row = $db->table('services_corporate_event_pricing')
            ->where('service_id', $serviceId)
            ->get()
            ->getRowArray();

        if (!$row || empty($row['pricing_details'])) {
            return null;
        }

        $decoded = json_decode((string) $row['pricing_details'], true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param array<string,mixed> $service
     * @param array<string,mixed>|null $locationRow
     * @return array<string,mixed>
     */
    public function mergeServiceLocation(array $service, ?array $locationRow): array
    {
        $base = [
            'latitude' => null,
            'longitude' => null,
            'all_travel_included' => 0,
            'no_travel_limit' => 0,
            'free_coverage_radius' => null,
            'paid_coverage_radius' => null,
            'travel_fee_per_km' => null,
            'strict_travel_radius' => 0,
            'fulfillment_type' => 'in_person',
            'postal_fee' => null,
            'free_postage_above' => null,
            'delivery_lead_time_days' => null,
        ];
        $row = $locationRow ?? [];
        $out = array_merge($base, $row);
        $keys = [
            'latitude', 'longitude', 'all_travel_included', 'no_travel_limit',
            'free_coverage_radius', 'paid_coverage_radius', 'travel_fee_per_km',
            'strict_travel_radius', 'fulfillment_type', 'postal_fee', 'free_postage_above',
            'delivery_lead_time_days',
        ];
        foreach ($keys as $k) {
            if (!isset($out[$k]) || $out[$k] === null || $out[$k] === '') {
                if (array_key_exists($k, $service) && $service[$k] !== null && $service[$k] !== '') {
                    $out[$k] = $service[$k];
                }
            }
        }

        return $out;
    }

    public function loadService(int $serviceId): ?array
    {
        $serviceModel = new ServiceModel();

        return $serviceModel->find($serviceId);
    }
}
