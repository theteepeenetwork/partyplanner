<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">
        <?= $this->include('dashboard/_customer_tabs') ?>

        <h4 class="mb-4">My Bookings</h4>

        <?php if (!empty($bookingItems)): ?>
            <?php
            $pending = array_filter($bookingItems, fn($b) => $b['status'] === 'pending');
            $accepted = array_filter($bookingItems, fn($b) => $b['status'] === 'accepted');
            $confirmed = array_filter($bookingItems, fn($b) => $b['status'] === 'confirmed');
            $declined = array_filter($bookingItems, fn($b) => in_array($b['status'], ['rejected', 'cancelled']));
            ?>

            <div class="d-flex gap-2 mb-4 flex-wrap">
                <span class="badge bg-warning text-dark p-2"><?= count($pending) ?> Pending</span>
                <span class="badge bg-success p-2"><?= count($accepted) ?> Accepted</span>
                <span class="badge bg-primary p-2"><?= count($confirmed) ?> Confirmed</span>
                <span class="badge bg-danger p-2"><?= count($declined) ?> Declined</span>
            </div>

            <?php foreach ($bookingItems as $item): ?>
                <div class="dash-card mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2 flex-wrap gap-1">
                                <?php
                                $statusClass = match($item['status']) {
                                    'pending' => 'bg-warning text-dark',
                                    'accepted' => 'bg-success',
                                    'confirmed' => 'bg-primary',
                                    'rejected', 'cancelled' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst($item['status']) ?></span>
                                <?php if ($item['payment_status'] === 'succeeded'): ?>
                                    <span class="badge bg-info">Deposit Paid (£<?= number_format($item['amount_paid'], 2) ?>)</span>
                                <?php elseif ($item['payment_status'] !== 'unpaid'): ?>
                                    <span class="badge bg-light text-dark"><?= ucfirst($item['payment_status']) ?></span>
                                <?php endif; ?>
                            </div>

                            <h6 class="mb-1"><?= esc($item['service_title']) ?></h6>
                            <div class="text-muted small">
                                <i class="fas fa-store me-1"></i><?= esc($item['vendor_name']) ?>
                            </div>
                            <div class="text-muted small mt-1">
                                <i class="fas fa-calendar me-1"></i><?= esc($item['event_title'] ?? '') ?>
                                <?php if (!empty($item['event_date'])): ?>
                                    — <?= date('d M Y', strtotime($item['event_date'])) ?>
                                <?php endif; ?>
                                <?php if (!empty($item['location'])): ?>
                                    <span class="ms-2"><i class="fas fa-map-marker-alt me-1"></i><?= esc($item['location']) ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($item['price']) || !empty($item['service_price'])): ?>
                                <div class="mt-2">
                                    <strong>Total:</strong> £<?= number_format($item['price'] ?? $item['service_price'] ?? 0, 2) ?>
                                    <?php if ($item['outstanding'] > 0): ?>
                                        <span class="text-danger ms-2">Outstanding: £<?= number_format($item['outstanding'], 2) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <a href="/service/view/<?= $item['service_id'] ?>" class="btn btn-sm btn-outline-primary me-1">View Service</a>
                            <!-- TODO: Link to message thread with this vendor -->
                            <a href="/profile/messages" class="btn btn-sm btn-outline-secondary">Message Vendor</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="dash-card text-center py-5">
                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                <h5>No bookings yet</h5>
                <p class="text-muted">Browse services and add them to your event to create bookings.</p>
                <a href="/browse-services" class="btn btn-primary">Browse Services</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</main>

<?= $this->include('footer') ?>
