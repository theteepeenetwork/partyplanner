<?= $this->include('header') ?>
<?= $this->include('service_create/css.php') ?>

<main class="container">
    <section>
        <?php if (isset($service)): ?>
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
                        <p><strong>Subcategory:</strong> <?= esc($category_names['sub'] ?? 'Not Selected') ?></p>
                        <p><strong>Further Subcategory:</strong> <?= esc($category_names['third'] ?? 'Not Selected') ?></p>

                        <!-- Pricing Options -->
                        <div class="pricing-options">
                            <h4>Pricing Options</h4>
                            <form action="<?= base_url('service/checkout/' . $service['id']) ?>" method="post">
                                <?php if (!empty($guestPricing)): ?>
                                    <div class="form-group">
                                        <label for="guestPricing">Guest-Based Pricing:</label>
                                        <select class="form-control" id="guestPricing" name="pricing_option">
                                            <?php foreach ($guestPricing as $pricing): ?>
                                                <option value="guest_<?= esc($pricing['id']) ?>">
                                                    <?= esc($pricing['min_guest']) ?> to <?= esc($pricing['max_guest']) ?> Guests:
                                                    £<?= esc($pricing['guest_price']) ?> per person
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($durationPricing)): ?>
                                    <div class="form-group">
                                        <label for="durationPricing">Duration-Based Pricing:</label>
                                        <select class="form-control" id="durationPricing" name="pricing_option">
                                            <?php foreach ($durationPricing as $pricing): ?>
                                                <?php if ($pricing['duration_type'] === 'hour'): ?>
                                                    <option value="hour_<?= esc($pricing['id']) ?>">
                                                        <?= esc($pricing['duration']) ?> Hour(s): £<?= esc($pricing['price']) ?>
                                                    </option>
                                                <?php elseif ($pricing['duration_type'] === 'day'): ?>
                                                    <option value="day_<?= esc($pricing['id']) ?>">
                                                        <?= esc($pricing['duration']) ?> Day(s): £<?= esc($pricing['price']) ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>


                                <?php if (!empty($tieredPackages)): ?>
                                    <div class="form-group">
                                        <label for="tieredPackages">Tiered Packages:</label>
                                        <select class="form-control" id="tieredPackages" name="pricing_option">
                                            <?php foreach ($tieredPackages as $package): ?>
                                                <option value="package_<?= esc($package['id']) ?>">
                                                    <?= esc($package['package_name']) ?>: £<?= esc($package['package_price']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php else: ?>
                                    <p>No tiered packages available to display.</p>
                                <?php endif; ?>



                                <!-- Optional Extras -->
                                <?php if (!empty($optional_extras)): ?>
                                    <div class="form-group">
                                        <label>Optional Extras:</label>
                                        <?php foreach ($optional_extras as $extra): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="extra_<?= esc($extra['id']) ?>"
                                                    name="extras[]" value="<?= esc($extra['id']) ?>">
                                                <label class="form-check-label" for="extra_<?= esc($extra['id']) ?>">
                                                    <?= esc($extra['name']) ?>: £<?= esc($extra['price']) ?>
                                                </label>
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

                                <!-- Action Buttons -->
                                <button type="submit" class="btn btn-primary">Add to Basket</button>
                                <button type="button" class="btn btn-outline-secondary">Add to Wishlist</button>
                                <a href="<?= base_url('service/edit/' . $service['id']) ?>" class="btn btn-primary">
                                    Edit Service
                                </a>

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