<div class="admin-danger-confirm shadow-sm">
    <div class="admin-danger-confirm__header">Delete this customer permanently?</div>
    <div class="admin-danger-confirm__body">
        <p class="admin-danger-confirm__back mb-0">
            <a href="<?= site_url('/admin/customers/' . $user['id']) ?>">&larr; Back to customer #<?= (int) $user['id'] ?></a>
        </p>
        <p class="fw-semibold text-danger mb-3">This cannot be undone. Data will be removed from the database.</p>
        <div class="admin-danger-summary">
            <div class="label">Account you are about to remove</div>
            <p class="mb-3"><strong><?= esc($user['name']) ?></strong><br><span class="text-muted"><?= esc($user['email']) ?></span></p>
            <div class="label">What will be deleted</div>
            <ul>
                <li>The customer user account</li>
                <li>All events, bookings, and payments tied to this customer</li>
                <li>Messages, favourites, carts, and other related records</li>
            </ul>
        </div>
        <p class="admin-danger-footnote mb-0">After deletion, the customer will not be able to log in and historical reservations will no longer appear in admin lists.</p>
        <form method="post" action="<?= site_url('/admin/customers/' . $user['id'] . '/delete') ?>" class="border-top pt-3 mt-3">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger">Yes, delete permanently</button>
            <a class="btn btn-outline-secondary ms-2" href="<?= site_url('/admin/customers/' . $user['id']) ?>">Cancel</a>
        </form>
    </div>
</div>
