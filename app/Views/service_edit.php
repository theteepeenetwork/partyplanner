<?= $this->include('header') ?>

<div class="ps-app">
<main data-screen-label="Edit service">

    <section class="page-head">
        <div class="container">
            <div class="breadcrumb">
                <a href="/profile/services">Dashboard</a><span class="sep">/</span>
                <a href="/profile/services">My services</a><span class="sep">/</span>
                <span class="cur">Edit service</span>
            </div>
            <p class="eyebrow on-dark">Edit service</p>
            <h1><?= esc($service['title']) ?></h1>
            <p class="ph-lead">Changes go live on your public profile once you save.</p>
        </div>
    </section>

    <section class="section" style="padding-block:clamp(34px,4vw,56px)">
        <div class="container">
            <div class="flow-wrap" style="max-width:840px">

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show"><?= session()->getFlashdata('success') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show"><?= session()->getFlashdata('error') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                <?php if (session()->has('errors')): ?>
                    <div class="alert alert-danger"><ul class="mb-0"><?php foreach (session('errors') as $err): ?><li><?= esc($err) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>

                <form action="/service/edit/<?= esc($service['id']) ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <!-- SECTION 1: Basic Information -->
                    <div class="flow-card" style="margin-bottom:22px">
                        <h2>Service details</h2>
                        <p class="flow-sub">The essentials customers see first.</p>

                        <div class="form-grid">
                            <div class="field-row">
                                <label for="title">Service Title</label>
                                <input type="text" class="input" id="title" name="title" value="<?= esc(old('title', $service['title'])) ?>" required>
                            </div>

                            <div class="field-row">
                                <label for="short_description">Short Description</label>
                                <input type="text" class="input" id="short_description" name="short_description" maxlength="200" value="<?= esc(old('short_description', $service['short_description'] ?? '')) ?>" required>
                                <span class="field-hint">Max 200 characters</span>
                            </div>

                            <div class="field-row">
                                <label for="description">Full Description</label>
                                <textarea class="textarea" id="description" name="description" rows="5" style="min-height:120px" required><?= esc(old('description', $service['description'])) ?></textarea>
                            </div>

                            <div class="field-row">
                                <label for="service_tags">Service Tags</label>
                                <input type="text" class="input" id="service_tags" name="service_tags" value="<?= esc(old('service_tags', $service['service_tags'] ?? '')) ?>" placeholder="e.g. pizza, Italian, wood-fired">
                                <span class="field-hint">Comma-separated tags to help customers find your service</span>
                            </div>

                            <div class="form-grid two" style="gap:14px">
                                <div class="field-row">
                                    <label for="category_id">Category</label>
                                    <select class="select-full" id="category_id" name="category_id">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <?php if (($cat['level'] ?? 0) === 0): ?>
                                                <option value="<?= $cat['id'] ?>" <?= (string) old('category_id', $service['category_id'] ?? '') === (string) $cat['id'] ? 'selected' : '' ?>><?= esc($cat['name']) ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="field-row">
                                    <label for="subcategory_id">Subcategory</label>
                                    <select class="select-full" id="subcategory_id" name="subcategory_id">
                                        <option value="">Select Subcategory</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-grid two" style="gap:14px">
                                <div class="field-row">
                                    <label for="third_category_id">Further subcategory <span class="opt">(optional)</span></label>
                                    <select class="select-full" id="third_category_id" name="third_category_id">
                                        <option value="">Select Further Subcategory</option>
                                    </select>
                                </div>
                                <div class="field-row">
                                    <label for="price">Base Price (£)</label>
                                    <div class="input-icon">
                                        <i class="fas fa-sterling-sign"></i>
                                        <input type="number" step="0.01" class="input" id="price" name="price" value="<?= esc(old('price', $service['price'] ?? '')) ?>" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: Images -->
                    <div class="flow-card" style="margin-bottom:22px">
                        <h2>Service Images</h2>
                        <p class="flow-sub">Show your best work — the first image is your cover.</p>

                        <?php if (!empty($images)): ?>
                            <div class="media-grid">
                                <?php foreach ($images as $img): ?>
                                    <div class="mtile<?= !empty($img['is_primary']) ? ' cover' : '' ?>">
                                        <img src="<?= base_url($img['thumbnail_path'] ?? $img['image_path']) ?>" alt="Service image">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="field-hint">No images uploaded yet.</p>
                        <?php endif; ?>

                        <div class="field-row" style="margin-top:18px">
                            <label for="images">Upload New Images</label>
                            <input type="file" class="input" id="images" name="images[]" multiple accept="image/*" style="padding-top:12px;height:auto">
                            <span class="field-hint">JPG, PNG — max 10MB each. New images will be added to existing ones.</span>
                        </div>
                    </div>

                    <!-- SECTION 3: Pricing (read-only summary) -->
                    <?php if (!empty($privatePricing)): ?>
                        <div class="flow-card" style="margin-bottom:22px">
                            <h2>Pricing</h2>
                            <p class="flow-sub">Pricing type: <strong><?= esc(str_replace('_', ' ', ucfirst($privatePricing['pricing_type'] ?? 'Not set'))) ?></strong></p>

                            <?php if (!empty($guestPricing)): ?>
                                <h6 class="fw-semibold">Guest-Based Pricing</h6>
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light"><tr><th>Min Guests</th><th>Max Guests</th><th>Price/Person</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($guestPricing as $gp): ?>
                                                <tr><td><?= esc($gp['min_guest']) ?></td><td><?= esc($gp['max_guest']) ?></td><td>£<?= number_format($gp['guest_price'] ?? 0, 2) ?></td></tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($durationPricing)): ?>
                                <h6 class="fw-semibold">Duration-Based Pricing</h6>
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light"><tr><th>Duration</th><th>Type</th><th>Price</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($durationPricing as $dp): ?>
                                                <tr><td><?= esc($dp['duration']) ?></td><td><?= ucfirst(esc($dp['duration_type'] ?? '')) ?></td><td>£<?= number_format($dp['price'] ?? 0, 2) ?></td></tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>

                            <?php if (($privatePricing['pricing_type'] ?? '') === 'custom_duration_pricing'): ?>
                                <h6 class="fw-semibold">Time slot pricing</h6>
                                <p class="field-hint mb-2">Optional fixed slots (e.g. morning / afternoon) with a set price. Customers can pick a slot when booking.</p>
                                <div id="timeblock-rows">
                                    <?php
                                    $tbRows = !empty($timeBlocks) ? $timeBlocks : [['start_time' => '', 'end_time' => '', 'price' => '']];
                                    foreach ($tbRows as $tb):
                                        $startVal = $tb['start_time'] ?? '';
                                        if (preg_match('/^(\d{1,2}:\d{2})/', (string) $startVal, $m)) {
                                            $startVal = $m[1];
                                        }
                                        $endVal = $tb['end_time'] ?? '';
                                        if (preg_match('/^(\d{1,2}:\d{2})/', (string) $endVal, $m)) {
                                            $endVal = $m[1];
                                        }
                                    ?>
                                    <div class="row g-2 mb-2 timeblock-row">
                                        <div class="col-md-3">
                                            <label class="form-label small">Start</label>
                                            <input type="time" class="form-control" name="timeblock_start[]" value="<?= esc($startVal) ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">End</label>
                                            <input type="time" class="form-control" name="timeblock_end[]" value="<?= esc($endVal) ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Price (£)</label>
                                            <input type="number" step="0.01" min="0" class="form-control" name="timeblock_price[]" value="<?= esc($tb['price'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="button" class="btn btn-ghost remove-timeblock">Remove</button>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="btn btn-ghost mb-3" id="add-timeblock"><i class="fas fa-plus"></i> Add time slot</button>
                            <?php endif; ?>

                            <?php if (!empty($tieredPackages)): ?>
                                <h6 class="fw-semibold">Tiered Packages</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light"><tr><th>Package</th><th>Description</th><th>Price</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($tieredPackages as $tp): ?>
                                                <tr><td><?= esc($tp['package_name']) ?></td><td><?= esc($tp['package_description'] ?? '') ?></td><td>£<?= number_format($tp['package_price'] ?? 0, 2) ?></td></tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>

                            <?php if (($privatePricing['pricing_type'] ?? '') === 'quantity_based_pricing'): ?>
                                <h6 class="fw-semibold">Quantity-based pricing</h6>
                                <div class="form-grid two" style="gap:14px">
                                    <div class="field-row">
                                        <label for="quantity_unit_price">Unit price (£)</label>
                                        <input type="number" step="0.01" min="0.01" class="input" id="quantity_unit_price"
                                            name="quantity_unit_price"
                                            value="<?= esc($quantityPricing['unit_price'] ?? '') ?>" required>
                                    </div>
                                    <div class="field-row">
                                        <label for="quantity_min_quantity">Min quantity</label>
                                        <input type="number" min="1" class="input" id="quantity_min_quantity"
                                            name="quantity_min_quantity"
                                            value="<?= esc($quantityPricing['min_quantity'] ?? 1) ?>" required>
                                    </div>
                                    <div class="field-row">
                                        <label for="quantity_max_quantity">Max quantity <span class="opt">(optional)</span></label>
                                        <input type="number" min="1" class="input" id="quantity_max_quantity"
                                            name="quantity_max_quantity"
                                            value="<?= esc($quantityPricing['max_quantity'] ?? '') ?>">
                                    </div>
                                    <div class="field-row">
                                        <label for="quantity_unit_label">Unit label</label>
                                        <input type="text" class="input" id="quantity_unit_label" name="quantity_unit_label"
                                            maxlength="50" value="<?= esc($quantityPricing['unit_label'] ?? 'items') ?>">
                                    </div>
                                </div>
                            <?php elseif (($privatePricing['pricing_type'] ?? '') !== 'custom_duration_pricing'): ?>
                                <p class="field-hint mt-2"><i class="fas fa-circle-info me-1"></i>To change pricing structure, please recreate the service or contact support.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- SECTION 4: Location & Coverage -->
                    <div class="flow-card" style="margin-bottom:22px">
                        <h2>Location &amp; Coverage</h2>
                        <p class="flow-sub">Where you operate and how you reach customers.</p>

                        <!-- Fulfillment type -->
                        <div class="field-row" style="margin-bottom:18px">
                            <label>How is this service fulfilled?</label>
                            <?php $currentFulfillment = $location['fulfillment_type'] ?? 'in_person'; ?>
                            <label class="check-line">
                                <input type="radio" name="fulfillment_type" id="fulfillment_in_person"
                                    value="in_person" <?= $currentFulfillment === 'in_person' ? 'checked' : '' ?>>
                                <span><strong>I attend the event in person</strong><br>e.g. photographer, DJ, caterer, florist</span>
                            </label>
                            <label class="check-line">
                                <input type="radio" name="fulfillment_type" id="fulfillment_postal"
                                    value="postal" <?= $currentFulfillment === 'postal' ? 'checked' : '' ?>>
                                <span><strong>Posted / delivered to the customer</strong><br>e.g. wedding favours, printed stationery, gift boxes</span>
                            </label>
                            <label class="check-line">
                                <input type="radio" name="fulfillment_type" id="fulfillment_both"
                                    value="both" <?= $currentFulfillment === 'both' ? 'checked' : '' ?>>
                                <span><strong>Both — I can attend in person or post to the customer</strong><br>e.g. cake makers who can deliver or set up</span>
                            </label>
                        </div>

                        <!-- Postal / delivery section -->
                        <div id="edit-postal-section" style="margin-bottom:18px">
                            <h6 class="fw-semibold">Postal &amp; Delivery Details</h6>
                            <div class="form-grid two" style="gap:14px">
                                <div class="field-row">
                                    <label for="postal_fee">Postage fee per order</label>
                                    <div class="input-icon">
                                        <i class="fas fa-sterling-sign"></i>
                                        <input type="number" class="input" id="postal_fee" name="postal_fee"
                                            min="0" step="0.01" placeholder="0.00"
                                            value="<?= esc($location['postal_fee'] ?? '') ?>">
                                    </div>
                                    <span class="field-hint">Set to 0 if postage is always free.</span>
                                </div>
                                <div class="field-row">
                                    <label for="free_postage_above">Free postage on orders over <span class="opt">(optional)</span></label>
                                    <div class="input-icon">
                                        <i class="fas fa-sterling-sign"></i>
                                        <input type="number" class="input" id="free_postage_above" name="free_postage_above"
                                            min="0" step="0.01" placeholder="e.g. 50.00"
                                            value="<?= esc($location['free_postage_above'] ?? '') ?>">
                                    </div>
                                    <span class="field-hint">Leave blank if no free postage threshold.</span>
                                </div>
                                <div class="field-row">
                                    <label for="delivery_lead_time_days">Dispatch time <span class="opt">(working days, optional)</span></label>
                                    <input type="number" class="input" id="delivery_lead_time_days"
                                        name="delivery_lead_time_days" min="1" step="1" placeholder="e.g. 5"
                                        value="<?= esc($location['delivery_lead_time_days'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- In-person location section -->
                        <div id="edit-location-section">
                            <div class="field-row" style="margin-bottom:14px">
                                <label for="service_location">Service Base Location</label>
                                <input type="text" class="input" id="service_location" name="service_location" value="<?= esc($location['service_location'] ?? '') ?>" placeholder="e.g. Newcastle upon Tyne">
                                <input type="hidden" name="latitude" value="<?= esc($location['latitude'] ?? '') ?>">
                                <input type="hidden" name="longitude" value="<?= esc($location['longitude'] ?? '') ?>">
                            </div>

                            <div class="form-grid two" style="grid-template-columns:1fr 1fr 1fr;gap:14px">
                                <div class="field-row">
                                    <label for="free_coverage_radius">Free Coverage (km)</label>
                                    <input type="number" class="input" id="free_coverage_radius" name="free_coverage_radius" value="<?= esc($location['free_coverage_radius'] ?? '') ?>">
                                </div>
                                <div class="field-row">
                                    <label for="paid_coverage_radius">Max Coverage (km)</label>
                                    <input type="number" class="input" id="paid_coverage_radius" name="paid_coverage_radius" value="<?= esc($location['paid_coverage_radius'] ?? '') ?>">
                                </div>
                                <div class="field-row">
                                    <label for="travel_fee_per_km">Travel Fee (£/km)</label>
                                    <input type="number" step="0.01" class="input" id="travel_fee_per_km" name="travel_fee_per_km" value="<?= esc($location['travel_fee_per_km'] ?? '') ?>">
                                </div>
                            </div>

                            <div style="display:flex;flex-wrap:wrap;gap:18px;margin-top:14px">
                                <label class="check-line">
                                    <input type="checkbox" id="all_travel_included" name="all_travel_included" value="1" <?= !empty($location['all_travel_included']) ? 'checked' : '' ?>>
                                    <span>All travel costs included</span>
                                </label>
                                <label class="check-line">
                                    <input type="checkbox" id="no_travel_limit" name="no_travel_limit" value="1" <?= !empty($location['no_travel_limit']) ? 'checked' : '' ?>>
                                    <span>No travel limit</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4b: Logistics, Capacity & Requirements -->
                    <div class="flow-card" style="margin-bottom:22px">
                        <h2>Logistics, Capacity &amp; Requirements</h2>
                        <p class="flow-sub">Tell customers what your service needs on site and who it suits. All optional — leave blank if not relevant.</p>

                        <div class="form-grid two" style="grid-template-columns:1fr 1fr;gap:14px">
                            <div class="field-row">
                                <label>Minimum capacity (guests)</label>
                                <input type="number" min="0" class="input" name="min_capacity" value="<?= esc($service['min_capacity'] ?? '') ?>" placeholder="e.g. 20">
                            </div>
                            <div class="field-row">
                                <label>Maximum capacity (guests)</label>
                                <input type="number" min="0" class="input" name="max_capacity" value="<?= esc($service['max_capacity'] ?? '') ?>" placeholder="e.g. 200">
                            </div>
                            <div class="field-row">
                                <label>Setup time (minutes)</label>
                                <input type="number" min="0" class="input" name="setup_minutes" value="<?= esc($service['setup_minutes'] ?? '') ?>" placeholder="e.g. 60">
                            </div>
                            <div class="field-row">
                                <label>Breakdown time (minutes)</label>
                                <input type="number" min="0" class="input" name="breakdown_minutes" value="<?= esc($service['breakdown_minutes'] ?? '') ?>" placeholder="e.g. 45">
                            </div>
                            <div class="field-row">
                                <label>Minimum notice (days)</label>
                                <input type="number" min="0" class="input" name="min_notice_days" value="<?= esc($service['min_notice_days'] ?? '') ?>" placeholder="e.g. 14">
                            </div>
                            <div class="field-row">
                                <label>Suitable for</label>
                                <?php $io = $service['indoor_outdoor'] ?? 'both'; ?>
                                <select class="select-full" name="indoor_outdoor">
                                    <option value="both" <?= $io === 'both' ? 'selected' : '' ?>>Indoor &amp; outdoor</option>
                                    <option value="indoor" <?= $io === 'indoor' ? 'selected' : '' ?>>Indoor only</option>
                                    <option value="outdoor" <?= $io === 'outdoor' ? 'selected' : '' ?>>Outdoor only</option>
                                </select>
                            </div>
                            <div class="field-row span2">
                                <label>Space required</label>
                                <input type="text" maxlength="120" class="input" name="space_required" value="<?= esc($service['space_required'] ?? '') ?>" placeholder="e.g. 5m x 5m flat ground">
                            </div>
                        </div>

                        <div style="display:flex;flex-wrap:wrap;gap:18px;margin-top:16px">
                            <label class="check-line">
                                <input type="checkbox" id="power_required" name="power_required" value="1" <?= !empty($service['power_required']) ? 'checked' : '' ?>>
                                <span>Mains power required</span>
                            </label>
                            <label class="check-line">
                                <input type="checkbox" id="water_required" name="water_required" value="1" <?= !empty($service['water_required']) ? 'checked' : '' ?>>
                                <span>Water access required</span>
                            </label>
                            <label class="check-line">
                                <input type="checkbox" id="vehicle_access_required" name="vehicle_access_required" value="1" <?= !empty($service['vehicle_access_required']) ? 'checked' : '' ?>>
                                <span>Vehicle access required</span>
                            </label>
                            <label class="check-line">
                                <input type="checkbox" id="equipment_provided" name="equipment_provided" value="1" <?= !empty($service['equipment_provided']) ? 'checked' : '' ?>>
                                <span>We provide our own equipment</span>
                            </label>
                        </div>
                    </div>

                    <!-- SECTION 5: Optional Extras -->
                    <div class="flow-card" style="margin-bottom:22px">
                        <h2>Optional Extras</h2>
                        <p class="flow-sub">Add-ons customers can choose alongside your service.</p>

                        <div id="extras-container">
                            <?php if (!empty($optionalExtras)): ?>
                                <?php foreach ($optionalExtras as $i => $extra): ?>
                                    <?php $isPerItem = ($extra['pricing_type'] ?? 'flat') === 'per_item'; ?>
                                    <div class="extra-row panel panel-pad" style="margin-bottom:16px">
                                        <div class="row g-2 mb-2">
                                            <div class="col-md-5">
                                                <label class="form-label small">Name</label>
                                                <input type="text" class="form-control form-control-sm" name="extra_name[]" value="<?= esc($extra['name']) ?>" placeholder="Name">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Price</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">£</span>
                                                    <input type="number" step="0.01" class="form-control" name="extra_price[]" value="<?= esc($extra['price']) ?>" placeholder="0">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Description</label>
                                                <input type="text" class="form-control form-control-sm" name="extra_description[]" value="<?= esc($extra['description'] ?? '') ?>" placeholder="Description">
                                            </div>
                                            <div class="col-md-1 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeExtraRow(this)"><i class="fas fa-times"></i></button>
                                            </div>
                                        </div>
                                        <div class="row g-2 align-items-start">
                                            <div class="col-md-4">
                                                <label class="form-label small">Pricing type</label>
                                                <select class="form-select form-select-sm edit-pricing-type-select" name="extra_pricing_type[]">
                                                    <option value="flat" <?= !$isPerItem ? 'selected' : '' ?>>Flat fee — one fixed price</option>
                                                    <option value="per_item" <?= $isPerItem ? 'selected' : '' ?>>Per item / per guest — price × quantity</option>
                                                </select>
                                                <div class="form-text small flat-hint <?= $isPerItem ? 'd-none' : '' ?>">Charged once regardless of order size.</div>
                                                <div class="form-text small per-item-hint <?= $isPerItem ? '' : 'd-none' ?>">Customer chooses how many they want.</div>
                                            </div>
                                            <div class="col-md-8 per-item-fields <?= $isPerItem ? '' : 'd-none' ?>">
                                                <div class="row g-2">
                                                    <div class="col-12">
                                                        <label class="form-label small">Unit label <span class="text-muted fw-normal">(optional)</span></label>
                                                        <input type="text" class="form-control form-control-sm" name="extra_unit_label[]"
                                                            placeholder="e.g. per bag, per guest, each"
                                                            value="<?= esc($extra['unit_label'] ?? '') ?>">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small">Min qty</label>
                                                        <input type="number" class="form-control form-control-sm" name="extra_min_quantity[]"
                                                            placeholder="e.g. 10" min="1" step="1"
                                                            value="<?= esc($extra['min_quantity'] ?? '') ?>">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small">Max qty</label>
                                                        <input type="number" class="form-control form-control-sm" name="extra_max_quantity[]"
                                                            placeholder="e.g. 500" min="1" step="1"
                                                            value="<?= esc($extra['max_quantity'] ?? '') ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <button type="button" class="btn btn-ghost" onclick="addExtraRow()">
                            <i class="fas fa-plus"></i> Add Extra
                        </button>
                    </div>

                    <!-- SECTION 6: Cancellation Policy -->
                    <div class="flow-card" style="margin-bottom:22px">
                        <h2>Cancellation Policy</h2>
                        <p class="flow-sub">Set expectations on cancellations and refunds.</p>
                        <div class="field-row">
                            <textarea class="textarea" name="cancellation_policy" rows="4" placeholder="Describe your cancellation and refund policy..."><?= esc($cancellation['cancellation_policy'] ?? $cancellation['policy'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- SECTION 7: Event Types (read-only) -->
                    <?php if (!empty($eventTypes)): ?>
                        <div class="flow-card" style="margin-bottom:22px">
                            <h2>Event Types</h2>
                            <p class="flow-sub">Event types are set during service creation.</p>
                            <div style="display:flex;gap:8px;flex-wrap:wrap">
                                <?php foreach ($eventTypes as $et): ?>
                                    <span class="badge green"><?= ucfirst(esc($et['event_type'])) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Status & Actions -->
                    <div class="flow-card" style="margin-bottom:22px">
                        <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:14px">
                            <div style="display:flex;align-items:center;gap:10px">
                                <span class="badge <?= ($service['status'] ?? '') === 'active' ? 'green' : 'grey' ?>">
                                    <?= ucfirst($service['status'] ?? 'draft') ?>
                                </span>
                                <span class="field-hint">Service ID: #<?= $service['id'] ?></span>
                            </div>
                            <div style="display:flex;gap:10px">
                                <a href="/service/view/<?= $service['id'] ?>" class="btn btn-ghost" target="_blank"><i class="fas fa-eye"></i> Preview</a>
                                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-circle-check"></i> Save Changes</button>
                            </div>
                        </div>
                    </div>

                </form>

                <!-- Delete Service (separate form) -->
                <div class="flow-card" style="margin-bottom:22px;border-left:3px solid #c0473e">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:14px">
                        <div>
                            <h6 class="mb-1" style="color:#a23a32;font-weight:700">Danger Zone</h6>
                            <p class="field-hint mb-0">Permanently delete this service and all associated data.</p>
                        </div>
                        <form action="/service/delete/<?= $service['id'] ?>" method="POST" onsubmit="return confirm('Are you sure? This cannot be undone.');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash me-1"></i>Delete Service</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>
</div>

<script>
    var categories = <?= json_encode($categories ?? []) ?>;
    var selectedSubcategoryId = <?= json_encode(old('subcategory_id', $service['subcategory_id'] ?? '')) ?>;
    var selectedThirdCategoryId = <?= json_encode(old('third_category_id', $service['third_category_id'] ?? '')) ?>;
</script>
<script src="<?= base_url('assets/js/category_cascade.js') ?>"></script>
<script>
    $(function () {
        if (typeof window.initCategoryCascade === 'function') {
            window.initCategoryCascade({
                rootSelect: '#category_id',
                subSelect: '#subcategory_id',
                thirdSelect: '#third_category_id',
                categories: categories,
                preselectSub: selectedSubcategoryId,
                preselectThird: selectedThirdCategoryId,
                subPlaceholder: 'Select Subcategory',
                thirdPlaceholder: 'Select Further Subcategory (optional)',
            });
        }
    });
</script>

<script>
// --- Optional Extras ---
function addExtraRow() {
    const container = document.getElementById('extras-container');
    const row = document.createElement('div');
    row.className = 'extra-row panel panel-pad';
    row.style.marginBottom = '16px';
    row.innerHTML = `
        <div class="row g-2 mb-2">
            <div class="col-md-5">
                <label class="form-label small">Name</label>
                <input type="text" class="form-control form-control-sm" name="extra_name[]" placeholder="Name">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Price</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">£</span>
                    <input type="number" step="0.01" class="form-control" name="extra_price[]" placeholder="0">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Description</label>
                <input type="text" class="form-control form-control-sm" name="extra_description[]" placeholder="Description">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeExtraRow(this)"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <div class="row g-2 align-items-start">
            <div class="col-md-4">
                <label class="form-label small">Pricing type</label>
                <select class="form-select form-select-sm edit-pricing-type-select" name="extra_pricing_type[]">
                    <option value="flat" selected>Flat fee — one fixed price</option>
                    <option value="per_item">Per item / per guest — price × quantity</option>
                </select>
                <div class="form-text small flat-hint">Charged once regardless of order size.</div>
                <div class="form-text small per-item-hint d-none">Customer chooses how many they want.</div>
            </div>
            <div class="col-md-8 per-item-fields d-none">
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label small">Unit label <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" class="form-control form-control-sm" name="extra_unit_label[]" placeholder="e.g. per bag, per guest, each">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Min qty</label>
                        <input type="number" class="form-control form-control-sm" name="extra_min_quantity[]" placeholder="e.g. 10" min="1" step="1">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Max qty</label>
                        <input type="number" class="form-control form-control-sm" name="extra_max_quantity[]" placeholder="e.g. 500" min="1" step="1">
                    </div>
                </div>
            </div>
        </div>
    `;
    container.appendChild(row);
    attachExtraRowListeners(row);
}

function removeExtraRow(btn) {
    const row = btn.closest('.extra-row');
    const allRows = document.querySelectorAll('.extra-row');
    if (allRows.length > 1) {
        row.remove();
    } else {
        // Clear instead of remove if it's the last row
        row.querySelectorAll('input[type="text"], input[type="number"], textarea').forEach(i => i.value = '');
        const sel = row.querySelector('.edit-pricing-type-select');
        if (sel) { sel.value = 'flat'; sel.dispatchEvent(new Event('change')); }
    }
}

function attachExtraRowListeners(row) {
    const select = row.querySelector('.edit-pricing-type-select');
    if (!select) return;
    select.addEventListener('change', function () {
        const isPerItem = this.value === 'per_item';
        row.querySelectorAll('.per-item-fields, .per-item-hint').forEach(el => el.classList.toggle('d-none', !isPerItem));
        row.querySelectorAll('.flat-hint').forEach(el => el.classList.toggle('d-none', isPerItem));
    });
}

// Attach listeners to existing rows rendered by PHP
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.extra-row').forEach(attachExtraRowListeners);
});

// --- Fulfillment type show/hide ---
(function () {
    const radios = document.querySelectorAll('[name="fulfillment_type"]');
    const locationSection = document.getElementById('edit-location-section');
    const postalSection = document.getElementById('edit-postal-section');

    function applyFulfillment() {
        const val = document.querySelector('[name="fulfillment_type"]:checked')?.value ?? 'in_person';
        const showLocation = val !== 'postal';
        const showPostal = val !== 'in_person';
        if (locationSection) locationSection.style.display = showLocation ? '' : 'none';
        if (postalSection) postalSection.style.display = showPostal ? '' : 'none';
    }

    radios.forEach(r => r.addEventListener('change', applyFulfillment));
    // Run on page load to reflect persisted value
    applyFulfillment();
})();

(function () {
    const container = document.getElementById('timeblock-rows');
    const addBtn = document.getElementById('add-timeblock');
    if (!container || !addBtn) return;

    addBtn.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 timeblock-row';
        row.innerHTML = `
            <div class="col-md-3">
                <label class="form-label small">Start</label>
                <input type="time" class="form-control" name="timeblock_start[]">
            </div>
            <div class="col-md-3">
                <label class="form-label small">End</label>
                <input type="time" class="form-control" name="timeblock_end[]">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Price (£)</label>
                <input type="number" step="0.01" min="0" class="form-control" name="timeblock_price[]">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-ghost remove-timeblock">Remove</button>
            </div>
        `;
        container.appendChild(row);
    });

    container.addEventListener('click', function (e) {
        if (!e.target.classList.contains('remove-timeblock')) return;
        const rows = container.querySelectorAll('.timeblock-row');
        if (rows.length > 1) {
            e.target.closest('.timeblock-row').remove();
        } else {
            e.target.closest('.timeblock-row').querySelectorAll('input').forEach(i => { i.value = ''; });
        }
    });
})();
</script>

<?= $this->include('footer') ?>
