<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">
        <?= $this->include('dashboard/_customer_tabs') ?>
        <div class="mb-4">
            <h4 class="mb-2">Payments</h4>
            <p class="dash-page-lead mb-0">A transparent record of deposits and balances. Figures update when payments succeed; confirm final amounts with your vendors before the big day.</p>
        </div>

        <!-- Payment Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-success-light mx-auto"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-value">£<?= number_format($totalPaid, 2) ?></div>
                    <div class="stat-label">Total Paid</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-light mx-auto"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="stat-value">£<?= number_format($totalOutstanding, 2) ?></div>
                    <div class="stat-label">Outstanding Balance</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-light mx-auto"><i class="fas fa-receipt"></i></div>

                    <div class="stat-value">£<?= number_format($totalPaid + $totalOutstanding, 2) ?></div>
                    <div class="stat-label">Total Event Spend</div>
                </div>
            </div>
        </div>

        <!-- Payment History by Event -->
        <div class="dash-card">
            <h5 class="mb-1"><i class="fas fa-history text-info me-2"></i>Payment History</h5>
            <p class="text-muted small mb-3">Payments and balances are grouped by event. Each event is checked out separately.</p>

            <?php if (!empty($paymentsByEvent)): ?>
                <?php foreach ($paymentsByEvent as $eventId => $eventGroup): ?>
                    <section class="border rounded p-3 mb-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                            <div>
                                <h6 class="mb-1"><?= esc($eventGroup['event_title'] ?? 'Event') ?></h6>
                                <?php if (!empty($eventGroup['event_date'])): ?>
                                    <p class="text-muted small mb-0"><?= date('d M Y', strtotime($eventGroup['event_date'])) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="badge bg-success">Paid £<?= number_format($eventGroup['paid'] ?? 0, 2) ?></span>
                                <?php if (($eventGroup['outstanding'] ?? 0) > 0): ?>
                                    <span class="badge bg-warning text-dark">Outstanding £<?= number_format($eventGroup['outstanding'], 2) ?></span>
                                <?php endif; ?>
                                <?php if ($eventId > 0): ?>
                                    <a href="/profile/my-bookings?event_id=<?= (int) $eventId ?>" class="btn btn-sm btn-outline-secondary">Event bookings</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($eventGroup['payments'])): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Vendor / Service</th>
                                            <th>Amount</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($eventGroup['payments'] as $payment): ?>
                                            <tr>
                                                <td class="small"><?= date('d M Y', strtotime($payment['created_at'])) ?></td>
                                                <td>
                                                    <div class="fw-bold small"><?= esc($payment['vendor_name'] ?? '') ?></div>
                                                    <div class="text-muted small"><?= esc($payment['service_name'] ?? '') ?></div>
                                                </td>
                                                <td class="fw-bold">£<?= number_format($payment['amount_paid'] ?? 0, 2) ?></td>
                                                <td>
                                                    <?php
                                                    $typeClass = match($payment['payment_type'] ?? 'deposit') {
                                                        'deposit' => 'bg-info',
                                                        'balance' => 'bg-primary',
                                                        'refund' => 'bg-warning text-dark',
                                                        default => 'bg-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge <?= $typeClass ?>"><?= ucfirst($payment['payment_type'] ?? 'Deposit') ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = match($payment['payment_status'] ?? '') {
                                                        'succeeded' => 'bg-success',
                                                        'pending' => 'bg-warning text-dark',
                                                        'failed' => 'bg-danger',
                                                        default => 'bg-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>"><?= ucfirst($payment['payment_status'] ?? 'Pending') ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mb-0">No completed payments for this event yet.</p>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5 px-3">
                    <div class="dash-empty-state">
                        <i class="fas fa-receipt fa-3x text-muted mb-3 d-block" aria-hidden="true"></i>
                        <h5 class="fw-semibold">No payments recorded yet</h5>
                        <p class="text-muted mb-4">Once you book a service and pay a deposit or balance, it will be listed here for easy reference.</p>
                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                            <a href="/browse-services" class="btn btn-primary">Browse services</a>
                            <a href="/profile/my-bookings" class="btn btn-outline-secondary">View my bookings</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>

<?= $this->include('footer') ?>
