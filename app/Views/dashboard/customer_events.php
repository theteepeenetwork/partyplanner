<?= $this->include('header') ?>
<main class="page-main">
<div class="dashboard-wrapper">
<div class="container">
    <?= $this->include('dashboard/_customer_tabs') ?>
    <?= $this->include('dashboard/_flash_alerts') ?>
    <div class="fye-page">
        <div class="fye-page-head">
            <div>
                <h1 class="fye-page-title">My events</h1>
                <p class="fye-page-sub">Every celebration you're planning. Open one to see its suppliers, budget and gaps.</p>
            </div>
            <a href="/event/create" class="fye-btn primary"><i class="fa-solid fa-plus"></i> New event</a>
        </div>

        <?php if (!empty($events)): ?>
            <div class="fye-gal">
                <?php foreach ($events as $event):
                    $booked = (int) ($event['servicesBooked'] ?? 0);
                    $max    = 8;
                    $pct    = $max > 0 ? min(100, round($booked / $max * 100)) : 0;
                    $days   = $event['days'] ?? null;
                ?>
                    <a href="/profile/events/<?= (int) $event['id'] ?>" class="gcard">
                        <div class="gc-ph"><?= esc($event['location'] ?? 'venue') ?></div>
                        <div class="gc-body">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
                                <span class="fye-pill accepted"><?= esc($event['event_type'] ?? 'Event') ?></span>
                                <?php if ($days !== null): ?>
                                    <span class="fye-faint fye-num" style="font-size:12px;font-weight:700"><?= $days ?> days</span>
                                <?php endif; ?>
                            </div>
                            <div class="gn" style="margin-top:9px"><?= esc($event['title']) ?></div>
                            <div class="gc">
                                <?php if (!empty($event['date'])): ?>
                                    <i class="fa-solid fa-calendar-day" style="color:var(--fye-terra);margin-right:6px"></i>
                                    <?= date('d M Y', strtotime($event['date'])) ?>
                                <?php endif; ?>
                            </div>
                            <div class="ev-prog" style="margin-top:12px">
                                <div class="lbl">
                                    <span><?= $booked ?> of <?= $max ?> booked</span>
                                    <?php if (!empty($event['totalCost']) && $event['totalCost'] > 0): ?>
                                        <span class="fye-num">£<?= number_format($event['totalCost']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="bar"><div class="fill" style="width:<?= $pct ?>%"></div></div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="icard text-center py-5">
                <i class="fa-solid fa-calendar-plus fa-3x mb-3 d-block fye-faint"></i>
                <h5 style="font-family:var(--fye-display)">No events yet</h5>
                <p class="fye-muted mb-4" style="font-size:13.5px">Set up your first event to save the basics, then browse and shortlist services.</p>
                <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
                    <a href="/event/create" class="fye-btn primary"><i class="fa-solid fa-plus"></i> Create your first event</a>
                    <a href="/browse-services" class="fye-btn ghost">Browse services</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>
</main>
<?= $this->include('footer') ?>
