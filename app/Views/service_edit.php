<?= $this->include('header') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container" style="max-width: 900px;">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Edit Service</h3>
            <a href="/profile/services" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Services</a>
        </div>

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
            <div class="dash-card mb-4">
                <h5><i class="fas fa-info-circle text-primary me-2"></i>Basic Information</h5>

                <div class="mb-3">
                    <label for="title" class="form-label">Service Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= esc(old('title', $service['title'])) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="short_description" class="form-label">Short Description <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="short_description" name="short_description" maxlength="200" value="<?= esc(old('short_description', $service['short_description'] ?? '')) ?>" required>
                    <div class="form-text">Max 200 characters</div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Full Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="description" name="description" rows="5" required><?= esc(old('description', $service['description'])) ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="service_tags" class="form-label">Service Tags</label>
                    <input type="text" class="form-control" id="service_tags" name="service_tags" value="<?= esc(old('service_tags', $service['service_tags'] ?? '')) ?>" placeholder="e.g. pizza, Italian, wood-fired">
                    <div class="form-text">Comma-separated tags to help customers find your service</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <?php if (($cat['level'] ?? 0) === 0): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (string) old('category_id', $service['category_id'] ?? '') === (string) $cat['id'] ? 'selected' : '' ?>><?= esc($cat['name']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="subcategory_id" class="form-label">Subcategory</label>
                        <select class="form-select" id="subcategory_id" name="subcategory_id">
                            <option value="">Select Subcategory</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="third_category_id" class="form-label">Further subcategory (optional)</label>
                        <select class="form-select" id="third_category_id" name="third_category_id">
                            <option value="">Select Further Subcategory</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="price" class="form-label">Base Price (£)</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= esc(old('price', $service['price'] ?? '')) ?>" placeholder="0.00">
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Images -->
            <div class="dash-card mb-4">
                <h5><i class="fas fa-images text-success me-2"></i>Service Images</h5>

                <?php if (!empty($images)): ?>
                    <div class="row g-2 mb-3">
                        <?php foreach ($images as $img): ?>
                            <div class="col-4 col-md-3">
                                <div class="position-relative">
                                    <img src="<?= base_url($img['thumbnail_path'] ?? $img['image_path']) ?>" class="rounded w-100" style="height:100px; object-fit:cover;" alt="Service image">
                                    <?php if ($img['is_primary']): ?>
                                        <span class="badge bg-primary position-absolute top-0 start-0 m-1" style="font-size:0.65rem;">Primary</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted small">No images uploaded yet.</p>
                <?php endif; ?>

                <div class="mb-2">
                    <label for="images" class="form-label">Upload New Images</label>
                    <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                    <div class="form-text">JPG, PNG — max 10MB each. New images will be added to existing ones.</div>
                </div>
            </div>

            <!-- SECTION 3: Pricing (read-only summary) -->
            <?php if (!empty($privatePricing)): ?>
                <div class="dash-card mb-4">
                    <h5><i class="fas fa-pound-sign text-warning me-2"></i>Pricing</h5>
                    <p class="text-muted small mb-2">Pricing type: <strong><?= esc(str_replace('_', ' ', ucfirst($privatePricing['pricing_type'] ?? 'Not set'))) ?></strong></p>

                    <?php if (!empty($guestPricing)): ?>
                        <h6>Guest-Based Pricing</h6>
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
                        <h6>Duration-Based Pricing</h6>
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

                    <?php if (!empty($tieredPackages)): ?>
                        <h6>Tiered Packages</h6>
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

                    <p class="text-muted small mt-2"><i class="fas fa-info-circle me-1"></i>To change pricing structure, please recreate the service or contact support.</p>
                </div>
            <?php endif; ?>

            <!-- SECTION 4: Location & Coverage -->
            <div class="dash-card mb-4">
                <h5><i class="fas fa-map-marker-alt text-danger me-2"></i>Location & Coverage</h5>

                <!-- Fulfillment type -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">How is this service fulfilled?</label>
                    <?php $currentFulfillment = $location['fulfillment_type'] ?? 'in_person'; ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="fulfillment_type" id="fulfillment_in_person"
                            value="in_person" <?= $currentFulfillment === 'in_person' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="fulfillment_in_person">
                            <strong>I attend the event in person</strong>
                            <span class="d-block text-muted small">e.g. photographer, DJ, caterer, florist</span>
                        </label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="radio" name="fulfillment_type" id="fulfillment_postal"
                            value="postal" <?= $currentFulfillment === 'postal' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="fulfillment_postal">
                            <strong>Posted / delivered to the customer</strong>
                            <span class="d-block text-muted small">e.g. wedding favours, printed stationery, gift boxes</span>
                        </label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="radio" name="fulfillment_type" id="fulfillment_both"
                            value="both" <?= $currentFulfillment === 'both' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="fulfillment_both">
                            <strong>Both — I can attend in person or post to the customer</strong>
                            <span class="d-block text-muted small">e.g. cake makers who can deliver or set up</span>
                        </label>
                    </div>
                </div>

                <!-- Postal / delivery section -->
                <div id="edit-postal-section" class="mb-3">
                    <h6 class="fw-semibold">Postal &amp; Delivery Details</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="postal_fee" class="form-label">Postage fee per order</label>
                            <div class="input-group">
                                <span class="input-group-text">£</span>
                                <input type="number" class="form-control" id="postal_fee" name="postal_fee"
                                    min="0" step="0.01" placeholder="0.00"
                                    value="<?= esc($location['postal_fee'] ?? '') ?>">
                            </div>
                            <div class="form-text">Set to 0 if postage is always free.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="free_postage_above" class="form-label">Free postage on orders over <span class="text-muted fw-normal">(optional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text">£</span>
                                <input type="number" class="form-control" id="free_postage_above" name="free_postage_above"
                                    min="0" step="0.01" placeholder="e.g. 50.00"
                                    value="<?= esc($location['free_postage_above'] ?? '') ?>">
                            </div>
                            <div class="form-text">Leave blank if no free postage threshold.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="delivery_lead_time_days" class="form-label">Dispatch time <span class="text-muted fw-normal">(working days, optional)</span></label>
                            <input type="number" class="form-control" id="delivery_lead_time_days"
                                name="delivery_lead_time_days" min="1" step="1" placeholder="e.g. 5"
                                value="<?= esc($location['delivery_lead_time_days'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- In-person location section -->
                <div id="edit-location-section">
                    <div class="mb-3">
                        <label for="service_location" class="form-label">Service Base Location</label>
                        <input type="text" class="form-control" id="service_location" name="service_location" value="<?= esc($location['service_location'] ?? '') ?>" placeholder="e.g. Newcastle upon Tyne">
                        <input type="hidden" name="latitude" value="<?= esc($location['latitude'] ?? '') ?>">
                        <input type="hidden" name="longitude" value="<?= esc($location['longitude'] ?? '') ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="free_coverage_radius" class="form-label">Free Coverage (km)</label>
                            <input type="number" class="form-control" id="free_coverage_radius" name="free_coverage_radius" value="<?= esc($location['free_coverage_radius'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="paid_coverage_radius" class="form-label">Max Coverage (km)</label>
                            <input type="number" class="form-control" id="paid_coverage_radius" name="paid_coverage_radius" value="<?= esc($location['paid_coverage_radius'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="travel_fee_per_km" class="form-label">Travel Fee (£/km)</label>
                            <input type="number" step="0.01" class="form-control" id="travel_fee_per_km" name="travel_fee_per_km" value="<?= esc($location['travel_fee_per_km'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="all_travel_included" name="all_travel_included" value="1" <?= !empty($location['all_travel_included']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="all_travel_included">All travel costs included</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="no_travel_limit" name="no_travel_limit" value="1" <?= !empty($location['no_travel_limit']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="no_travel_limit">No travel limit</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 5: Optional Extras -->
            <div class="dash-card mb-4">
                <h5><i class="fas fa-plus-circle text-info me-2"></i>Optional Extras</h5>

                <div id="extras-container">
                    <?php if (!empty($optionalExtras)): ?>
                        <?php foreach ($optionalExtras as $i => $extra): ?>
                            <?php $isPerItem = ($extra['pricing_type'] ?? 'flat') === 'per_item'; ?>
                            <div class="extra-row mb-4 p-3 border rounded bg-light">
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

                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addExtraRow()">
                    <i class="fas fa-plus me-1"></i>Add Extra
                </button>
            </div>

            <!-- SECTION 6: Cancellation Policy -->
            <div class="dash-card mb-4">
                <h5><i class="fas fa-shield-alt text-secondary me-2"></i>Cancellation Policy</h5>
                <textarea class="form-control" name="cancellation_policy" rows="4" placeholder="Describe your cancellation and refund policy..."><?= esc($cancellation['cancellation_policy'] ?? $cancellation['policy'] ?? '') ?></textarea>
            </div>

            <!-- SECTION 7: Event Types (read-only) -->
            <?php if (!empty($eventTypes)): ?>
                <div class="dash-card mb-4">
                    <h5><i class="fas fa-calendar-check text-success me-2"></i>Event Types</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <?php foreach ($eventTypes as $et): ?>
                            <span class="badge bg-primary"><?= ucfirst(esc($et['event_type'])) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-muted small mt-2"><i class="fas fa-info-circle me-1"></i>Event types are set during service creation.</p>
                </div>
            <?php endif; ?>

            <!-- Status & Actions -->
            <div class="dash-card mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge <?= ($service['status'] ?? '') === 'active' ? 'bg-success' : 'bg-secondary' ?> me-2">
                            <?= ucfirst($service['status'] ?? 'draft') ?>
                        </span>
                        <span class="text-muted small">Service ID: #<?= $service['id'] ?></span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="/service/view/<?= $service['id'] ?>" class="btn btn-outline-primary" target="_blank"><i class="fas fa-eye me-1"></i>Preview</a>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Save Changes</button>
                    </div>
                </div>
            </div>

        </form>

        <!-- Delete Service (separate form) -->
        <div class="dash-card mb-4 border-danger border-start border-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 text-danger">Danger Zone</h6>
                    <p class="text-muted small mb-0">Permanently delete this service and all associated data.</p>
                </div>
                <form action="/service/delete/<?= $service['id'] ?>" method="POST" onsubmit="return confirm('Are you sure? This cannot be undone.');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash me-1"></i>Delete Service</button>
                </form>
            </div>
        </div>

    </div>
</div>
</main>

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
    row.className = 'extra-row mb-4 p-3 border rounded bg-light';
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
</script>

<?= $this->include('footer') ?>
