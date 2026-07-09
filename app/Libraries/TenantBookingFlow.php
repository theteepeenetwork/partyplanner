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
     * Set by findOrCreateCustomer(): true when the last guest checkout created
     * a brand-new customer account (vs reusing one already registered to the
     * email). Only a freshly-created account is safe to "claim" with a password
     * on the confirmation page — reusing someone's existing account there would
     * be a takeover.
     */
    private bool $lastCustomerCreated = false;

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
     * Normalised estimator config for the storefront instant-quote card, keyed
     * off the SAME pricing_type taxonomy the quote pipeline uses (guest / custom
     * duration / tiered packages / quantity). It is display/estimation only —
     * the exact charge always comes from EventQuoteBuilder on the service page.
     *
     * Returns one of:
     *  - per_guest / per_unit : slider (min/max/default + tiered rate bands)
     *  - per_hour / per_session / package : segmented options (exact price each)
     *  - fixed  : one exact price, no input
     *  - quote_only : bespoke — no client-side maths (corporate/unconfigured)
     *
     * NB there is deliberately no "base + per-guest" model: the schema has no
     * such combined table, so such services resolve to per_guest or quote_only
     * rather than inventing a taxonomy.
     *
     * @return array{model: string, exact: bool, unitLabel?: string, slider?: array, options?: list<array>, fixed?: float}
     */
    public function estimatorModel(int $serviceId): array
    {
        $db      = Database::connect();
        $private = (new ServicePrivatePricingModel())->where('service_id', $serviceId)->first();
        $type    = (string) ($private['pricing_type'] ?? '');
        $pid     = (int) ($private['id'] ?? 0);
        $flat    = (float) ($private['price'] ?? 0);

        if ($type === 'guest_based_pricing' && $pid > 0) {
            $bands = [];

            foreach ($db->table('services_guest_based_pricing')->where('private_event_pricing_id', $pid)
                ->orderBy('min_guest', 'ASC')->get()->getResultArray() as $t) {
                $bands[] = ['min' => max(1, (int) $t['min_guest']), 'max' => max(1, (int) $t['max_guest']), 'rate' => (float) $t['guest_price']];
            }
            if ($bands !== []) {
                return $this->sliderEstimator('per_guest', 'guest', $bands);
            }
        }

        if ($type === 'quantity_based_pricing' && $pid > 0) {
            $rows  = $db->table('services_quantity_pricing')->where('private_event_pricing_id', $pid)
                ->orderBy('min_quantity', 'ASC')->get()->getResultArray();
            $bands = [];

            foreach ($rows as $t) {
                $min     = max(1, (int) $t['min_quantity']);
                $bands[] = ['min' => $min, 'max' => max($min, (int) ($t['max_quantity'] ?: $min)), 'rate' => (float) $t['unit_price']];
            }
            if ($bands !== []) {
                $label = trim((string) ($rows[0]['unit_label'] ?? ''));

                return $this->sliderEstimator('per_unit', $label !== '' ? $label : 'unit', $bands);
            }
        }

        if ($type === 'custom_duration_pricing' && $pid > 0) {
            $blocks = (new ServiceTimeBlockModel())->getByServiceId($serviceId);
            if ($blocks !== []) {
                $opts = [];

                foreach ($blocks as $b) {
                    $s      = substr((string) ($b['start_time'] ?? ''), 0, 5);
                    $e      = substr((string) ($b['end_time'] ?? ''), 0, 5);
                    $opts[] = ['label' => $s !== '' && $e !== '' ? $s . '–' . $e : 'Session', 'price' => (float) ($b['price'] ?? 0), 'token' => 'timeblock_' . (int) $b['id']];
                }

                return ['model' => 'per_session', 'exact' => true, 'options' => $opts];
            }

            $opts = [];

            foreach ($db->table('services_custom_duration_pricing')->where('private_event_pricing_id', $pid)
                ->orderBy('duration', 'ASC')->get()->getResultArray() as $t) {
                $unit   = ($t['duration_type'] ?? '') === 'day' ? 'day' : 'hr';
                $n      = (int) $t['duration'];
                $opts[] = ['label' => $n . ' ' . $unit . ($n === 1 ? '' : 's'), 'price' => (float) $t['price'], 'token' => 'duration_' . (int) $t['id']];
            }
            if ($opts !== []) {
                return ['model' => 'per_hour', 'exact' => true, 'options' => $opts];
            }
        }

        if ($type === 'tiered_packages_pricing' && $pid > 0) {
            $pkgs = $db->table('services_tiered_packages_pricing')->where('private_event_pricing_id', $pid)
                ->orderBy('package_price', 'ASC')->get()->getResultArray();
            if (count($pkgs) === 1) {
                return ['model' => 'fixed', 'exact' => true, 'fixed' => (float) $pkgs[0]['package_price']];
            }
            if (count($pkgs) > 1) {
                $opts = [];

                foreach ($pkgs as $p) {
                    $opts[] = ['label' => (string) ($p['package_name'] ?? 'Package'), 'price' => (float) $p['package_price'], 'token' => 'package_' . (int) $p['id']];
                }

                return ['model' => 'package', 'exact' => true, 'options' => $opts];
            }
        }

        if ($flat > 0) {
            return ['model' => 'fixed', 'exact' => true, 'fixed' => $flat];
        }

        return ['model' => 'quote_only', 'exact' => false];
    }

    /**
     * Slider estimator config (per-guest / per-unit): overall min/max, a
     * sensible default, and the tiered rate bands so the live total is accurate
     * across bands.
     *
     * @param list<array{min: int, max: int, rate: float}> $bands
     *
     * @return array{model: string, exact: bool, unitLabel: string, slider: array}
     */
    private function sliderEstimator(string $model, string $unitLabel, array $bands): array
    {
        $min = min(array_column($bands, 'min'));
        $max = max(max(array_column($bands, 'max')), $min + 1);

        return [
            'model'     => $model,
            'exact'     => true,
            'unitLabel' => $unitLabel,
            'slider'    => ['min' => $min, 'max' => $max, 'default' => min($max, max($min, 50)), 'bands' => $bands],
        ];
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
     * @return array{bookingId: int, userId: int, newAccount: bool}
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

        return ['bookingId' => $bookingId, 'userId' => $userId, 'newAccount' => $this->lastCustomerCreated];
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

        $this->lastCustomerCreated = false;

        $existing = $userModel->where('email', $email)->first();
        if ($existing) {
            return (int) $existing['id'];
        }

        $this->lastCustomerCreated = true;

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
