<?= $this->include('header') ?>
<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">
        <?= $this->include('dashboard/_vendor_tabs') ?>
        <h4>Adjust quote — <?= esc($item['event_title'] ?? '') ?></h4>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?>
        <?php endif; ?>
        <?php if ($original): ?>
            <div class="dash-card mb-3">
                <h6>Original automated estimate</h6>
                <?= view('partials/quote_breakdown', ['quoteDetail' => $original, 'collapseId' => 900]) ?>
            </div>
        <?php endif; ?>
        <form method="post" class="dash-card">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Revised line items (JSON)</label>
                <textarea class="form-control font-monospace" name="lines_json" rows="8"><?= esc($draft['lines'] ?? ($original['lines'] ?? '[]')) ?></textarea>
                <div class="form-text">Array of objects: {"label":"...", "amount": 123.45}</div>
            </div>
            
            
            <div class="mb-3">
                <label class="form-label">Notes to customer</label>
                <textarea class="form-control" name="vendor_notes" rows="3"><?= esc($draft['vendor_notes'] ?? '') ?></textarea>
            </div>
            <?php if (!empty($templates)): ?>
                <p class="small text-muted">Templates: <?php foreach ($templates as $t): ?><?= esc($t['name']) ?>; <?php endforeach; ?></p>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Save draft</button>
            <?php if ($draft): ?>
                <form method="post" action="/profile/vendor-quote/<?= (int) $item['id'] ?>/send" class="d-inline ms-2" onsubmit="return confirm('Send revised quote to customer?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success">Send to customer</button>
                </form>
            <?php endif; ?>
            <a href="/profile/bookings" class="btn btn-outline-secondary ms-2">Back</a>
        </form>
    </div>
</div>
</main>
<?= $this->include('footer') ?>
