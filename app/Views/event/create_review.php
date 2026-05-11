<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container" style="max-width: 700px;">
        <h3 class="mb-2">Create Your Event</h3>
        <p class="text-muted mb-4">Review your event details before creating.</p>

        <?php $currentStep = 4; ?>
        <?= $this->include('event/_progress') ?>

        <div class="dash-card">
            <h5><i class="fas fa-clipboard-check text-primary me-2"></i>Event Summary</h5>

            <div class="table-responsive">
                <table class="table table-borderless">
                    <tr><th class="text-muted" style="width:35%;">Event Name</th><td class="fw-bold"><?= esc($step1['title']) ?></td></tr>
                    <tr><th class="text-muted">Event Type</th><td><?= esc($step1['event_type']) ?></td></tr>
                    <tr><th class="text-muted">Date</th><td><?= date('d F Y', strtotime($step1['date'])) ?></td></tr>
                    <tr><th class="text-muted">Guests</th><td><?= esc($step1['guest_count']) ?> guests</td></tr>

                    <?php if (!empty($step2['venue_name'])): ?>
                        <tr><th class="text-muted">Venue</th><td><?= esc($step2['venue_name']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($step2['town_city'])): ?>
                        <tr><th class="text-muted">Location</th><td><?= esc($step2['town_city']) ?><?= !empty($step2['postcode']) ? ', ' . esc($step2['postcode']) : '' ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($step2['indoor_outdoor'])): ?>
                        <tr><th class="text-muted">Setting</th><td><?= ucfirst(esc($step2['indoor_outdoor'])) ?></td></tr>
                    <?php endif; ?>

                    <?php if (!empty($step3['budget_min']) || !empty($step3['budget_max'])): ?>
                        <tr><th class="text-muted">Budget</th><td>
                            <?= !empty($step3['budget_min']) ? '£' . number_format($step3['budget_min']) : '' ?>
                            <?= (!empty($step3['budget_min']) && !empty($step3['budget_max'])) ? ' – ' : '' ?>
                            <?= !empty($step3['budget_max']) ? '£' . number_format($step3['budget_max']) : '' ?>
                        </td></tr>
                    <?php endif; ?>
                    <?php if (!empty($step3['style_theme'])): ?>
                        <tr><th class="text-muted">Style</th><td><?= esc($step3['style_theme']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($step3['notes'])): ?>
                        <tr><th class="text-muted">Notes</th><td><?= esc($step3['notes']) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>

            <form method="post" action="/event/store">
                <div class="d-flex justify-content-between">
                    <a href="/event/create/step3" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
                    <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-check me-1"></i>Create Event</button>
                </div>
            </form>
        </div>
    </div>
</div>
</main>

<?= $this->include('footer') ?>
