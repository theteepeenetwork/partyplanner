<?= $this->include('header') ?>

<div class="dashboard-wrapper">
    <div class="container">
        <?= $this->include('dashboard/_vendor_tabs') ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">My Services</h4>
            <a href="/service/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add New Service</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($services)): ?>
            <div class="row g-3">
                <?php foreach ($services as $service): ?>
                    <?php $isActive = ($service['status'] ?? '') === 'active'; ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="dash-card h-100 <?= !$isActive ? 'border-start border-3 border-secondary' : '' ?>" style="<?= !$isActive ? 'opacity:0.75;' : '' ?>">

                            <!-- Image -->
                            <?php if (!empty($service['images'])): ?>
                                <img src="<?= base_url($service['images'][0]['thumbnail_path'] ?? $service['images'][0]['image_path']) ?>"
                                     class="rounded mb-2" style="width:100%; height:160px; object-fit:cover;" alt="<?= esc($service['title']) ?>">
                            <?php else: ?>
                                <div class="rounded mb-2 d-flex align-items-center justify-content-center bg-light" style="height:160px;">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-image fa-2x mb-1"></i>
                                        <div class="small">No image</div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Title + Status Badge -->
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="mb-0 me-2"><?= esc($service['title']) ?></h6>
                                <span class="badge <?= $isActive ? 'bg-success' : 'bg-secondary' ?> flex-shrink-0">
                                    <?= $isActive ? 'Active' : 'Inactive' ?>
                                </span>
                            </div>

                            <!-- Category -->
                            <?php if (!empty($service['category_name'])): ?>
                                <div class="small text-muted mb-1"><i class="fas fa-tag me-1"></i><?= esc($service['category_name']) ?></div>
                            <?php endif; ?>

                            <!-- Short description -->
                            <?php if (!empty($service['short_description'])): ?>
                                <p class="small text-muted mb-2"><?= esc(substr($service['short_description'], 0, 80)) ?><?= strlen($service['short_description'] ?? '') > 80 ? '...' : '' ?></p>
                            <?php endif; ?>

                            <!-- Price -->
                            <?php if (!empty($service['price']) && $service['price'] > 0): ?>
                                <p class="fw-bold text-primary mb-2">From £<?= number_format($service['price'], 2) ?></p>
                            <?php endif; ?>

                            <!-- Toggle Switch -->
                            <div class="d-flex align-items-center justify-content-between mb-3 p-2 bg-light rounded">
                                <span class="small fw-bold"><?= $isActive ? 'Live on marketplace' : 'Hidden from customers' ?></span>
                                <form method="post" action="/service/toggle-status/<?= $service['id'] ?>" class="mb-0">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                               id="toggle-<?= $service['id'] ?>"
                                               <?= $isActive ? 'checked' : '' ?>
                                               onchange="this.closest('form').submit();"
                                               style="cursor:pointer; width:3em; height:1.5em;">
                                    </div>
                                </form>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="/service/view/<?= $service['id'] ?>" class="btn btn-sm btn-outline-primary flex-fill">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                                <a href="/service/edit/<?= $service['id'] ?>" class="btn btn-sm btn-outline-secondary flex-fill">
                                    <i class="fas fa-pen me-1"></i>Edit
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="dash-card text-center py-5">
                <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                <h5>No services yet</h5>
                <p class="text-muted">Create your first service listing to start receiving bookings.</p>
                <a href="/service/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Create Your First Service</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->include('footer') ?>
