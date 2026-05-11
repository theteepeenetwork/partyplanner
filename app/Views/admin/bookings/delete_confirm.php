<div class="alert alert-danger">
    <h1 class="h4">Delete this booking?</h1>
    <p>Booking #<?= (int) $booking['id'] ?> — status <?= esc($booking['status'] ?? '') ?>. Payments and line items will be removed.</p>
    <form method="post" action="<?= site_url('/admin/bookings/' . $booking['id'] . '/delete') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger">Confirm delete</button>
        <a class="btn btn-secondary" href="<?= site_url('/admin/bookings/' . $booking['id']) ?>">Cancel</a>
    </form>
</div>
