<div class="admin-danger-confirm shadow-sm">
    <div class="admin-danger-confirm__header">Delete this vendor permanently?</div>
    <div class="admin-danger-confirm__body">
        <p class="admin-danger-confirm__back mb-0">
            <a href="<?= site_url('/admin/vendors/' . $user['id']) ?>">&larr; Back to vendor #<?= (int) $user['id'] ?></a>
        </p>
        <p class="fw-semibold text-danger mb-3">This cannot be undone. A large amount of related data will be removed.</p>
        <div class="admin-danger-summary">
            <div class="label">Account you are about to remove</div>
            <p class="mb-3"><strong><?= esc($user['name']) ?></strong><br><span class="text-muted"><?= esc($user['email']) ?></span></p>
            <div class="label">What will be deleted</div>
            <ul>
                <li>The vendor user account and profile</li>
                <li>All services and their media, pricing, and calendar data</li>
                <li>Bookings for those services and linked chat rooms</li>
            </ul>
        </div>
        <p class="admin-danger-footnote mb-0">Customers may lose access to conversations and pending work tied to this vendor.</p>
        <form method="post" action="<?= site_url('/admin/vendors/' . $user['id'] . '/delete') ?>" class="border-top pt-3 mt-3">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger">Yes, delete permanently</button>
            <a class="btn btn-outline-secondary ms-2" href="<?= site_url('/admin/vendors/' . $user['id']) ?>">Cancel</a>
        </form>
    </div>
</div>
