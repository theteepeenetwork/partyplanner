<div class="admin-danger-confirm shadow-sm">
    <div class="admin-danger-confirm__header">Delete this booking?</div>
    <div class="admin-danger-confirm__body">
        <p class="admin-danger-confirm__back mb-0">
            <a href="<?= site_url('/admin/bookings/' . $booking['id']) ?>">&larr; Back to booking #<?= (int) $booking['id'] ?></a>
        </p>
        <p class="fw-semibold text-danger mb-3">This removes the booking and related payment records. This cannot be undone.</p>
        <div class="admin-danger-summary">
            <div class="label">Booking</div>
            <p class="mb-0"><strong>#<?= (int) $booking['id'] ?></strong> — status: <?= esc($booking['status'] ?? '—') ?></p>
            <div class="label mt-3">What will be deleted</div>
            <ul>
                <li>This booking row and its line items</li>
                <li>Associated payment rows for this booking</li>
            </ul>
        </div>
        <p class="admin-danger-footnote mb-0">Use this only if the reservation should disappear from reports and customer history.</p>
        <form method="post" action="<?= site_url('/admin/bookings/' . $booking['id'] . '/delete') ?>" class="border-top pt-3 mt-3 mb-0">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger">Yes, delete booking</button>
            <a class="btn btn-outline-secondary ms-2" href="<?= site_url('/admin/bookings/' . $booking['id']) ?>">Cancel</a>
        </form>
    </div>
</div>
