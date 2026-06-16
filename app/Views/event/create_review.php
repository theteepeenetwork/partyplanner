<?= $this->include('header') ?>

<div class="ps-app">
<main data-screen-label="Plan an event">
    <section class="page-head">
        <div class="container">
            <div class="breadcrumb"><a href="/">Home</a><span class="sep">/</span><a href="/event/create/step1">Plan an event</a><span class="sep">/</span><span class="cur">Review</span></div>
            <p class="eyebrow">Start planning</p>
            <h1>Review your event</h1>
            <p class="ph-lead">Check the details below, then create your event to start adding services and gathering quotes.</p>
        </div>
    </section>

    <div class="container">
        <div class="flow-wrap">
            <?php $this->setData(['currentStep' => 4]); ?>
            <?= $this->include('event/_progress') ?>

            <div class="flow-card">
                <h2>Looks good?</h2>
                <p class="flow-sub">Here's your event brief. You can always edit it later.</p>

                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tr><th class="text-muted" style="width:35%;">Event Name</th><td class="fw-bold"><?= esc($step1['title']) ?></td></tr>
                        <tr><th class="text-muted">Event Type</th><td><?= esc($step1['event_type']) ?></td></tr>
                        <tr><th class="text-muted">Date</th><td><?= date('d F Y', strtotime($step1['date'])) ?></td></tr>
                        <tr><th class="text-muted">Guests</th><td><?= esc($step1['guest_count']) ?> guests</td></tr>
                        <tr><th class="text-muted">Format</th><td><?= (($step1['event_setting'] ?? 'private') === 'public') ? 'Public / pitch event' : 'Private event' ?></td></tr>

                        <?php if (!empty($step2['venue_name'])): ?>
                            <tr><th class="text-muted">Venue</th><td><?= esc($step2['venue_name']) ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($step2['town_city'])): ?>
                            <tr><th class="text-muted">Location</th><td><?= esc($step2['town_city']) ?><?= !empty($step2['postcode']) ? ', ' . esc($step2['postcode']) : '' ?></td></tr>
                        <?php endif; ?>
                        <?php if (($step1['event_setting'] ?? 'private') === 'public' && isset($step2['organiser_pitch_fee']) && $step2['organiser_pitch_fee'] !== ''): ?>
                            <tr><th class="text-muted">Pitch / stand fee</th><td>£<?= number_format((float) $step2['organiser_pitch_fee'], 2) ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($step2['indoor_outdoor'])): ?>
                            <tr><th class="text-muted">Setting</th><td><?= ucfirst(esc($step2['indoor_outdoor'])) ?></td></tr>
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
                    <?= csrf_field() ?>
                    <div class="flow-actions">
                        <a href="/event/create/step3" class="btn btn-ghost btn-lg"><i class="fas fa-arrow-left"></i> Back</a>
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-check"></i> Create Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</div>

<?= $this->include('footer') ?>
