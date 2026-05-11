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
                            <p class="service-price mt-2">From £<?= number_format($service['price'], 2) ?></p>
                        <?php endif; ?>

                        <!-- Pricing Options -->
                        <div class="pricing-options">
                            <?php $hasPricing = !empty($guestPricing) || !empty($durationPricing) || !empty($tieredPackages); ?>
                            <?php if ($hasPricing): ?>
                                <h4>Pricing Options</h4>
                            <?php endif; ?>

                            <form action="<?= base_url('cart/add/' . $service['id']) ?>" method="post">
                                <?php if (!empty($guestPricing)): ?>
                                    <div class="form-group">
                                        <label for="guestPricing">Guest-Based Pricing:</label>
                                        <select class="form-control" id="guestPricing" name="pricing_option">
                                            <?php foreach ($guestPricing as $pricing): ?>
                                                <option value="guest_<?= esc($pricing['id']) ?>">
                                                    <?= esc($pricing['min_guest'] ?? $pricing['min_guests'] ?? '') ?> to <?= esc($pricing['max_guest'] ?? $pricing['max_guests'] ?? '') ?> Guests:
                                                    £<?= esc($pricing['guest_price'] ?? $pricing['price'] ?? '') ?> per person
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
                                                <option value="duration_<?= esc($pricing['id']) ?>">
                                                    <?= esc($pricing['duration_hours'] ?? $pricing['duration'] ?? '') ?> Hour(s): £<?= esc($pricing['price']) ?>
                                                </option>
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
                                                    <?= esc($package['package_name']) ?>: £<?= esc($package['package_price'] ?? $package['price'] ?? '') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
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
                                <div class="mt-3">
                                    <?php if (session()->has('user_id') && session()->get('role') === 'vendor' && $service['vendor_id'] == session()->get('user_id')): ?>
                                        <a href="<?= base_url('service/edit/' . $service['id']) ?>" class="btn btn-primary">
                                            Edit Service
                                        </a>
                                    <?php else: ?>
                                        <a href="/event/add-to-event/<?= $service['id'] ?>" class="btn btn-primary btn-lg">
                                            <i class="fas fa-calendar-plus me-1"></i>Add to Event
                                        </a>
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
