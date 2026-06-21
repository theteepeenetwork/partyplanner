<?= $this->include('header') ?>
<main class="page-main">
<div class="dashboard-wrapper">
<div class="container">
    <?= $this->include('dashboard/_vendor_tabs') ?>
    <?= $this->include('dashboard/_flash_alerts') ?>
    <div class="fye-page">
        <div class="fye-page-head">
            <div>
                <h1 class="fye-page-title">My services</h1>
                <p class="fye-page-sub">The listings customers can request. Complete listings convert more enquiries.</p>
            </div>
            <a href="/service/create" class="fye-btn primary"><i class="fa-solid fa-plus"></i> Add a service</a>
        </div>

        <?php if (!empty($services)): ?>
            <div class="fye-gal">
                <?php foreach ($services as $svc):
                    $checks = [
                        !empty($svc['description']),
                        !empty($svc['images']),
                        !empty($svc['price']),
                        !empty($svc['cancellation_policy']),
                    ];
                    $done  = count(array_filter($checks));
                    $pct   = round($done / 4 * 100);
                    $fillColor = $done === 4 ? 'var(--fye-sage)' : 'var(--fye-gold)';
                    $pillClass = $done === 4 ? 'confirmed' : 'pending';
                ?>
                    <a href="/service/edit/<?= (int)$svc['id'] ?>" class="gcard">
                        <?php if (!empty($svc['images'])): ?>
                            <img src="<?= base_url(esc($svc['images'][0]['thumbnail_path'] ?? $svc['images'][0]['image_path'] ?? '')) ?>" class="gc-img" alt="<?= esc($svc['title']) ?>">
                        <?php else: ?>
                            <div class="gc-ph" style="background:var(--fye-gold-tint);color:var(--fye-gold)">
                                <i class="fa-solid fa-image" style="font-size:20px"></i>
                            </div>
                        <?php endif; ?>
                        <div class="gc-body">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
                                <div class="gn"><?= esc($svc['title']) ?></div>
                                <span class="fye-pill <?= $pillClass ?>" style="font-size:11px;white-space:nowrap"><?= $done ?>/4</span>
                            </div>
                            <div class="gc" style="margin-top:4px">
                                <?php if (!empty($svc['price'])): ?>from £<?= number_format((float)$svc['price'], 2) ?> · <?php endif; ?>
                                <?php if (isset($svc['bookings_count'])): ?><?= (int)$svc['bookings_count'] ?> bookings<?php endif; ?>
                            </div>
                            <div class="ev-prog" style="margin-top:12px">
                                <div class="bar">
                                    <div class="fill" style="width:<?= $pct ?>%;background:<?= $fillColor ?>"></div>
                                </div>
                            </div>
                            <?php if (($svc['status'] ?? '') !== 'active'): ?>
                                <div style="margin-top:8px"><span class="fye-pill declined" style="font-size:11px">Inactive</span></div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="icard text-center py-5">
                <i class="fa-solid fa-concierge-bell fa-3x mb-3 d-block fye-faint"></i>
                <h5 style="font-family:var(--fye-display)">No services yet</h5>
                <p class="fye-muted mb-4" style="font-size:13.5px">Create your first listing to start receiving enquiries from customers.</p>
                <a href="/service/create" class="fye-btn primary"><i class="fa-solid fa-plus"></i> Add your first service</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>
</main>
<?= $this->include('footer') ?>
