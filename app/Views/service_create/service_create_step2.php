<?= $this->include('header') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/service-form.css'); ?>">



<?= $this->include('service_create/css.php') ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/service-wizard.css'); ?>">

<?= $this->include('service_create/wizard_rail') ?>

<main class="container mt-4 pp-wizard-page">

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success">
            <?php
            $successMessages = session('success');
            if (is_array($successMessages)) {
                foreach ($successMessages as $message): ?>
                    <p><?= esc($message) ?></p>
                <?php endforeach;
            } else {
                echo esc($successMessages);
            }
            ?>
        </div>
    <?php endif; ?>

    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger">
            <?php
            $errorMessages = session('errors');
            if (is_array($errorMessages)) {
                foreach ($errorMessages as $key => $message): ?>
                    <p><?= esc($key) ?>: <?= esc($message) ?></p>
                <?php endforeach;
            } else {
                echo esc($errorMessages);
            }
            ?>
        </div>
    <?php endif; ?>



    <form action="/service/step2" method="POST" class="service-form" id="step2Form">
        <?= csrf_field() ?>

        <section>
            <h4>Event Availability</h4>

            <!-- Event Types -->
            <div class="form-group">
                <label>Available for Events:</label>

                <div id="eventTypeErrors" role="alert" aria-live="polite" aria-atomic="true" class="d-none"></div>

                <!-- Public Events -->
                <?php
                $selectedEventTypes = session('step3_data.event_types') ?? old('event_types', []);
                if (!is_array($selectedEventTypes)) {
                    $selectedEventTypes = explode(',', $selectedEventTypes); // Handle cases where the data is a string
                }
                ?>

                <!-- Public Events -->
                <?php
                // Fetch the selected event types from the session or old input
                $selectedEventTypes = session('step2_data.event_types') ?? old('event_types', []);
                if (!is_array($selectedEventTypes)) {
                    $selectedEventTypes = explode(',', $selectedEventTypes); // Ensure the data is an array
                }
                ?>

                <!-- Public Events -->
                <div class="form-check">
                    <input type="checkbox" class="form-check-input event-type" id="event_public" name="event_types[]"
                        value="public" <?= in_array('public', $selectedEventTypes) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="event_public">Public Events</label>
                </div>

                <!-- Private Events -->
                <div class="form-check">
                    <input type="checkbox" class="form-check-input event-type" id="event_private" name="event_types[]"
                        value="private" <?= in_array('private', $selectedEventTypes) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="event_private">Private Events</label>
                </div>


                <!-- Pricing Type Section -->
                <div id="pricingTypeSection" class="pitch-group" style="display: none; margin-top: 10px;">
                    <?php $selectedPricingType = session('step3_data.pricing_type') ?? old('pricing_type'); ?>

                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="guest_based_pricing" name="pricing_type"
                            value="guest_based_pricing" <?= $selectedPricingType === 'guest_based_pricing' ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="guest_based_pricing">Guest-based Pricing</label>
                    </div>

                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="custom_duration_pricing"
                            name="pricing_type" value="custom_duration_pricing"
                            <?= $selectedPricingType === 'custom_duration_pricing' ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="custom_duration_pricing">Duration Pricing</label>
                    </div>

                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="tiered_packages_pricing"
                            name="pricing_type" value="tiered_packages_pricing"
                            <?= $selectedPricingType === 'tiered_packages_pricing' ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="tiered_packages_pricing">Tiered Packages
                            Pricing</label>
                    </div>

                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="quantity_based_pricing"
                            name="pricing_type" value="quantity_based_pricing"
                            <?= $selectedPricingType === 'quantity_based_pricing' ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="quantity_based_pricing">Per-item / quantity pricing (e.g. favours)</label>
                    </div>

                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="custom_quote"
                            name="pricing_type" value="custom_quote"
                            <?= $selectedPricingType === 'custom_quote' ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="custom_quote">Custom quote (price on request) — for bespoke jobs you quote individually</label>
                    </div>
                </div>

                <!-- Corporate Events -->
                <div class="form-check">
                    <input type="checkbox" class="form-check-input event-type" id="event_corporate" name="event_types[]"
                        value="corporate" <?= in_array('corporate', $selectedEventTypes) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="event_corporate">Corporate Events</label>
                </div>


                <?php if (session('errors.event_types')): ?>
                    <div class="invalid-feedback d-block"><?= session('errors.event_types') ?></div>
                <?php endif; ?>
            </div>
        </section>

    </form>
</main>

<?= $this->include('service_create/wizard_nav') ?>

<script>
    const step2Data = <?= json_encode(session('step2_data') ?? []) ?>;
    const step3Data = <?= json_encode(session('step3_data') ?? []) ?>;
</script>
<script src="<?= base_url('assets/js/service_forms/step2.js') ?>"></script>
