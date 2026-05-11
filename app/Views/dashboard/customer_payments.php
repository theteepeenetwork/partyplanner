<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">
        <?= $this->include('dashboard/_customer_tabs') ?>

        <h4 class="mb-4">Payments</h4>

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

        <!-- Payment History -->
        <div class="dash-card">
            <h5 class="mb-3"><i class="fas fa-history text-info me-2"></i>Payment History</h5>

            <?php if (!empty($payments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Vendor / Service</th>
                                <th>Event</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td class="small"><?= date('d M Y', strtotime($payment['created_at'])) ?></td>
                                    <td>
                                        <div class="fw-bold small"><?= esc($payment['vendor_name'] ?? '') ?></div>
                                        <div class="text-muted small"><?= esc($payment['service_name'] ?? '') ?></div>
                                    </td>
                                    <td class="small"><?= esc($payment['event_name'] ?? '') ?></td>
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
                <div class="text-center py-4">
                    <i class="fas fa-credit-card fa-2x text-muted mb-2"></i>
                    <p class="text-muted">No payment history yet. Payments will appear here after you book services.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>

<?= $this->include('footer') ?>
