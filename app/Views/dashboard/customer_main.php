<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">

        <?= $this->include('dashboard/_customer_tabs') ?>
        <?= $this->include('dashboard/_flash_alerts') ?>

        <div class="ra-body">

            <!-- Header -->
            <div class="ra-head">
                <h1>Welcome back, <?= esc($user['name']) ?> 👋</h1>
                <p>Your private planning hub. Bookings, messages and payments live in one place, so you always know what's next.</p>
            </div>

            <!-- Countdown cards — sorted soonest-first (controller sorts $events by days ASC) -->
            <?php if (!empty($events)): ?>
            <div class="ra-countdowns">
                <?php foreach (array_slice($events, 0, 3) as $i => $event): ?>
                    <?php
                    $days = $event['days'] ?? null;
                    $isLead = ($i === 0);
                    ?>
                    <a href="/profile/events" class="cd <?= $isLead ? 'lead' : '' ?>">
                        <div class="cd-top">
                            <span class="fye-pill accepted"><?= esc($event['event_type'] ?? 'Event') ?></span>
                            <?php if ($isLead): ?>
                                <span class="cd-flag"><i class="fa-solid fa-arrow-right"></i> Up next</span>
                            <?php endif; ?>
                        </div>
                        <div class="cd-title"><?= esc($event['title']) ?></div>
                        <?php if ($days !== null): ?>
                            <div class="cd-num">
                                <b class="fye-num"><?= $days ?></b>
                                <span><?= $days === 1 ? 'day to go' : 'days to go' ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="cd-meta">
                            <i class="fa-solid fa-calendar-day"></i>
                            <?= !empty($event['date']) ? date('d M Y', strtotime($event['date'])) : '—' ?>
                            <?php if (!empty($event['location'])): ?>
                                &nbsp;·&nbsp;<?= esc($event['location']) ?>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Stat tiles -->
            <div class="ra-stats">
                <?php
                $statTiles = [
                    ['v' => $totalPendingRequests, 'l' => 'Pending',      'ic' => 'fa-clock',         'tone' => 'gold'],
                    ['v' => $totalAccepted,        'l' => 'Accepted',     'ic' => 'fa-check',         'tone' => 'sage'],
                    ['v' => $totalAwaitingPayment, 'l' => 'Awaiting pay', 'ic' => 'fa-pound-sign',    'tone' => 'terra'],
                    ['v' => $totalConfirmed,       'l' => 'Confirmed',    'ic' => 'fa-calendar-check','tone' => 'slate'],
                    ['v' => $totalDeclined,        'l' => 'Declined',     'ic' => 'fa-xmark',         'tone' => 'plum'],
                ];
                ?>
                <?php foreach ($statTiles as $tile): ?>
                    <div class="ra-stat">
                        <div class="ic <?= $tile['tone'] ?>"><i class="fa-solid <?= $tile['ic'] ?>"></i></div>
                        <div class="v fye-num"><?= (int) $tile['v'] ?></div>
                        <div class="l"><?= $tile['l'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Main 2-col grid -->
            <div class="ra-grid">

                <!-- LEFT column -->
                <div class="ra-col">

                    <!-- Needs your attention -->
                    <div class="fye-card">
                        <h2><i class="fa-solid fa-circle-exclamation"></i> Needs your attention</h2>
                        <?php
                        $attItems = [];
                        if ($totalAccepted > 0) {
                            $attItems[] = ['tone' => 'sage', 'ic' => 'fa-check-circle',
                                't' => 'Vendor accepted a booking',
                                'd' => $totalAccepted . ' booking' . ($totalAccepted > 1 ? 's have' : ' has') . ' been accepted — review and confirm',
                                'cta' => 'Review', 'href' => '/profile/my-bookings'];
                        }
                        if ($totalAwaitingPayment > 0) {
                            $attItems[] = ['tone' => 'gold', 'ic' => 'fa-credit-card',
                                't' => 'Deposit required',
                                'd' => $totalAwaitingPayment . ' booking' . ($totalAwaitingPayment > 1 ? 's are' : ' is') . ' accepted and awaiting your deposit',
                                'cta' => 'Pay now', 'href' => '/profile/my-bookings'];
                        }
                        if ($unreadMessages > 0) {
                            $attItems[] = ['tone' => 'slate', 'ic' => 'fa-envelope',
                                't' => 'New messages',
                                'd' => $unreadMessages . ' unread message' . ($unreadMessages > 1 ? 's' : '') . ' from your suppliers',
                                'cta' => 'Open', 'href' => '/profile/messages'];
                        }
                        if ($totalDeclined > 0) {
                            $attItems[] = ['tone' => 'terra', 'ic' => 'fa-times-circle',
                                't' => 'A request was declined',
                                'd' => $totalDeclined . ' request' . ($totalDeclined > 1 ? 's were' : ' was') . ' declined — find an alternative',
                                'cta' => 'Browse', 'href' => '/browse-services'];
                        }
                        if (empty($events)) {
                            $attItems[] = ['tone' => 'slate', 'ic' => 'fa-calendar-plus',
                                't' => 'No events created yet',
                                'd' => 'Create your first event to start planning and booking services',
                                'cta' => 'Create event', 'href' => '/event/create'];
                        }
                        ?>
                        <?php if (!empty($attItems)): ?>
                            <?php foreach ($attItems as $a): ?>
                                <div class="att <?= $a['tone'] ?>">
                                    <div class="ai ic <?= $a['tone'] ?>"><i class="fa-solid <?= $a['ic'] ?>"></i></div>
                                    <div>
                                        <div class="at"><?= esc($a['t']) ?></div>
                                        <div class="ad"><?= esc($a['d']) ?></div>
                                    </div>
                                    <a href="<?= $a['href'] ?>" class="fye-btn ghost sm aa"><?= esc($a['cta']) ?></a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fa-solid fa-check-circle fa-2x mb-2" style="color:var(--fye-sage)"></i>
                                <p class="fye-muted mb-0" style="font-size:13.5px">You're all caught up — nothing needs your attention right now.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- My events -->
                    <div class="fye-card">
                        <div class="fye-card-head">
                            <div>
                                <h2 style="margin-bottom:0"><i class="fa-solid fa-calendar"></i> My events</h2>
                                <div class="sub">Each event holds the bookings and budget for that celebration.</div>
                            </div>
                            <a href="/event/create" class="fye-btn primary sm"><i class="fa-solid fa-plus"></i> New event</a>
                        </div>
                        <?php if (!empty($events)): ?>
                            <?php foreach ($events as $event): ?>
                                <?php
                                $booked = (int) ($event['servicesBooked'] ?? 0);
                                $max    = 8;
                                $cost   = (float) ($event['totalCost'] ?? 0);
                                $pct    = $max > 0 ? min(100, round($booked / $max * 100)) : 0;
                                ?>
                                <a href="/browse-services?event_id=<?= (int) $event['id'] ?>" class="ev">
                                    <div class="ev-top">
                                        <div>
                                            <div class="ev-title"><?= esc($event['title']) ?></div>
                                            <div class="ev-meta">
                                                <?php if (!empty($event['date'])): ?>
                                                    <span><i class="fa-solid fa-calendar-day"></i><?= date('d M Y', strtotime($event['date'])) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($event['location'])): ?>
                                                    <span><i class="fa-solid fa-location-dot"></i><?= esc($event['location']) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($event['guest_count'])): ?>
                                                    <span><i class="fa-solid fa-user-group"></i><?= (int) $event['guest_count'] ?> guests</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if (!empty($event['event_type'])): ?>
                                            <span class="fye-pill accepted"><?= esc($event['event_type']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ev-prog">
                                        <div class="lbl">
                                            <span>Key services booked</span>
                                            <span class="fye-num"><?= $booked ?>/<?= $max ?></span>
                                        </div>
                                        <div class="bar"><div class="fill" style="width:<?= $pct ?>%"></div></div>
                                    </div>
                                    <?php if ($cost > 0): ?>
                                        <div class="ev-cost">Estimated spend <b class="fye-num">£<?= number_format($cost) ?></b></div>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fa-solid fa-calendar-plus fa-2x mb-3 d-block" style="color:var(--fye-ink-3)"></i>
                                <h5 class="fw-semibold" style="font-family:var(--fye-display)">No events yet</h5>
                                <p class="fye-muted mb-4" style="font-size:13.5px">Create an event to save your date, guest count, and venue — then add services.</p>
                                <a href="/event/create" class="fye-btn primary"><i class="fa-solid fa-plus"></i> Create your first event</a>
                            </div>
                        <?php endif; ?>
                    </div>

                </div><!-- /left -->

                <!-- RIGHT column -->
                <div class="ra-col">

                    <!-- Payment summary -->
                    <div class="fye-card">
                        <h2><i class="fa-solid fa-credit-card"></i> Payment summary</h2>
                        <div class="kv">
                            <span class="k">Deposits paid</span>
                            <span class="v">£<?= number_format($depositsPaid, 2) ?></span>
                        </div>
                        <div class="kv">
                            <span class="k">Remaining balance</span>
                            <span class="v">£<?= number_format(max(0, $totalSpend - $depositsPaid), 2) ?></span>
                        </div>
                        <div class="kv total">
                            <span class="k">Total event spend</span>
                            <span class="v">£<?= number_format($totalSpend, 2) ?></span>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="fye-card">
                        <h2><i class="fa-solid fa-comments"></i> Messages</h2>
                        <?php if (!empty($recentMessages)): ?>
                            <?php foreach ($recentMessages as $msg): ?>
                                <?php
                                $senderName = $msg['sender_name'] ?? 'Vendor';
                                $initials = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', $senderName))));
                                $initials = substr($initials, 0, 2);
                                $isUnread = empty($msg['is_read']);
                                $msgTime = !empty($msg['created_at']) ? date('d M', strtotime($msg['created_at'])) : '';
                                ?>
                                <div class="msg">
                                    <div class="av"><?= esc($initials) ?></div>
                                    <div style="min-width:0">
                                        <div class="who"><?= esc($senderName) ?></div>
                                        <div class="snip"><?= esc(substr($msg['message'] ?? '', 0, 60)) ?></div>
                                    </div>
                                    <div class="t">
                                        <?= esc($msgTime) ?>
                                        <?php if ($isUnread): ?><span class="dot"></span><?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div style="margin-top:12px">
                                <a href="/profile/messages" class="fye-btn ghost sm">View all messages</a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fa-solid fa-comments fa-2x mb-2 d-block" style="color:var(--fye-ink-3)"></i>
                                <p class="fye-muted mb-3" style="font-size:12.5px">No conversations yet. Message a vendor from a booking or service page.</p>
                                <a href="/browse-services" class="fye-btn primary sm">Browse services</a>
                            </div>
                        <?php endif; ?>
                    </div>

                </div><!-- /right -->

            </div><!-- /ra-grid -->
        </div><!-- /ra-body -->

    </div>
</div>
</main>

<?= $this->include('footer') ?>
