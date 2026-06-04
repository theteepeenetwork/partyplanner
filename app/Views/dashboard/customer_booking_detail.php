<?= $this->include('header') ?>
<main class="page-main">
<div class="dashboard-wrapper">
<div class="container">
    <?= $this->include('dashboard/_customer_tabs') ?>
    <?= $this->include('dashboard/_flash_alerts') ?>
    <div class="fye-page">
        <a href="/profile/my-bookings" class="fye-back"><i class="fa-solid fa-arrow-left"></i> All bookings</a>

        <div class="fye-detail">
            <!-- Left column -->
            <div class="col">
                <div class="icard">
                    <!-- Vendor header -->
                    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                        <div class="lava"><?= esc(strtoupper(substr($item['vendor_name'] ?? 'V', 0, 2))) ?></div>
                        <div style="flex:1">
                            <div style="font-weight:800;font-size:19px"><?= esc($item['vendor_name'] ?? '—') ?></div>
                            <div class="fye-muted" style="font-size:13px"><?= esc($item['service_title'] ?? '') ?> · for <?= esc($item['event_title'] ?? '') ?></div>
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

                    <!-- Quote breakdown -->
                    <?php
                    $total   = (float)($item['price'] ?? $item['service_price'] ?? 0);
                    $deposit = round($total * 0.15, 2);
                    $balance = $total - ($item['payment_status'] === 'succeeded' ? $deposit : 0);
                    ?>
                    <div class="fye-quote" style="margin-top:18px">
                        <div class="qrow"><span class="fye-muted"><?= esc($item['service_title'] ?? 'Service') ?></span><span class="fye-num">£<?= number_format($total, 2) ?></span></div>
                        <div class="qrow"><span class="fye-muted">Deposit (15%)</span><span class="fye-num">£<?= number_format($deposit, 2) ?></span></div>
                        <div class="qrow"><span class="fye-muted"><?= $item['payment_status'] === 'succeeded' ? 'Deposit paid' : 'Balance on the day' ?></span><span class="fye-num">£<?= number_format($balance, 2) ?></span></div>
                        <div class="qrow total"><span>Total</span><b class="fye-num">£<?= number_format($total, 2) ?></b></div>
                    </div>

                    <!-- Contextual actions -->
                    <div class="fye-actions" style="margin-top:18px">
                        <?php if ($item['status'] === 'accepted' && ($item['payment_status'] ?? '') !== 'succeeded'): ?>
                            <a href="/profile/payments" class="fye-btn primary"><i class="fa-solid fa-lock"></i> Pay deposit £<?= number_format($deposit, 2) ?></a>
                        <?php elseif ($item['status'] === 'pending'): ?>
                            <span class="fye-btn ghost"><i class="fa-solid fa-clock"></i> Awaiting vendor reply</span>
                        <?php elseif ($item['status'] === 'confirmed'): ?>
                            <span class="fye-btn ghost" style="color:var(--fye-sage)"><i class="fa-solid fa-check"></i> Confirmed &amp; paid</span>
                        <?php endif; ?>
                        <?php if (!in_array($item['status'] ?? '', ['rejected', 'cancelled'], true)): ?>
                            <a href="<?= base_url('profile/messages/start/' . (int)$item['service_id']) ?>" class="fye-btn ghost"><i class="fa-solid fa-comment"></i> Message vendor</a>
                        <?php endif; ?>
                        <?php if (in_array($item['status'] ?? '', ['rejected', 'cancelled'], true)): ?>
                            <a href="/browse-services" class="fye-btn primary"><i class="fa-solid fa-rotate"></i> Find alternative</a>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($item['pending_vendor_quote'])): ?>
                        <div class="icard" style="margin-top:16px;border-color:var(--fye-gold-tint);background:var(--fye-gold-tint)">
                            <div style="font-weight:700">Revised quote: £<?= number_format((float)$item['pending_vendor_quote']['total'], 2) ?></div>
                            <form method="post" action="/profile/vendor-quote/<?= (int)$item['id'] ?>/accept" style="margin-top:8px">
                                <?= csrf_field() ?>
                                <button type="submit" class="fye-btn primary sm"><i class="fa-solid fa-check"></i> Accept revised quote</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quote detail if available -->
                <?php if (!empty($item['quote_detail'])): ?>
                    <?= view('partials/quote_breakdown', ['quoteDetail' => $item['quote_detail'], 'collapseId' => (int)$item['id']]) ?>
                <?php endif; ?>
            </div>

            <!-- Right column -->
            <div class="col">
                <div class="icard">
                    <h3><i class="fa-solid fa-calendar-heart"></i> Event</h3>
                    <a href="/profile/events/<?= (int)$item['event_id'] ?>" class="srow clickable" style="border-bottom:none">
                        <div class="si"><i class="fa-solid fa-champagne-glasses"></i></div>
                        <div>
                            <div class="sn"><?= esc($item['event_title'] ?? '—') ?></div>
                            <div class="sc"><?= !empty($item['event_date']) ? date('d M Y', strtotime($item['event_date'])) : '' ?><?= !empty($item['event_location']) ? ' · ' . esc($item['event_location']) : '' ?></div>
                        </div>
                        <i class="fa-solid fa-chevron-right fye-faint" style="margin-left:auto"></i>
                    </a>
                </div>

                <div class="icard">
                    <h3><i class="fa-solid fa-circle-info"></i> What happens next</h3>
                    <div class="csub" style="margin-top:8px;line-height:1.6">
                        <?php if ($item['status'] === 'accepted'): ?>
                            Pay the deposit to confirm this supplier. The balance is due on the event day.
                        <?php elseif ($item['status'] === 'pending'): ?>
                            The supplier has 7 days to respond to your request. We'll notify you the moment they reply.
                        <?php elseif ($item['status'] === 'confirmed'): ?>
                            You're all set. The supplier will be in touch closer to the date to finalise details.
                        <?php else: ?>
                            This supplier couldn't take the booking. Browse alternatives for this category.
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (in_array($item['id'] ?? 0, $reviewableIds ?? [], true)): ?>
                    <div class="icard" style="text-align:center">
                        <a href="<?= base_url('review/create/' . (int)$item['id']) ?>" class="fye-btn primary block">
                            <i class="fa-solid fa-star"></i> Leave a review
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>
</main>
<?= $this->include('footer') ?>
