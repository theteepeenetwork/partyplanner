<div class="admin-toolbar d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="admin-page-title mb-0">Review #<?= (int) $review['id'] ?></h1>
    <div class="btn-group">
        <a class="btn btn-outline-secondary" href="<?= site_url('/admin/reviews') ?>">Back</a>
        <a class="btn btn-outline-primary" href="<?= site_url('/admin/reviews/' . $review['id'] . '/edit') ?>">Edit</a>
        <a class="btn btn-outline-danger" href="<?= site_url('/admin/reviews/' . $review['id'] . '/delete') ?>">Remove</a>
    </div>
</div>
<div class="card shadow-sm"><div class="card-body">
    <p class="mb-1">
        <strong>Rating:</strong>
        <?= str_repeat('★', (int) $review['rating']) ?><span class="text-muted"><?= str_repeat('☆', 5 - (int) $review['rating']) ?></span>
        (<?= (int) $review['rating'] ?>/5)
    </p>
    <p class="mb-1"><strong>Customer:</strong> <?= esc($review['customer_name'] ?? '—') ?></p>
    <p class="mb-1"><strong>Service:</strong> <?= esc($review['service_title'] ?? '—') ?> <span class="text-muted">(#<?= (int) $review['service_id'] ?>)</span></p>
    <p class="mb-1"><strong>Vendor:</strong> <?= esc($review['vendor_name'] ?? '—') ?> <span class="text-muted">(#<?= (int) $review['vendor_id'] ?>)</span></p>
    <p class="mb-1"><strong>Submitted:</strong> <?= esc($review['created_at'] ?? '') ?></p>
    <?php if (!empty($review['flagged'])): ?>
        <p class="mb-1"><span class="badge bg-warning text-dark">Flagged — contained masked language</span></p>
    <?php endif; ?>
    <h2 class="h6 mt-3"><?= esc($review['title']) ?></h2>
    <div><?= nl2br(esc($review['comment'])) ?></div>
</div></div>
