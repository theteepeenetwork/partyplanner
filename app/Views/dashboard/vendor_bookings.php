<?= $this->include('header') ?>
<main class="page-main">
<div class="dashboard-wrapper">
<div class="container">
    <?= $this->include('dashboard/_vendor_tabs') ?>
    <?= $this->include('dashboard/_flash_alerts') ?>
    <div class="fye-page">
        <div class="fye-page-head">
            <div>
                <h1 class="fye-page-title">Requests &amp; bookings</h1>
                <p class="fye-page-sub">Every enquiry across your services, grouped by where it stands. Respond fast to win more work.</p>
            </div>
            <a href="/profile/calendar" class="fye-btn ghost"><i class="fa-solid fa-calendar"></i> Calendar</a>
        </div>

        <?php if (!empty($bookingItems)):
            $groups = [
                ['pending',   'New — needs your response',   'fa-inbox'],
                ['accepted',  'Accepted — upcoming',         'fa-paper-plane'],
                ['confirmed', 'Confirmed &amp; upcoming',    'fa-calendar-check'],
                ['rejected',  'Declined / expired',          'fa-circle-xmark'],
                ['cancelled', 'Cancelled',                   'fa-circle-xmark'],
            ];
            foreach ($groups as [$status, $label, $icon]):
                $rows = array_filter($bookingItems, fn($b) => ($b['status'] ?? '') === $status);
                if (empty($rows)) continue;
        ?>
            <div>
                <div class="glabel">
                    <i class="fa-solid <?= $icon ?>"></i>
                    <?= $label ?>
                    <span class="ln"></span>
                    <span><?= count($rows) ?></span>
                </div>
                <?php foreach ($rows as $item):
                    // Priority based on days until event
                    $prio = 'lo';
                    if (!empty($item['event_date'])) {
                        $daysAway = (int)(new DateTime('today'))->diff(new DateTime($item['event_date']))->days;
                        if ($daysAway <= 56)  $prio = 'hi';
                        elseif ($daysAway <= 112) $prio = 'md';
                    }
                    $eventDate = !empty($item['event_date']) ? new DateTime($item['event_date']) : null;
                ?>
                    <a href="/profile/request/<?= (int)$item['id'] ?>" class="lrow clickable" style="grid-template-columns:52px 1fr auto auto auto">
                        <?php if ($eventDate): ?>
                            <div class="dchip">
                                <div class="m"><?= $eventDate->format('M') ?></div>
                                <div class="d"><?= $eventDate->format('d') ?></div>
                            </div>
                        <?php else: ?>
                            <div class="dchip" style="background:var(--fye-paper-2)"></div>
                        <?php endif; ?>
                        <div>
                            <div class="ti"><?= esc($item['service_title'] ?? '—') ?><?= !empty($item['guest_count']) ? ' · ' . (int)$item['guest_count'] . ' guests' : '' ?></div>
                            <div class="me"><i class="fa-solid fa-user"></i><?= esc($item['customer_name'] ?? '—') ?> — <?= esc($item['event_title'] ?? '') ?></div>
                        </div>
                        <?php if ($prio === 'hi' && $status === 'pending'): ?>
                            <span class="fye-pill pending">Urgent</span>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>
                        <span class="fye-pill <?= $status === 'confirmed' ? 'confirmed' : ($status === 'accepted' ? 'accepted' : ($status === 'pending' ? 'pending' : 'declined')) ?>"><?= ucfirst($item['status'] ?? '') ?></span>
                        <div class="amt-lg">£<?= number_format((float)($item['price'] ?? $item['service_price'] ?? 0), 2) ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <?php else: ?>
            <div class="icard text-center py-5">
                <i class="fa-solid fa-inbox fa-3x mb-3 d-block fye-faint"></i>
                <h5 style="font-family:var(--fye-display)">No requests yet</h5>
                <p class="fye-muted mb-4" style="font-size:13.5px">When customers request your services, they'll appear here.</p>
                <a href="/service/list" class="fye-btn primary"><i class="fa-solid fa-plus"></i> Add a service</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>
</main>
<?= $this->include('footer') ?>
