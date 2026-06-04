<?= $this->include('header') ?>
<main class="page-main">
<div class="dashboard-wrapper">
<div class="container">
    <?= $this->include('dashboard/_customer_tabs') ?>
    <?= $this->include('dashboard/_flash_alerts') ?>
    <div class="fye-page">
        <a href="/profile/events" class="fye-back"><i class="fa-solid fa-arrow-left"></i> All events</a>

        <!-- Hero band -->
        <div class="hero-band">
            <div class="ph-strip"></div>
            <div class="hb-body">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap">
                    <div>
                        <span class="fye-pill accepted"><?= esc($event['event_type'] ?? 'Event') ?></span>
                        <h1 style="margin-top:10px"><?= esc($event['title']) ?></h1>
                        <div class="hb-meta">
                            <?php if (!empty($event['date'])): ?>
                                <span><i class="fa-solid fa-calendar-day"></i><?= date('d M Y', strtotime($event['date'])) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($event['location'])): ?>
                                <span><i class="fa-solid fa-location-dot"></i><?= esc($event['location']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($event['guest_count'])): ?>
                                <span><i class="fa-solid fa-user-group"></i><?= (int)$event['guest_count'] ?> guests</span>
                            <?php endif; ?>
                            <?php if (isset($event['days']) && $event['days'] !== null): ?>
                                <span><i class="fa-solid fa-hourglass-half"></i><?= (int)$event['days'] ?> days to go</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="/browse-services?event_id=<?= (int)$event['id'] ?>" class="fye-btn primary">
                        <i class="fa-solid fa-plus"></i> Add a service
                    </a>
                </div>
            </div>
        </div>

        <div class="fye-detail">
            <!-- Left column -->
            <div class="col">
                <!-- Booked suppliers -->
                <div class="icard">
                    <h3><i class="fa-solid fa-handshake"></i> Booked suppliers</h3>
                    <div class="csub"><?= count($liveBookings) ?> of <?= count($planningCategories) ?> categories covered for this event.</div>
                    <?php if (!empty($liveBookings)): ?>
                        <div style="margin-top:14px">
                            <?php foreach ($liveBookings as $b):
                                $statusClass = match ($b['status'] ?? '') {
                                    'confirmed' => 'confirmed',
                                    'accepted'  => 'accepted',
                                    'pending'   => 'pending',
                                    default     => 'declined',
                                };
                            ?>
                                <a href="/profile/my-bookings/<?= (int)$b['id'] ?>" class="srow clickable">
                                    <div class="si"><i class="fa-solid fa-concierge-bell"></i></div>
                                    <div>
                                        <div class="sn"><?= esc($b['vendor_name'] ?? '—') ?></div>
                                        <div class="sc"><?= esc($b['service_title'] ?? '—') ?></div>
                                    </div>
                                    <div class="right">
                                        <span class="fye-pill <?= $statusClass ?>"><?= ucfirst($b['status'] ?? '') ?></span>
                                        <div class="sc fye-num" style="margin-top:4px">£<?= number_format((float)($b['price'] ?? 0), 2) ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="fye-muted" style="font-size:13.5px;margin-top:12px">No suppliers booked yet.</div>
                    <?php endif; ?>
                </div>

                <!-- Still to arrange -->
                <div class="icard">
                    <h3><i class="fa-solid fa-list-check"></i> Still to arrange</h3>
                    <div class="csub">Categories without a confirmed supplier yet.</div>
                    <div style="display:flex;flex-wrap:wrap;gap:9px;margin-top:14px">
                        <?php
                        $bookedCats = array_map(fn($b) => $b['category_name'] ?? '', $liveBookings);
                        foreach ($planningCategories as $cat):
                            $hasIt = in_array($cat, $bookedCats, true);
                        ?>
                            <span class="fye-pill <?= $hasIt ? 'confirmed' : 'action' ?>" style="font-size:12.5px;padding:6px 12px">
                                <i class="fa-solid <?= $hasIt ? 'fa-check' : 'fa-plus' ?>" style="margin-right:2px"></i><?= esc($cat) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right column -->
            <div class="col">
                <!-- Budget -->
                <div class="icard budget">
                    <h3><i class="fa-solid fa-wallet"></i> Budget</h3>
                    <?php
                    $committed  = array_sum(array_map(fn($b) => (float)($b['price'] ?? 0), $liveBookings));
                    $paid       = array_sum(array_map(fn($b) => (float)($b['amount_paid'] ?? 0), $liveBookings));
                    $budget     = (float)($event['budget'] ?? 0);
                    $paidPct    = $budget > 0 ? min(100, $paid / $budget * 100) : 0;
                    $duePct     = $budget > 0 ? min(100, max(0, ($committed - $paid) / $budget * 100)) : 0;
                    ?>
                    <div style="display:flex;align-items:baseline;gap:8px;margin:12px 0 14px">
                        <span class="fye-num" style="font-family:var(--fye-display);font-size:32px;font-weight:600;color:var(--fye-terra-deep)">£<?= number_format($committed) ?></span>
                        <span class="fye-muted" style="font-size:13px">committed<?= $budget > 0 ? ' of £' . number_format($budget) : '' ?></span>
                    </div>
                    <?php if ($budget > 0): ?>
                        <div class="track">
                            <div class="seg" style="width:<?= $paidPct ?>%;background:var(--fye-sage)"></div>
                            <div class="seg" style="width:<?= $duePct ?>%;background:var(--fye-gold)"></div>
                        </div>
                        <div class="legend">
                            <span><i class="fa-solid fa-circle" style="color:var(--fye-sage)"></i>Paid £<?= number_format($paid) ?></span>
                            <span><i class="fa-solid fa-circle" style="color:var(--fye-gold)"></i>Due £<?= number_format(max(0, $committed - $paid)) ?></span>
                            <span><i class="fa-solid fa-circle" style="color:var(--fye-paper-2)"></i>Unallocated £<?= number_format(max(0, $budget - $committed)) ?></span>
                        </div>
                    <?php endif; ?>
                    <a href="/profile/payments" class="fye-btn ghost sm block" style="margin-top:16px">View payments</a>
                </div>

                <!-- At a glance -->
                <div class="icard">
                    <h3><i class="fa-solid fa-clipboard-list"></i> At a glance</h3>
                    <div>
                        <div class="kv"><span class="k">Guests</span><span class="v fye-num"><?= (int)($event['guest_count'] ?? 0) ?></span></div>
                        <div class="kv"><span class="k">Suppliers booked</span><span class="v fye-num"><?= count($liveBookings) ?></span></div>
                        <div class="kv"><span class="k">Awaiting action</span><span class="v fye-num"><?= count(array_filter($liveBookings, fn($b) => $b['status'] === 'accepted')) ?></span></div>
                        <?php if (isset($event['days']) && $event['days'] !== null): ?>
                            <div class="kv"><span class="k">Days remaining</span><span class="v fye-num"><?= max(0, (int)$event['days']) ?></span></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</main>
<?= $this->include('footer') ?>
