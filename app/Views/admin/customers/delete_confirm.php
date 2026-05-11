<div class="alert alert-danger">
    <h1 class="h4">Delete customer permanently?</h1>
    <p>This removes the customer account and all related events, bookings, messages, favourites, carts, and payments. This cannot be undone.</p>
    <p><strong><?= esc($user['name']) ?></strong> (<?= esc($user['email']) ?>)</p>
    <form method="post" action="<?= site_url('/admin/customers/' . $user['id'] . '/delete') ?>" class="mt-3">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger">Confirm delete</button>
        <a class="btn btn-secondary" href="<?= site_url('/admin/customers/' . $user['id']) ?>">Cancel</a>
    </form>
</div>
