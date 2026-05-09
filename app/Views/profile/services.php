<?= $this->include('service_create/css.php') ?>

<h2>Active Listings</h2>

<?php if (!empty($activeServices)): ?>
    <div class="row">
        <?php foreach ($activeServices as $service): ?>
            <div class="col-md-4 mb-4">
                <div class="service-card">
                    <?php if (!empty($service['images']) && isset($service['images'][0]['thumbnail_path'])): ?>
                        <img src="<?= esc($service['images'][0]['thumbnail_path']) ?>" class="service-card-image"
                            alt="<?= esc($service['title']) ?>">
                    <?php else: ?>
                        <img src="<?= base_url('uploads/default-service.png') ?>" class="service-card-image" alt="Default Image">
                    <?php endif; ?>

                    <div class="service-card-content">
                        <h5 class="service-card-title"><?= esc($service['title']) ?></h5>
                        <p class="service-card-description"><?= esc($service['short_description']) ?></p>
                        <a href="<?= base_url('service/view/' . esc($service['id'])) ?>" class="service-card-button">View
                            Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>You don't have any active services yet.</p>
<?php endif; ?>


<h2>Inactive Listings</h2>
<?php if (!empty($inactiveServices)): ?>
    <div class="row">
        <?php foreach ($inactiveServices as $service): ?>
            <div class="col-md-4 mb-4">
                <div class="service-card inactive">
                    <?php if (!empty($service['images']) && isset($service['images'][0]['thumbnail_path'])): ?>
                        <img src="<?= esc($service['images'][0]['thumbnail_path']) ?>" class="service-card-image"
                            alt="<?= esc($service['title']) ?>">
                    <?php else: ?>
                        <img src="<?= base_url('uploads/default-service.png') ?>" class="service-card-image" alt="Default Image">
                    <?php endif; ?>

                    <div class="service-card-content">
                        <h5 class="service-card-title"><?= esc($service['title']) ?></h5>
                        <p class="service-card-description"><?= esc($service['short_description']) ?></p>

                        <!-- Disabled button for inactive services -->
                        <a href="<?= base_url('service/view/' . esc($service['id'])) ?>"
                            class="service-card-button disabled">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>You don't have any inactive services yet.</p>
<?php endif; ?>


<a href="/service/create" class="btn btn-primary mb-3">Add New Service</a>
</div>