<?= $this->include('header') ?>

<div class="dashboard-wrapper">
    <div class="container" style="max-width: 700px;">
        <div class="text-center py-5">
            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
            <h3>Booking Request Submitted!</h3>
            <p class="text-muted">Your deposit has been paid and booking requests have been sent to the vendors.</p>
        </div>

        <div class="dash-card mb-3">
            <h5>Booking Summary</h5>
            <p class="text-muted small mb-3">
                Event: <strong><?= esc($event['title'] ?? '') ?></strong>
                <?php if (!empty($event['date'])): ?> — <?= date('d M Y', strtotime($event['date'])) ?><?php endif; ?>
            </p>

            <?php foreach ($items as $item): ?>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <span class="fw-bold"><?= esc($item['service_title']) ?></span>
                        <div class="text-muted small"><?= esc($item['vendor_name']) ?></div>
                    </div>
                    <span class="badge bg-warning text-dark">Pending Vendor Approval</span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="dash-card bg-light mb-3">
            <h6>What happens next?</h6>
            <ol class="small text-muted mb-0">
                <li class="mb-2">Each vendor will review your booking request</li>
                <li class="mb-2">Vendors typically respond within 24-48 hours</li>
                <li class="mb-2">You'll receive a notification when they accept or decline</li>
                <li>Once accepted, your booking is confirmed for the event date</li>
            </ol>
        </div>

        <div class="d-flex justify-content-center gap-3">
            <a href="/profile/my-bookings" class="btn btn-primary">View My Bookings</a>
            <a href="/browse-services" class="btn btn-outline-primary">Browse More Services</a>
        </div>
    </div>
</div>

<?= $this->include('footer') ?>
