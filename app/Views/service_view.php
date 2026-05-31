<?= $this->include('header') ?>

<main class="container">
    <section class="service-view-section">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (isset($service)): ?>
            <?php
            $pricingType = $privatePricing['pricing_type'] ?? null;
            if ($pricingType === null) {
                if (!empty($guestPricing)) {
                    $pricingType = 'guest_based_pricing';
                } elseif (!empty($durationPricing)) {
                    $pricingType = 'custom_duration_pricing';
                } elseif (!empty($tieredPackages)) {
                    $pricingType = 'tiered_packages_pricing';
                } elseif (!empty($quantityTiers ?? $quantityPricing ?? null)) {
                    $pricingType = 'quantity_based_pricing';
                }
            }
            $quantityTiers = $quantityTiers ?? (isset($quantityPricing) && is_array($quantityPricing) ? [$quantityPricing] : []);
            $showGuest = $pricingType === 'guest_based_pricing';
            $showDuration = $pricingType === 'custom_duration_pricing';
            $showPackages = $pricingType === 'tiered_packages_pricing';
            $showQuantity = $pricingType === 'quantity_based_pricing' && $quantityTiers !== [];
            $qtyMin = 1;
            $qtyMax = null;
            foreach ($quantityTiers as $qt) {
                $tMin = max(1, (int) ($qt['min_quantity'] ?? 1));
                $qtyMin = min($qtyMin, $tMin);
                $tMaxRaw = $qt['max_quantity'] ?? null;
                if ($tMaxRaw !== null && $tMaxRaw !== '') {
                    $tMax = max($tMin, (int) $tMaxRaw);
                    $qtyMax = $qtyMax === null ? $tMax : max($qtyMax, $tMax);
                } else {
                    $qtyMax = null;
                }
            }
            $qtyDefault = $qtyMin;
            ?>
            <div class="service-preview card service-view-card">
                <div class="row g-0">
                    <!-- Left: Gallery -->
                    <div class="col-md-6">
                        <div class="gallery">
                            <?= $this->include('components/gallery_view', ['images' => $images]) ?>
                        </div>
                    </div>

                    <!-- Right: Service Details -->
                    <div class="col-md-6 service-view-details">
                        <h2 class="service-title"><?= esc($service['title']) ?></h2>
                        <p class="service-short-description"><?= esc($service['short_description']) ?></p>
                        <p class="service-description"><?= nl2br(esc($service['description'])) ?></p>

                        <!-- Categories -->
                        <ul class="service-meta">
                            <li><strong>Category:</strong> <?= esc($category_names['main'] ?? 'Not Selected') ?></li>
                            <?php if (!empty($category_names['sub'])): ?>
                                <li><strong>Subcategory:</strong> <?= esc($category_names['sub']) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($category_names['third'])): ?>
                                <li><strong>Style:</strong> <?= esc($category_names['third']) ?></li>
                            <?php endif; ?>
                        </ul>

                        <?php if (!empty($service['price'])): ?>
                            <p class="service-price mt-2">From £<?= number_format((float) $service['price'], 2) ?></p>
                        <?php endif; ?>

                        <!-- Fulfillment / delivery info -->
                        <?php
                        $fulfillmentType = $location['fulfillment_type'] ?? 'in_person';
                        if ($fulfillmentType === 'postal' || $fulfillmentType === 'both'):
                        ?>
                        <div class="mb-3 p-3 bg-light rounded border">
                            <p class="mb-1 fw-semibold"><i class="fas fa-box me-1 text-primary"></i>
                                <?= $fulfillmentType === 'both' ? 'Available to post or attend in person' : 'Posted / delivered to you' ?>
                            </p>
                            <?php if (isset($location['postal_fee'])): ?>
                                <?php $postalFee = (float) $location['postal_fee']; ?>
                                <p class="mb-1 small text-muted">
                                    Postage: <?= $postalFee === 0.0 ? 'Free' : '£' . number_format($postalFee, 2) ?>
                                    <?php if (!empty($location['free_postage_above'])): ?>
                                        (free on orders over £<?= number_format((float) $location['free_postage_above'], 2) ?>)
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($location['delivery_lead_time_days'])): ?>
                                <p class="mb-0 small text-muted">Typical dispatch: <?= (int) $location['delivery_lead_time_days'] ?> working day<?= $location['delivery_lead_time_days'] == 1 ? '' : 's' ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Good to know: capacity, logistics & requirements -->
                        <?php
                        $cap = '';
                        if (!empty($service['min_capacity']) && !empty($service['max_capacity'])) {
                            $cap = (int) $service['min_capacity'] . '–' . (int) $service['max_capacity'] . ' guests';
                        } elseif (!empty($service['max_capacity'])) {
                            $cap = 'Up to ' . (int) $service['max_capacity'] . ' guests';
                        } elseif (!empty($service['min_capacity'])) {
                            $cap = 'From ' . (int) $service['min_capacity'] . ' guests';
                        }
                        $reqs = [];
                        if (!empty($service['power_required']))           $reqs[] = 'Mains power';
                        if (!empty($service['water_required']))           $reqs[] = 'Water access';
                        if (!empty($service['vehicle_access_required']))  $reqs[] = 'Vehicle access';
                        $io = $service['indoor_outdoor'] ?? 'both';
                        $hasGoodToKnow = $cap !== '' || $reqs !== [] || $io !== 'both'
                            || !empty($service['space_required']) || !empty($service['setup_minutes'])
                            || !empty($service['breakdown_minutes']) || !empty($service['min_notice_days'])
                            || !empty($service['equipment_provided']);
                        if ($hasGoodToKnow):
                        ?>
                        <div class="mb-3 p-3 bg-light rounded border">
                            <p class="mb-2 fw-semibold"><i class="fas fa-circle-info me-1 text-primary"></i>Good to know</p>
                            <ul class="service-meta mb-0 small">
                                <?php if ($cap !== ''): ?><li><strong>Capacity:</strong> <?= esc($cap) ?></li><?php endif; ?>
                                <?php if ($io !== 'both'): ?><li><strong>Suitable for:</strong> <?= $io === 'indoor' ? 'Indoor events only' : 'Outdoor events only' ?></li><?php endif; ?>
                                <?php if (!empty($service['space_required'])): ?><li><strong>Space required:</strong> <?= esc($service['space_required']) ?></li><?php endif; ?>
                                <?php if (!empty($service['min_notice_days'])): ?><li><strong>Minimum notice:</strong> <?= (int) $service['min_notice_days'] ?> day<?= $service['min_notice_days'] == 1 ? '' : 's' ?></li><?php endif; ?>
                                <?php if (!empty($service['setup_minutes'])): ?><li><strong>Setup time:</strong> <?= (int) $service['setup_minutes'] ?> mins</li><?php endif; ?>
                                <?php if (!empty($service['breakdown_minutes'])): ?><li><strong>Breakdown time:</strong> <?= (int) $service['breakdown_minutes'] ?> mins</li><?php endif; ?>
                                <?php if ($reqs !== []): ?><li><strong>On-site requirements:</strong> <?= esc(implode(', ', $reqs)) ?></li><?php endif; ?>
                                <?php if (!empty($service['equipment_provided'])): ?><li><strong>Equipment:</strong> Supplier provides their own</li><?php endif; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Pricing Options -->
                        <div class="pricing-panel">
                            <?php $hasPricing = $showGuest || $showDuration || $showPackages || $showQuantity; ?>
                            <?php if ($hasPricing): ?>
                                <h5 class="pricing-panel-heading">Pricing Options</h5>
                            <?php endif; ?>

                            <form action="<?= site_url('event/add-to-event/' . $service['id']) ?>" method="post">
                                <?= csrf_field() ?>
                                <?php if ($showGuest): ?>
                                    <div class="form-group">
                                        <label class="d-block">Guest-based pricing</label>
                                        <?php if (!empty($guestPricing)): ?>
                                            <ul class="list-unstyled small border rounded p-3 bg-light mb-2">
                                                <?php foreach ($guestPricing as $pricing): ?>
                                                    <li class="mb-1">
                                                        <?= esc($pricing['min_guest'] ?? $pricing['min_guests'] ?? '') ?> to <?= esc($pricing['max_guest'] ?? $pricing['max_guests'] ?? '') ?> guests:
                                                        <strong>£<?= esc(number_format((float) ($pricing['guest_price'] ?? $pricing['price'] ?? 0), 2)) ?></strong> per person
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                        <p class="text-muted small mb-0">The band that matches your event’s guest count is applied automatically when you add this service to an event (you do not need to pick a range here).</p>
                                    </div>
                                <?php endif; ?>

                                <?php if ($showDuration): ?>
                                    <div class="form-group">
                                        <label for="durationPricing">Duration-Based Pricing:</label>
                                        <select class="form-control" id="durationPricing" name="pricing_option" required>
                                            <?php if (!empty($timeBlocks)): ?>
                                                <optgroup label="Time slots">
                                                    <?php foreach ($timeBlocks as $block): ?>
                                                        <?php
                                                        $start = preg_match('/^(\d{1,2}:\d{2})/', (string) ($block['start_time'] ?? ''), $sm) ? $sm[1] : '';
                                                        $end = preg_match('/^(\d{1,2}:\d{2})/', (string) ($block['end_time'] ?? ''), $em) ? $em[1] : '';
                                                        ?>
                                                        <option value="timeblock_<?= esc($block['id']) ?>">
                                                            <?= esc($start) ?> – <?= esc($end) ?>: £<?= esc(number_format((float) ($block['price'] ?? 0), 2)) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endif; ?>
                                            <?php if (!empty($durationPricing)): ?>
                                                <optgroup label="Duration">
                                                    <?php foreach ($durationPricing as $pricing): ?>
                                                        <option value="duration_<?= esc($pricing['id']) ?>">
                                                            <?= esc($pricing['duration_hours'] ?? $pricing['duration'] ?? '') ?> <?= (($pricing['duration_type'] ?? '') === 'day') ? 'Day(s)' : 'Hour(s)' ?>: £<?= esc(number_format((float) ($pricing['price'] ?? 0), 2)) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>

                                <?php if ($showPackages): ?>
                                    <div class="form-group">
                                        <label for="tieredPackages">Tiered Packages:</label>
                                        <select class="form-control" id="tieredPackages" name="pricing_option" required>
                                            <?php foreach ($tieredPackages as $package): ?>
                                                <option value="package_<?= esc($package['id']) ?>">
                                                    <?= esc($package['package_name']) ?>: £<?= esc($package['package_price'] ?? $package['price'] ?? '') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>

                                <?php if ($showQuantity && $quantityTiers !== []): ?>
                                    <div class="form-group">
                                        <?php $unitLabel = esc($quantityTiers[0]['unit_label'] ?? 'items'); ?>
                                        <label for="orderQuantity">Order quantity (<?= $unitLabel ?>):</label>
                                        <?php if (count($quantityTiers) > 1): ?>
                                            <ul class="list-unstyled small border rounded p-3 bg-light mb-2">
                                                <?php foreach ($quantityTiers as $qt): ?>
                                                    <li class="mb-1">
                                                        <?php
                                                        $qMin = (int) ($qt['min_quantity'] ?? 1);
                                                        $qMaxRaw = $qt['max_quantity'] ?? null;
                                                        ?>
                                                        <?php if ($qMaxRaw !== null && $qMaxRaw !== ''): ?>
                                                            <?= $qMin ?>–<?= (int) $qMaxRaw ?>:
                                                        <?php else: ?>
                                                            <?= $qMin ?>+:
                                                        <?php endif; ?>
                                                        <strong>£<?= number_format((float) ($qt['unit_price'] ?? 0), 2) ?></strong> each
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                        <input type="number" class="form-control quote-refresh-input" id="orderQuantity"
                                            name="order_quantity"
                                            value="<?= (int) $qtyDefault ?>"
                                            min="<?= (int) $qtyMin ?>"
                                            <?= $qtyMax !== null ? 'max="' . (int) $qtyMax . '"' : '' ?>
                                            required>
                                        <?php if (count($quantityTiers) === 1 && !empty($quantityTiers[0]['unit_price'])): ?>
                                            <p class="text-muted small mb-0 mt-1">
                                                £<?= number_format((float) $quantityTiers[0]['unit_price'], 2) ?> per <?= $unitLabel ?>
                                            </p>
                                        <?php else: ?>
                                            <p class="text-muted small mb-0 mt-1">Unit price depends on the band that matches your quantity.</p>
                                        <?php endif; ?>
                                        <input type="hidden" name="pricing_option" id="qtyPricingOption" value="qty_<?= (int) $qtyDefault ?>">
                                    </div>
                                <?php endif; ?>

                                <!-- Optional Extras -->
                                <?php if (!empty($optional_extras)): ?>
                                    <div class="form-group">
                                        <label class="fw-semibold">Optional Extras:</label>
                                        <?php foreach ($optional_extras as $extra):
                                            $isPerItem = ($extra['pricing_type'] ?? 'flat') === 'per_item';
                                            $unitLabel = esc($extra['unit_label'] ?? 'per item');
                                        ?>
                                            <div class="mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input extra-checkbox" type="checkbox"
                                                        id="extra_<?= esc($extra['id']) ?>"
                                                        name="extras[]" value="<?= esc($extra['id']) ?>"
                                                        <?= $isPerItem ? 'data-per-item="1"' : '' ?>>
                                                    <label class="form-check-label" for="extra_<?= esc($extra['id']) ?>">
                                                        <strong><?= esc($extra['name']) ?></strong>
                                                        — £<?= number_format((float) $extra['price'], 2) ?>
                                                        <?= $isPerItem ? esc($unitLabel) : '' ?>
                                                    </label>
                                                </div>
                                                <?php if (!empty($extra['description'])): ?>
                                                    <p class="text-muted small mb-1 ms-4"><?= esc($extra['description']) ?></p>
                                                <?php endif; ?>
                                                <?php if ($isPerItem): ?>
                                                    <div class="extra-qty-wrap ms-4 mt-1" id="qty_wrap_<?= esc($extra['id']) ?>" style="display:none">
                                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                                            <label class="form-label mb-0 small text-muted">Quantity (optional):</label>
                                                            <input type="number" class="form-control form-control-sm quote-refresh-input"
                                                                style="width:90px"
                                                                name="extra_qty[<?= esc($extra['id']) ?>]"
                                                                value=""
                                                                placeholder="Auto"
                                                                min="<?= (int) ($extra['min_quantity'] ?? 1) ?>"
                                                                <?= !empty($extra['max_quantity']) ? 'max="' . (int) $extra['max_quantity'] . '"' : '' ?>
                                                                title="Leave blank to use your event’s guest count">
                                                            <?php if (!empty($extra['min_quantity']) || !empty($extra['max_quantity'])): ?>
                                                                <span class="text-muted small">
                                                                    <?= !empty($extra['min_quantity']) ? 'Min: ' . (int) $extra['min_quantity'] : '' ?>
                                                                    <?= (!empty($extra['min_quantity']) && !empty($extra['max_quantity'])) ? ' &middot; ' : '' ?>
                                                                    <?= !empty($extra['max_quantity']) ? 'Max: ' . (int) $extra['max_quantity'] : '' ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <p class="small text-muted mb-0 ms-0 mt-1"><?= !empty($showQuantity) ? 'If you leave this blank, we price this extra using your order quantity above.' : 'If you leave this blank, we price this extra using your event’s guest count (after you pick the event).' ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Cancellation Policy -->
                                <?php if (!empty($cancellation_policy)): ?>
                                    <div class="form-group">
                                        <h5>Cancellation Policy:</h5>
                                        <p><?= nl2br(esc($cancellation_policy)) ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($preview_event_id) && session()->get('role') === 'customer'): ?>
                                    <div id="live-quote-preview" class="alert alert-secondary small mb-3" data-service-id="<?= (int) $service['id'] ?>" data-event-id="<?= (int) $preview_event_id ?>">
                                        <strong>Estimated total:</strong> <span id="live-quote-total">—</span>
                                        <div id="live-quote-errors" class="text-danger mt-1"></div>
                                        <ul id="live-quote-lines" class="mb-0 ps-3"></ul>
                                    </div>
                                <?php endif; ?>

                                <!-- Action Buttons -->
                                <div class="service-view-actions mt-4">
                                    <?php if (session()->has('user_id') && session()->get('role') === 'vendor' && $service['vendor_id'] == session()->get('user_id')): ?>
                                        <a href="<?= base_url('service/edit/' . $service['id']) ?>" class="btn btn-primary">
                                            Edit Service
                                        </a>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-calendar-plus me-1"></i>Add to Event
                                        </button>
                                    <?php endif; ?>
                                    <?php if (!empty($message_vendor_eligible) && !empty($message_vendor_url)): ?>
                                        <a href="<?= esc($message_vendor_url) ?>" class="btn btn-outline-primary btn-lg">
                                            <i class="fas fa-comment-dots me-1"></i>Message vendor
                                        </a>
                                    <?php elseif (session()->has('user_id') && session()->get('role') === 'customer' && (int) $service['vendor_id'] !== (int) session()->get('user_id')): ?>
                                        <span class="small text-muted align-middle" title="Complete a booking request for this listing first">
                                            <i class="fas fa-lock me-1"></i>Messaging unlocks after you book
                                        </span>
                                    <?php elseif (!session()->has('user_id')): ?>
                                        <a href="/login" class="btn btn-outline-secondary btn-lg">Log in to book</a>
                                    <?php endif; ?>
                                    <a href="/browse-services" class="btn btn-outline-secondary">Back to Services</a>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p>Service not found.</p>
        <?php endif; ?>
    </section>
</main>

<?= $this->include('footer') ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const preview = document.getElementById('live-quote-preview');
    if (preview) {
        const form = preview.closest('form');
        const serviceId = preview.dataset.serviceId;
        const eventId = preview.dataset.eventId;
        const refreshQuote = function () {
            if (!form) return;
            const fd = new FormData(form);
            const params = new URLSearchParams();
            const po = fd.get('pricing_option');
            if (po) params.set('pricing_option', po);
            fd.getAll('extras[]').forEach(function (id) { params.append('extras[]', id); });
            const url = '/event/quote-preview/' + serviceId + '/' + eventId + '?' + params.toString();
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    document.getElementById('live-quote-total').textContent = data.total != null ? '£' + Number(data.total).toFixed(2) : '—';
                    const errEl = document.getElementById('live-quote-errors');
                    errEl.textContent = (data.errors || []).join(' ');
                    const ul = document.getElementById('live-quote-lines');
                    ul.innerHTML = '';
                    (data.lines || []).forEach(function (line) {
                        if (line.code === 'platform_commission') return;
                        const li = document.createElement('li');
                        li.textContent = line.label + ': £' + Number(line.amount).toFixed(2);
                        ul.appendChild(li);
                    });
                })
                .catch(function () {});
        };
        if (form) {
            form.addEventListener('change', refreshQuote);
            refreshQuote();
        }
    }

    document.querySelectorAll('.extra-checkbox[data-per-item="1"]').forEach(function (checkbox) {
        const extraId = checkbox.value;
        const qtyWrap = document.getElementById('qty_wrap_' + extraId);
        if (!qtyWrap) return;

        checkbox.addEventListener('change', function () {
            qtyWrap.style.display = this.checked ? '' : 'none';
        });
    });
});
</script>
