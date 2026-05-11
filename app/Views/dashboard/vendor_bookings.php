<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">
        <?= $this->include('dashboard/_vendor_tabs') ?>

        <h4 class="mb-4">Bookings</h4>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (!empty($bookingItems)): ?>
            <?php
            $pending = array_filter($bookingItems, fn($b) => $b['status'] === 'pending');
            $accepted = array_filter($bookingItems, fn($b) => in_array($b['status'], ['accepted', 'confirmed']));
            $declined = array_filter($bookingItems, fn($b) => in_array($b['status'], ['rejected', 'cancelled']));
            ?>

            <!-- Summary badges -->
            <div class="d-flex gap-2 mb-4 flex-wrap">
                <span class="badge bg-warning text-dark p-2"><?= count($pending) ?> Pending</span>
                <span class="badge bg-success p-2"><?= count($accepted) ?> Accepted</span>
                <span class="badge bg-danger p-2"><?= count($declined) ?> Declined</span>
            </div>

            <!-- Pending Bookings -->
            <?php if (!empty($pending)): ?>
                <h5 class="mb-3"><i class="fas fa-clock text-warning me-2"></i>Pending Approval</h5>
                <?php foreach ($pending as $item): ?>
                    <div class="dash-card mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-warning text-dark me-2">Pending</span>
                                    <?php if (!empty($item['payment_status']) && $item['payment_status'] === 'succeeded'): ?>
                                        <span class="badge bg-info">Deposit Paid</span>
                                    <?php endif; ?>
                                </div>
                                <h6 class="mb-1"><?= esc($item['event_title'] ?? 'Event') ?></h6>
                                <div class="text-muted small">
                                    <i class="fas fa-user me-1"></i><?= esc($item['customer_name'] ?? '') ?>
                                    <span class="ms-2"><i class="fas fa-tag me-1"></i><?= esc($item['event_type'] ?? '') ?></span>
                                    <span class="ms-2"><i class="fas fa-calendar me-1"></i><?= !empty($item['event_date']) ? date('d M Y', strtotime($item['event_date'])) : '' ?></span>
                                    <span class="ms-2"><i class="fas fa-map-marker-alt me-1"></i><?= esc($item['location'] ?? '') ?></span>
                                </div>
                                <div class="mt-2 small">
                                    <strong>Service:</strong> <?= esc($item['service_title'] ?? '') ?>
                                    <?php if (!empty($item['package_name'])): ?>
                                        <span class="ms-2"><strong>Package:</strong> <?= esc($item['package_name']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($item['guest_count'])): ?>
                                        <span class="ms-2"><strong>Guests:</strong> <?= esc($item['guest_count']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($item['price'])): ?>
                                        <span class="ms-2"><strong>Price:</strong> £<?= number_format($item['price'], 2) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-muted small mt-1">
                                    <i class="fas fa-clock me-1"></i>Requested: <?= !empty($item['request_date']) ? date('d M Y H:i', strtotime($item['request_date'])) : '' ?>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <a href="<?= base_url('profile/messages/by-booking/' . (int) $item['id']) ?>" class="btn btn-sm btn-outline-primary mb-2 d-block d-md-inline-block">Message customer</a>
                                <form method="POST" action="/profile/update-booking-status/<?= $item['id'] ?>" class="d-inline">
                                    <input type="hidden" name="status" value="accepted">
                                    <button type="submit" class="btn btn-sm btn-success me-1"><i class="fas fa-check me-1"></i>Accept</button>
                                </form>
                                <form method="POST" action="/profile/update-booking-status/<?= $item['id'] ?>" class="d-inline">
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times me-1"></i>Decline</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Accepted Bookings -->
            <?php if (!empty($accepted)): ?>
                <h5 class="mb-3 mt-4"><i class="fas fa-check-circle text-success me-2"></i>Accepted / Confirmed</h5>
                <?php foreach ($accepted as $item): ?>
                    <div class="dash-card mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-success me-2"><?= ucfirst($item['status']) ?></span>
                                    <?php if (!empty($item['payment_status']) && $item['payment_status'] === 'succeeded'): ?>
                                        <span class="badge bg-info">Deposit Paid (£<?= number_format($item['amount_paid'] ?? 0, 2) ?>)</span>
                                    <?php endif; ?>
                                </div>
                                <h6 class="mb-1"><?= esc($item['event_title'] ?? '') ?></h6>
                                <div class="text-muted small">
                                    <i class="fas fa-user me-1"></i><?= esc($item['customer_name'] ?? '') ?>
                                    <span class="ms-2"><i class="fas fa-calendar me-1"></i><?= !empty($item['event_date']) ? date('d M Y', strtotime($item['event_date'])) : '' ?></span>
                                    <span class="ms-2"><i class="fas fa-map-marker-alt me-1"></i><?= esc($item['location'] ?? '') ?></span>
                                </div>
                                <div class="mt-1 small">
                                    <strong>Service:</strong> <?= esc($item['service_title'] ?? '') ?>
                                    <?php if (!empty($item['price'])): ?> — £<?= number_format($item['price'], 2) ?><?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-3 text-md-end mt-2 mt-md-0">
                                <a href="<?= base_url('profile/messages/by-booking/' . (int) $item['id']) ?>" class="btn btn-sm btn-outline-primary mb-2">Message customer</a><br>
                                <span class="badge bg-light text-dark"><?= !empty($item['event_date']) ? date('d M', strtotime($item['event_date'])) : '' ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Declined Bookings -->
            <?php if (!empty($declined)): ?>
                <h5 class="mb-3 mt-4"><i class="fas fa-times-circle text-danger me-2"></i>Declined / Cancelled</h5>
                <?php foreach ($declined as $item): ?>
                    <div class="dash-card mb-3" style="opacity:0.6;">
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge bg-danger me-2"><?= ucfirst($item['status']) ?></span>
                        </div>
                        <h6 class="mb-1"><?= esc($item['event_title'] ?? '') ?></h6>
                        <div class="text-muted small">
                            <i class="fas fa-user me-1"></i><?= esc($item['customer_name'] ?? '') ?>
                            — <?= esc($item['service_title'] ?? '') ?>
                            — <?= !empty($item['event_date']) ? date('d M Y', strtotime($item['event_date'])) : '' ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php else: ?>
            <div class="dash-card text-center py-5">
                <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                <h5>No bookings yet</h5>
                <p class="text-muted">Booking requests from customers will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
</main>

<?= $this->include('footer') ?>
