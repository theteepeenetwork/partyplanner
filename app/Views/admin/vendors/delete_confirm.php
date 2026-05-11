<div class="alert alert-danger">
    <h1 class="h4">Delete vendor permanently?</h1>
    <p>This removes the vendor, all services (and related data), calendar entries, bookings tied to those services, messages, and vendor profile data.</p>
    <p><strong><?= esc($user['name']) ?></strong> (<?= esc($user['email']) ?>)</p>
    <form method="post" action="<?= site_url('/admin/vendors/' . $user['id'] . '/delete') ?>" class="mt-3">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger">Confirm delete</button>
        <a class="btn btn-secondary" href="<?= site_url('/admin/vendors/' . $user['id']) ?>">Cancel</a>
    </form>
</div>
