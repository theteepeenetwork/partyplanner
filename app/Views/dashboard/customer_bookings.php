<?= $this->include('header') ?>
<main class="page-main">
<div class="dashboard-wrapper">
<div class="container">
    <?= $this->include('dashboard/_customer_tabs') ?>
    <?= $this->include('dashboard/_flash_alerts') ?>
    <div class="fye-page">
        <div class="fye-page-head">
            <div>
                <h1 class="fye-page-title">Bookings</h1>
                <p class="fye-page-sub">Every supplier request across your events, grouped by where it stands.</p>
            </div>
            <a href="/browse-services" class="fye-btn primary"><i class="fa-solid fa-magnifying-glass"></i> Find suppliers</a>
        </div>

        <?php if (!empty($bookingItems)):
            $groups = [
                ['accepted',  'Accepted — action needed',  'fa-circle-check'],
                ['pending',   'Pending — awaiting vendor',  'fa-clock'],
                ['confirmed', 'Confirmed',                  'fa-calendar-check'],
                ['rejected',  'Declined',                   'fa-circle-xmark'],
                ['cancelled', 'Cancelled',                  'fa-circle-xmark'],
            ];
            foreach ($groups as [$status, $label, $icon]):
                $rows = array_filter($bookingItems, fn($b) => $b['status'] === $status);
                if (empty($rows)) continue;
        ?>
            <div>
                <div class="glabel">
                    <i class="fa-solid <?= $icon ?>"></i>
                    <?= esc($label) ?>
                    <span class="ln"></span>
                    <span><?= count($rows) ?></span>
                </div>
                <?php foreach ($rows as $item):
                    $initials = strtoupper(substr($item['vendor_name'] ?? 'V', 0, 2));
                ?>
                    <a href="/profile/my-bookings/<?= (int) $item['id'] ?>" class="lrow clickable" style="grid-template-columns:42px 1fr auto auto">
                        <div class="lava"><?= esc($initials) ?></div>
                        <div>
                            <div class="ti"><?= esc($item['vendor_name'] ?? '—') ?></div>
                            <div class="me"><?= esc($item['service_title'] ?? '—') ?> — <span style="color:var(--fye-ink-3)"><?= esc($item['event_title'] ?? '') ?></span></div>
                        </div>
                        <span class="fye-pill <?= $item['status'] === 'confirmed' ? 'confirmed' : ($item['status'] === 'accepted' ? 'accepted' : ($item['status'] === 'pending' ? 'pending' : 'declined')) ?>"><?= ucfirst($item['status']) ?></span>
                        <div class="amt-lg">£<?= number_format((float) ($item['price'] ?? $item['service_price'] ?? 0), 2) ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <?php else: ?>
            <div class="icard text-center py-5">
                <i class="fa-solid fa-handshake fa-3x mb-3 d-block fye-faint"></i>
                <h5 style="font-family:var(--fye-display)">No bookings yet</h5>
                <p class="fye-muted mb-4" style="font-size:13.5px">Add services to an event basket and pay a deposit to send requests.</p>
                <a href="/profile/events" class="fye-btn primary">My events</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>
</main>
<?= $this->include('footer') ?>
