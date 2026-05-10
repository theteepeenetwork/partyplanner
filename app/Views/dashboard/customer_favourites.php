<?= $this->include('header') ?>

<div class="dashboard-wrapper">
    <div class="container">
        <?= $this->include('dashboard/_customer_tabs') ?>

        <h4 class="mb-4">My Favourites</h4>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (!empty($favourites)): ?>
            <div class="row g-3">
                <?php foreach ($favourites as $fav): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="dash-card h-100">
                            <?php if (!empty($fav['image'])): ?>
                                <img src="<?= base_url($fav['image']) ?>" class="rounded mb-2" style="width:100%; height:140px; object-fit:cover;" alt="<?= esc($fav['service']['title']) ?>">
                            <?php else: ?>
                                <div class="rounded mb-2 d-flex align-items-center justify-content-center bg-light" style="height:140px;">
                                    <i class="fas fa-image fa-2x text-muted"></i>
                                </div>
                            <?php endif; ?>

                            <h6 class="mb-1"><?= esc($fav['service']['title']) ?></h6>
                            <div class="text-muted small mb-1">
                                <i class="fas fa-store me-1"></i><?= esc($fav['vendor_name']) ?>
                            </div>
                            <?php if (!empty($fav['category_name'])): ?>
                                <span class="badge bg-light text-dark small mb-1"><?= esc($fav['category_name']) ?></span>
                            <?php endif; ?>

                            <?php if (!empty($fav['service']['price'])): ?>
                                <p class="fw-bold text-primary mb-2">From £<?= number_format($fav['service']['price'], 2) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($fav['service']['service_location'])): ?>
                                <div class="text-muted small mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= esc($fav['service']['service_location']) ?>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex gap-1 flex-wrap">
                                <a href="/service/view/<?= $fav['service']['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="/browse-services" class="btn btn-sm btn-outline-success">Add to Event</a>
                                <a href="/profile/favourites/remove/<?= $fav['favourite_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove from favourites?');">
                                    <i class="fas fa-heart-broken"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="dash-card text-center py-5">
                <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                <h5>No favourites yet</h5>
                <p class="text-muted">Save services you like while browsing to find them here later.</p>
                <a href="/browse-services" class="btn btn-primary">Browse Services</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->include('footer') ?>
