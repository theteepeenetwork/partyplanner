<?= $this->include('header') ?>
<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">
        <?= $this->include('dashboard/_vendor_tabs') ?>
        <h4>Quote analytics (30 days)</h4>
        <p><a href="/profile/quote-settings">Automation settings</a></p>
        <?php if (empty($metrics)): ?>
            <p class="text-muted">No analytics recorded yet. Quotes will appear after customers request bookings.</p>
        <?php else: ?>
            <table class="table table-sm dash-card">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Generated</th>
                        <th>Accepted</th>
                        <th>Auto-accepted</th>
                        <th>Avg total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($metrics as $m): ?>
                        <tr>
                            <td><?= esc($m['metric_date']) ?></td>
                            <td><?= (int) ($m['quotes_generated'] ?? 0) ?></td>
                            <td><?= (int) ($m['quotes_accepted'] ?? 0) ?></td>
                            <td><?= (int) ($m['auto_accepted'] ?? 0) ?></td>
                            <td><?= $m['avg_total'] !== null ? '£' . number_format((float) $m['avg_total'], 2) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</main>
<?= $this->include('footer') ?>
