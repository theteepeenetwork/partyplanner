<?= $this->include('header') ?>
<main class="page-main">
<div class="dashboard-wrapper">
<div class="container">
    <?= $this->include('dashboard/_customer_tabs') ?>
    <?= $this->include('dashboard/_flash_alerts') ?>
    <div class="fye-page">
        <h1 class="fye-page-title">Saved suppliers</h1>
        <p class="fye-page-sub" style="margin-bottom:22px">Suppliers you've favourited while browsing. Send a request when you're ready.</p>

        <?php if (!empty($favourites)): ?>
            <div class="fye-gal">
                <?php foreach ($favourites as $fav):
                    $svc     = $fav['service'];
                    $price   = !empty($svc['price']) ? 'from £' . number_format((float)$svc['price'], 2) : null;
                    $loc     = $svc['service_location'] ?? null;
                ?>
                    <div class="gcard" style="position:relative">
                        <?php if (!empty($fav['image'])): ?>
                            <img src="<?= base_url(esc($fav['image'])) ?>" class="gc-img" alt="<?= esc($svc['title']) ?>">
                        <?php else: ?>
                            <div class="gc-ph"><?= esc($fav['category_name'] ?? 'service') ?></div>
                        <?php endif; ?>
                        <div class="gc-body">
                            <div class="gn"><?= esc($svc['title']) ?></div>
                            <div class="gc"><?= esc($fav['vendor_name']) ?><?= $loc ? ' · ' . esc($loc) : '' ?></div>
                            <?php if (!empty($fav['category_name'])): ?>
                                <div style="margin-top:4px"><span class="fye-pill action" style="font-size:11px"><?= esc($fav['category_name']) ?></span></div>
                            <?php endif; ?>
                            <div class="gmeta" style="margin-top:11px">
                                <span class="price"><?= $price ?? 'Price on request' ?></span>
                            </div>
                            <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap">
                                <a href="/service/view/<?= (int)$svc['id'] ?>" class="fye-btn ghost sm">View</a>
                                <a href="/service/view/<?= (int)$svc['id'] ?>" class="fye-btn primary sm">Add to event</a>
                                <a href="/profile/favourites/remove/<?= (int)$fav['favourite_id'] ?>"
                                   class="fye-btn sm" style="background:transparent;color:var(--fye-plum);border-color:var(--fye-plum-tint)"
                                   onclick="return confirm('Remove from favourites?')">
                                    <i class="fa-solid fa-heart-crack"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="icard text-center py-5">
                <i class="fa-solid fa-heart fa-3x mb-3 d-block fye-faint"></i>
                <h5 style="font-family:var(--fye-display)">Nothing saved yet</h5>
                <p class="fye-muted mb-4" style="font-size:13.5px">Tap the heart on a service page to add it to your favourites.</p>
                <a href="/browse-services" class="fye-btn primary">Browse services</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>
</main>
<?= $this->include('footer') ?>
