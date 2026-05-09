<?= $this->include('header') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/service-form.css'); ?>">

<?= $this->include('service_create/css.php') ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">


<main class="container">

    <!-- Success and Error Messages -->
    <?php if (session()->has('success')): ?>
        <div class="alert alert-success">
            <?= session('success') ?>
        </div>
    <?php elseif (session()->has('error')): ?>
        <div class="alert alert-danger">
            <?= session('error') ?>
        </div>
    <?php endif; ?>
    <form action="/service/step4" method="POST" enctype="multipart/form-data" id="publicEventForm" class="service-form">
        <?= csrf_field() ?>

        <section id="travel-coverage">
            <h4>Location and Coverage</h4>

            <!-- Service Base Location -->
            <div class="form-group">
                <label for="service_location">Service Base Location:</label>
                <input type="text" class="form-control" id="service_location" name="service_location"
                    placeholder="Enter address or postcode" required value="<?= old('service_location') ?>">
                <small id="serviceLocationHelp" class="form-text text-muted">
                    Set the starting point for your service.
                </small>
                <input type="hidden" id="latitude" name="latitude" value="<?= old('latitude') ?>">
                <input type="hidden" id="longitude" name="longitude" value="<?= old('longitude') ?>">
            </div>

            <!-- All Travel Included Checkbox -->
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="all_travel_included" name="all_travel_included"
                        value="1" <?= old('all_travel_included') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="all_travel_included">All Travel Included in Price</label>
                </div>
                <small class="form-text text-muted">
                    Include all travel costs within your service price. No additional travel fees will be charged, and
                    distance-based pricing fields will be disabled.
                    <span class="info-icon" tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus"
                        data-bs-html="true" data-bs-title="All travel included" data-bs-content="
            <p>
    Selecting this option means you include all travel costs within your service price,
    with no additional distance-based charges.
</p>

<p>
    You must still set an included coverage radius to define the <strong>maximum distance
    you are willing to travel</strong> for this price, unless you also select
    <strong>No Travel Limit (National Coverage)</strong>.
</p>

<p>
    Your service will only appear for events within this radius, even though no travel
    fees are charged. This helps ensure availability remains practical and manageable.
</p>
        ">
                        <i class="bi bi-question-circle"></i>
                    </span>
                </small>
            </div>

            <!-- No Travel Limit test Checkbox -->
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="no_travel_limit" name="no_travel_limit"
                        value="1" <?= old('no_travel_limit') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="no_travel_limit">No Travel Limit (National Coverage)</label>
                </div>
                <small class="form-text text-muted">
                    Your service covers all of Scotland, England, and Wales. You can still specify a free coverage
                    radius.
                    <span class="info-icon" tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus"
                        data-bs-html="true" data-bs-title="National coverage" data-bs-content="
            <p>
                Selecting this option allows your service to appear for events anywhere in
                Scotland, England, and Wales.
            </p>
            <p>
                You can still specify a free coverage radius. Travel beyond this distance can
                be charged per kilometre if applicable.
            </p>
            <p>
                This option controls availability, not pricing. Travel fees are calculated
                using your coverage and fee settings below.
            </p>
        ">
                        <i class="bi bi-question-circle"></i>
                    </span>
                </small>

            </div>

            <!-- Free Coverage Radius -->
            <div class="form-group">
                <label for="free_coverage_radius">Included Coverage Radius (km):</label>
                <input type="number" class="form-control" id="free_coverage_radius" name="free_coverage_radius"
                    placeholder="Enter free coverage radius in km" value="<?= old('free_coverage_radius') ?>">
            </div>

            <!-- Paid Coverage Radius -->
            <div class="form-group">
                <label for="paid_coverage_radius">Paid Coverage Radius (km):</label>
                <input type="number" class="form-control" id="paid_coverage_radius" name="paid_coverage_radius"
                    placeholder="Enter paid coverage radius in km" value="<?= old('paid_coverage_radius') ?>">
            </div>

            <!-- Travel Fee Per KM -->
            <div class="form-group">
                <label for="travel_fee_per_km">Travel Fee Per KM (£):</label>
                <div class="input-group mb-2">
                    <span class="input-group-text">£</span>
                    <input type="number" class="form-control" id="travel_fee_per_km" name="travel_fee_per_km" min="0"
                        step="0.05" placeholder="Enter fee per km beyond free coverage"
                        value="<?= old('travel_fee_per_km') ?>">
                </div>

            </div>

            <!-- Map -->
            <div id="map" style="width: 100%; height: 400px;"></div>

            <button type="submit" class="btn btn-primary">
                <?= empty($step5_data) ? "Next" : "Review" ?>

            </button>

        </section>
    </form>


</main>

<script>
    const step4Data = <?= json_encode(session('step4_data') ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS) ?>;
</script>
<script src="<?= base_url('assets/js/service_forms/step4.js') ?>"></script>