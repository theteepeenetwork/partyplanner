<!-- Services Tab -->
<div class="tab-pane fade" id="services" role="tabpanel" aria-labelledby="services-tab">
            <h2>Active Listings</h2>
            <?php if (!empty($activeServices)): ?>
                <div class="row">
                    <?php foreach ($activeServices as $service): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <?php if (!empty($service['images'])): ?>
                                    <img src="<?= base_url(esc($service['images'][0]['thumbnail_path'])) ?>" class="card-img-top" alt="<?= esc($service['title']) ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?= esc($service['title']) ?></h5>
                                    <p class="card-text"><?= esc($service['short_description']) ?></p>
                                    <p class="card-text">Price: £<?= esc($service['price']) ?></p>
                                    <a href="<?= base_url('service/view/' . esc($service['id'])) ?>" class="btn btn-primary btn-sm">View Details</a>
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
                            <div class="card">
                                <?php if (!empty($service['images'])): ?>
                                    <img src="<?= base_url(esc($service['images'][0]['thumbnail_path'])) ?>" class="card-img-top" alt="<?= esc($service['title']) ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?= esc($service['title']) ?></h5>
                                    <p class="card-text"><?= esc($service['short_description']) ?></p>
                                    <p class="card-text">Price: £<?= esc($service['price']) ?></p>
                                    <a href="<?= base_url('service/view/' . esc($service['id'])) ?>" class="btn btn-primary btn-sm">View Details</a>
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