<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Service #<?= (int) $service['id'] ?></h1>
    <div class="btn-group">
        <a class="btn btn-outline-secondary" href="<?= site_url('/admin/services') ?>">Back</a>
        <a class="btn btn-outline-primary" href="<?= site_url('/admin/services/' . $service['id'] . '/edit') ?>">Edit</a>
        <form method="post" action="<?= site_url('/admin/services/' . $service['id'] . '/toggle') ?>" class="d-inline">
            <?= csrf_field() ?>
            <button class="btn btn-outline-warning" type="submit">Toggle active</button>
        </form>
        <a class="btn btn-outline-danger" href="<?= site_url('/admin/services/' . $service['id'] . '/delete') ?>">Remove</a>
    </div>
</div>
<div class="row g-3">
    <div class="col-md-8">
        <div class="card shadow-sm"><div class="card-body">
            <p class="mb-1"><strong>Vendor:</strong> <?php if ($vendor): ?><a href="<?= site_url('/admin/vendors/' . $vendor['id']) ?>"><?= esc($vendor['name']) ?></a><?php else: ?>—<?php endif; ?></p>
            <p class="mb-1"><strong>Status:</strong> <?= esc($service['status'] ?? '') ?> <?php if (!empty($service['deleted_at'])): ?><span class="badge bg-secondary">soft-deleted <?= esc($service['deleted_at']) ?></span><?php endif; ?></p>
            <p class="mb-1"><strong>Price:</strong> <?= esc((string) ($service['price'] ?? '')) ?></p>
            <h2 class="h6 mt-3">Description</h2>
            <div><?= nl2br(esc($service['description'] ?? '')) ?></div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6">Images</h2>
            <?php foreach ($service['images'] ?? [] as $img): ?>
                <?php if (!empty($img['image_path'])): ?>
                    <img src="<?= esc(base_url($img['image_path'])) ?>" alt="" class="img-fluid mb-2 rounded border">
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if (empty($service['images'])): ?><p class="text-muted small mb-0">No gallery images</p><?php endif; ?>
        </div></div>
    </div>
</div>
