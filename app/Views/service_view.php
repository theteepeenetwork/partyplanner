<?= $this->include('header') ?>
<?= $this->include('service_create/css.php') ?>

<main class="container">
    <section>
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
                }
            }
            $showGuest = $pricingType === 'guest_based_pricing';
            $showDuration = $pricingType === 'custom_duration_pricing';
            $showPackages = $pricingType === 'tiered_packages_pricing';
            ?>
            <div class="service-preview card">
                <div class="row">
                    <!-- Left: Gallery -->
                    <div class="col-md-6">
                        <div class="gallery">
                            <?= $this->include('components/gallery_view', ['images' => $images]) ?>
                        </div>
                    </div>

                    <!-- Right: Service Details -->
                    <div class="col-md-6">
                        <h2 class="service-title"><?= esc($service['title']) ?></h2>
                        <h5 class="service-short-description text-muted"><?= esc($service['short_description']) ?></h5>
                        <p class="service-description"><?= nl2br(esc($service['description'])) ?></p>

                        <!-- Categories -->
                        <p><strong>Category:</strong> <?= esc($category_names['main'] ?? 'Not Selected') ?></p>
                        <?php if (!empty($category_names['sub'])): ?>
                            <p><strong>Subcategory:</strong> <?= esc($category_names['sub']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($category_names['third'])): ?>
                            <p><strong>Further Subcategory:</strong> <?= esc($category_names['third']) ?></p>
                        <?php endif; ?>

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

                        <!-- Pricing Options -->
                        <div class="pricing-options">
                            <?php $hasPricing = $showGuest || $showDuration || $showPackages; ?>
                            <?php if ($hasPricing): ?>
                                <h4>Pricing Options</h4>
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
                                            <?php foreach ($durationPricing as $pricing): ?>
                                                <option value="duration_<?= esc($pricing['id']) ?>">
                                                    <?= esc($pricing['duration_hours'] ?? $pricing['duration'] ?? '') ?> Hour(s): £<?= esc($pricing['price']) ?>
                                                </option>
                                            <?php endforeach; ?>
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
                                                            <input type="number" class="form-control form-control-sm"
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
                                                        <p class="small text-muted mb-0 ms-0 mt-1">If you leave this blank, we price this extra using your event’s guest count (after you pick the event).</p>
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
                                <div class="mt-3">
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
                                        <a href="<?= esc($message_vendor_url) ?>" class="btn btn-outline-primary btn-lg ms-2">
                                            <i class="fas fa-comment-dots me-1"></i>Message vendor
                                        </a>
                                    <?php elseif (session()->has('user_id') && session()->get('role') === 'customer' && (int) $service['vendor_id'] !== (int) session()->get('user_id')): ?>
                                        <span class="d-inline-block ms-2 small text-muted align-middle" title="Complete a booking request for this listing first">
                                            <i class="fas fa-lock me-1"></i>Messaging unlocks after you book
                                        </span>
                                    <?php elseif (!session()->has('user_id')): ?>
                                        <a href="/login" class="btn btn-outline-secondary btn-lg ms-2">Log in to book</a>
                                    <?php endif; ?>
                                    <a href="/browse-services" class="btn btn-outline-secondary ms-2">Back to Services</a>
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
