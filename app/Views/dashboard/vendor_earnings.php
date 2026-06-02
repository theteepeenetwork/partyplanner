<?= $this->include('header') ?>
<main class="page-main">
<div class="dashboard-wrapper">
<div class="container">
    <?= $this->include('dashboard/_vendor_tabs') ?>
    <?= $this->include('dashboard/_flash_alerts') ?>
    <div class="fye-page">
        <h1 class="fye-page-title">Earnings &amp; payouts</h1>
        <p class="fye-page-sub" style="margin-bottom:22px">What you've earned, what's settled, and what's on the way.</p>

        <div class="fye-minis">
            <div class="mini-stat">
                <div class="v fye-num">£<?= number_format((float)($earningsThisMonth ?? 0), 0) ?></div>
                <div class="l">This month</div>
            </div>
            <div class="mini-stat">
                <div class="v fye-num" style="color:var(--fye-sage)">£<?= number_format((float)($settledTotal ?? $earningsThisMonth ?? 0), 0) ?></div>
                <div class="l">Settled (90 days)</div>
            </div>
            <div class="mini-stat">
                <div class="v fye-num" style="color:var(--fye-gold)">£<?= number_format((float)($pendingTotal ?? 0), 0) ?></div>
                <div class="l">Pending payout</div>
            </div>
            <div class="mini-stat">
                <div class="v fye-num">£<?= number_format((float)($avgMonthly ?? 0), 0) ?></div>
                <div class="l">Avg / month</div>
            </div>
        </div>

        <div class="fye-detail">
            <!-- Left: earnings chart -->
            <div class="col">
                <div class="icard">
                    <h3><i class="fa-solid fa-chart-column"></i> Six-month trend</h3>
                    <?php if (!empty($monthlyEarnings)): ?>
                        <?php $maxVal = max(array_column($monthlyEarnings, 'amount') ?: [1]); ?>
                        <div class="bar-chart" style="margin-top:18px">
                            <?php foreach ($monthlyEarnings as $i => $m):
                                $h = $maxVal > 0 ? round((float)$m['amount'] / $maxVal * 120) : 4;
                                $isLast = $i === count($monthlyEarnings) - 1;
                            ?>
                                <div class="bar-col">
                                    <span class="bar-val" style="color:<?= $isLast ? 'var(--fye-terra-deep)' : 'var(--fye-ink-3)' ?>">
                                        £<?= number_format((float)$m['amount'] / 1000, 1) ?>k
                                    </span>
                                    <div class="bar-fill" style="height:<?= $h ?>px;background:<?= $isLast ? 'var(--fye-terra)' : 'var(--fye-terra-tint)' ?>"></div>
                                    <span class="bar-lbl"><?= esc($m['month']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="fye-muted" style="font-size:13.5px;margin-top:16px">No earnings data yet. Earnings appear once you've completed bookings.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: payout history -->
            <div class="col">
                <div class="icard">
                    <h3><i class="fa-solid fa-building-columns"></i> Payout history</h3>
                    <?php if (!empty($payoutHistory)): ?>
                        <div style="margin-top:12px">
                            <?php foreach ($payoutHistory as $p):
                                $isSettled = ($p['status'] ?? '') === 'settled';
                            ?>
                                <div class="srow">
                                    <div class="si" style="background:<?= $isSettled ? 'var(--fye-sage-tint)' : 'var(--fye-gold-tint)' ?>;color:<?= $isSettled ? 'var(--fye-sage)' : 'var(--fye-gold)' ?>">
                                        <i class="fa-solid <?= $isSettled ? 'fa-check' : 'fa-clock' ?>"></i>
                                    </div>
                                    <div>
                                        <div class="sn fye-num">£<?= number_format((float)($p['amount'] ?? 0), 2) ?></div>
                                        <div class="sc"><?= esc($p['reference'] ?? $p['ref'] ?? '') ?></div>
                                    </div>
                                    <div class="right">
                                        <div class="sc fye-num"><?= esc($p['date'] ?? '') ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="fye-muted" style="font-size:13.5px;margin-top:12px">No payouts recorded yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</main>
<?= $this->include('footer') ?>
