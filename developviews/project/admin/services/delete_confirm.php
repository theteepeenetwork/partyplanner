<div class="admin-danger-confirm shadow-sm">
    <div class="admin-danger-confirm__header">Permanently remove this service?</div>
    <div class="admin-danger-confirm__body">
        <p class="admin-danger-confirm__back mb-0">
            <a href="<?= site_url('/admin/services/' . $service['id']) ?>">&larr; Back to service #<?= (int) $service['id'] ?></a>
        </p>
        <p class="fw-semibold text-danger mb-3">This is a <strong>hard delete</strong>, not a soft-archive. It cannot be undone.</p>
        <div class="admin-danger-summary">
            <div class="label">Service you are about to remove</div>
            <p class="mb-3"><strong><?= esc($service['title']) ?></strong> <span class="text-muted">(#<?= (int) $service['id'] ?>)</span></p>
            <div class="label">What will be deleted</div>
            <ul>
                <li>The service row, gallery images, and pricing lines</li>
                <li>Favourites, cart lines, and booking line items (empty bookings are removed)</li>
                <li>Chat rooms scoped to this service</li>
            </ul>
        </div>
        <p class="admin-danger-footnote mb-0">Vendor storefronts and customers will no longer see this listing.</p>
        <form method="post" action="<?= site_url('/admin/services/' . $service['id'] . '/delete') ?>" class="border-top pt-3 mt-3 mb-0">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger">Yes, delete permanently</button>
            <a class="btn btn-outline-secondary ms-2" href="<?= site_url('/admin/services/' . $service['id']) ?>">Cancel</a>
        </form>
    </div>
</div>
