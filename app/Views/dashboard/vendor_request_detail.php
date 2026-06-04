<?= $this->include('header') ?>
<main class="page-main">
<div class="dashboard-wrapper">
<div class="container">
    <?= $this->include('dashboard/_vendor_tabs') ?>
    <?= $this->include('dashboard/_flash_alerts') ?>
    <div class="fye-page">
        <a href="/profile/bookings" class="fye-back"><i class="fa-solid fa-arrow-left"></i> Requests &amp; bookings</a>

        <div class="fye-detail">
            <!-- Left column -->
            <div class="col">
                <div class="icard">
                    <!-- Customer header -->
                    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                        <div class="lava"><?= esc(strtoupper(substr($item['customer_name'] ?? 'C', 0, 2))) ?></div>
                        <div style="flex:1">
                            <div style="font-weight:800;font-size:19px"><?= esc($item['customer_name'] ?? '—') ?></div>
                            <div class="fye-muted" style="font-size:13px"><?= esc($item['event_title'] ?? '—') ?></div>
                        </div>
                        <?php
                        $statusClass = match ($item['status'] ?? '') {
                            'confirmed' => 'confirmed',
                            'accepted'  => 'accepted',
                            'pending'   => 'pending',
                            default     => 'declined',
                        };
                        ?>
                        <span class="fye-pill <?= $statusClass ?>"><?= ucfirst($item['status'] ?? '') ?></span>
                    </div>

                    <!-- Meta -->
                    <div class="hb-meta" style="margin-top:16px">
                        <?php if (!empty($item['service_title'])): ?>
                            <span><i class="fa-solid fa-concierge-bell"></i><?= esc($item['service_title']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($item['event_date'])): ?>
                            <span><i class="fa-solid fa-calendar-day"></i><?= date('d M Y', strtotime($item['event_date'])) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($item['guest_count'])): ?>
                            <span><i class="fa-solid fa-user-group"></i><?= (int)$item['guest_count'] ?> guests</span>
                        <?php endif; ?>
                    </div>

                    <!-- Quote breakdown -->
                    <?php
                    $total   = (float)($item['price'] ?? $item['service_price'] ?? 0);
                    $guests  = (int)($item['guest_count'] ?? 0);
                    $perHead = $guests > 0 ? round($total / $guests, 2) : 0;
                    ?>
                    <div class="fye-quote" style="margin-top:18px">
                        <?php if ($guests > 0 && $perHead > 0): ?>
                            <div class="qrow"><span class="fye-muted"><?= esc($item['service_title'] ?? 'Service') ?> · <?= $guests ?> × £<?= number_format($perHead, 2) ?></span><span class="fye-num">£<?= number_format($total, 2) ?></span></div>
                        <?php else: ?>
                            <div class="qrow"><span class="fye-muted"><?= esc($item['service_title'] ?? 'Service') ?></span><span class="fye-num">£<?= number_format($total, 2) ?></span></div>
                        <?php endif; ?>
                        <div class="qrow"><span class="fye-muted">Service &amp; setup</span><span class="fye-muted">included</span></div>
                        <div class="qrow total"><span>Quote total</span><b class="fye-num">£<?= number_format($total, 2) ?></b></div>
                    </div>

                    <!-- Actions -->
                    <div class="fye-actions" style="margin-top:18px">
                        <?php if (($item['status'] ?? '') === 'pending'): ?>
                            <form method="post" action="/profile/update-booking-status/<?= (int)$item['id'] ?>" style="display:contents">
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="accepted">
                                <button type="submit" class="fye-btn primary"><i class="fa-solid fa-check"></i> Accept request</button>
                            </form>
                            <a href="/profile/vendor-quote/<?= (int)$item['id'] ?>" class="fye-btn ghost"><i class="fa-solid fa-file-invoice"></i> Send custom quote</a>
                            <form method="post" action="/profile/update-booking-status/<?= (int)$item['id'] ?>" style="display:contents;margin-left:auto">
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="fye-btn danger" style="margin-left:auto" onclick="return confirm('Decline this request?')">Decline</button>
                            </form>
                        <?php elseif (($item['status'] ?? '') === 'accepted'): ?>
                            <span class="fye-btn ghost"><i class="fa-solid fa-clock"></i> Awaiting customer</span>
                            <a href="/profile/vendor-quote/<?= (int)$item['id'] ?>" class="fye-btn ghost"><i class="fa-solid fa-pen"></i> Edit quote</a>
                        <?php elseif (($item['status'] ?? '') === 'confirmed'): ?>
                            <span class="fye-btn ghost" style="color:var(--fye-sage)"><i class="fa-solid fa-check"></i> Confirmed booking</span>
                        <?php endif; ?>
                        <?php if (!in_array($item['status'] ?? '', ['rejected', 'cancelled'], true)): ?>
                            <a href="<?= base_url('profile/messages/by-booking/' . (int)$item['id']) ?>" class="fye-btn ghost"><i class="fa-solid fa-comment"></i> Message customer</a>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($item['quote_detail'])): ?>
                        <?= view('partials/quote_breakdown', ['quoteDetail' => $item['quote_detail'], 'collapseId' => (int)$item['id']]) ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right column -->
            <div class="col">
                <div class="icard">
                    <h3><i class="fa-solid fa-user"></i> Customer</h3>
                    <div>
                        <div class="kv"><span class="k">Name</span><span class="v"><?= esc($item['customer_name'] ?? '—') ?></span></div>
                        <div class="kv"><span class="k">Event</span><span class="v"><?= esc($item['event_title'] ?? '—') ?></span></div>
                        <?php if (!empty($item['event_date'])): ?>
                            <div class="kv"><span class="k">Date</span><span class="v fye-num"><?= date('d M Y', strtotime($item['event_date'])) ?></span></div>
                        <?php endif; ?>
                        <?php if (!empty($item['location'])): ?>
                            <div class="kv"><span class="k">Location</span><span class="v"><?= esc($item['location']) ?></span></div>
                        <?php endif; ?>
                        <div class="kv"><span class="k">Quote expires</span><span class="v" style="color:var(--fye-terra-deep)">in 7 days</span></div>
                    </div>
                </div>
                <div class="icard">
                    <h3><i class="fa-solid fa-lightbulb"></i> Tip</h3>
                    <div class="csub" style="margin-top:8px;line-height:1.6">Replies within 4 hours are 3× more likely to convert. Personalise your response with the customer's event name and guest count.</div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</main>
<?= $this->include('footer') ?>
