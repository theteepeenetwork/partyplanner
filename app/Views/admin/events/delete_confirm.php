<div class="admin-danger-confirm shadow-sm">
    <div class="admin-danger-confirm__header">Delete this event?</div>
    <div class="admin-danger-confirm__body">
        <p class="admin-danger-confirm__back mb-0">
            <a href="<?= site_url('/admin/events/' . $event['id']) ?>">&larr; Back to event #<?= (int) $event['id'] ?></a>
        </p>
        <p class="fw-semibold text-danger mb-3">This removes the event and related planning and payment data. This cannot be undone.</p>
        <div class="admin-danger-summary">
            <div class="label">Event you are about to remove</div>
            <p class="mb-3"><strong><?= esc($event['title']) ?></strong></p>
            <div class="label">What will be deleted</div>
            <ul>
                <li>The event record and basket lines</li>
                <li>Bookings, payments, and booking items linked to this event</li>
            </ul>
        </div>
        <p class="admin-danger-footnote mb-0">The customer’s party page and reservation history for this date will be cleared.</p>
        <form method="post" action="<?= site_url('/admin/events/' . $event['id'] . '/delete') ?>" class="border-top pt-3 mt-3 mb-0">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger">Yes, delete event</button>
            <a class="btn btn-outline-secondary ms-2" href="<?= site_url('/admin/events/' . $event['id']) ?>">Cancel</a>
        </form>
    </div>
</div>
