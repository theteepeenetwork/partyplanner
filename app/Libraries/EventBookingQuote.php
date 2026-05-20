<?php

namespace App\Libraries;

/**
 * Computes customer-facing totals when adding a service to an event basket.
 */
class EventBookingQuote
{
    /**
     * @param array<string,mixed> $service            Row from `services`
     * @param array<string,mixed> $event              Row from `events` (expects guest_count, event_setting, latitude, longitude, organiser_pitch_fee)
     * @param array<string,mixed>|null $location      Row from `services_locations` or null
     * @param list<array<string,mixed>> $publicBands  Rows from `services_public_event_pricing`
     * @param array<string,mixed>|null $privatePricing Row from `services_private_event_pricing`
     * @param list<array<string,mixed>> $guestTiers
     * @param list<array<string,mixed>> $durationTiers
     * @param list<array<string,mixed>> $timeBlocks       Rows from `service_time_blocks`
     * @param list<array<string,mixed>> $packages
     * @param list<array<string,mixed>> $quantityTiers Rows from `services_quantity_pricing` (volume bands)
     * @param array<int, float|array{price: float, name?: string, pricing_type?: string, min_quantity?: int|null, max_quantity?: int|null, unit_label?: string|null}> $extrasById
     * @param list<int|string> $selectedExtraIds
     * @param string|null $pricingOption               e.g. guest_12, duration_3, package_4
     * @param array<int, int> $extraQuantitiesById     Optional per-extra quantity overrides (per_item extras only)
     * @param array<string,mixed>|null $corporatePricing Decoded services_corporate_event_pricing.pricing_details
     * @return array{lines: list<array{code:string,label:string,amount:float}>, total: float, warnings: list<string>, errors: list<string>, distance_km: ?float}
     */
    public function calculate(
        array $service,
        array $event,
        ?array $location,
        array $publicBands,
        ?array $privatePricing,
        array $guestTiers,
        array $durationTiers,
        array $packages,
        array $quantityTiers = [],
        array $extrasById,
        array $selectedExtraIds,
        ?string $pricingOption,
        array $extraQuantitiesById = [],
        ?array $corporatePricing = null,
        ?int $orderQuantity = null,
        array $timeBlocks = []
    ): array {
        $warnings = [];
        $errors  = [];
        $lines   = [];

        $guestCount = max(1, (int) ($event['guest_count'] ?? 1));
        $setting    = $event['event_setting'] ?? 'private';

        if ($setting === 'public') {
            $publicResult = $this->publicEventSubtotal($service, $event, $publicBands, $guestCount);
            $lines = array_merge($lines, $publicResult['lines']);
            $warnings = array_merge($warnings, $publicResult['warnings']);
            $errors   = array_merge($errors, $publicResult['errors']);
            $commissionResult = $this->publicCommissionLine($publicBands, $guestCount, $lines);
            $lines = array_merge($lines, $commissionResult['lines']);
            $warnings = array_merge($warnings, $commissionResult['warnings']);
        } else {
            $privateResult = $this->privateEventSubtotal(
                $service,
                $privatePricing,
                $guestTiers,
                $durationTiers,
                $timeBlocks,
                $packages,
                $quantityTiers,
                $guestCount,
                $pricingOption,
                $orderQuantity
            );
            $lines = array_merge($lines, $privateResult['lines']);
            $warnings = array_merge($warnings, $privateResult['warnings']);
            $errors   = array_merge($errors, $privateResult['errors']);
        }

        $defaultItemQuantity = $guestCount;
        if (is_array($privatePricing)
            && ($privatePricing['pricing_type'] ?? '') === 'quantity_based_pricing'
            && $quantityTiers !== []) {
            $resolvedQty = $this->resolveOrderQuantityFromTiers($quantityTiers, $pricingOption, $orderQuantity);
            if ($resolvedQty !== null) {
                $defaultItemQuantity = $resolvedQty;
            }
        }

        $extrasResult = $this->extrasLines($extrasById, $selectedExtraIds, $defaultItemQuantity, $extraQuantitiesById);
        $lines = array_merge($lines, $extrasResult['lines']);

        if ($this->isCorporateEvent($event) && $corporatePricing !== null) {
            $corpResult = $this->corporateModifiers($corporatePricing, $lines);
            $lines = array_merge($lines, $corpResult['lines']);
            $warnings = array_merge($warnings, $corpResult['warnings']);
            $errors = array_merge($errors, $corpResult['errors']);
        }

        $merchandiseSubtotal = $this->sumLineAmounts($lines);

        $postalResult = $this->postalLines($location ?? [], $merchandiseSubtotal, $event);
        $lines = array_merge($lines, $postalResult['lines']);
        $warnings = array_merge($warnings, $postalResult['warnings']);

        $fulfillmentType = strtolower(trim((string) (($location ?? [])['fulfillment_type'] ?? 'in_person')));
        $distanceKm = null;
        if ($fulfillmentType !== 'postal') {
            $distanceKm = $this->distanceKm($service, $location, $event);
            $strictTravel = !empty($location['strict_travel_radius']);
            if ($distanceKm === null) {
                $msg = 'Travel could not be calculated (missing coordinates). Add a full postcode to your event, or confirm travel with the vendor.';
                if ($strictTravel) {
                    $errors[] = $msg;
                } else {
                    $warnings[] = $msg;
                }
            } else {
                $travel = $this->computeTravel((float) $distanceKm, $location ?? []);
                $lines  = array_merge($lines, $travel['lines']);
                foreach ($travel['warnings'] as $tw) {
                    if ($strictTravel && $this->isTravelRadiusWarning($tw)) {
                        $errors[] = $tw;
                    } else {
                        $warnings[] = $tw;
                    }
                }
            }
        }

        $total = 0.0;
        foreach ($lines as $line) {
            if (($line['code'] ?? '') === 'platform_commission') {
                continue;
            }
            $total += $line['amount'];
        }
        $total = round($total, 2);

        $budgetResult = $this->budgetValidation($event, $total);
        $warnings = array_merge($warnings, $budgetResult['warnings']);
        $errors = array_merge($errors, $budgetResult['errors']);

        return [
            'lines'        => $lines,
            'total'        => $total,
            'warnings'     => $warnings,
            'errors'       => $errors,
            'distance_km'  => $distanceKm,
        ];
    }

    /**
     * @param list<array<string,mixed>> $publicBands
     * @return array{lines: list<array{code:string,label:string,amount:float}>, warnings: list<string>, errors: list<string>}
     */
    private function publicEventSubtotal(array $service, array $event, array $publicBands, int $guestCount): array
    {
        $lines    = [];
        $warnings = [];
        $errors   = [];

        $base = (float) ($service['price'] ?? 0);
        if ($base > 0) {
            $lines[] = [
                'code'   => 'public_base',
                'label'  => 'Service fee (public listing)',
                'amount' => round($base, 2),
            ];
        } else {
            $warnings[] = 'This listing has no base service price on file; only pitch and travel are included in the estimate.';
        }

        $warnings = array_merge($warnings, $this->validatePublicAttendanceCoverage($publicBands, $guestCount));

        $band = $this->matchAttendanceBand($publicBands, $guestCount);
        if ($band === null) {
            $rangeSummary = $this->formatAttendanceBandRanges($publicBands);
            if ($rangeSummary !== '') {
                $errors[] = sprintf(
                    'Expected attendance (%d) does not match any band configured for this vendor’s public event pricing. Configured bands: %s.',
                    $guestCount,
                    $rangeSummary
                );
            } else {
                $errors[] = 'Expected attendance does not match any band configured for this vendor’s public event pricing.';
            }

            return compact('lines', 'warnings', 'errors');
        }

        if ($this->isNearAttendanceBandEdge($band, $guestCount)) {
            $warnings[] = 'Your guest count is near the edge of a pricing band; confirm the correct tier with the vendor.';
        }

        $vendorMaxPitch = (float) ($band['max_pitch_fee'] ?? 0);
        $organiserPitch = $event['organiser_pitch_fee'];
        if ($organiserPitch !== null && $organiserPitch !== '') {
            $pitch = (float) $organiserPitch;
            if ($pitch > $vendorMaxPitch + 0.004) {
                $warnings[] = 'Organiser pitch fee exceeds the maximum this vendor accepted for this attendance band; they may decline the booking.';
            }
        } else {
            $pitch = $vendorMaxPitch;
            $warnings[] = 'No organiser pitch fee saved on your event; using the vendor’s maximum pitch for this attendance band as an estimate.';
        }

        if ($pitch > 0) {
            $lines[] = [
                'code'   => 'pitch_fee',
                'label'  => 'Pitch / stand fee (public event)',
                'amount' => round($pitch, 2),
            ];
        }

        return compact('lines', 'warnings', 'errors');
    }

    /**
     * @param list<array<string,mixed>> $publicBands
     * @return array<string,mixed>|null
     */
    private function matchAttendanceBand(array $publicBands, int $guestCount): ?array
    {
        foreach ($publicBands as $row) {
            $min = (int) ($row['min_attendance'] ?? 0);
            $max = (int) ($row['max_attendance'] ?? 0);
            if ($min > 0 && $max > 0 && $guestCount >= $min && $guestCount <= $max) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param list<array<string,mixed>> $guestTiers
     * @param list<array<string,mixed>> $durationTiers
     * @param list<array<string,mixed>> $timeBlocks
     * @param list<array<string,mixed>> $packages
     * @param array<string,mixed>|null $quantityPricing
     * @return array{lines: list<array{code:string,label:string,amount:float}>, warnings: list<string>, errors: list<string>}
     */
    private function privateEventSubtotal(
        array $service,
        ?array $privatePricing,
        array $guestTiers,
        array $durationTiers,
        array $timeBlocks,
        array $packages,
        array $quantityTiers = [],
        int $guestCount,
        ?string $pricingOption,
        ?int $orderQuantity = null
    ): array {
        $lines    = [];
        $warnings = [];
        $errors   = [];

        $type = $privatePricing['pricing_type'] ?? null;

        if ($type === 'guest_based_pricing') {
            $warnings = array_merge($warnings, $this->validateGuestTierCoverage($guestTiers, $guestCount));

            $tier = $this->resolveGuestTier($guestTiers, $guestCount, $pricingOption);
            if ($tier === null) {
                $rangeSummary = $this->formatGuestTierRanges($guestTiers);
                if ($rangeSummary !== '') {
                    $errors[] = sprintf(
                        'Guest count (%d) does not match any guest-based price band for this service. Configured bands: %s.',
                        $guestCount,
                        $rangeSummary
                    );
                } else {
                    $errors[] = 'Guest count does not match any guest-based price band for this service.';
                }

                return compact('lines', 'warnings', 'errors');
            }

            if ($this->isNearGuestTierEdge($tier, $guestCount)) {
                $warnings[] = 'Your guest count is near the edge of a pricing band; confirm the correct tier with the vendor.';
            }

            $perHead = (float) ($tier['guest_price'] ?? 0);
            $sub     = round($perHead * $guestCount, 2);
            $lines[] = [
                'code'   => 'guest_based',
                'label'  => 'Guest-based service (' . $guestCount . ' guests × £' . number_format($perHead, 2) . ')',
                'amount' => $sub,
            ];
            return compact('lines', 'warnings', 'errors');
        }

        if ($type === 'quantity_based_pricing') {
            if ($quantityTiers === []) {
                $errors[] = 'This service does not have quantity-based pricing configured.';
                return compact('lines', 'warnings', 'errors');
            }

            $warnings = array_merge($warnings, $this->validateQuantityTierCoverage($quantityTiers));

            $qty = $this->resolveOrderQuantityFromTiers($quantityTiers, $pricingOption, $orderQuantity);
            if ($qty === null) {
                $errors[] = 'Please enter a valid order quantity for this service.';
                return compact('lines', 'warnings', 'errors');
            }

            $tier = $this->resolveQuantityTier($quantityTiers, $qty);
            if ($tier === null) {
                $rangeSummary = $this->formatQuantityTierRanges($quantityTiers);
                if ($rangeSummary !== '') {
                    $errors[] = sprintf(
                        'Order quantity (%d) does not match any price band for this service. Configured bands: %s.',
                        $qty,
                        $rangeSummary
                    );
                } else {
                    $errors[] = 'Order quantity does not match any price band for this service.';
                }

                return compact('lines', 'warnings', 'errors');
            }

            if ($this->isNearQuantityTierEdge($tier, $qty)) {
                $warnings[] = 'Your order quantity is near the edge of a price band; confirm the correct unit price with the vendor.';
            }

            $unitPrice = (float) ($tier['unit_price'] ?? 0);
            if ($unitPrice <= 0) {
                $errors[] = 'Quantity-based unit price is not configured for this service.';
                return compact('lines', 'warnings', 'errors');
            }

            $unitLabel = trim((string) ($tier['unit_label'] ?? 'items'));
            if ($unitLabel === '') {
                $unitLabel = 'items';
            }

            $serviceName = trim((string) ($service['title'] ?? 'Service'));
            $sub = round($unitPrice * $qty, 2);
            $lines[] = [
                'code'   => 'quantity_based',
                'label'  => sprintf(
                    '%s (%d %s × £%s)',
                    $serviceName,
                    $qty,
                    $unitLabel,
                    number_format($unitPrice, 2)
                ),
                'amount' => $sub,
            ];

            return compact('lines', 'warnings', 'errors');
        }

        if ($type === 'custom_duration_pricing') {
            $timeBlock = $this->resolveTimeBlockRow($timeBlocks, $pricingOption);
            if ($timeBlock !== null) {
                $price = (float) ($timeBlock['price'] ?? 0);
                $lines[] = [
                    'code'   => 'time_block',
                    'label'  => 'Time slot (' . $this->formatTimeBlockRange($timeBlock) . ')',
                    'amount' => round($price, 2),
                ];

                return compact('lines', 'warnings', 'errors');
            }

            $row = $this->resolveDurationRow($durationTiers, $pricingOption);
            if ($row === null) {
                $errors[] = 'Please choose a duration option for this service.';
                return compact('lines', 'warnings', 'errors');
            }
            $price = (float) ($row['price'] ?? 0);
            $unit  = ($row['duration_type'] ?? '') === 'day' ? 'day(s)' : 'hour(s)';
            $lines[] = [
                'code'   => 'duration',
                'label'  => 'Duration (' . (int) ($row['duration'] ?? 0) . ' ' . $unit . ')',
                'amount' => round($price, 2),
            ];
            return compact('lines', 'warnings', 'errors');
        }

        if ($type === 'tiered_packages_pricing') {
            $row = $this->resolvePackageRow($packages, $pricingOption);
            if ($row === null) {
                $errors[] = 'Please choose a package for this service.';
                return compact('lines', 'warnings', 'errors');
            }
            $price = (float) ($row['package_price'] ?? 0);
            $name  = (string) ($row['package_name'] ?? 'Package');
            $lines[] = [
                'code'   => 'package',
                'label'  => 'Package: ' . $name,
                'amount' => round($price, 2),
            ];
            return compact('lines', 'warnings', 'errors');
        }

        $fallback = (float) ($service['price'] ?? 0);
        if ($fallback > 0) {
            $lines[] = [
                'code'   => 'listing_price',
                'label'  => 'Service fee',
                'amount' => round($fallback, 2),
            ];
            $warnings[] = 'This service has no structured private pricing; using the listing price only.';
        } else {
            $errors[] = 'This service does not have applicable private pricing for your event.';
        }

        return compact('lines', 'warnings', 'errors');
    }

    /**
     * @param list<array<string,mixed>> $guestTiers
     * @return array<string,mixed>|null
     */
    private function resolveGuestTier(array $guestTiers, int $guestCount, ?string $pricingOption): ?array
    {
        // Only honour guest_<id> when that tier actually covers the event guest count (ignore stale UI/session picks).
        if ($pricingOption !== null && preg_match('/^guest_(\d+)$/', $pricingOption, $m)) {
            $id = (int) $m[1];
            foreach ($guestTiers as $t) {
                if ((int) ($t['id'] ?? 0) !== $id) {
                    continue;
                }
                $min = (int) ($t['min_guest'] ?? $t['min_guests'] ?? 0);
                $max = (int) ($t['max_guest'] ?? $t['max_guests'] ?? 0);
                if ($min > 0 && $max > 0 && $guestCount >= $min && $guestCount <= $max) {
                    return $t;
                }
                break;
            }
        }

        foreach ($guestTiers as $t) {
            $min = (int) ($t['min_guest'] ?? $t['min_guests'] ?? 0);
            $max = (int) ($t['max_guest'] ?? $t['max_guests'] ?? 0);
            if ($min > 0 && $max > 0 && $guestCount >= $min && $guestCount <= $max) {
                return $t;
            }
        }

        return null;
    }

    /**
     * @param list<array<string,mixed>> $timeBlocks
     * @return array<string,mixed>|null
     */
    private function resolveTimeBlockRow(array $timeBlocks, ?string $pricingOption): ?array
    {
        if ($timeBlocks === [] || $pricingOption === null) {
            return null;
        }
        if (!preg_match('/^timeblock_(\d+)$/', $pricingOption, $m)) {
            return null;
        }
        $id = (int) $m[1];
        foreach ($timeBlocks as $block) {
            if ((int) ($block['id'] ?? 0) === $id) {
                return $block;
            }
        }

        return null;
    }

    /**
     * @param array<string,mixed> $block
     */
    private function formatTimeBlockRange(array $block): string
    {
        $start = $this->formatClockTime((string) ($block['start_time'] ?? ''));
        $end = $this->formatClockTime((string) ($block['end_time'] ?? ''));
        if ($start === '' || $end === '') {
            return 'selected slot';
        }

        return $start . ' – ' . $end;
    }

    private function formatClockTime(string $time): string
    {
        $time = trim($time);
        if ($time === '') {
            return '';
        }
        if (preg_match('/^(\d{1,2}):(\d{2})/', $time, $m)) {
            return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }

        return $time;
    }

    /**
     * @param list<array<string,mixed>> $durationTiers
     * @return array<string,mixed>|null
     */
    private function resolveDurationRow(array $durationTiers, ?string $pricingOption): ?array
    {
        if ($pricingOption !== null && preg_match('/^duration_(\d+)$/', $pricingOption, $m)) {
            $id = (int) $m[1];
            foreach ($durationTiers as $t) {
                if ((int) ($t['id'] ?? 0) === $id) {
                    return $t;
                }
            }
        }

        return $durationTiers[0] ?? null;
    }

    /**
     * @param list<array<string,mixed>> $packages
     * @return array<string,mixed>|null
     */
    private function resolvePackageRow(array $packages, ?string $pricingOption): ?array
    {
        if ($pricingOption !== null && preg_match('/^package_(\d+)$/', $pricingOption, $m)) {
            $id = (int) $m[1];
            foreach ($packages as $t) {
                if ((int) ($t['id'] ?? 0) === $id) {
                    return $t;
                }
            }
        }

        return $packages[0] ?? null;
    }

    /**
     * @param list<array<string,mixed>> $quantityTiers
     */
    private function resolveOrderQuantityFromTiers(array $quantityTiers, ?string $pricingOption, ?int $explicitOrderQty = null): ?int
    {
        $bounds = $this->quantityTierGlobalBounds($quantityTiers);
        $minQ = $bounds['min'];
        $maxQ = $bounds['max'];

        $qty = null;
        if ($explicitOrderQty !== null && $explicitOrderQty > 0) {
            $qty = $explicitOrderQty;
        } elseif ($pricingOption !== null && preg_match('/^qty_(\d+)$/', $pricingOption, $m)) {
            $qty = (int) $m[1];
        }

        if ($qty === null || $qty <= 0) {
            $qty = $minQ;
        }

        $qty = max($qty, $minQ);
        if ($maxQ !== null) {
            $qty = min($qty, $maxQ);
        }

        return $qty > 0 ? $qty : null;
    }

    /**
     * @param list<array<string,mixed>> $quantityTiers
     * @return array{min: int, max: int|null}
     */
    private function quantityTierGlobalBounds(array $quantityTiers): array
    {
        $minQ = PHP_INT_MAX;
        $maxQ = null;
        $hasUnlimited = false;
        foreach ($quantityTiers as $tier) {
            $tMin = max(1, (int) ($tier['min_quantity'] ?? 1));
            $minQ = min($minQ, $tMin);
            $tMaxRaw = $tier['max_quantity'] ?? null;
            if ($tMaxRaw !== null && $tMaxRaw !== '') {
                $tMax = max($tMin, (int) $tMaxRaw);
                $maxQ = $maxQ === null ? $tMax : max($maxQ, $tMax);
            } else {
                $hasUnlimited = true;
            }
        }

        if ($hasUnlimited) {
            $maxQ = null;
        }

        return ['min' => $minQ === PHP_INT_MAX ? 1 : $minQ, 'max' => $maxQ];
    }

    /**
     * @param list<array<string,mixed>> $quantityTiers
     * @return array<string,mixed>|null
     */
    private function resolveQuantityTier(array $quantityTiers, int $qty): ?array
    {
        foreach ($quantityTiers as $tier) {
            $min = max(1, (int) ($tier['min_quantity'] ?? 1));
            $maxRaw = $tier['max_quantity'] ?? null;
            $max = ($maxRaw !== null && $maxRaw !== '') ? max($min, (int) $maxRaw) : null;
            if ($qty >= $min && ($max === null || $qty <= $max)) {
                return $tier;
            }
        }

        return null;
    }

    /**
     * @param list<array<string,mixed>> $quantityTiers
     * @return list<string>
     */
    private function validateQuantityTierCoverage(array $quantityTiers): array
    {
        $warnings = [];
        $ranges = $this->collectQuantityTierRanges($quantityTiers);
        if (count($ranges) < 2) {
            return $warnings;
        }

        usort($ranges, static fn ($a, $b) => $a['min'] <=> $b['min']);
        for ($i = 0; $i < count($ranges) - 1; $i++) {
            if ($ranges[$i]['max'] !== null && $ranges[$i + 1]['min'] > $ranges[$i]['max'] + 1) {
                $warnings[] = sprintf(
                    'Quantity bands have a gap between %d and %d; confirm pricing with the vendor if your order falls in that range.',
                    $ranges[$i]['max'] + 1,
                    $ranges[$i + 1]['min'] - 1
                );
            }
        }

        return $warnings;
    }

    /**
     * @param list<array<string,mixed>> $quantityTiers
     * @return list<array{min: int, max: int|null}>
     */
    private function collectQuantityTierRanges(array $quantityTiers): array
    {
        $ranges = [];
        foreach ($quantityTiers as $tier) {
            $min = max(1, (int) ($tier['min_quantity'] ?? 1));
            $maxRaw = $tier['max_quantity'] ?? null;
            $max = ($maxRaw !== null && $maxRaw !== '') ? max($min, (int) $maxRaw) : null;
            $ranges[] = ['min' => $min, 'max' => $max];
        }

        return $ranges;
    }

    /**
     * @param list<array<string,mixed>> $quantityTiers
     */
    private function formatQuantityTierRanges(array $quantityTiers): string
    {
        $parts = [];
        foreach ($quantityTiers as $tier) {
            $min = max(1, (int) ($tier['min_quantity'] ?? 1));
            $maxRaw = $tier['max_quantity'] ?? null;
            $price = (float) ($tier['unit_price'] ?? 0);
            if ($maxRaw !== null && $maxRaw !== '') {
                $parts[] = sprintf('%d–%d @ £%s', $min, (int) $maxRaw, number_format($price, 2));
            } else {
                $parts[] = sprintf('%d+ @ £%s', $min, number_format($price, 2));
            }
        }

        return implode('; ', $parts);
    }

    /**
     * @param array<string,mixed> $tier
     */
    private function isNearQuantityTierEdge(array $tier, int $qty): bool
    {
        $min = max(1, (int) ($tier['min_quantity'] ?? 1));
        $maxRaw = $tier['max_quantity'] ?? null;
        if ($maxRaw === null || $maxRaw === '') {
            return false;
        }
        $max = max($min, (int) $maxRaw);
        $width = $max - $min + 1;
        $threshold = max(1, (int) ceil($width * 0.05));

        return ($qty - $min) < $threshold || ($max - $qty) < $threshold;
    }

    /**
     * @param array<int, float|array{price: float, name?: string, pricing_type?: string, min_quantity?: int|null, max_quantity?: int|null, unit_label?: string|null}> $extrasById
     * @param list<int|string> $selectedExtraIds
     * @param int $defaultItemQuantity Default quantity for per_item extras when no override is given
     * @param array<int, int> $extraQuantitiesById
     * @return array{lines: list<array{code:string,label:string,amount:float}>}
     */
    private function extrasLines(array $extrasById, array $selectedExtraIds, int $defaultItemQuantity, array $extraQuantitiesById = []): array
    {
        $lines = [];
        foreach ($selectedExtraIds as $rawId) {
            $id = (int) $rawId;
            if ($id <= 0 || !array_key_exists($id, $extrasById)) {
                continue;
            }
            $meta  = $extrasById[$id];
            $price = is_array($meta) ? (float) ($meta['price'] ?? 0) : (float) $meta;
            $name  = is_array($meta) ? (string) ($meta['name'] ?? 'Optional extra') : 'Optional extra';

            $pricingType = is_array($meta)
                ? strtolower(trim((string) ($meta['pricing_type'] ?? 'flat')))
                : 'flat';
            if ($pricingType !== 'per_item') {
                $lines[] = [
                    'code'   => 'extra_' . $id,
                    'label'  => 'Optional extra: ' . $name,
                    'amount' => round($price, 2),
                ];
                continue;
            }

            $minConfigured = is_array($meta) && array_key_exists('min_quantity', $meta) && $meta['min_quantity'] !== null && $meta['min_quantity'] !== ''
                ? (int) $meta['min_quantity']
                : 0;
            $maxConfigured = is_array($meta) && array_key_exists('max_quantity', $meta) && $meta['max_quantity'] !== null && $meta['max_quantity'] !== ''
                ? (int) $meta['max_quantity']
                : 0;

            $minQ = $minConfigured > 0 ? max(1, $minConfigured) : 1;
            $maxQ = $maxConfigured > 0 ? max($minQ, $maxConfigured) : null;

            $requested = $extraQuantitiesById[$id] ?? null;
            if ($requested === null || $requested <= 0) {
                $qty = $defaultItemQuantity;
            } else {
                $qty = (int) $requested;
            }

            $qty = max($qty, $minQ);
            if ($maxQ !== null) {
                $qty = min($qty, $maxQ);
            }

            $unitWord = '';
            if (is_array($meta)) {
                $unitWord = trim((string) ($meta['unit_label'] ?? ''));
            }
            if ($unitWord === '') {
                $unitWord = 'item';
            }

            $amount = round($price * $qty, 2);
            $lines[] = [
                'code'   => 'extra_' . $id,
                'label'  => sprintf(
                    'Optional extra: %s (%d %s × £%s)',
                    $name,
                    $qty,
                    $unitWord,
                    number_format($price, 2)
                ),
                'amount' => $amount,
            ];
        }

        return ['lines' => $lines];
    }

    /**
     * @param array<string,mixed> $service
     * @param array<string,mixed>|null $location
     * @param array<string,mixed> $event
     */
    private function distanceKm(array $service, ?array $location, array $event): ?float
    {
        $vLat = null;
        $vLon = null;
        if ($location !== null) {
            $lat = $location['latitude'] ?? null;
            $lon = $location['longitude'] ?? null;
            if ($lat !== null && $lat !== '' && $lon !== null && $lon !== '') {
                $vLat = (float) $lat;
                $vLon = (float) $lon;
            }
        }
        if ($vLat === null || $vLon === null) {
            $slat = $service['latitude'] ?? null;
            $slon = $service['longitude'] ?? null;
            if ($slat !== null && $slat !== '' && $slon !== null && $slon !== '') {
                $vLat = (float) $slat;
                $vLon = (float) $slon;
            }
        }

        if ($vLat === null || $vLon === null) {
            return null;
        }

        $elat = $event['latitude'] ?? null;
        $elon = $event['longitude'] ?? null;
        if ($elat === null || $elat === '' || $elon === null || $elon === '') {
            return null;
        }

        $eLat = (float) $elat;
        $eLon = (float) $elon;

        return round(self::haversineKm($vLat, $vLon, $eLat, $eLon), 1);
    }

    public static function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371.0;
        $dLat  = deg2rad($lat2 - $lat1);
        $dLon  = deg2rad($lon2 - $lon1);
        $a     = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earth * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    /**
     * Travel rules mirror vendor step 4 / review copy.
     *
     * @param array<string,mixed> $loc Normalised location row (may be empty)
     * @return array{lines: list<array{code:string,label:string,amount:float}>, warnings: list<string>}
     */
    public function computeTravel(float $distanceKm, array $loc): array
    {
        $lines    = [];
        $warnings = [];

        $all   = !empty($loc['all_travel_included']);
        $nat   = !empty($loc['no_travel_limit']);
        $free  = isset($loc['free_coverage_radius']) && $loc['free_coverage_radius'] !== '' ? (int) $loc['free_coverage_radius'] : 0;
        $paid  = isset($loc['paid_coverage_radius']) && $loc['paid_coverage_radius'] !== '' ? (int) $loc['paid_coverage_radius'] : 0;
        $fee   = isset($loc['travel_fee_per_km']) && $loc['travel_fee_per_km'] !== '' ? (float) $loc['travel_fee_per_km'] : 0.0;

        $d = max(0.0, $distanceKm);

        if ($all && $nat) {
            return ['lines' => [], 'warnings' => []];
        }

        if ($all && !$nat) {
            if ($free <= 0) {
                $warnings[] = 'Vendor includes travel but did not specify a radius; travel charge assumed £0.';
                return ['lines' => [], 'warnings' => $warnings];
            }
            if ($d <= $free) {
                return ['lines' => [], 'warnings' => []];
            }
            if ($fee <= 0) {
                $warnings[] = 'Distance exceeds the vendor’s included travel radius; per-km rate not set—confirm travel cost with the vendor.';
                return ['lines' => [], 'warnings' => $warnings];
            }
            $km   = $d - $free;
            $cost = round($km * $fee, 2);
            $lines[] = [
                'code'   => 'travel',
                'label'  => sprintf('Travel beyond %d km included (%.1f km × £%s / km)', $free, $km, number_format($fee, 2)),
                'amount' => $cost,
            ];
            return compact('lines', 'warnings');
        }

        if ($nat && !$all) {
            if ($free > 0) {
                $km = max(0.0, $d - $free);
                $cost = round($km * $fee, 2);
                if ($km > 0) {
                    $lines[] = [
                        'code'   => 'travel',
                        'label'  => sprintf('Travel after %d free km (%.1f km × £%s / km)', $free, $km, number_format($fee, 2)),
                        'amount' => $cost,
                    ];
                }
                return compact('lines', 'warnings');
            }
            $cost = round($d * $fee, 2);
            $lines[] = [
                'code'   => 'travel',
                'label'  => sprintf('Travel (%.1f km × £%s / km)', $d, number_format($fee, 2)),
                'amount' => $cost,
            ];
            return compact('lines', 'warnings');
        }

        if ($free > 0 && $paid > 0 && $fee > 0) {
            $billable = min(max(0.0, $d - $free), (float) $paid);
            $cost     = round($billable * $fee, 2);
            if ($billable > 0) {
                $lines[] = [
                    'code'   => 'travel',
                    'label'  => sprintf(
                        'Travel (%.1f billable km within %d–%d km zone × £%s / km)',
                        $billable,
                        $free,
                        $free + $paid,
                        number_format($fee, 2)
                    ),
                    'amount' => $cost,
                ];
            }
            if ($d > $free + $paid) {
                $warnings[] = sprintf(
                    'Venue is about %.1f km away; this exceeds the vendor’s quoted maximum service radius (%d km). Confirm availability.',
                    $d,
                    $free + $paid
                );
            }
            return compact('lines', 'warnings');
        }

        if ($free > 0 && $paid <= 0) {
            if ($d <= $free) {
                return ['lines' => [], 'warnings' => []];
            }
            if ($fee > 0) {
                $km = $d - $free;
                $lines[] = [
                    'code'   => 'travel',
                    'label'  => sprintf('Travel beyond %d km included (%.1f km × £%s / km)', $free, $km, number_format($fee, 2)),
                    'amount' => round($km * $fee, 2),
                ];
                return compact('lines', 'warnings');
            }
            $warnings[] = 'Venue is outside the vendor’s included travel radius; confirm travel cost with the vendor.';
            return compact('lines', 'warnings');
        }

        if ($free <= 0 && $paid > 0 && $fee > 0) {
            $bill = min($d, (float) $paid);
            $lines[] = [
                'code'   => 'travel',
                'label'  => sprintf('Travel (up to %.1f km charged × £%s / km)', $bill, number_format($fee, 2)),
                'amount' => round($bill * $fee, 2),
            ];
            if ($d > $paid) {
                $warnings[] = 'Venue may be beyond the maximum distance this vendor quoted; confirm availability.';
            }
            return compact('lines', 'warnings');
        }

        if ($free > 0 && $fee <= 0) {
            if ($d <= $free) {
                return ['lines' => [], 'warnings' => []];
            }
            $warnings[] = 'Venue is outside the vendor’s included travel radius; no per-km rate is configured—confirm with the vendor.';
            return compact('lines', 'warnings');
        }

        $warnings[] = 'Travel pricing is incomplete for this listing; confirm travel with the vendor.';
        return compact('lines', 'warnings');
    }

    /**
     * @param array<string,mixed> $event
     */
    private function isCorporateEvent(array $event): bool
    {
        $type = strtolower(trim((string) ($event['event_type'] ?? '')));

        return $type === 'corporate' || str_contains($type, 'corporate');
    }

    /**
     * @param array<string,mixed> $corporatePricing
     * @param list<array{code:string,label:string,amount:float}> $existingLines
     * @return array{lines: list<array{code:string,label:string,amount:float}>, warnings: list<string>, errors: list<string>}
     */
    private function corporateModifiers(array $corporatePricing, array $existingLines): array
    {
        $lines = [];
        $warnings = [];
        $errors = [];

        if (empty($corporatePricing['corporate_enabled'])) {
            $errors[] = 'This vendor has not enabled corporate event bookings for this listing.';
            return compact('lines', 'warnings', 'errors');
        }

        $subtotal = 0.0;
        foreach ($existingLines as $line) {
            $subtotal += (float) ($line['amount'] ?? 0);
        }

        $minSpend = isset($corporatePricing['corporate_min_spend']) && $corporatePricing['corporate_min_spend'] !== ''
            ? (float) $corporatePricing['corporate_min_spend']
            : 0.0;
        if ($minSpend > 0 && $subtotal < $minSpend - 0.004) {
            $shortfall = round($minSpend - $subtotal, 2);
            $lines[] = [
                'code' => 'corporate_min_spend',
                'label' => 'Minimum corporate spend adjustment',
                'amount' => $shortfall,
            ];
            $warnings[] = 'A minimum corporate spend applies; the estimate includes an adjustment to meet it.';
        }

        $sType = strtolower(trim((string) ($corporatePricing['corporate_surcharge_type'] ?? 'none')));
        $sValue = isset($corporatePricing['corporate_surcharge_value']) && $corporatePricing['corporate_surcharge_value'] !== ''
            ? (float) $corporatePricing['corporate_surcharge_value']
            : 0.0;
        if ($sType === 'percent' && $sValue > 0) {
            $amount = round($subtotal * ($sValue / 100), 2);
            if ($amount > 0) {
                $lines[] = [
                    'code' => 'corporate_surcharge',
                    'label' => sprintf('Corporate surcharge (%s%%)', number_format($sValue, 1)),
                    'amount' => $amount,
                ];
            }
        } elseif ($sType === 'flat' && $sValue > 0) {
            $lines[] = [
                'code' => 'corporate_surcharge',
                'label' => 'Corporate surcharge (flat)',
                'amount' => round($sValue, 2),
            ];
        }

        $invoiceFee = isset($corporatePricing['corporate_invoice_fee']) && $corporatePricing['corporate_invoice_fee'] !== ''
            ? (float) $corporatePricing['corporate_invoice_fee']
            : 0.0;
        if ($invoiceFee > 0) {
            $lines[] = [
                'code' => 'corporate_invoice_fee',
                'label' => 'Corporate invoicing fee',
                'amount' => round($invoiceFee, 2),
            ];
        }

        return compact('lines', 'warnings', 'errors');
    }

    /**
     * @param list<array{code:string,label:string,amount:float}> $lines
     */
    private function sumLineAmounts(array $lines): float
    {
        $total = 0.0;
        foreach ($lines as $line) {
            if (($line['code'] ?? '') === 'platform_commission') {
                continue;
            }
            $total += (float) ($line['amount'] ?? 0);
        }

        return round($total, 2);
    }

    /**
     * @param array<string,mixed> $loc
     * @param array<string,mixed> $event
     * @return array{lines: list<array{code:string,label:string,amount:float}>, warnings: list<string>}
     */
    private function postalLines(array $loc, float $merchandiseSubtotal = 0.0, array $event = []): array
    {
        $lines = [];
        $warnings = [];
        $ftype = strtolower(trim((string) ($loc['fulfillment_type'] ?? 'in_person')));
        if ($ftype !== 'postal' && $ftype !== 'both') {
            return compact('lines', 'warnings');
        }

        $warnings = array_merge($warnings, $this->deliveryLeadTimeWarnings($loc, $event));

        $freeAbove = isset($loc['free_postage_above']) && $loc['free_postage_above'] !== '' && $loc['free_postage_above'] !== null
            ? (float) $loc['free_postage_above']
            : null;
        if ($freeAbove !== null && $freeAbove > 0 && $merchandiseSubtotal >= $freeAbove - 0.004) {
            $warnings[] = sprintf('Free postage applied (order over £%s)', number_format($freeAbove, 2));

            return compact('lines', 'warnings');
        }

        $fee = isset($loc['postal_fee']) && $loc['postal_fee'] !== '' && $loc['postal_fee'] !== null
            ? (float) $loc['postal_fee']
            : 0.0;
        if ($fee <= 0) {
            $warnings[] = 'Postal delivery is offered but no postage fee is configured; confirm delivery cost with the vendor.';

            return compact('lines', 'warnings');
        }

        $lines[] = [
            'code' => 'postal_fee',
            'label' => $ftype === 'both' ? 'Postage / delivery fee' : 'Postage fee',
            'amount' => round($fee, 2),
        ];

        return compact('lines', 'warnings');
    }

    /**
     * @param array<string,mixed> $loc
     * @param array<string,mixed> $event
     * @return list<string>
     */
    private function deliveryLeadTimeWarnings(array $loc, array $event): array
    {
        $warnings = [];
        $leadDays = isset($loc['delivery_lead_time_days']) && $loc['delivery_lead_time_days'] !== '' && $loc['delivery_lead_time_days'] !== null
            ? (int) $loc['delivery_lead_time_days']
            : 0;
        if ($leadDays <= 0) {
            return $warnings;
        }

        $dayWord = $leadDays === 1 ? 'day' : 'days';
        $warnings[] = sprintf(
            'Allow at least %d working %s before your event date for dispatch',
            $leadDays,
            $dayWord
        );

        $eventDate = $event['date'] ?? null;
        if ($eventDate === null || $eventDate === '') {
            return $warnings;
        }

        $eventTs = strtotime((string) $eventDate);
        if ($eventTs === false) {
            return $warnings;
        }

        $daysUntil = (int) floor(($eventTs - strtotime('today')) / 86400);
        if ($daysUntil < $leadDays) {
            $warnings[] = sprintf(
                'Your event date may be too soon for postal dispatch; allow at least %d working %s before the event.',
                $leadDays,
                $dayWord
            );
        }

        return $warnings;
    }

    /**
     * @param list<array<string,mixed>> $publicBands
     * @param list<array{code:string,label:string,amount:float}> $linesSoFar
     * @return array{lines: list<array{code:string,label:string,amount:float}>, warnings: list<string>}
     */
    private function publicCommissionLine(array $publicBands, int $guestCount, array $linesSoFar): array
    {
        $lines = [];
        $warnings = [];
        $band = $this->matchAttendanceBand($publicBands, $guestCount);
        if ($band === null) {
            return compact('lines', 'warnings');
        }

        $pct = isset($band['commission_percentage']) && $band['commission_percentage'] !== '' && $band['commission_percentage'] !== null
            ? (float) $band['commission_percentage']
            : 0.0;
        if ($pct <= 0) {
            return compact('lines', 'warnings');
        }

        $subtotal = 0.0;
        foreach ($linesSoFar as $line) {
            $subtotal += (float) ($line['amount'] ?? 0);
        }
        if ($subtotal <= 0) {
            return compact('lines', 'warnings');
        }

        $commission = round($subtotal * ($pct / 100), 2);
        if ($commission > 0) {
            $lines[] = [
                'code' => 'platform_commission',
                'label' => sprintf('Platform commission estimate (%s%% — informational)', number_format($pct, 1)),
                'amount' => $commission,
            ];
            $warnings[] = 'Commission line is informational for public events; confirm final payout with the vendor.';
        }

        return compact('lines', 'warnings');
    }

    /**
     * @param array<string,mixed> $event
     * @return array{warnings: list<string>, errors: list<string>}
     */
    private function budgetValidation(array $event, float $total): array
    {
        $warnings = [];
        $errors = [];
        $min = isset($event['budget_min']) && $event['budget_min'] !== '' && $event['budget_min'] !== null
            ? (float) $event['budget_min']
            : null;
        $max = isset($event['budget_max']) && $event['budget_max'] !== '' && $event['budget_max'] !== null
            ? (float) $event['budget_max']
            : null;

        if ($max !== null && $max > 0 && $total > $max + 0.004) {
            $warnings[] = sprintf(
                'Estimated total (£%s) exceeds your event budget maximum (£%s).',
                number_format($total, 2),
                number_format($max, 2)
            );
        }
        if ($min !== null && $min > 0 && $total < $min - 0.004) {
            $warnings[] = sprintf(
                'Estimated total (£%s) is below your stated budget minimum (£%s).',
                number_format($total, 2),
                number_format($min, 2)
            );
        }

        return compact('warnings', 'errors');
    }

    private function isTravelRadiusWarning(string $message): bool
    {
        return str_contains($message, 'exceeds the vendor')
            || str_contains($message, 'beyond the maximum')
            || str_contains($message, 'outside the vendor');
    }

    /**
     * @param list<array<string,mixed>> $guestTiers
     * @return list<string>
     */
    private function validateGuestTierCoverage(array $guestTiers, int $guestCount): array
    {
        $ranges = $this->collectGuestTierRanges($guestTiers);
        if ($ranges === [] || !$this->countFallsInRangeGap($ranges, $guestCount)) {
            return [];
        }

        return [
            sprintf(
                'Your event has %d guests, which falls between configured guest pricing bands (%s). Confirm pricing with the vendor.',
                $guestCount,
                $this->formatRangeList($ranges)
            ),
        ];
    }

    /**
     * @param list<array<string,mixed>> $publicBands
     * @return list<string>
     */
    private function validatePublicAttendanceCoverage(array $publicBands, int $guestCount): array
    {
        $ranges = $this->collectAttendanceBandRanges($publicBands);
        if ($ranges === [] || !$this->countFallsInRangeGap($ranges, $guestCount)) {
            return [];
        }

        return [
            sprintf(
                'Expected attendance (%d) falls between configured public event bands (%s). Confirm pricing with the vendor.',
                $guestCount,
                $this->formatRangeList($ranges)
            ),
        ];
    }

    /**
     * @param list<array{min:int,max:int}> $ranges
     */
    private function countFallsInRangeGap(array $ranges, int $count): bool
    {
        if ($this->countMatchesAnyRange($ranges, $count)) {
            return false;
        }

        usort($ranges, static fn (array $a, array $b): int => $a['min'] <=> $b['min']);

        foreach ($ranges as $i => $range) {
            if ($count < $range['min']) {
                return true;
            }
            if ($count > $range['max'] && isset($ranges[$i + 1]) && $count < $ranges[$i + 1]['min']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array{min:int,max:int}> $ranges
     */
    private function countMatchesAnyRange(array $ranges, int $count): bool
    {
        foreach ($ranges as $range) {
            if ($count >= $range['min'] && $count <= $range['max']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array<string,mixed>> $guestTiers
     * @return list<array{min:int,max:int}>
     */
    private function collectGuestTierRanges(array $guestTiers): array
    {
        $ranges = [];
        foreach ($guestTiers as $tier) {
            [$min, $max] = $this->normalizeGuestTierBounds($tier);
            if ($min > 0 && $max > 0 && $max >= $min) {
                $ranges[] = ['min' => $min, 'max' => $max];
            }
        }

        return $ranges;
    }

    /**
     * @param list<array<string,mixed>> $publicBands
     * @return list<array{min:int,max:int}>
     */
    private function collectAttendanceBandRanges(array $publicBands): array
    {
        $ranges = [];
        foreach ($publicBands as $row) {
            $min = (int) ($row['min_attendance'] ?? 0);
            $max = (int) ($row['max_attendance'] ?? 0);
            if ($min > 0 && $max > 0 && $max >= $min) {
                $ranges[] = ['min' => $min, 'max' => $max];
            }
        }

        return $ranges;
    }

    /**
     * @param list<array{min:int,max:int}> $ranges
     */
    private function formatRangeList(array $ranges): string
    {
        usort($ranges, static fn (array $a, array $b): int => $a['min'] <=> $b['min']);
        $parts = [];
        foreach ($ranges as $range) {
            $parts[] = $range['min'] . '–' . $range['max'];
        }

        return implode(', ', $parts);
    }

    /**
     * @param list<array<string,mixed>> $guestTiers
     */
    private function formatGuestTierRanges(array $guestTiers): string
    {
        $ranges = $this->collectGuestTierRanges($guestTiers);
        if ($ranges === []) {
            return '';
        }

        $parts = [];
        foreach ($ranges as $range) {
            $parts[] = $range['min'] . '–' . $range['max'] . ' guests';
        }

        return implode(', ', $parts);
    }

    /**
     * @param list<array<string,mixed>> $publicBands
     */
    private function formatAttendanceBandRanges(array $publicBands): string
    {
        $ranges = $this->collectAttendanceBandRanges($publicBands);
        if ($ranges === []) {
            return '';
        }

        $parts = [];
        foreach ($ranges as $range) {
            $parts[] = $range['min'] . '–' . $range['max'] . ' attendees';
        }

        return implode(', ', $parts);
    }

    /**
     * @param array<string,mixed> $tier
     */
    private function normalizeGuestTierBounds(array $tier): array
    {
        $min = (int) ($tier['min_guest'] ?? $tier['min_guests'] ?? 0);
        $max = (int) ($tier['max_guest'] ?? $tier['max_guests'] ?? 0);

        return [$min, $max];
    }

    /**
     * @param array<string,mixed> $tier
     */
    private function isNearGuestTierEdge(array $tier, int $guestCount): bool
    {
        [$min, $max] = $this->normalizeGuestTierBounds($tier);
        if ($min <= 0 || $max <= 0 || $max < $min) {
            return false;
        }

        $threshold = max(1, (int) ceil(($max - $min) * 0.05));

        return ($guestCount - $min) <= $threshold || ($max - $guestCount) <= $threshold;
    }

    /**
     * @param array<string,mixed> $band
     */
    private function isNearAttendanceBandEdge(array $band, int $guestCount): bool
    {
        $min = (int) ($band['min_attendance'] ?? 0);
        $max = (int) ($band['max_attendance'] ?? 0);
        if ($min <= 0 || $max <= 0 || $max < $min) {
            return false;
        }

        $threshold = max(5, (int) ceil(($max - $min) * 0.05));

        return ($guestCount - $min) <= $threshold || ($max - $guestCount) <= $threshold;
    }
}
