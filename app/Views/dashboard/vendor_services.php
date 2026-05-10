<?= $this->include('header') ?>

<div class="dashboard-wrapper">
    <div class="container">
        <?= $this->include('dashboard/_vendor_tabs') ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">My Services</h4>
            <a href="/service/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add New Service</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php
        $activeServices = array_filter($services, fn($s) => ($s['status'] ?? '') === 'active');
        $inactiveServices = array_filter($services, fn($s) => ($s['status'] ?? '') !== 'active');
        ?>

        <!-- Active Services -->
        <h5 class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Active Services (<?= count($activeServices) ?>)</h5>

        <?php if (!empty($activeServices)): ?>
            <div class="row g-3 mb-4">
                <?php foreach ($activeServices as $service): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="dash-card h-100">
                            <?php if (!empty($service['images'])): ?>
                                <img src="<?= base_url($service['images'][0]['thumbnail_path'] ?? $service['images'][0]['image_path']) ?>" 
                                     class="rounded mb-2" style="width:100%; height:140px; object-fit:cover;" alt="<?= esc($service['title']) ?>">
                            <?php else: ?>
                                <div class="rounded mb-2 d-flex align-items-center justify-content-center bg-light" style="height:140px;">
                                    <i class="fas fa-image fa-2x text-muted"></i>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="mb-0"><?= esc($service['title']) ?></h6>
                                <span class="badge bg-success">Active</span>
                            </div>

                            <?php if (!empty($service['category_name'])): ?>
                                <small class="text-muted"><?= esc($service['category_name']) ?></small>
                            <?php endif; ?>

                            <?php if (!empty($service['price'])): ?>
                                <p class="fw-bold text-primary mb-2 mt-1">From £<?= number_format($service['price'], 2) ?></p>
                            <?php endif; ?>

                            <div class="d-flex gap-1 flex-wrap">
                                <a href="/service/view/<?= $service['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="/service/edit/<?= $service['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <a href="/service/deactivate/<?= $service['id'] ?>" class="btn btn-sm btn-outline-warning">Deactivate</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="dash-card text-center py-4 mb-4">
                <i class="fas fa-briefcase fa-2x text-muted mb-2"></i>
                <p class="text-muted">No active services yet.</p>
                <a href="/service/create" class="btn btn-primary">Create Your First Service</a>
            </div>
        <?php endif; ?>

        <!-- Inactive Services -->
        <?php if (!empty($inactiveServices)): ?>
            <h5 class="mb-3"><i class="fas fa-pause-circle text-muted me-2"></i>Inactive Services (<?= count($inactiveServices) ?>)</h5>
            <div class="row g-3 mb-4">
                <?php foreach ($inactiveServices as $service): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="dash-card h-100" style="opacity: 0.7;">
                            <?php if (!empty($service['images'])): ?>
                                <img src="<?= base_url($service['images'][0]['thumbnail_path'] ?? $service['images'][0]['image_path']) ?>" 
                                     class="rounded mb-2" style="width:100%; height:140px; object-fit:cover;" alt="<?= esc($service['title']) ?>">
                            <?php else: ?>
                                <div class="rounded mb-2 d-flex align-items-center justify-content-center bg-light" style="height:140px;">
                                    <i class="fas fa-image fa-2x text-muted"></i>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="mb-0"><?= esc($service['title']) ?></h6>
                                <span class="badge bg-secondary">Inactive</span>
                            </div>

                            <?php if (!empty($service['price'])): ?>
                                <p class="fw-bold text-muted mb-2 mt-1">From £<?= number_format($service['price'], 2) ?></p>
                            <?php endif; ?>

                            <div class="d-flex gap-1 flex-wrap">
                                <a href="/service/view/<?= $service['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="/service/edit/<?= $service['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <a href="/service/reactivate/<?= $service['id'] ?>" class="btn btn-sm btn-outline-success">Activate</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?= $this->include('footer') ?>
