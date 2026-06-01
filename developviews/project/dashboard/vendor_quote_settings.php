<?= $this->include('header') ?>
<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">
        <?= $this->include('dashboard/_vendor_tabs') ?>
        <h4>Quote automation</h4>
        <?php if (session()->getFlashdata('success')): ?>
            
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <form method="post" action="/profile/quote-settings" class="dash-card">
            <?= csrf_field() ?>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="auto_accept_enabled" id="auto_accept" value="1"
                    <?= !empty($settings['auto_accept_enabled']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="auto_accept">Enable auto-accept when rules match</label>
            </div>
            <div class="mb-3">
                <label class="form-label">Max auto-accept amount (£)</label>
                <input type="number" step="0.01" class="form-control" name="max_auto_accept_amount"
                    value="<?= esc($settings['max_auto_accept_amount'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Minimum lead time (days)</label>
                <input type="number" class="form-control" name="min_lead_days" value="<?= esc($settings['min_lead_days'] ?? 0) ?>">
            </div>
            <?php
            $allowed = $settings['allowed_event_settings'] ?? '[]';
            if (is_string($allowed)) {
                $allowed = json_decode($allowed, true) ?: [];
            }
            ?>
            <div class="mb-3">
                <label class="form-label">Allowed event formats</label>
                <?php foreach (['private', 'public'] as $fmt): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="allowed_event_settings[]" value="<?= $fmt ?>"
                            <?= in_array($fmt, $allowed, true) ? 'checked' : '' ?>>
                        <label class="form-check-label"><?= ucfirst($fmt) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="require_within_travel_radius" value="1"
                    <?= ($settings['require_within_travel_radius'] ?? 1) ? 'checked' : '' ?>>
                <label class="form-check-label">Require within travel radius (no travel warnings)</label>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="blackout_respect" value="1"
                    <?= ($settings['blackout_respect'] ?? 1) ? 'checked' : '' ?>>
                <label class="form-check-label">Respect blackout dates and date conflicts</label>
            </div>
            <button type="submit" class="btn btn-primary">Save settings</button>
        </form>
        <p class="mt-3"><a href="/profile/quote-analytics">View quote analytics</a></p>
    </div>
</div>
</main>
<?= $this->include('footer') ?>
