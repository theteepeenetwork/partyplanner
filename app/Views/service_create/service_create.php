<?= $this->include('header') ?>
<style>

</style>



<main class="container mt-4">
    <h2>Create Service</h2>

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




    <form action="/service/create" method="POST" enctype="multipart/form-data" id="serviceForm">
        <?= csrf_field() ?>


        <!-- Progress Bar -->
        <div class="progress mb-4">
            <div class="progress-bar" role="progressbar" style="width: 0%;" id="progressBar"></div>
        </div>

        <!-- Step 1: Basic Service Information -->


        <!-- Step 1 -->
        <!-- Step 1 -->
        <!-- Step 1 -->
        <!-- Step 1 -->
        <!-- Step 1 -->
        <!-- Step 1 -->
        <!-- Step 1 -->
        <section id="step1" class="step">
            <h4>Service Information</h4>

            <!-- Title with Info Icon -->
            <div class="form-group">
                <label for="title">Title:</label>
                <span class="info-icon info-trigger" data-title="Why Is Commission Required?"
                    data-content="Not every event will ask for a commission. In instances where commission is required, 
          it is because the organiser provides significant support to ensure vendor success, such as:
          <ul><li>Marketing the event to attract high footfall.</li>
          <li>Covering event-related costs like venue setup and logistics.</li>
          <li>Reducing upfront costs with a profit-sharing model.</li></ul>
          By sharing a small percentage of your sales, you benefit from reduced risks and access to well-organised events." title="Click for more information">?</span>
                <input type="text" class="form-control <?= session('errors.title') ? 'is-invalid' : '' ?>" id="title"
                    name="title" value="<?= old('title') ?>" required maxlength="100"
                    placeholder="E.g., Wedding Photography Package">
                <div class="invalid-feedback"><?= session('errors.title') ?></div>
            </div>

            <!-- Short Description -->
            <div class="form-group">
                <label for="short_description">Short Description:</label>
                <input type="text" class="form-control <?= session('errors.short_description') ? 'is-invalid' : '' ?>"
                    id="short_description" name="short_description" value="<?= old('short_description') ?>" required
                    maxlength="200" placeholder="Provide a brief description (max 200 characters)">
                <div class="invalid-feedback"><?= session('errors.short_description') ?></div>
            </div>

            <!-- Long Description -->
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea class="form-control <?= session('errors.description') ? 'is-invalid' : '' ?>" id="description"
                    name="description" rows="5" required
                    placeholder="Detailed description of your service..."><?= old('description') ?></textarea>
                <div class="invalid-feedback"><?= session('errors.description') ?></div>
            </div>

            <!-- Service Tags -->
            <div class="form-group">
                <label for="service_tags">Service Tags:</label>
                <div class="tag-container" id="tagContainer">
                    <!-- The visible input for tags is required initially -->
                    <input type="text" id="service_tags_input" class="tag-input"
                        placeholder="Enter tags (comma-separated)" required>
                </div>
                <input type="hidden" name="service_tags" id="service_tags_hidden">
                <small class="form-text text-muted">Example: wedding, catering, food, photography</small>
            </div>



            <!-- ===================== IMAGES ===================== -->
            <!-- ===================== IMAGES ===================== -->
            <!-- ===================== IMAGES ===================== -->
            <!-- ===================== IMAGES ===================== -->
            <!-- ===================== IMAGES ===================== -->
            <!-- ===================== IMAGES ===================== -->
            <!-- ===================== IMAGES ===================== -->



            <h4>Images</h4>
            <div class="form-group">
                <label for="images">Upload Images:</label>
                <input type="file" class="form-control-file <?= session('errors.images') ? 'is-invalid' : '' ?>"
                    id="images" name="images[]" multiple accept="image/jpeg, image/png" required>
                <div class="invalid-feedback"><?= session('errors.images') ?></div>
                <small class="form-text text-muted">Accepted formats: JPG, PNG. Max size: 5MB each.</small>
            </div>

            <!-- Image Preview Section -->
            <div id="imagePreviewContainer" class="d-flex flex-wrap"></div>





            <!-- ===================== CATEGORIES ===================== -->
            <!-- ===================== CATEGORIES ===================== -->
            <!-- ===================== CATEGORIES ===================== -->
            <!-- ===================== CATEGORIES ===================== -->
            <!-- ===================== CATEGORIES ===================== -->
            <!-- ===================== CATEGORIES ===================== -->
            <!-- ===================== CATEGORIES ===================== -->





            <h4>Categories</h4>
            <!-- Main Category -->
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select class="form-control" id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <?php if ($category['level'] === 0): ?>
                            <option value="<?= esc($category['id']) ?>"><?= esc($category['name']) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Please select a category.</div>
            </div>

            <!-- Subcategory -->
            <div class="form-group">
                <label for="subcategory_id">Subcategory:</label>
                <select class="form-control" id="subcategory_id" name="subcategory_id" disabled>
                    <option value="">Select Subcategory</option>
                </select>
                <div class="invalid-feedback">Please select a subcategory.</div>
            </div>

            <!-- Third Category -->
            <div class="form-group">
                <label for="third_category_id">Further Subcategory (optional):</label>
                <select class="form-control" id="third_category_id" name="third_category_id" disabled>
                    <option value="">Select Further Subcategory</option>
                </select>
                <small class="form-text text-muted">Optional selection.</small>
            </div>

            <button type="button" class="btn btn-secondary back-btn" data-previous="step1">Back</button>
            <button type="button" class="btn btn-primary next-btn" data-next="step2">Next</button>
        </section>


        <!-- Step 2 Section -->
        <!-- Step 2 Section -->
        <!-- Step 2 Section -->
        <!-- Step 2 Section -->
        <!-- Step 2 Section -->
        <!-- Step 2 Section -->
        <!-- Step 2 Section -->
        <!-- Step 2 Section -->
        <!-- Step 2 Section -->
        <section>
            <h4>Event Availability</h4>
            <div class="form-group">
                <label for="event_types">Available for Events:</label>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input event-type" id="event_public" name="event_types[]"
                        value="public">
                    <label class="form-check-label" for="event_public">Public Events</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input event-type" id="event_private" name="event_types[]"
                        value="private">
                    <label class="form-check-label" for="event_private">Private Events</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input event-type" id="event_corporate" name="event_types[]"
                        value="corporate">
                    <label class="form-check-label" for="event_corporate">Corporate Events</label>
                </div>
            </div>

            <div class="invalid-feedback d-none" id="eventTypesError">
                Please select at least one event type.
            </div>

            <!-- Existing Navigation Buttons -->
            <button type="button" class="btn btn-secondary back-btn" data-previous="step3">Back</button>
            <button type="button" class="btn btn-primary next-btn" data-next="step5" id="step4-next-btn"
                disabled>Next</button>
        </section>



        <!-- Step 3: PUBLIC PRIVATE CORPORATE Selection -->
        <!-- Step 3: PUBLIC PRIVATE CORPORATE Selection -->
        <!-- Step 3: PUBLIC PRIVATE CORPORATE Selection -->
        <!-- Step 3: PUBLIC PRIVATE CORPORATE Selection -->
        <!-- Step 3: PUBLIC PRIVATE CORPORATE Selection -->
        <!-- Step 3: PUBLIC PRIVATE CORPORATE Selection -->
        <!-- Step 3: PUBLIC PRIVATE CORPORATE Selection -->

        <div id="event-specific-section">
            <!-- Include event form sections but keep them hidden initially -->
            <!-- ===================== public ===================== -->
            <!-- ===================== public ===================== -->
            <!-- ===================== public ===================== -->
            <!-- ===================== public ===================== -->
            <!-- ===================== public ===================== -->
            <!-- ===================== public ===================== -->
            <!-- ===================== public ===================== -->

            <div id="public-event-form" class="event-form" style="display:;">





                <section class="pricing-section">
                    <h4>Public Event Pricing</h4>

                    <!-- Commission Percentage -->
                    <div class="form-group">
                        <label for="commission_percentage">Commission Percentage:</label>
                        <span class="info-icon info-trigger" data-title="Why Is Commission Required?"
                            data-content="Not every event will ask for a commission. In instances where commission is required, 
        it is because the organiser provides significant support to ensure vendor success, such as:
        <ul><li>Marketing the event to attract high footfall.</li>
        <li>Covering event-related costs like venue setup and logistics.</li>
        <li>Reducing upfront costs with a profit-sharing model.</li></ul>
        By sharing a small percentage of your sales, you benefit from reduced risks and access to well-organised events." title="Click for more information">?</span>
                        <div class="input-group">

                            <div class="form-row">

                                <input type="number" class="form-control" id="commission_percentage"
                                    name="commission_percentage" placeholder="Commission (%) you are willing to offer">

                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <small class="form-text">
                            Enter the commission percentage you are willing to offer for this service.
                            Leave blank if
                            not
                            applicable.
                        </small>
                    </div>

                    <!-- Attendance Threshold and Pitch Price -->
                    <div class="form-group">
                        <label for="pitchFeeContainer">
                            Attendance Threshold and Pitch Price:
                        </label>
                        <span class="info-icon info-trigger" data-title="What is Attendance Threshold and Pitch Price?"
                            data-content="<div>
          <p>Enter the range of attendees you can accommodate and the maximum pitch fee for each range.</p>
          <p><strong>Example:</strong></p>
          <p>For a capacity of 10,000 attendees:</p>
          <ul>
            <li><strong>0–5000 attendees:</strong> £[Fee]</li>
            <li><strong>5001–10,000 attendees:</strong> £[Fee]</li>
          </ul>
        </div>" title="Click for more information">?</span>
                        <div id="pitchFeeContainer">
                            <!-- Button fills this div by calling addPitchFee -->
                        </div>

                        <!-- Button to add a new fee range -->
                        <button type="button" class="btn btn-primary" id="addPitchFee">Add Another Fee
                            Range</button>
                </section>


            </div>

            <!-- Include event form sections but keep them hidden initially -->
            <!-- ===================== PRIVATE ===================== -->
            <!-- ===================== PRIVATE ===================== -->
            <!-- ===================== PRIVATE ===================== -->
            <!-- ===================== PRIVATE ===================== -->
            <!-- ===================== PRIVATE ===================== -->
            <!-- ===================== PRIVATE ===================== -->
            <!-- ===================== PRIVATE ===================== -->
            <div id="private-event-form" class="event-form" style="display:none;">
                <section class="pricing-section">
                    <h4>Private Event Pricing</h4>


                    <!-- Pricing Type Selection (Guest-Based, Custom Duration, Tiered Packages) -->
                    <!-- Radio Buttons -->
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="guest_based_pricing" name="pricing_type"
                            value="guest_based">
                        <label class="custom-control-label" for="guest_based_pricing">Guest-based Pricing</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="custom_duration_pricing"
                            name="pricing_type" value="custom_duration">
                        <label class="custom-control-label" for="custom_duration_pricing">Duration Pricing</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="tiered_packages_pricing"
                            name="pricing_type" value="tiered_packages">
                        <label class="custom-control-label" for="tiered_packages_pricing">Tiered Packages
                            Pricing</label>
                    </div>

                    <!-- Guest-Based Pricing Section (Collapsible) -->
                    <!-- Guest-Based Pricing Section -->

                    <div id="guestPricingContainer" style="display: none;">
                        <h5>Guest-based Pricing</h5>
                        <p class="form-text text-muted">
                            Specify the price per guest based on the range of attendees. For example, 1–25
                            guests: £25 per guest.
                        </p>

                        <!-- Container for Dynamic Guest Ranges -->
                        <div id="guestPricingList"></div>

                        <!-- Add Row Button -->
                        <button type="button" class="btn btn-primary" id="addGuestPricing">Add Another Guest
                            Range</button>
                    </div>





                    <!-- Custom Duration Pricing Section (Collapsible) -->
                    <!-- Custom Duration Pricing Section -->

                    <div id="customDurationContainer" style="display: none;">
                        <h5>Duration Pricing</h5>

                        <div class="form-group">
                            <label>Select Pricing Options:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enableHours" checked>
                                <label class="form-check-label" for="enableHours">Enable Hours Pricing</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enableDays" checked>
                                <label class="form-check-label" for="enableDays">Enable Days Pricing</label>
                            </div>
                        </div>

                        <div id="hoursSection" class="pitch-group">
                            <h5>Hours Pricing</h5>
                            <!-- Missing container added here -->
                            <div id="hoursList"></div>
                            <button type="button" class="btn btn-primary" id="addHourRow">Add Another Hour</button>
                        </div>

                        <div id="daysSection" class="pitch-group">
                            <h5>Days Pricing</h5>
                            <!-- Missing container added here -->
                            <div id="daysList"></div>
                            <button type="button" class="btn btn-primary" id="addDayRow">Add Another Day</button>
                        </div>
                    </div>





                    <!-- Tiered Packages Pricing Section (Collapsible) -->
                    <div id="tieredPackageContainer" class="" style="display: none;">
                        <h5>Tiered Packages Pricing</h5>
                        <div id="tieredPackageList">
                            <!-- Package items will be added here dynamically -->
                        </div>
                        <button type="button" class="btn btn-primary" id="addPackage">Add Another
                            Package</button>
                    </div>


            </div>


            <!-- ===================== CORPORATE ===================== -->
            <!-- ===================== CORPORATE ===================== -->
            <!-- ===================== CORPORATE ===================== -->
            <!-- ===================== CORPORATE ===================== -->
            <!-- ===================== CORPORATE ===================== -->
            <!-- ===================== CORPORATE ===================== -->
            <!-- ===================== CORPORATE ===================== -->
            <div id="corporate-event-form" class="event-form" style="display:none;">
                <section class="pricing-section">
                    <h4>Public Event Pricing</h4>

                    <!-- Commission-Based Pricing Input -->
                    <div id="commissionSection">
                        <div class="form-group">
                            <label for="commission_percentage">Commission Percentage:</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="commission_percentage"
                                    name="commission_percentage" placeholder="Commission (%) you are willing to offer">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Willing to Pay Up to (Commission Cap) - Needs more consideration
        <div class="form-group">
            <label for="max_commission">Willing to Pay Up to (Commission Cap):</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">£</span>
                </div>
                <input type="number" class="form-control" id="max_commission" name="max_commission"
                    placeholder="Max commission you're willing to pay">
            </div>
        </div>
    </div> -->

                    </div>

                    <!-- Button to add more pitch fee ranges -->
                    <button type="button" class="btn btn-primary" id="addPitchFee">Add Another Pitch
                        Fee</button>



                    <h4>Licenses and Permits</h4>
                    <div class="form-group">
                        <label for="license">Upload Licenses or Permits (optional):</label>
                        <input type="file" class="form-control-file" id="license" name="license">
                        <small class="form-text text-muted">If required for public events.</small>
                    </div>
                </section>


            </div>
        </div>

        <button type="button" class="btn btn-secondary back-btn" data-previous="step2">Back</button>
        <button type="button" class="btn btn-primary next-btn" data-next="step4">Next</button>

        </div>
        </section>




        <!-- Step 4: Location -->
        <!-- Step 4: Location -->
        <!-- Step 4: Location -->
        <!-- Step 4: Location -->
        <!-- Step 4: Location -->
        <!-- Step 4: Location -->
        <!-- Step 4: Location -->

        <section id="travel-coverage" class="">
            <h4>Location and Coverage</h4>

            <!-- Service Base Location -->
            <div class="form-group">
                <label for="service_location">Service Base Location:</label>
                <input type="text" class="form-control" id="service_location" name="service_location"
                    placeholder="Enter address or postcode" required>
                <small id="serviceLocationHelp" class="form-text text-muted">
                    Set the starting point for your service.
                </small>
                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">
            </div>

            <!-- All Travel Included Checkbox -->
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="all_travel_included" name="all_travel_included"
                        value="1">
                    <label class="form-check-label" for="all_travel_included">All Travel Included in
                        Price</label>
                </div>
                <small class="form-text text-muted">
                    Include all travel costs in your service price. No additional fees are charged.
                    Selecting this
                    option will grey out the Paid Coverage Radius and Travel Fee Per KM fields.
                </small>
            </div>

            <!-- No Travel Limit Checkbox -->
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="no_travel_limit" name="no_travel_limit">
                    <label class="form-check-label" for="no_travel_limit">No Travel Limit (National
                        Coverage)</label>
                </div>
                <small class="form-text text-muted">
                    Your service covers all of Scotland, England, and Wales. You can still specify a free
                    coverage
                    radius. Selecting this option will grey out the Paid Coverage Radius field and remove
                    the Paid
                    Coverage Radius circle from the map.
                </small>
            </div>




            <!-- Free Coverage Radius -->
            <div class="form-group">
                <label for="free_coverage_radius">Free Coverage Radius (km):</label>
                <input type="number" class="form-control" id="free_coverage_radius" name="free_coverage_radius"
                    placeholder="Enter free coverage radius in km" aria-describedby="freeCoverageHelp">
                <small id="freeCoverageHelp" class="form-text text-muted">
                    Distance you’re willing to travel for free (e.g., 50 km).
                </small>
            </div>

            <!-- Paid Coverage Radius -->
            <div class="form-group">
                <label for="paid_coverage_radius">Maximum Distance You're Willing to Travel for an
                    Additional Fee
                    (km):</label>
                <input type="number" class="form-control" id="paid_coverage_radius" name="paid_coverage_radius"
                    placeholder="Enter paid coverage radius in km" aria-describedby="paidCoverageHelp">
                <small id="paidCoverageHelp" class="form-text text-muted">
                    Maximum distance you’re willing to travel beyond the free range (e.g., 100 km).
                </small>
            </div>

            <!-- Travel Fee Per KM -->
            <div class="form-group">
                <label for="travel_fee_per_km">Travel Fee Per KM (£):</label>
                <input type="number" class="form-control" id="travel_fee_per_km" name="travel_fee_per_km"
                    placeholder="Enter fee per km beyond free coverage" step="0.01" onblur="formatDecimal(this)">
                <small class="form-text text-muted">
                    Fee charged per kilometre beyond the free coverage radius (e.g., £0.50).
                </small>
            </div>

            <!-- Map -->
            <div id="map" style="width: 100%; height: 400px;"></div>

            <button type="button" class="btn btn-secondary back-btn" data-previous="step3">Back</button>
            <button type="button" class="btn btn-primary next-btn" data-next="step5">Next</button>
        </section>
        </section>



        <!-- Step 5: Optional Extras -->
        <!-- Step 5: Optional Extras -->
        <!-- Step 5: Optional Extras -->
        <!-- Step 5: Optional Extras -->
        <!-- Step 5: Optional Extras -->
        <!-- Step 5: Optional Extras -->
        <!-- Step 5: Optional Extras -->
        <section>
            <h4>Optional Extras</h4>
            <small class="form-text text-muted">
                Add any additional services or items that clients can select for an extra fee. For example:
                Extra staff, equipment upgrades, or personalised add-ons.
            </small>
            <!-- Optional Extras Section -->
            <div>
                <h5>Optional Extras</h5>
                <p class="form-text text-muted">Add any optional extras your service offers, such as additional
                    staff or equipment.
                </p>

                <!-- Optional Extras Container -->
                <div id="optionalExtrasContainer"></div>

                <!-- Add Button -->
                <button type="button" class="btn btn-primary mb-3" id="addOptionalExtra">Add Another
                    Extra</button><br />

                <!-- Navigation Buttons -->
                <button type="button" class="btn btn-secondary back-btn" data-previous="step4">Back</button>
                <button type="button" class="btn btn-primary next-btn" data-next="step6">Next</button>
            </div>
        </section>

        <!-- Step 6: Cancellation -->
        <!-- Step 6: Cancellation -->
        <!-- Step 6: Cancellation -->
        <!-- Step 6: Cancellation -->
        <!-- Step 6: Cancellation -->
        <!-- Step 6: Cancellation -->
        <section>
            <h4>Cancellation Policy</h4>
            <div class="form-group">
                <label for="cancellation_policy">Cancellation Policy:</label>
                <textarea class="form-control" id="cancellation_policy" name="cancellation_policy" rows="4"
                    placeholder="Enter your cancellation policy here..." required></textarea>
            </div>

            <button type="button" class="btn btn-secondary back-btn" data-previous="step5">Back</button>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create Service</button>
            </div>
        </section>





    </form>



</main>
<?= $this->include('service_create/service_create_scripts') ?>