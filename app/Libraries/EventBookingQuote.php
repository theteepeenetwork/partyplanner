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
     * @param list<array<string,mixed>> $packages
     * @param array<int, float|array{price: float, name?: string, pricing_type?: string, min_quantity?: int|null, max_quantity?: int|null, unit_label?: string|null}> $extrasById
     * @param list<int|string> $selectedExtraIds
     * @param string|null $pricingOption               e.g. guest_12, duration_3, package_4
     * @param array<int, int> $extraQuantitiesById     Optional per-extra quantity overrides (per_item extras only)
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
        array $extrasById,
        array $selectedExtraIds,
        ?string $pricingOption,
        array $extraQuantitiesById = []
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
        } else {
            $privateResult = $this->privateEventSubtotal(
                $service,
                $privatePricing,
                $guestTiers,
                $durationTiers,
                $packages,
                $guestCount,
                $pricingOption
            );
            $lines = array_merge($lines, $privateResult['lines']);
            $warnings = array_merge($warnings, $privateResult['warnings']);
            $errors   = array_merge($errors, $privateResult['errors']);
        }

        $extrasResult = $this->extrasLines($extrasById, $selectedExtraIds, $guestCount, $extraQuantitiesById);
        $lines = array_merge($lines, $extrasResult['lines']);

        $distanceKm = $this->distanceKm($service, $location, $event);
        if ($distanceKm === null) {
            $warnings[] = 'Travel could not be calculated (missing coordinates). Add a full postcode to your event, or confirm travel with the vendor.';
        } else {
            $travel = $this->computeTravel((float) $distanceKm, $location ?? []);
            $lines  = array_merge($lines, $travel['lines']);
            $warnings = array_merge($warnings, $travel['warnings']);
        }

        $total = 0.0;
        foreach ($lines as $line) {
            $total += $line['amount'];
        }
        $total = round($total, 2);

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

        $band = $this->matchAttendanceBand($publicBands, $guestCount);
        if ($band === null) {
            $errors[] = 'Expected attendance does not match any band configured for this vendor’s public event pricing.';
            return compact('lines', 'warnings', 'errors');
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
     * @param list<array<string,mixed>> $packages
     * @return array{lines: list<array{code:string,label:string,amount:float}>, warnings: list<string>, errors: list<string>}
     */
    private function privateEventSubtotal(
        array $service,
        ?array $privatePricing,
        array $guestTiers,
        array $durationTiers,
        array $packages,
        int $guestCount,
        ?string $pricingOption
    ): array {
        $lines    = [];
        $warnings = [];
        $errors   = [];

        $type = $privatePricing['pricing_type'] ?? null;

        if ($type === 'guest_based_pricing') {
            $tier = $this->resolveGuestTier($guestTiers, $guestCount, $pricingOption);
            if ($tier === null) {
                $errors[] = 'Guest count does not match any guest-based price band for this service.';
                return compact('lines', 'warnings', 'errors');
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

        if ($type === 'custom_duration_pricing') {
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
        if ($pricingOption !== null && preg_match('/^guest_(\d+)$/', $pricingOption, $m)) {
            $id = (int) $m[1];
            foreach ($guestTiers as $t) {
                if ((int) ($t['id'] ?? 0) === $id) {
                    return $t;
                }
            }
        }

        foreach ($guestTiers as $t) {
            $min = (int) ($t['min_guest'] ?? 0);
            $max = (int) ($t['max_guest'] ?? 0);
            if ($min > 0 && $max > 0 && $guestCount >= $min && $guestCount <= $max) {
                return $t;
            }
        }

        return null;
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
     * @param array<int, float|array{price: float, name?: string, pricing_type?: string, min_quantity?: int|null, max_quantity?: int|null, unit_label?: string|null}> $extrasById
     * @param list<int|string> $selectedExtraIds
     * @param array<int, int> $extraQuantitiesById
     * @return array{lines: list<array{code:string,label:string,amount:float}>}
     */
    private function extrasLines(array $extrasById, array $selectedExtraIds, int $guestCount, array $extraQuantitiesById = []): array
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

            $pricingType = is_array($meta) ? (string) ($meta['pricing_type'] ?? 'flat') : 'flat';
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
                $qty = $guestCount;
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
}
