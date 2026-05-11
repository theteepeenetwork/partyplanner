<div class="alert alert-danger">
    <h1 class="h4">Permanently remove service?</h1>
    <p>This performs a <strong>hard delete</strong>: the service row, images, pricing rows, favourites, carts, booking line items (empty bookings are removed), and related chat rooms for this service.</p>
    <p><strong><?= esc($service['title']) ?></strong> (#<?= (int) $service['id'] ?>)</p>
    <form method="post" action="<?= site_url('/admin/services/' . $service['id'] . '/delete') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger">Confirm permanent delete</button>
        <a class="btn btn-secondary" href="<?= site_url('/admin/services/' . $service['id']) ?>">Cancel</a>
    </form>
</div>
