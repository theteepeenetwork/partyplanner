<?= $this->include('header') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/service-form.css'); ?>">

<?= $this->include('service_create/css.php') ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">


<main class="container">

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success"><?= session('success') ?></div>
    <?php elseif (session()->has('error')): ?>
        <div class="alert alert-danger"><?= session('error') ?></div>
    <?php endif; ?>

    <form action="/service/step4" method="POST" enctype="multipart/form-data" id="publicEventForm" class="service-form">
        <?= csrf_field() ?>

        <section id="travel-coverage">
            <h4>Delivery &amp; Location</h4>

            <!-- Fulfillment type -->
            <div class="form-group mb-4">
                <label class="form-label fw-semibold">How is this service fulfilled?</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="fulfillment_type" id="fulfillment_in_person"
                        value="in_person" checked>
                    <label class="form-check-label" for="fulfillment_in_person">
                        <strong>I attend the event in person</strong>
                        <span class="d-block text-muted small">e.g. photographer, DJ, caterer, florist</span>
                    </label>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="radio" name="fulfillment_type" id="fulfillment_postal"
                        value="postal">
                    <label class="form-check-label" for="fulfillment_postal">
                        <strong>Posted / delivered to the customer</strong>
                        <span class="d-block text-muted small">e.g. wedding favours, printed stationery, gift boxes</span>
                    </label>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="radio" name="fulfillment_type" id="fulfillment_both"
                        value="both">
                    <label class="form-check-label" for="fulfillment_both">
                        <strong>Both — I can attend in person or post to the customer</strong>
                        <span class="d-block text-muted small">e.g. cake makers who can deliver or set up</span>
                    </label>
                </div>
            </div>

            <!-- Postal / delivery section — shown for postal or both -->
            <div id="postal-section" style="display:none">
                <h5>Postal &amp; Delivery Details</h5>

                <div class="form-group">
                    <label for="postal_fee">Postage fee per order</label>
                    <div class="input-group" style="max-width:200px">
                        <span class="input-group-text">£</span>
                        <input type="number" class="form-control" id="postal_fee" name="postal_fee"
                            min="0" step="0.01" placeholder="0.00" value="<?= old('postal_fee') ?>">
                    </div>
                    <small class="form-text text-muted">Set to 0 if postage is always free.</small>
                </div>

                <div class="form-group">
                    <label for="free_postage_above">Free postage on orders over (optional)</label>
                    <div class="input-group" style="max-width:200px">
                        <span class="input-group-text">£</span>
                        <input type="number" class="form-control" id="free_postage_above" name="free_postage_above"
                            min="0" step="0.01" placeholder="e.g. 50.00" value="<?= old('free_postage_above') ?>">
                    </div>
                    <small class="form-text text-muted">Leave blank if there is no free postage threshold.</small>
                </div>

                <div class="form-group">
                    <label for="delivery_lead_time_days">Typical dispatch time (working days, optional)</label>
                    <input type="number" class="form-control" id="delivery_lead_time_days"
                        name="delivery_lead_time_days" min="1" step="1" style="max-width:120px"
                        placeholder="e.g. 5" value="<?= old('delivery_lead_time_days') ?>">
                    <small class="form-text text-muted">How many working days from order to dispatch.</small>
                </div>
            </div>

            <!-- In-person location/travel section — shown for in_person or both -->
            <div id="location-section">
                <h5 id="location-section-heading">Location &amp; Travel Coverage</h5>

                <div class="form-group">
                    <label for="service_location">Service Base Location</label>
                    <input type="text" class="form-control" id="service_location" name="service_location"
                        placeholder="Enter address or postcode" value="<?= old('service_location') ?>">
                    <small class="form-text text-muted">Starting point used to calculate travel distance and fees.</small>
                    <input type="hidden" id="latitude" name="latitude" value="<?= old('latitude') ?>">
                    <input type="hidden" id="longitude" name="longitude" value="<?= old('longitude') ?>">
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="all_travel_included" name="all_travel_included"
                            value="1" <?= old('all_travel_included') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="all_travel_included">All Travel Included in Price</label>
                    </div>
                    <small class="form-text text-muted">
                        No additional travel fees charged. You must still set a max coverage radius.
                        <span class="info-icon" tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus"
                            data-bs-html="true" data-bs-title="All travel included" data-bs-content="
<p>All travel costs are included within your service price — no distance-based charges.</p>
<p>You must still set an included coverage radius to define the maximum distance you will travel for this price, unless you also select <strong>No Travel Limit</strong>.</p>
"><i class="bi bi-question-circle"></i></span>
                    </small>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="no_travel_limit" name="no_travel_limit"
                            value="1" <?= old('no_travel_limit') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="no_travel_limit">No Travel Limit (National Coverage)</label>
                    </div>
                    <small class="form-text text-muted">
                        Your service covers all of Scotland, England, and Wales.
                        <span class="info-icon" tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus"
                            data-bs-html="true" data-bs-title="National coverage" data-bs-content="
<p>Your service will appear for events anywhere in Scotland, England, and Wales.</p>
<p>You can still specify a free coverage radius. Travel beyond it can be charged per kilometre.</p>
"><i class="bi bi-question-circle"></i></span>
                    </small>
                </div>

                <div class="form-group">
                    <label for="free_coverage_radius">Included Coverage Radius (km)</label>
                    <input type="number" class="form-control" id="free_coverage_radius" name="free_coverage_radius"
                        placeholder="e.g. 30" value="<?= old('free_coverage_radius') ?>">
                </div>

                <div class="form-group">
                    <label for="paid_coverage_radius">Additional Paid Coverage Radius (km)</label>
                    <input type="number" class="form-control" id="paid_coverage_radius" name="paid_coverage_radius"
                        placeholder="e.g. 20" value="<?= old('paid_coverage_radius') ?>">
                </div>

                <div class="form-group">
                    <label for="travel_fee_per_km">Travel Fee Per KM</label>
                    <div class="input-group mb-2" style="max-width:200px">
                        <span class="input-group-text">£</span>
                        <input type="number" class="form-control" id="travel_fee_per_km" name="travel_fee_per_km"
                            min="0" step="0.05" placeholder="e.g. 0.50" value="<?= old('travel_fee_per_km') ?>">
                    </div>
                </div>

                <div id="map" style="width: 100%; height: 400px;"></div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <?= empty($step5_data) ? "Next" : "Save &amp; Review" ?>
                </button>
            </div>

        </section>
    </form>

</main>

<script>
    const step4Data = <?= json_encode(session('step4_data') ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS) ?>;
</script>
<script src="<?= base_url('assets/js/service_forms/step4.js') ?>"></script>
