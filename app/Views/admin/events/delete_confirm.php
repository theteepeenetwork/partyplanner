<div class="alert alert-danger">
    <h1 class="h4">Delete event?</h1>
    <p>Event <strong><?= esc($event['title']) ?></strong> and its basket lines, bookings, payments, and booking items will be removed.</p>
    <form method="post" action="<?= site_url('/admin/events/' . $event['id'] . '/delete') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger">Confirm delete</button>
        <a class="btn btn-secondary" href="<?= site_url('/admin/events/' . $event['id']) ?>">Cancel</a>
    </form>
</div>
