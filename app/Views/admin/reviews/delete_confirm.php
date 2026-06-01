<div class="admin-danger-confirm shadow-sm">
    <div class="admin-danger-confirm__header">Permanently remove this review?</div>
    <div class="admin-danger-confirm__body">
        <p class="admin-danger-confirm__back mb-0">
            <a href="<?= site_url('/admin/reviews/' . $review['id']) ?>">&larr; Back to review #<?= (int) $review['id'] ?></a>
        </p>
        <p class="fw-semibold text-danger mb-3">This is a <strong>hard delete</strong>, not a soft-archive. It cannot be undone.</p>
        <div class="admin-danger-summary">
            <div class="label">Review you are about to remove</div>
            <p class="mb-3">
                <strong><?= esc($review['title']) ?></strong>
                <span class="text-muted">(#<?= (int) $review['id'] ?>)</span>
                — <?= (int) $review['rating'] ?>/5 by <?= esc($review['customer_name'] ?? 'a customer') ?>
                on <?= esc($review['service_title'] ?? 'a service') ?>
            </p>
            <div class="label">What will happen</div>
            <ul>
                <li>The review is removed from the service page and the vendor's profile</li>
                <li>The vendor's average rating and review count are recalculated automatically</li>
            </ul>
        </div>
        <form method="post" action="<?= site_url('/admin/reviews/' . $review['id'] . '/delete') ?>" class="border-top pt-3 mt-3 mb-0">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger">Yes, delete permanently</button>
            <a class="btn btn-outline-secondary ms-2" href="<?= site_url('/admin/reviews/' . $review['id']) ?>">Cancel</a>
        </form>
    </div>
</div>
