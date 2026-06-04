<?= $this->include('header') ?>
<main class="page-main">
<div class="dashboard-wrapper">
<div class="container">
    <?= $this->include('dashboard/_customer_tabs') ?>
    <?= $this->include('dashboard/_flash_alerts') ?>
    <div class="fye-page">
        <h1 class="fye-page-title">Payments</h1>
        <p class="fye-page-sub" style="margin-bottom:22px">Deposits, balances and receipts across all your events.</p>

        <div class="fye-minis">
            <div class="mini-stat"><div class="v fye-num" style="color:var(--fye-sage)">£<?= number_format((float) ($totalPaid ?? 0), 2) ?></div><div class="l">Deposits paid</div></div>
            <div class="mini-stat"><div class="v fye-num" style="color:var(--fye-gold)">£<?= number_format((float) ($totalOutstanding ?? 0), 2) ?></div><div class="l">Due / outstanding</div></div>
            <div class="mini-stat"><div class="v fye-num">£<?= number_format(max(0, (float)($totalOutstanding ?? 0)), 2) ?></div><div class="l">Remaining balance</div></div>
            <div class="mini-stat"><div class="v fye-num">£<?= number_format((float)($totalPaid ?? 0) + (float)($totalOutstanding ?? 0), 2) ?></div><div class="l">Total event spend</div></div>
        </div>

        <?php if (!empty($payments)): ?>
            <div class="icard">
                <table class="ptable">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Event</th>
                            <th>Status</th>
                            <th class="r">Amount paid</th>
                            <th class="r">Outstanding</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $p):
                            $isPaid = ($p['payment_status'] ?? '') === 'succeeded';
                        ?>
                            <tr>
                                <td>
                                    <div class="vendor-name"><?= esc($p['vendor_name'] ?? '—') ?></div>
                                    <div class="fye-muted" style="font-weight:400;font-size:12px"><?= esc($p['service_name'] ?? '') ?></div>
                                </td>
                                <td class="fye-muted"><?= esc($p['event_name'] ?? '—') ?></td>
                                <td><?= $isPaid ? '<span class="fye-pill confirmed">Paid</span>' : '<span class="fye-pill pending">Pending</span>' ?></td>
                                <td class="r fye-num">£<?= number_format((float) ($p['amount_paid'] ?? 0), 2) ?></td>
                                <td class="r fye-num fye-muted">—</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="icard text-center py-5">
                <i class="fa-solid fa-receipt fa-3x mb-3 d-block fye-faint"></i>
                <p class="fye-muted" style="font-size:13.5px">No payment records yet. Payments appear here once you've paid a deposit.</p>
                <a href="/profile/my-bookings" class="fye-btn primary" style="margin-top:12px">View bookings</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>
</main>
<?= $this->include('footer') ?>
