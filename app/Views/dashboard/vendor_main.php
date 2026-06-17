<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">

        <?= $this->include('dashboard/_vendor_tabs') ?>
        <?= $this->include('dashboard/_flash_alerts') ?>

        <div class="cc-body">

            <!-- MAIN COLUMN -->
            <div class="cc-main">

                <!-- Head -->
                <div class="cc-head">
                    <h1>Kitchen command centre</h1>
                    <div class="meta"><?= (int) $pendingBookings ?> request<?= $pendingBookings !== 1 ? 's' : '' ?> open<?php if (!empty($payouts['next'])): ?> · next payout <?= esc($payouts['next']) ?><?php endif; ?></div>
                </div>

                <!-- KPI strip -->
                <div class="cc-kpis">
                    <?php
                    $kpis = [
                        ['l' => 'Open req',    'ic' => 'fa-inbox',          'v' => $pendingBookings,     'd' => '', 'cls' => ''],
                        ['l' => 'Upcoming',    'ic' => 'fa-calendar-check', 'v' => $upcomingBookings,    'd' => '', 'cls' => 'up'],
                        ['l' => 'This month',  'ic' => 'fa-pound-sign',     'v' => '£' . number_format($earningsThisMonth, 0), 'd' => '', 'cls' => 'up'],
                        ['l' => 'Active svcs', 'ic' => 'fa-briefcase',      'v' => $activeServicesCount, 'd' => '', 'cls' => ''],
                        ['l' => 'Avg reply',   'ic' => 'fa-reply',          'v' => isset($avgResponseHours) && $avgResponseHours !== null ? $avgResponseHours . 'h' : '—', 'd' => '', 'cls' => ''],
                    ];
                    ?>
                    <?php foreach ($kpis as $k): ?>
                        <div class="cc-kpi">
                            <div class="l"><i class="fa-solid <?= $k['ic'] ?>"></i><?= $k['l'] ?></div>
                            <div class="v"><?= $k['v'] ?></div>
                            <?php if ($k['d']): ?><div class="d <?= $k['cls'] ?>"><?= $k['d'] ?></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Action queue — pending requests -->
                <div class="cc-panel">
                    <div class="cc-panel-h">
                        <div class="t">
                            <i class="fa-solid fa-inbox"></i>
                            Requests to action
                            <?php if ($pendingBookings > 0): ?>
                                <span class="ct"><?= (int) $pendingBookings ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="/profile/bookings" class="fye-btn ghost sm">View all</a>
                    </div>
                    <?php if (!empty($pendingBookingsList)): ?>
                        <?php foreach ($pendingBookingsList as $req): ?>
                            <?php
                            // Priority based on days until event
                            $prio = 'lo';
                            if (!empty($req['event_date'])) {
                                $daysAway = (int) (new DateTime('today'))->diff(new DateTime($req['event_date']))->days;
                                if ($daysAway <= 56)  $prio = 'hi';
                                elseif ($daysAway <= 112) $prio = 'md';
                            }
                            $guestCount = !empty($req['guest_count']) ? (int) $req['guest_count'] : null;
                            $eventDate  = !empty($req['event_date']) ? date('d M', strtotime($req['event_date'])) : '—';
                            $itemPrice  = !empty($req['price']) ? '£' . number_format((float) $req['price']) : '';
                            $when       = !empty($req['item_created_at']) ? date('d M', strtotime($req['item_created_at'])) : '';
                            ?>
                            <div class="cc-row">
                                <div class="cc-prio <?= $prio ?>"></div>
                                <div class="cc-ic ic terra"><i class="fa-solid fa-concierge-bell"></i></div>
                                <div>
                                    <div class="ti"><?= esc($req['service_title'] ?? '—') ?><?php if ($guestCount): ?> · <?= $guestCount ?> guests<?php endif; ?></div>
                                    <div class="me"><?= esc($req['customer_name'] ?? 'Customer') ?> — <?= esc($req['event_title'] ?? '—') ?></div>
                                </div>
                                <div class="when"><?= esc($eventDate) ?></div>
                                <?php if ($itemPrice): ?><div class="val"><?= esc($itemPrice) ?></div><?php endif; ?>
                                <a href="/profile/bookings" class="fye-btn primary sm">Review</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4" style="color:var(--fye-ink-3);font-size:13.5px">
                            <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
                            No pending requests — you're all clear.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Service health -->
                <div class="cc-panel">
                    <div class="cc-panel-h">
                        <div class="t"><i class="fa-solid fa-heart-pulse"></i> Service health</div>
                        <a href="/profile/services" class="manage-link">Manage</a>
                    </div>
                    <?php if (!empty($serviceHealthItems)): ?>
                        <?php foreach ($serviceHealthItems as $svc): ?>
                            <?php
                            $done = (int) $svc['has_description'] + (int) $svc['has_images'] + (int) $svc['has_price'] + (int) $svc['has_cancellation'];
                            $prioClass = $done === 4 ? 'ok' : ($done >= 2 ? 'md' : 'hi');
                            $pct = round($done / 4 * 100);
                            $fillColor = $done === 4 ? 'var(--fye-sage)' : 'var(--fye-gold)';
                            ?>
                            <div class="cc-row health-row">
                                <div class="cc-prio <?= $prioClass ?>"></div>
                                <div>
                                    <div class="ti"><?= esc($svc['title']) ?></div>
                                    <div class="me"><?= isset($svc['bookings_count']) ? (int) $svc['bookings_count'] . ' bookings all-time' : '' ?></div>
                                </div>
                                <div class="ev-prog" style="align-self:center">
                                    <div class="bar"><div class="fill" style="width:<?= $pct ?>%;background:<?= $fillColor ?>"></div></div>
                                </div>
                                <div class="when fye-num"><?= $done ?>/4</div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4" style="color:var(--fye-ink-3);font-size:13.5px">
                            <a href="/service/list" class="fye-btn primary sm"><i class="fa-solid fa-plus"></i> Add your first service</a>
                        </div>
                    <?php endif; ?>
                </div>

            </div><!-- /cc-main -->

            <!-- STICKY RAIL -->
            <div class="cc-rail">

                <!-- Payouts -->
                <div class="cc-rblock">
                    <h3>Payouts</h3>
                    <div class="cc-gauge">
                        <?php
                        $settled = (float) ($payouts['settled'] ?? $earningsThisMonth);
                        $pending = (float) ($payouts['pending'] ?? 0);
                        $total   = $settled + $pending;
                        $settledPct = $total > 0 ? round($settled / $total * 100) : 0;
                        ?>
                        <div class="lbl">
                            <span class="fye-muted">Settled</span>
                            <b>£<?= number_format($settled) ?></b>
                        </div>
                        <div class="track">
                            <div class="seg" style="width:<?= $settledPct ?>%;background:var(--fye-sage)"></div>
                        </div>
                        <div class="kv-small"><span class="k">Pending</span><span class="v">£<?= number_format($pending) ?></span></div>
                        <?php if (!empty($payouts['next'])): ?>
                            <div class="kv-small"><span class="k">Next payout</span><span class="v"><?= esc($payouts['next']) ?></span></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Upcoming -->
                <div class="cc-rblock">
                    <h3>Upcoming</h3>
                    <?php if (!empty($upcomingBookingsList)): ?>
                        <?php foreach (array_slice($upcomingBookingsList, 0, 4) as $b): ?>
                            <?php
                            $dt = !empty($b['event_date']) ? new DateTime($b['event_date']) : null;
                            ?>
                            <div class="cc-mini">
                                <?php if ($dt): ?>
                                    <div class="db">
                                        <div class="m"><?= $dt->format('M') ?></div>
                                        <div class="d"><?= $dt->format('d') ?></div>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="nm"><?= esc($b['event_title'] ?? 'Event') ?></div>
                                    <div class="sb"><?= esc($b['customer_name'] ?? '') ?><?= !empty($b['location']) ? ' · ' . esc($b['location']) : '' ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="font-size:12.5px;color:var(--fye-ink-3)">No upcoming bookings yet.</p>
                    <?php endif; ?>
                </div>

                <!-- Recent activity -->
                <div class="cc-rblock">
                    <h3>Recent activity</h3>
                    <?php if (!empty($pendingBookingsList)): ?>
                        <?php foreach (array_slice($pendingBookingsList, 0, 4) as $item): ?>
                            <?php
                            $ts = $item['item_created_at'] ?? $item['created_at'] ?? null;
                            $when = '';
                            if ($ts) {
                                $diff = (new DateTime('now'))->diff(new DateTime($ts));
                                if ($diff->days >= 1)   $when = (new DateTime($ts))->format('d M');
                                elseif ($diff->h >= 1)  $when = $diff->h . 'h ago';
                                elseif ($diff->i >= 1)  $when = $diff->i . 'm ago';
                                else                    $when = 'Just now';
                            }
                            ?>
                            <div class="act">
                                <span class="ad" style="background:var(--fye-gold)"></span>
                                <span style="flex:1">New request from <strong><?= esc($item['customer_name'] ?? 'Customer') ?></strong> for <?= esc($item['service_title'] ?? 'a service') ?></span>
                                <?php if ($when): ?><span class="at"><?= esc($when) ?></span><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif (!empty($upcomingBookingsList)): ?>
                        <?php foreach (array_slice($upcomingBookingsList, 0, 4) as $item): ?>
                            <div class="act">
                                <span class="ad" style="background:var(--fye-sage)"></span>
                                <span style="flex:1">Booking confirmed: <strong><?= esc($item['customer_name'] ?? 'Customer') ?></strong></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="font-size:12.5px;color:var(--fye-ink-3)">No recent activity yet.</p>
                    <?php endif; ?>
                </div>

                <!-- Quick actions -->
                <div class="cc-rblock">
                    <h3>Quick actions</h3>
                    <div class="cc-quick">
                        <a href="/service/list"><i class="fa-solid fa-plus"></i>Add service</a>
                        <a href="/profile/calendar"><i class="fa-solid fa-calendar-day"></i>Availability</a>
                        <a href="/profile/messages"><i class="fa-solid fa-comments"></i>Messages</a>
                        <a href="/profile/quote-analytics"><i class="fa-solid fa-chart-line"></i>Analytics</a>
                    </div>
                </div>

            </div><!-- /cc-rail -->

        </div><!-- /cc-body -->

    </div>
</div>
</main>

<?= $this->include('footer') ?>
