<?= $this->include('header') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/service-form.css'); ?>">

<?= $this->include('service_create/css.php') ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">


<main class="container">



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


    <form action="/service/step3" method="POST" enctype="multipart/form-data" id="publicEventForm" class="service-form">
        <?= csrf_field() ?>

        <div id="public-event-form" class="event-form" style="display: none;">
            <section class="pricing-section">
                <h4>Public Event Pricing</h4>


                <!-- Commission Percentage -->
                <div id="commissionContainer" style="display:none;">
                    <div class="form-group">
                        <label for="commission_percentage">
                            Commission Percentage

                        </label>

                        <div class="input-group">
                            <input type="number" class="form-control" id="commission_percentage"
                                name="commission_percentage" placeholder="Commission (%) you are willing to offer"
                                min="0" step="0.01">
                            <span class="input-group-text">%</span>
                        </div>

                        <small class="form-text text-muted">
                            Optional. Used only for public events that operate on a commission basis.
                            <span class="info-icon" tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus"
                                data-bs-html="true" data-bs-title="How commission works" data-bs-content="
                    <p>Commission is only applicable to certain public events where the organiser provides additional support.</p>
                    <p>This may include marketing to drive footfall, covering infrastructure costs, or reducing upfront pitch fees.</p>
                    <p><strong>This is optional.</strong> Leave blank if you do not wish to offer commission-based bookings.</p>
                "><i class="bi bi-question-circle"></i></span>
                        </small>
                    </div>
                </div>


                <!-- Attendance Threshold and Pitch Price -->
                <div class="form-group">
                    <label for="pitchFeeContainer">
                        Attendance Range and Maximum Pitch Fee
                    </label>

                    <p class="form-text text-muted">
                        This section controls when your service appears for public events based on
                        expected attendance and the pitch fee charged by the organiser.
                        <span class="info-icon" tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus"
                            data-bs-html="true" data-bs-title="How this works" data-bs-content="
            <p>For each attendance range, enter the <strong>maximum pitch fee</strong> you are willing to pay.</p>
            <p>Your service will only appear for events where:</p>
            <ul>
                <li>The expected attendance falls within your range</li>
                <li>The organiser’s pitch fee does not exceed your limit</li>
            </ul>
            <p><strong>Example:</strong> 8,000–12,000 attendees with a £500 max pitch fee will present your service in search results for a
            10,000-attendee event charging £500 per pitch or less.</p>
        ">
                            <i class="bi bi-question-circle"></i>
                        </span>

                    <div id="attendancePricingErrors"></div>
                    <div id="pitchFeeContainer"></div>

                    <button type="button" class="btn btn-primary" id="addPitchFee">Add Another Fee Range</button>
                </div>
            </section>
        </div>
        </p>







        <div id="private-event-form" class="event-form" style="display: none;">
            <section class="pricing-section">
                <h4>Private Event Pricing</h4>

                <!-- Guest-Based Pricing Section (Collapsible) -->
                <!-- Guest-Based Pricing Section -->


                <?php if ($pricingType === 'guest_based_pricing'): ?>
                    <!-- Show Guest-Based Pricing Form -->
                    <div id="guestPricingContainer" style="">
                        <h5>Guest-based Pricing</h5>
                        <p class="form-text text-muted">
                            Specify the price per guest based on the range of attendees. For example, 1–25
                            guests: £25 per guest.
                        <div id="guestPricingErrors"></div>
                        </p>

                        <!-- Container for Dynamic Guest Ranges -->
                        <div id="guestPricingList"></div>

                        <!-- Add Row Button -->
                        <button type="button" class="btn btn-primary" id="addGuestPricing">Add Another Guest
                            Range</button>
                    </div>
                <?php elseif ($pricingType === 'custom_duration_pricing'): ?>
                    <div id="customDurationContainer" style="">
                        <h5>Duration Pricing</h5>

                        <div class="form-group">
                            <label>Select Pricing Options:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="enableHours" id="enableHours" checked>
                                <label class="form-check-label" for="enableHours">Enable Hours Pricing</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="enableDays" id="enableDays" checked>
                                <label class="form-check-label" for="enableDays">Enable Days Pricing</label>
                            </div>
                        </div>

                        <div id="hoursSection" class="pitch-group" style="display: none;">
                            <h5>Hours Pricing</h5>
                            <div id="hourPricingErrors"></div>
                            <!-- Missing container added here -->
                            <div id="hoursList" class="display: none"></div>
                            <button type="button" class="btn btn-primary" id="addHourRow">Add Another Hour</button>
                        </div>

                        <div id="daysSection" class="pitch-group" style="display: none;">
                            <h5>Days Pricing</h5>
                            <div id="dayPricingErrors"></div>
                            <!-- Missing container added here -->
                            <div id="daysList"></div>
                            <button type="button" class="btn btn-primary" id="addDayRow">Add Another Day</button>
                        </div>
                    </div>
                <?php elseif ($pricingType === 'tiered_packages_pricing'): ?>
                    <div id="tieredPackageContainer" class="" style="">
                        <h5>Tiered Packages Pricing</h5>
                        <div id="tieredPackageList">
                            <!-- Package items will be added here dynamically -->
                        </div>
                        <button type="button" class="btn btn-primary" id="addPackage">Add Another
                            Package</button>
                    </div>
                <?php elseif ($pricingType === 'quantity_based_pricing'): ?>
                    <div id="quantityPricingContainer">
                        <h5>Per-item / quantity pricing</h5>
                        <p class="form-text text-muted">
                            Set a unit price multiplied by order quantity (e.g. wedding favours). Customers choose how many items they need when booking.
                        </p>
                        <?php
                        $qtyUnitPrice = session('step3_data.unit_price') ?? old('unit_price');
                        $qtyMin = session('step3_data.min_quantity') ?? old('min_quantity', 1);
                        $qtyMax = session('step3_data.max_quantity') ?? old('max_quantity');
                        $qtyLabel = session('step3_data.unit_label') ?? old('unit_label', 'items');
                        ?>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="unit_price" class="form-label">Unit price (£)</label>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="unit_price" name="unit_price"
                                    value="<?= esc($qtyUnitPrice) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="min_quantity" class="form-label">Minimum quantity</label>
                                <input type="number" min="1" class="form-control" id="min_quantity" name="min_quantity"
                                    value="<?= esc($qtyMin) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="max_quantity" class="form-label">Maximum quantity (optional)</label>
                                <input type="number" min="1" class="form-control" id="max_quantity" name="max_quantity"
                                    value="<?= esc($qtyMax) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="unit_label" class="form-label">Unit label</label>
                                <input type="text" class="form-control" id="unit_label" name="unit_label" maxlength="50"
                                    value="<?= esc($qtyLabel) ?>" placeholder="e.g. favours, items">
                            </div>
                        </div>
                    </div>
                <?php elseif ($pricingType === 'custom_quote'): ?>
                    <div class="alert alert-info">
                        <strong>Custom quote (price on request).</strong>
                        You don't set prices here. Customers will see "Price on request" and send you an
                        enquiry, and you reply with a bespoke quote for their event. There is nothing to
                        configure on this step — continue to the next step.
                    </div>
                <?php else: ?>
                    <p>Please select a pricing type in Step 2.</p>
                <?php endif; ?>

        </div>

        <div id="corporate-event-form" class="event-form" style="display:none;">
            <section class="pricing-section">
                <h4>Corporate Event Options</h4>
                <p class="form-text text-muted">
                    Corporate events use the same pricing structure as private/public. This section controls invoicing,
                    compliance and corporate-only modifiers.
                </p>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="corporate_enabled" name="corporate_enabled"
                        value="1">
                    <label class="form-check-label" for="corporate_enabled">I accept corporate bookings</label>
                </div>

                <div id="corporateBillingSection" style="display:none;">
                    <h5>Billing & Payment</h5>
                    <div id="corporateErrorsBilling"></div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="corporate_invoice_supported"
                            name="corporate_invoice_supported" value="1">
                        <label class="form-check-label" for="corporate_invoice_supported">Invoice supported</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="corporate_po_supported"
                            name="corporate_po_supported" value="1">
                        <label class="form-check-label" for="corporate_po_supported">Purchase orders supported</label>
                    </div>

                    <div class="mt-2">
                        <label class="form-label">Payment terms supported</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="corporate_payment_terms[]"
                                value="due_on_booking" id="term_due">
                            <label class="form-check-label" for="term_due">Due on booking</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="corporate_payment_terms[]"
                                value="net_7" id="term_7">
                            <label class="form-check-label" for="term_7">Net 7</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="corporate_payment_terms[]"
                                value="net_14" id="term_14">
                            <label class="form-check-label" for="term_14">Net 14</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="corporate_payment_terms[]"
                                value="net_30" id="term_30">
                            <label class="form-check-label" for="term_30">Net 30</label>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label" for="corporate_accounts_email">Accounts payable email</label>
                        <input type="email" class="form-control" id="corporate_accounts_email"
                            name="corporate_accounts_email" placeholder="accounts@company.com">
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="corporate_vat_registered"
                            name="corporate_vat_registered" value="1">
                        <label class="form-check-label" for="corporate_vat_registered">VAT registered</label>
                    </div>

                    <div class="mt-2" id="corporate_vat_number_wrap" style="display:none;">
                        <label class="form-label" for="corporate_vat_number">VAT number</label>
                        <input type="text" class="form-control" id="corporate_vat_number" name="corporate_vat_number"
                            placeholder="GB123456789">
                    </div>
                </div>

                <div id="corporateComplianceSection" class="mt-4" style="display:none;">
                    <h5>Compliance</h5>
                    <div id="corporateErrorsCompliance"></div>

                    <label class="form-label" for="corporate_pli_level">Public liability insurance</label>
                    <select class="form-select" id="corporate_pli_level" name="corporate_pli_level">
                        <option value="none">None / not stated</option>
                        <option value="1m">£1m</option>
                        <option value="2m">£2m</option>
                        <option value="5m">£5m</option>
                        <option value="10m">£10m</option>
                    </select>

                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="corporate_risk_assessment"
                            name="corporate_risk_assessment" value="1">
                        <label class="form-check-label" for="corporate_risk_assessment">Can provide risk
                            assessment</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="corporate_method_statement"
                            name="corporate_method_statement" value="1">
                        <label class="form-check-label" for="corporate_method_statement">Can provide method
                            statement</label>
                    </div>

                    <label class="form-label mt-3" for="corporate_pat_testing">PAT testing</label>
                    <select class="form-select" id="corporate_pat_testing" name="corporate_pat_testing">
                        <option value="na">Not applicable</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>

                    <label class="form-label mt-3" for="corporate_dbs">DBS checks</label>
                    <select class="form-select" id="corporate_dbs" name="corporate_dbs">
                        <option value="na">Not applicable</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>

                <div id="corporateModifiersSection" class="mt-4" style="display:none;">
                    <h5>Corporate Modifiers (Optional)</h5>
                    <div id="corporateErrorsModifiers"></div>

                    <label class="form-label" for="corporate_surcharge_type">Corporate surcharge</label>
                    <select class="form-select" id="corporate_surcharge_type" name="corporate_surcharge_type">
                        <option value="none">None</option>
                        <option value="fixed">Fixed (GBP)</option>
                        <option value="percent">Percent (%)</option>
                    </select>

                    <div class="mt-2" id="corporate_surcharge_value_wrap" style="display:none;">
                        <label class="form-label" for="corporate_surcharge_value">Surcharge value</label>
                        <input type="number" class="form-control" id="corporate_surcharge_value"
                            name="corporate_surcharge_value" min="0" step="0.01">
                    </div>

                    <div class="mt-3">
                        <label class="form-label" for="corporate_invoice_fee">Invoice admin fee (GBP)</label>
                        <input type="number" class="form-control" id="corporate_invoice_fee"
                            name="corporate_invoice_fee" min="0" step="0.01">
                    </div>

                    <div class="mt-3">
                        <label class="form-label" for="corporate_min_spend">Minimum corporate spend (GBP)</label>
                        <input type="number" class="form-control" id="corporate_min_spend" name="corporate_min_spend"
                            min="0" step="0.01">
                    </div>
                </div>
            </section>
        </div>
        <br />

        <button type="submit" class="btn btn-primary">
            <?= empty($step4_data) ? "Next" : "Review" ?>

        </button>

    </form>




</main>

<!-- Templates for dynamic rows -->
<template id="pitchFeeTemplate">
    <div class="form-group pitch-group">
        <label>Attendance Range and Pitch Fee:</label>
        <div class="all-rows-container">
            <div class="input-group mb-2">
                <span class="input-group-text">From</span>
                <input type="number" class="form-control" name="min_attendance[]" placeholder="Min (e.g. 5000)">
            </div>
            <div class="input-group mb-2">
                <span class="input-group-text">to</span>
                <input type="number" class="form-control" name="max_attendance[]" placeholder="Max (e.g. 10000)">
            </div>
            <div class="input-group mb-2">
                <span class="input-group-text">GBP</span>
                <input type="number" class="form-control" name="max_pitch_fee[]" placeholder="Pitch Fee (GBP)" min="0"
                    step="0.01">
                <button type="button" class="btn btn-danger remove-pitch-fee ms-2">
                    <span class="bi bi-x-lg"></span>
                </button>
            </div>
        </div>
    </div>
</template>

<template id="guestRowTemplate">
    <div class="form-group pitch-group">
        <div class="all-rows-container">
            <div class="input-group mb-2">
                <span class="input-group-text">From</span>
                <input type="number" class="form-control" name="min_guest[]" placeholder="Min Guests" min="0" required>
            </div>
            <div class="input-group mb-2">
                <span class="input-group-text">to</span>
                <input type="number" class="form-control" name="max_guest[]" placeholder="Max Guests" min="0" required>
            </div>
            <div class="input-group mb-2">
                <span class="input-group-text">GBP</span>
                <input type="number" class="form-control" name="guest_price[]" placeholder="Price per Guest (GBP)"
                    min="0" step="0.01" required>
                <button type="button" class="btn btn-danger remove-guest-range">
                    <span class="bi bi-x-lg"></span>
                </button>
            </div>
        </div>
    </div>
</template>

<template id="hourRowTemplate">
    <div class="form-group">
        <div class="d-flex align-items-center">
            <span class="input-group-text">Hours</span>
            <input type="number" class="form-control" name="hour_number[]" placeholder="Hours" min="1" step="1">
            <span class="input-group-text">GBP</span>
            <input type="number" class="form-control" name="hour_price[]" placeholder="Price (GBP)" min="0" step="0.01">
            <button type="button" class="btn btn-danger remove-hour">
                <span class="bi bi-x-lg"></span>
            </button>
        </div>
    </div>
</template>

<template id="dayRowTemplate">
    <div class="form-group">
        <div class="d-flex align-items-center">
            <span class="input-group-text">Days</span>
            <input type="number" class="form-control" name="day_number[]" placeholder="Days" min="1" step="1">
            <span class="input-group-text">GBP</span>
            <input type="number" class="form-control" name="day_price[]" placeholder="Price (GBP)" min="0" step="0.01">
            <button type="button" class="btn btn-danger remove-day">
                <span class="bi bi-x-lg"></span>
            </button>
        </div>
    </div>
</template>

<template id="packageRowTemplate">
    <div class="form-group pitch-group">
        <div class="all-rows-container">
            <div class="input-group mb-2">
                <input type="text" class="form-control" name="package_name[]" placeholder="Package Name">
            </div>
            <div class="input-group mb-2">
                <input type="text" class="form-control" name="package_description[]" placeholder="Description">
            </div>
            <div class="input-group mb-2">
                <span class="input-group-text">GBP</span>
                <input type="number" class="form-control" name="package_price[]" placeholder="Price (GBP)" min="0"
                    step="0.01">
                <button type="button" class="btn btn-danger remove-package">
                    <span class="bi bi-x-lg"></span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    const step2Data = <?= json_encode(session()->get('step2_data') ?? []) ?>;
    const step3Data = <?= json_encode(session()->get('step3_data') ?? []) ?>;
    const arrayCounts = <?= json_encode($arrayCounts ?? []) ?>;
</script>



<script src="<?= base_url('assets/js/service_forms/step3.js') ?>"></script>