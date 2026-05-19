<?php

namespace App\Libraries;

use App\Models\BookingItemModel;
use App\Models\QuoteAutomationLogModel;
use App\Models\VendorQuoteSettingsModel;

/**
 * Evaluates vendor rules and optionally auto-accepts booking items.
 */
class VendorQuoteAutomation
{
    /**
     * @param array<string,mixed> $bookingItem Row with booking + event fields joined
     * @param array{lines: list<array>, total: float, warnings: list<string>, errors: list<string>, distance_km: ?float} $quote
     * @return array{auto_accepted: bool, reason: string}
     */
    public function evaluateAfterCheckout(array $bookingItem, array $quote, int $vendorId, int $serviceId): array
    {
        $settingsModel = new VendorQuoteSettingsModel();
        $settings = $settingsModel->resolveForService($vendorId, $serviceId);

        if (!$settings || empty($settings['auto_accept_enabled'])) {
            return ['auto_accepted' => false, 'reason' => 'auto_accept_disabled'];
        }

        if (!empty($quote['errors'])) {
            return ['auto_accepted' => false, 'reason' => 'quote_errors'];
        }

        $total = (float) ($quote['total'] ?? $bookingItem['price'] ?? 0);
        $maxAmount = isset($settings['max_auto_accept_amount']) && $settings['max_auto_accept_amount'] !== ''
            ? (float) $settings['max_auto_accept_amount']
            : null;
        if ($maxAmount !== null && $maxAmount > 0 && $total > $maxAmount + 0.004) {
            return ['auto_accepted' => false, 'reason' => 'over_max_amount'];
        }

        if (!empty($settings['require_within_travel_radius'])) {
            foreach ($quote['warnings'] ?? [] as $w) {
                if (is_string($w) && (
                    str_contains($w, 'exceeds the vendor')
                    || str_contains($w, 'beyond the maximum')
                    || str_contains($w, 'outside the vendor')
                )) {
                    return ['auto_accepted' => false, 'reason' => 'travel_warning'];
                }
            }
        }

        $allowed = $settings['allowed_event_settings'] ?? null;
        if (is_string($allowed)) {
            $allowed = json_decode($allowed, true);
        }
        if (is_array($allowed) && $allowed !== []) {
            $setting = $bookingItem['event_setting'] ?? 'private';
            if (!in_array($setting, $allowed, true)) {
                return ['auto_accepted' => false, 'reason' => 'event_setting_not_allowed'];
            }
        }

        $minLead = (int) ($settings['min_lead_days'] ?? 0);
        if ($minLead > 0 && !empty($bookingItem['event_date'])) {
            $eventTs = strtotime((string) $bookingItem['event_date']);
            $minTs = strtotime('+' . $minLead . ' days');
            if ($eventTs !== false && $eventTs < $minTs) {
                return ['auto_accepted' => false, 'reason' => 'insufficient_lead_time'];
            }
        }

        if (!empty($settings['blackout_respect'])) {
            $checker = new ServiceAvailabilityChecker();
            $availErrors = $checker->check(
                $serviceId,
                $vendorId,
                $bookingItem['event_date'] ?? null
            );
            if ($availErrors !== []) {
                return ['auto_accepted' => false, 'reason' => 'unavailable'];
            }
        }

        $itemModel = new BookingItemModel();
        $itemModel->update((int) $bookingItem['id'], ['status' => 'accepted']);

        $log = new QuoteAutomationLogModel();
        $log->insert([
            'booking_item_id' => (int) $bookingItem['id'],
            'action' => 'auto_accept',
            'details' => json_encode(['total' => $total], JSON_UNESCAPED_UNICODE),
        ]);

        return ['auto_accepted' => true, 'reason' => 'rules_matched'];
    }
}
