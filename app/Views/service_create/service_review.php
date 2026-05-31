<?= $this->include('header'); ?>
<?= $this->include('service_create/css.php'); ?>

<style>
    .review-section table {
        width: 100%;
        max-width: 500px;
        margin: 10px auto;
        /* Center align */
        border-collapse: collapse;
        border: 1px solid #ddd;
    }

    .review-section th,
    .review-section td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
    }

    .review-section th {
        background-color: var(--primary-blue-dark);
        /* Light gray background for table headers */
        font-weight: bold;
    }

    .review-section tr:nth-child(even) {

        /* Alternating row colors for better readability */
    }

    .review-section table caption {
        font-weight: bold;
        margin-bottom: 5px;
    }
</style>


<main class="container mt-4">

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Error:</strong> <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <form action="/service/saveService" method="POST">
        <?= csrf_field() ?>
        <h2>Review Your Service</h2>
        <div class="review-section form-section">

            <section id="basicInfoSection">
                <h4>Step 1: Basic Information</h4>

                <!-- Basic Information -->
                <?php if (!empty($serviceData['step1'])): ?>
                    <p><strong>Title:</strong> <?= esc($serviceData['step1']['title'] ?? 'No title provided'); ?></p>
                    <p><strong>Short Description:</strong>
                        <?= esc($serviceData['step1']['short_description'] ?? 'No short description provided') ?></p>
                    <p><strong>Description:</strong>
                        <?= esc($serviceData['step1']['description'] ?? 'No description provided') ?></p>
                    <p><strong>Service Tags:</strong>
                        <?= esc($serviceData['step1']['service_tags'] ?? 'No tags provided') ?></p>
                <?php else: ?>
                    <p><strong>Title:</strong> No title provided</p>
                    <p><strong>Short Description:</strong> No short description provided</p>
                    <p><strong>Description:</strong> No description provided</p>
                    <p><strong>Service Tags:</strong> No tags provided</p>
                <?php endif; ?>


                <!-- Image Preview -->
                <div class="image-preview">
                    <strong>Uploaded Images:</strong>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <?php if (!empty(session('uploaded_images'))): ?>
                            <?php foreach (session('uploaded_images') as $image): ?>
                                <?php if (isset($image['image_path'])): ?>
                                    <div style="border: 1px solid #ddd; padding: 10px; text-align: center;">
                                        <img src="<?= base_url($image['image_path']) ?>" alt="Uploaded Image"
                                            style="width: 100px; height: auto; display: block; margin-bottom: 5px;">
                                        <?php if (!empty($image['is_primary']) && $image['is_primary']): ?>
                                            <span style="color: green; font-weight: bold;">Primary</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div style="color: red; font-style: italic;">Image path is missing for this file.</div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No images uploaded.</p>
                        <?php endif; ?>
                    </div>
                </div>


                <!-- Category Information -->
                <div class="form-group">
                    <p><strong>Main Category:</strong>
                        <?= esc($serviceData['category_names']['main'] ?? 'No main category provided') ?></p>
                </div>
                <div class="form-group">
                    <p><strong>Subcategory:</strong>
                        <?= esc($serviceData['category_names']['sub'] ?? 'No subcategory provided') ?></p>
                </div>
                <div class="form-group">
                    <p><strong>Further Subcategory (optional):</strong>
                        <?= esc($serviceData['category_names']['third'] ?? 'Not specified') ?></p>
                </div>
                <a href="/service/step1" class="btn btn-secondary mt-2">Edit</a>
            </section>

            <section id="eventTypesSection">
                <div class="review-section">
                    <h4>Step 2: Event Types</h4>
                    <?php
                    // Convert event_types to an array if it's a string
                    $eventTypes = is_string($serviceData['step2']['event_types'])
                        ? explode(',', $serviceData['step2']['event_types'])
                        : ($serviceData['step2']['event_types'] ?? []);

                    // Build the sentence
                    $eventTypesSentence = '';
                    if (!empty($eventTypes)) {
                        $eventTypesList = implode(', ', $eventTypes); // Join the event types into a string
                        $eventTypesSentence = esc($serviceData['step1']['title']) . " will provide services for " . $eventTypesList . " events.";

                        // Add details for private event pricing
                        if (in_array('private', $eventTypes)) {
                            $pricingType = $serviceData['step2']['pricing_type'] ?? 'Not Specified';
                            $pricingDescription = match ($pricingType) {
                                'guest_based_pricing' => "Your private pricing packages are offered based on guest numbers.",
                                'custom_duration_pricing' => "Your private pricing packages are based on the duration required.",
                                'tiered_packages_pricing' => "Your private pricing packages are offered as tiered packages.",
                                'quantity_based_pricing' => "Your private pricing is based on order quantity (price × items).",
                                default => "Your private pricing packages are customizable."
                            };
                            $eventTypesSentence .= " $pricingDescription";
                        }
                    } else {
                        $eventTypesSentence = "No event types have been selected.";
                    }
                    ?>
                    <p><?= esc($eventTypesSentence) ?></p>
                </div>
                <a href="/service/step2" class="btn btn-secondary mt-2">Edit</a>
            </section>

            <section id="pricingSection">
                <div class="review-section">
                    <h4>Step 3: Pricing</h4>

                    <?php if (!isset($serviceData['step3']) || empty($serviceData['step3'])): ?>
                        <p>No pricing information provided.</p>
                    <?php else: ?>

                        <?php
                        // Normalise event types
                        $eventTypes = $serviceData['step2']['event_types'] ?? [];
                        if (is_string($eventTypes)) {
                            $eventTypes = array_map('trim', explode(',', $eventTypes));
                        } elseif (!is_array($eventTypes)) {
                            $eventTypes = [];
                        }

                        // Pricing type from Step 2 (used for private pricing)
                        $pricingType = $serviceData['step2']['pricing_type'] ?? null;

                        // Small helpers
                        $yesNo = fn($v) => !empty($v) ? 'Yes' : 'No';
                        $money = fn($v) => '£' . number_format((float) ($v ?? 0), 2, '.', '');
                        ?>

                        <!-- Commission (Public only) -->
                        <?php if (in_array('public', $eventTypes, true) && isset($serviceData['step3']['commission_percentage']) && $serviceData['step3']['commission_percentage'] !== ''): ?>
                            <p><strong>Commission Percentage (Public Events):</strong>
                                <?= esc($serviceData['step3']['commission_percentage']) ?>%
                            </p>
                        <?php endif; ?>

                        <!-- Public Event Pricing -->
                        <?php if (in_array('public', $eventTypes, true)): ?>
                            <h5>Public Event Pricing</h5>
                            <?php if (!empty($serviceData['step3']['min_attendance'])): ?>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Attendance Range</th>
                                            <th>Pitch Fee</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($serviceData['step3']['min_attendance'] as $i => $minAttendance): ?>
                                            <tr>
                                                <td>
                                                    <?= esc($minAttendance) ?> to
                                                    <?= esc($serviceData['step3']['max_attendance'][$i] ?? '') ?> attendees
                                                </td>
                                                <td><?= $money($serviceData['step3']['max_pitch_fee'][$i] ?? 0) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No public pricing ranges added.</p>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Private Event Pricing -->
                        <?php if (in_array('private', $eventTypes, true)): ?>
                            <h5>Private Event Pricing</h5>

                            <?php
                            $pricingLabel = match ($pricingType) {
                                'guest_based_pricing' => 'Guest-based Pricing',
                                'custom_duration_pricing' => 'Duration Pricing',
                                'tiered_packages_pricing' => 'Tiered Packages Pricing',
                                'quantity_based_pricing' => 'Per-item / quantity pricing',
                                default => 'Not specified'
                            };
                            ?>
                            <p><strong>Pricing Type:</strong> <?= esc($pricingLabel) ?></p>

                            <?php if ($pricingType === 'guest_based_pricing'): ?>
                                <?php if (!empty($serviceData['step3']['min_guest'])): ?>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Guest Range</th>
                                                <th>Price per Guest</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($serviceData['step3']['min_guest'] as $i => $minGuest): ?>
                                                <tr>
                                                    <td><?= esc($minGuest) ?> to <?= esc($serviceData['step3']['max_guest'][$i] ?? '') ?>
                                                        guests</td>
                                                    <td><?= $money($serviceData['step3']['guest_price'][$i] ?? 0) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p>No guest pricing ranges added.</p>
                                <?php endif; ?>

                            <?php elseif ($pricingType === 'custom_duration_pricing'): ?>

                                <?php $enableHours = !empty($serviceData['step3']['enableHours']); ?>
                                <?php $enableDays = !empty($serviceData['step3']['enableDays']); ?>

                                <?php if ($enableHours && !empty($serviceData['step3']['hour_number'])): ?>
                                    <h6>Hourly Pricing</h6>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Hours</th>
                                                <th>Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($serviceData['step3']['hour_number'] as $i => $hour): ?>
                                                <tr>
                                                    <td><?= esc($hour) ?></td>
                                                    <td><?= $money($serviceData['step3']['hour_price'][$i] ?? 0) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>

                                <?php if ($enableDays && !empty($serviceData['step3']['day_number'])): ?>
                                    <h6>Daily Pricing</h6>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Days</th>
                                                <th>Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($serviceData['step3']['day_number'] as $i => $day): ?>
                                                <tr>
                                                    <td><?= esc($day) ?></td>
                                                    <td><?= $money($serviceData['step3']['day_price'][$i] ?? 0) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>

                                <?php if (!$enableHours && !$enableDays): ?>
                                    <p>No duration options enabled.</p>
                                <?php endif; ?>

                            <?php elseif ($pricingType === 'tiered_packages_pricing'): ?>

                                <?php if (!empty($serviceData['step3']['package_name'])): ?>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Package</th>
                                                <th>Description</th>
                                                <th>Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($serviceData['step3']['package_name'] as $i => $packageName): ?>
                                                <tr>
                                                    <td><?= esc($packageName) ?></td>
                                                    <td><?= esc($serviceData['step3']['package_description'][$i] ?? '') ?></td>
                                                    <td><?= $money($serviceData['step3']['package_price'][$i] ?? 0) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p>No packages added.</p>
                                <?php endif; ?>

                            <?php elseif ($pricingType === 'quantity_based_pricing'): ?>
                                <?php if (!empty($serviceData['step3']['unit_price'])): ?>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Unit price</th>
                                                <td><?= $money($serviceData['step3']['unit_price']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Quantity range</th>
                                                <td>
                                                    <?= (int) ($serviceData['step3']['min_quantity'] ?? 1) ?>
                                                    <?php if (!empty($serviceData['step3']['max_quantity'])): ?>
                                                        – <?= (int) $serviceData['step3']['max_quantity'] ?>
                                                    <?php else: ?>
                                                        + (no maximum)
                                                    <?php endif; ?>
                                                    <?= esc($serviceData['step3']['unit_label'] ?? 'items') ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p>No quantity pricing configured.</p>
                                <?php endif; ?>

                            <?php else: ?>
                                <p>Private pricing type not set. Please return to Step 2.</p>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Corporate Event Options (NOT pricing tables) -->
                        <?php if (in_array('corporate', $eventTypes, true)): ?>
                            <h5>Corporate Event Options</h5>

                            <?php $corpEnabled = !empty($serviceData['step3']['corporate_enabled']); ?>

                            <p><strong>Accept corporate bookings:</strong> <?= $corpEnabled ? 'Yes' : 'No' ?></p>

                            <?php if ($corpEnabled): ?>

                                <?php
                                $terms = $serviceData['step3']['corporate_payment_terms'] ?? [];
                                if (is_string($terms)) {
                                    $terms = array_map('trim', explode(',', $terms));
                                }
                                $termsLabel = [];
                                foreach ((array) $terms as $t) {
                                    $termsLabel[] = match ($t) {
                                        'due_on_booking' => 'Due on booking',
                                        'net_7' => 'Net 7',
                                        'net_14' => 'Net 14',
                                        'net_30' => 'Net 30',
                                        default => $t
                                    };
                                }

                                $sType = $serviceData['step3']['corporate_surcharge_type'] ?? 'none';
                                $sValue = $serviceData['step3']['corporate_surcharge_value'] ?? null;

                                $surchargeText = 'None';
                                if ($sType === 'fixed' && $sValue !== null && $sValue !== '') {
                                    $surchargeText = $money($sValue);
                                } elseif ($sType === 'percent' && $sValue !== null && $sValue !== '') {
                                    $surchargeText = esc($sValue) . '%';
                                }
                                ?>

                                <table>
                                    <thead>
                                        <tr>
                                            <th>Corporate capability</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Invoice supported</td>
                                            <td><?= $yesNo($serviceData['step3']['corporate_invoice_supported'] ?? null) ?></td>
                                        </tr>
                                        <tr>
                                            <td>PO supported</td>
                                            <td><?= $yesNo($serviceData['step3']['corporate_po_supported'] ?? null) ?></td>
                                        </tr>
                                        <tr>
                                            <td>Payment terms</td>
                                            <td><?= !empty($termsLabel) ? esc(implode(', ', $termsLabel)) : 'Not specified' ?></td>
                                        </tr>
                                        <tr>
                                            <td>Accounts payable email</td>
                                            <td><?= esc($serviceData['step3']['corporate_accounts_email'] ?? 'Not provided') ?></td>
                                        </tr>
                                        <tr>
                                            <td>VAT registered</td>
                                            <td><?= $yesNo($serviceData['step3']['corporate_vat_registered'] ?? null) ?></td>
                                        </tr>
                                        <tr>
                                            <td>VAT number</td>
                                            <td><?= esc($serviceData['step3']['corporate_vat_number'] ?? 'Not provided') ?></td>
                                        </tr>
                                        <tr>
                                            <td>Public liability insurance</td>
                                            <td><?= esc($serviceData['step3']['corporate_pli_level'] ?? 'Not provided') ?></td>
                                        </tr>
                                        <tr>
                                            <td>Risk assessment available</td>
                                            <td><?= $yesNo($serviceData['step3']['corporate_risk_assessment'] ?? null) ?></td>
                                        </tr>
                                        <tr>
                                            <td>Method statement available</td>
                                            <td><?= $yesNo($serviceData['step3']['corporate_method_statement'] ?? null) ?></td>
                                        </tr>
                                        <tr>
                                            <td>PAT testing</td>
                                            <td><?= esc($serviceData['step3']['corporate_pat_testing'] ?? 'na') ?></td>
                                        </tr>
                                        <tr>
                                            <td>DBS checks</td>
                                            <td><?= esc($serviceData['step3']['corporate_dbs'] ?? 'na') ?></td>
                                        </tr>
                                        <tr>
                                            <td>Corporate surcharge</td>
                                            <td><?= esc($surchargeText) ?></td>
                                        </tr>
                                        <tr>
                                            <td>Invoice admin fee</td>
                                            <td><?= $money($serviceData['step3']['corporate_invoice_fee'] ?? 0) ?></td>
                                        </tr>
                                        <tr>
                                            <td>Minimum corporate spend</td>
                                            <td><?= $money($serviceData['step3']['corporate_min_spend'] ?? 0) ?></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <p class="form-text text-muted">
                                    Note: Corporate events use the same pricing structure as private/public. The values above affect
                                    eligibility and any corporate-only modifiers.
                                </p>

                            <?php endif; ?>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>

                <a href="/service/step3" class="btn btn-secondary mt-2">Edit</a>
            </section>


            <section id="locationSection">
                <div class="review-section">
                    <h4>Step 4: Location and Coverage</h4>

                    <?php
                        $fulfillmentType = $serviceData['step4']['fulfillment_type'] ?? 'in_person';
                        $fulfillmentLabels = [
                            'in_person' => 'I attend the event in person',
                            'postal'    => 'Posted / delivered to the customer',
                            'both'      => 'Both — in person and postal',
                        ];
                    ?>
                    <p><strong>Fulfillment:</strong>
                        <?= esc($fulfillmentLabels[$fulfillmentType] ?? ucfirst($fulfillmentType)) ?>
                    </p>

                    <?php if ($fulfillmentType === 'postal' || $fulfillmentType === 'both'): ?>
                        <p><strong>Postage Fee:</strong>
                            <?php if (isset($serviceData['step4']['postal_fee']) && $serviceData['step4']['postal_fee'] > 0): ?>
                                £<?= esc(number_format((float) $serviceData['step4']['postal_fee'], 2)) ?>
                            <?php else: ?>
                                Free
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($serviceData['step4']['free_postage_above'])): ?>
                            <p><strong>Free Postage On Orders Over:</strong>
                                £<?= esc(number_format((float) $serviceData['step4']['free_postage_above'], 2)) ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($serviceData['step4']['delivery_lead_time_days'])): ?>
                            <p><strong>Typical Dispatch Time:</strong>
                                <?= esc($serviceData['step4']['delivery_lead_time_days']) ?> working day(s)
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($fulfillmentType === 'in_person' || $fulfillmentType === 'both'): ?>
                        <p><strong>Service Location:</strong>
                            <?= esc($serviceData['step4']['service_location'] ?? 'Not Provided') ?>
                        </p>
                        <p><strong>All Travel Included:</strong>
                            <?= isset($serviceData['step4']['all_travel_included']) && $serviceData['step4']['all_travel_included'] ? 'Yes' : 'No' ?>
                        </p>
                        <p><strong>National Coverage:</strong>
                            <?= isset($serviceData['step4']['no_travel_limit']) && $serviceData['step4']['no_travel_limit'] ? 'Yes' : 'No' ?>
                        </p>
                        <p><strong>Included Coverage Radius:</strong>
                            up to <?= esc($serviceData['step4']['free_coverage_radius'] ?? 'Not Provided'); ?> km</p>
                        <?php if (isset($serviceData['step4']['paid_coverage_radius'])): ?>
                            <p><strong>Paid Coverage Radius:</strong>
                                <?= esc($serviceData['step4']['paid_coverage_radius'] ?? 'Not limited') ?> km</p>
                        <?php endif; ?>
                        <p><strong>Travel Fee Per KM:</strong>
                            £<?= esc($serviceData['step4']['travel_fee_per_km'] ?? '0.00') ?>
                        </p>
                    <?php endif; ?>
                </div>



                <div id="locationSummary"></div>
                <a href="/service/step4" class="btn btn-secondary mt-2">Edit</a>
            </section>

            <section id="optionalExtrasSection">
                <div class="review-section">
                    <h4>Step 5: Optional Extras</h4>
                    <?php if (!empty($serviceData['step5'])): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Optional Extra</th>
                                    <th>Description</th>
                                    <th>Price (£)</th>
                                    <th>Pricing</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($serviceData['step5'] as $extra): ?>
                                    <?php
                                    $pricingDetail = '';
                                    if (array_key_exists('quantity', $extra) && $extra['quantity'] !== '' && $extra['quantity'] !== null) {
                                        $pricingDetail = (string) $extra['quantity'];
                                    } elseif (($extra['pricing_type'] ?? 'flat') === 'per_item') {
                                        $unit = trim((string) ($extra['unit_label'] ?? '')) ?: 'item';
                                        $min = $extra['min_quantity'] ?? null;
                                        $max = $extra['max_quantity'] ?? null;
                                        $pricingDetail = 'Per ' . $unit;
                                        if ($min !== null && $max !== null) {
                                            $pricingDetail .= ' — quantities ' . (int) $min . ' to ' . (int) $max;
                                        } elseif ($min !== null) {
                                            $pricingDetail .= ' — minimum ' . (int) $min;
                                        } elseif ($max !== null) {
                                            $pricingDetail .= ' — maximum ' . (int) $max;
                                        }
                                    } else {
                                        $pricingDetail = 'Flat fee';
                                    }
                                    ?>
                                    <tr>
                                        <td><?= esc($extra['name']) ?></td>
                                        <td><?= esc($extra['description']) ?></td>
                                        <td><?= '£' . number_format((float) $extra['price'], 2, '.', '') ?></td>
                                        <td>
                                            <?php if (($extra['pricing_type'] ?? 'flat') === 'per_item'): ?>
                                                Per <?= esc($extra['unit_label'] ?: 'item') ?>
                                                <?php if (!empty($extra['min_quantity']) || !empty($extra['max_quantity'])): ?>
                                                    (<?= esc($extra['min_quantity'] ?? 1) ?>–<?= esc($extra['max_quantity'] ?? '∞') ?>)
                                                <?php endif; ?>
                                            <?php else: ?>
                                                Flat fee
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No optional extras added.</p>
                    <?php endif; ?>
                </div>
                <a href="/service/step5" class="btn btn-secondary mt-2">Edit</a>
            </section>

            <section id="cancelationPolicySection">
                <div class="review-section">
                    <h4>Step 6: Cancellation Policy</h4>
                    <p><?= esc($serviceData['step6']['cancellation_policy'] ?? 'Not provided') ?></p>
                </div>
                <a href="/service/step6" class="btn btn-secondary">Edit</a>
            </section>

        </div><!-- /.review-section -->

        <div class="text-center my-4">
            <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-check-circle me-2"></i>Confirm and Submit</button>
        </div>
    </form>
</main>

<?= $this->include('footer') ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Extract data from the PHP-rendered page
        const location = "<?= esc($serviceData['step4']['service_location'] ?? 'your location') ?>";
        const allTravelIncluded = <?= isset($serviceData['step4']['all_travel_included']) ? (int) $serviceData['step4']['all_travel_included'] : 0 ?>;
        const noTravelLimit = <?= isset($serviceData['step4']['no_travel_limit']) ? (int) $serviceData['step4']['no_travel_limit'] : 0 ?>;
        const freeRadius = <?= isset($serviceData['step4']['free_coverage_radius']) ? (int) $serviceData['step4']['free_coverage_radius'] : 'null' ?>;
        const paidRadius = <?= isset($serviceData['step4']['paid_coverage_radius']) ? (int) $serviceData['step4']['paid_coverage_radius'] : 'null' ?>;
        const travelFee = <?= isset($serviceData['step4']['travel_fee_per_km']) ? (float) $serviceData['step4']['travel_fee_per_km'] : 0 ?>;

        let summary = '';

        if (allTravelIncluded && noTravelLimit) {
            summary = `All travel fees are included from ${location} and cover all of Scotland, England, and Wales.`;
        } else if (allTravelIncluded) {
            summary = `All travel fees are included from ${location} and will travel up to ${freeRadius} km.`;
        } else if (noTravelLimit) {
            if (freeRadius) {
                summary = `Your service covers all of Scotland, England, and Wales from ${location}. You will travel up to ${freeRadius} km included in the price and charge £${travelFee} per km beyond that.`;
            } else {
                summary = `Your service covers all of Scotland, England, and Wales from ${location}. You will travel nationally and charge £${travelFee} per km.`;
            }
        } else {
            // No checkboxes selected
            if (freeRadius && paidRadius && travelFee) {
                const totalRadius = parseInt(freeRadius) + parseInt(paidRadius);
                summary = `You will travel up to ${freeRadius} km from ${location} at no extra cost. Beyond this, you can travel an additional ${paidRadius} km for £${travelFee} per km, totaling up to ${totalRadius} km.`;
            } else if (freeRadius) {
                summary = `You will travel up to ${freeRadius} km from ${location} included in the price.`;
            } else if (paidRadius && travelFee) {
                summary = `You will travel up to ${paidRadius} km from ${location} at £${travelFee} per km.`;
            } else {
                summary = 'Please fill in your travel details to generate a summary.';
            }
        }

        if (!summary) {
            summary = 'Please fill in your travel details to generate a summary.';
        }

        // Update the locationSummary div
        const locationSummaryDiv = document.getElementById('locationSummary');
        if (locationSummaryDiv) {
            locationSummaryDiv.textContent = summary;
        }
    });
</script>