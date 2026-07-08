<?php

namespace App\Libraries;

use App\Models\BookingItemModel;
use App\Models\BookingModel;
use App\Models\ChatRoomModel;
use App\Models\EventModel;
use App\Models\PaymentsModel;
use App\Models\ServiceOptionalExtrasModel;
use App\Models\ServicePrivatePricingModel;
use App\Models\ServiceTimeBlockModel;
use App\Models\UserModel;
use Config\Database;

/**
 * White-label storefront booking flow (guest checkout).
 *
 * Pricing is entirely EventQuoteBuilder's job — this library only prepares
 * the option-picker context for the service page and persists a confirmed
 * quote as the SAME records the marketplace checkout writes (user, event,
 * booking, booking_item with quote_breakdown JSON, payments row), so vendor
 * dashboards, automation, webhooks and reporting see one shape of booking.
 */
class TenantBookingFlow
{
    /**
     * Option-picker context for a service's instant-quote form: which pricing
     * model applies, the selectable options (duration/package/time block) as
     * pricing_option tokens, and which inputs the model needs.
     *
     * @return array{type: string, options: list<array{token: string, label: string, sub: string}>, needsGuests: bool, needsQuantity: bool, minQuantity: int}
     */
    public function pricingContext(int $serviceId): array
    {
        $private = (new ServicePrivatePricingModel())->where('service_id', $serviceId)->first();
        $type    = (string) ($private['pricing_type'] ?? '');
        $pid     = (int) ($private['id'] ?? 0);

        $out = [
            'type'           => $type,
            'options'        => [],
            'needsGuests'    => $type === 'guest_based_pricing',
            'needsQuantity'  => $type === 'quantity_based_pricing',
            'needsStartTime' => false,
            'minQuantity'    => 1,
        ];

        $db = Database::connect();

        if ($type === 'custom_duration_pricing' && $pid > 0) {
            $blocks = (new ServiceTimeBlockModel())->getByServiceId($serviceId);
            if ($blocks !== []) {
                // Fixed time blocks already carry their own clock window — the
                // customer picks the block, not a start time.
                foreach ($blocks as $b) {
                    $out['options'][] = [
                        'token' => 'timeblock_' . (int) $b['id'],
                        'label' => (string) ($b['label'] ?? 'Time block'),
                        'sub'   => '£' . number_format((float) ($b['price'] ?? 0), 2),
                    ];
                }
            } else {
                $tiers = $db->table('services_custom_duration_pricing')
                    ->where('private_event_pricing_id', $pid)
                    ->orderBy('duration', 'ASC')->get()->getResultArray();

                foreach ($tiers as $t) {
                    $unit             = ($t['duration_type'] ?? '') === 'day' ? 'day' : 'hour';
                    $n                = (int) ($t['duration'] ?? 0);
                    // An hours-based tier needs a start time to become a
                    // bookable slot; day tiers stay whole-date (multi-day is a
                    // separate backlog item).
                    if ($unit === 'hour') {
                        $out['needsStartTime'] = true;
                    }
                    $out['options'][] = [
                        'token' => 'duration_' . (int) $t['id'],
                        'label' => $n . ' ' . $unit . ($n === 1 ? '' : 's'),
                        'sub'   => '£' . number_format((float) ($t['price'] ?? 0), 2),
                    ];
                }
            }
        }

        if ($type === 'tiered_packages_pricing' && $pid > 0) {
            $tiers = $db->table('services_tiered_packages_pricing')
                ->where('private_event_pricing_id', $pid)
                ->orderBy('package_price', 'ASC')->get()->getResultArray();

            foreach ($tiers as $t) {
                $out['options'][] = [
                    'token' => 'package_' . (int) $t['id'],
                    'label' => (string) ($t['package_name'] ?? 'Package'),
                    'sub'   => '£' . number_format((float) ($t['package_price'] ?? 0), 2),
                ];
            }
        }

        if ($type === 'quantity_based_pricing' && $pid > 0) {
            $first = $db->table('services_quantity_pricing')
                ->where('private_event_pricing_id', $pid)
                ->orderBy('min_quantity', 'ASC')->get(1)->getRowArray();
            if ($first !== null) {
                $out['minQuantity'] = max(1, (int) ($first['min_quantity'] ?? 1));
            }
        }

        return $out;
    }

    /**
     * Optional extras with display metadata for the instant-quote form.
     *
     * @return list<array<string,mixed>>
     */
    public function extrasForForm(int $serviceId): array
    {
        return (new ServiceOptionalExtrasModel())->where('service_id', $serviceId)->findAll();
    }

    /**
     * Resolve the booked clock window from the chosen pricing option:
     *  - a fixed time block carries its own start/end;
     *  - an hours-duration tier runs from the customer's chosen start for that
     *    many hours (needsStart until a start is supplied);
     *  - everything else (guest/quantity/package/day) has no intra-day window
     *    and books whole-date.
     *
     * @return array{start: ?string, end: ?string, needsStart: bool}
     */
    public function resolveWindow(int $serviceId, ?string $pricingOption, ?string $startTime): array
    {
        $none = ['start' => null, 'end' => null, 'needsStart' => false];
        if ($pricingOption === null || $pricingOption === '') {
            return $none;
        }

        $db = Database::connect();

        if (preg_match('/^timeblock_(\d+)$/', $pricingOption, $m)) {
            $block = $db->table('service_time_blocks')
                ->where('id', (int) $m[1])->where('service_id', $serviceId)
                ->get()->getRowArray();
            if ($block && ! empty($block['start_time']) && ! empty($block['end_time'])) {
                return ['start' => (string) $block['start_time'], 'end' => (string) $block['end_time'], 'needsStart' => false];
            }

            return $none;
        }

        if (preg_match('/^duration_(\d+)$/', $pricingOption, $m)) {
            $tier = $db->table('services_custom_duration_pricing')
                ->where('id', (int) $m[1])->where('service_id', $serviceId)
                ->get()->getRowArray();
            if ($tier === null || ($tier['duration_type'] ?? '') === 'day') {
                return $none; // day tiers book whole-date
            }

            $hours = (int) ($tier['duration'] ?? 0);
            $start = self::normaliseTime($startTime);
            if ($start === null || $hours < 1) {
                return ['start' => null, 'end' => null, 'needsStart' => true];
            }

            [$h, $min]  = array_map('intval', explode(':', $start));
            $endMinutes = min(24 * 60, $h * 60 + $min + $hours * 60);

            return [
                'start'      => $start,
                'end'        => sprintf('%02d:%02d:00', intdiv($endMinutes, 60), $endMinutes % 60),
                'needsStart' => false,
            ];
        }

        return $none;
    }

    /**
     * 'H:MM'/'HH:MM'(:SS) → canonical 'HH:MM:SS', or null if not a valid time.
     */
    private static function normaliseTime(?string $time): ?string
    {
        $time = trim((string) $time);
        if (! preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $time, $m)) {
            return null;
        }
        $h   = (int) $m[1];
        $min = (int) $m[2];
        if ($h > 23 || $min > 59) {
            return null;
        }

        return sprintf('%02d:%02d:00', $h, $min);
    }

    /**
     * "From £X" display price for landing cards/heros, derived from the
     * cheapest tier of the service's pricing model. Display-only — real
     * totals always come from EventQuoteBuilder.
     *
     * @param array<string,mixed> $service
     *
     * @return array{amount: float, per: string}
     */
    public function fromPrice(array $service): array
    {
        $serviceId = (int) ($service['id'] ?? 0);
        $private   = (new ServicePrivatePricingModel())->where('service_id', $serviceId)->first();
        $type      = (string) ($private['pricing_type'] ?? '');
        $pid       = (int) ($private['id'] ?? 0);
        $db        = Database::connect();

        if ($type === 'guest_based_pricing' && $pid > 0) {
            $row = $db->table('services_guest_based_pricing')->selectMin('guest_price')
                ->where('private_event_pricing_id', $pid)->get()->getRowArray();
            if (! empty($row['guest_price'])) {
                return ['amount' => (float) $row['guest_price'], 'per' => 'guest'];
            }
        }

        if ($type === 'custom_duration_pricing' && $pid > 0) {
            $row = $db->table('services_custom_duration_pricing')
                ->where('private_event_pricing_id', $pid)
                ->orderBy('price', 'ASC')->get(1)->getRowArray();
            if ($row !== null) {
                return ['amount' => (float) $row['price'], 'per' => ($row['duration_type'] ?? '') === 'day' ? 'day' : ''];
            }
        }

        if ($type === 'tiered_packages_pricing' && $pid > 0) {
            $row = $db->table('services_tiered_packages_pricing')->selectMin('package_price')
                ->where('private_event_pricing_id', $pid)->get()->getRowArray();
            if (! empty($row['package_price'])) {
                return ['amount' => (float) $row['package_price'], 'per' => ''];
            }
        }

        if ($type === 'quantity_based_pricing' && $pid > 0) {
            $row = $db->table('services_quantity_pricing')
                ->where('private_event_pricing_id', $pid)
                ->orderBy('unit_price', 'ASC')->get(1)->getRowArray();
            if ($row !== null) {
                return ['amount' => (float) $row['unit_price'], 'per' => trim((string) ($row['unit_label'] ?? ''))];
            }
        }

        return ['amount' => (float) ($service['price'] ?? 0), 'per' => ''];
    }

    /**
     * Nearest free dates around an unavailable one (handoff frame 1h): scan
     * outwards day by day, closest first, never in the past, availability
     * decided by the same ServiceAvailabilityChecker a real quote uses.
     *
     * @return list<array{date: string, label: string}>
     */
    public function nearestFreeDates(int $serviceId, int $vendorId, string $date, int $count = 3): array
    {
        $checker = new ServiceAvailabilityChecker();
        $base    = strtotime($date);
        $today   = strtotime(date('Y-m-d'));
        $out     = [];

        for ($offset = 1; $offset <= 21 && count($out) < $count; $offset++) {
            foreach ([-1, 1] as $dir) {
                $ts = $base + $dir * $offset * 86400;
                if ($ts < $today) {
                    continue;
                }
                $candidate = date('Y-m-d', $ts);
                if ($checker->check($serviceId, $vendorId, $candidate) === []) {
                    $out[] = ['date' => $candidate, 'label' => date('D j M', $ts)];
                    if (count($out) >= $count) {
                        break;
                    }
                }
            }
        }

        usort($out, static fn ($a, $b) => strcmp($a['date'], $b['date']));

        return $out;
    }

    /**
     * Persist a paid-or-processing tenant quote as a real booking. Creates
     * (or reuses, by email) a customer account for the guest, an event for
     * the date/location, then booking + booking_item + payments rows in the
     * exact shape EventController::processCheckout() writes, and fires the
     * same vendor/customer notifications, analytics and confirmation logic.
     *
     * @param array<string,mixed>                               $site    vendor_sites row (tenant)
     * @param array<string,mixed>                               $service services row (already ownership-checked)
     * @param array<string,mixed>                               $quote   session quote payload (see TenantController::quote)
     * @param array{name: string, email: string, phone: string} $guest
     *
     * @return array{bookingId: int, userId: int}
     */
    public function createGuestBooking(
        array $site,
        array $service,
        array $quote,
        array $guest,
        ?string $paymentIntentId,
        bool $paidNow,
        bool $stripeConfigured,
    ): array {
        $userId  = $this->findOrCreateCustomer($guest);
        $eventId = $this->createEvent($userId, $site, $quote);

        $total   = round((float) $quote['total'], 2);
        $deposit = DepositCalculator::forTotal($total);

        // Booked clock window (time-based services) — persisted so future
        // availability checks can see the slot this booking occupies.
        $startTime = $quote['start_time'] ?? null;
        $endTime   = $quote['end_time'] ?? null;

        $bookingModel = new BookingModel();
        $bookingModel->insert([
            'user_id'           => $userId,
            'event_id'          => $eventId,
            'status'            => 'pending',
            'start_time'        => $startTime,
            'end_time'          => $endTime,
            'payment_intent_id' => $paymentIntentId ?: null,
            'balance_due'       => max(0, round($total - $deposit, 2)),
            'payment_plan'      => 'single',
        ]);
        $bookingId = (int) $bookingModel->getInsertID();

        $breakdown = [
            'lines'         => $quote['lines'] ?? [],
            'warnings'      => $quote['warnings'] ?? [],
            'warning_codes' => $quote['warning_codes'] ?? [],
            'distance_km'   => $quote['distance_km'] ?? null,
        ];

        $bookingItemModel = new BookingItemModel();
        $bookingItemModel->insert([
            'booking_id'      => $bookingId,
            'service_id'      => (int) $service['id'],
            'quantity'        => max(1, (int) ($quote['order_quantity'] ?? 1)),
            'package_name'    => $quote['pricing_label'] ?? null,
            'guest_count'     => $quote['event']['guest_count'] ?? null,
            'price'           => $total,
            'status'          => 'pending',
            'start_time'      => $startTime,
            'end_time'        => $endTime,
            'quote_breakdown' => json_encode($breakdown, JSON_UNESCAPED_UNICODE),
            'quote_warnings'  => json_encode($breakdown['warnings'], JSON_UNESCAPED_UNICODE),
            'extras_snapshot' => json_encode($quote['extras'] ?? []),
        ]);
        $bookingItemId = (int) $bookingItemModel->getInsertID();

        $vendorId = (int) $service['vendor_id'];
        (new ChatRoomModel())->ensureRoom($vendorId, $userId, (int) $service['id']);
        (new QuoteAnalyticsRecorder())->recordQuoteGenerated($vendorId, (int) $service['id'], $total);

        $notifier = new QuoteNotifier();
        $item     = [
            'id'              => $bookingItemId,
            'service_id'      => (int) $service['id'],
            'quantity'        => max(1, (int) ($quote['order_quantity'] ?? 1)),
            'package_name'    => $quote['pricing_label'] ?? null,
            'estimated_total' => $total,
            'deposit_amount'  => $deposit,
            'extras'          => json_encode($quote['extras'] ?? []),
            'quote_breakdown' => json_encode($breakdown, JSON_UNESCAPED_UNICODE),
            'event_title'     => $quote['event']['title'] ?? 'Storefront booking',
            'event_date'      => $quote['event']['date'] ?? null,
            'event_setting'   => 'private',
        ];
        $notifier->sendVendorNewQuoteNotification($vendorId, $userId, (int) $service['id'], $item, $breakdown);
        $notifier->sendCustomerQuoteConfirmed($userId, $vendorId, (int) $service['id'], $breakdown);

        $paymentsModel   = new PaymentsModel();
        $existingPayment = $paymentIntentId
            ? $paymentsModel->where('payment_intent_id', $paymentIntentId)->first()
            : null;
        $paymentStatus = $paidNow ? 'succeeded' : 'processing';

        if ($existingPayment) {
            // Webhook won the race — attach amount/status, keep its row.
            $paymentsModel->update($existingPayment['id'], [
                'booking_id'     => $bookingId,
                'payment_status' => $paymentStatus,
                'amount_paid'    => $deposit,
            ]);
        } else {
            $paymentsModel->insert([
                'booking_id'        => $bookingId,
                'payment_intent_id' => $paymentIntentId ?: null,
                'payment_status'    => $paymentStatus,
                'amount_paid'       => $deposit,
                'currency'          => 'gbp',
                'payment_method'    => $stripeConfigured ? 'stripe' : 'simulated',
                'payment_type'      => 'deposit',
                'description'       => 'Deposit for ' . ($service['title'] ?? 'service')
                    . ' — ' . ($site['business_name'] ?? 'storefront'),
            ]);
        }

        if ($paidNow) {
            (new BookingConfirmation())->confirmBooking($bookingId);
        }

        return ['bookingId' => $bookingId, 'userId' => $userId];
    }

    /**
     * Guest checkout account: reuse the account already registered for this
     * email, otherwise create a customer with an unguessable password (the
     * guest can set a real one later via the normal password-reset flow).
     */
    private function findOrCreateCustomer(array $guest): int
    {
        $userModel = new UserModel();
        $email     = strtolower(trim((string) $guest['email']));

        $existing = $userModel->where('email', $email)->first();
        if ($existing) {
            return (int) $existing['id'];
        }

        $base     = preg_replace('/[^a-z0-9]/', '', strstr($email, '@', true) ?: 'guest') ?: 'guest';
        $username = $base;

        while ($userModel->where('username', $username)->countAllResults() > 0) {
            $username = $base . '_' . substr(bin2hex(random_bytes(3)), 0, 5);
        }

        $userModel->insert([
            'name'     => trim((string) $guest['name']) ?: 'Guest',
            'username' => $username,
            'email'    => $email,
            'password' => password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT),
            'role'     => 'customer',
        ]);

        return (int) $userModel->getInsertID();
    }

    /**
     * The event row backing the booking — one per tenant checkout, carrying
     * the date/location the quote was priced against.
     */
    private function createEvent(int $userId, array $site, array $quote): int
    {
        $e          = $quote['event'];
        $eventModel = new EventModel();

        $eventModel->insert([
            'user_id'       => $userId,
            'title'         => ($site['business_name'] ?? 'Storefront') . ' booking',
            'event_type'    => 'Private party',
            'date'          => $e['date'] ?? null,
            'guest_count'   => $e['guest_count'] ?? null,
            'event_setting' => 'private',
            'latitude'      => $e['latitude'] ?? null,
            'longitude'     => $e['longitude'] ?? null,
            'location'      => $e['location'] ?? null,
            'postcode'      => $e['postcode'] ?? null,
            'town_city'     => $e['town_city'] ?? null,
            'status'        => 'active',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return (int) $eventModel->getInsertID();
    }
}
