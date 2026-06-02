<?= $this->include('header') ?>
<main class="page-main">
<div class="dashboard-wrapper">
<div class="container">
    <?= $this->include('dashboard/_vendor_tabs') ?>
    <?= $this->include('dashboard/_flash_alerts') ?>
    <div class="fye-page">
        <div class="fye-page-head">
            <div>
                <h1 class="fye-page-title">Host profile</h1>
                <p class="fye-page-sub">Your public page. This is what customers see when they discover you.</p>
            </div>
            <a href="/vendor/<?= (int)$user['id'] ?>" class="fye-btn ghost" target="_blank">
                <i class="fa-solid fa-up-right-from-square"></i> View public page
            </a>
        </div>

        <!-- Hero band with cover + avatar -->
        <div class="hero-band">
            <div class="ph-strip" style="height:170px"></div>
            <div class="hb-body" style="display:flex;align-items:flex-end;gap:18px;flex-wrap:wrap">
                <?php if (!empty($user['host_photo_path'])): ?>
                    <img src="<?= base_url(esc($user['host_photo_path'])) ?>"
                         style="width:72px;height:72px;border-radius:50%;object-fit:cover;margin-top:-54px;border:4px solid #fff;flex:0 0 auto"
                         alt="<?= esc($user['name']) ?>">
                <?php else: ?>
                    <div class="lava" style="width:72px;height:72px;font-size:24px;border-radius:50%;margin-top:-54px;border:4px solid #fff;flex:0 0 auto">
                        <?= strtoupper(substr($user['name'] ?? 'V', 0, 2)) ?>
                    </div>
                <?php endif; ?>
                <div style="flex:1">
                    <h1 style="font-size:26px;margin:0"><?= esc($user['name']) ?></h1>
                    <div class="hb-meta" style="margin-top:6px">
                        <?php if (!empty($user['host_tagline'])): ?>
                            <span><i class="fa-solid fa-tag"></i><?= esc($user['host_tagline']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <button type="button" class="fye-btn primary" data-bs-toggle="collapse" data-bs-target="#hostEditForm">
                    <i class="fa-solid fa-pen"></i> Edit profile
                </button>
            </div>
        </div>

        <!-- Edit form (collapsible) -->
        <div class="collapse" id="hostEditForm">
            <div class="icard" style="margin-bottom:22px">
                <form action="/profile/host-profile" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div style="margin-bottom:16px">
                        <label style="font-size:12.5px;font-weight:700;margin-bottom:6px;display:block">Profile photo</label>
                        <input type="file" name="host_photo" accept="image/*" class="form-control form-control-sm">
                    </div>
                    <div style="margin-bottom:16px">
                        <label style="font-size:12.5px;font-weight:700;margin-bottom:6px;display:block">Tagline</label>
                        <input type="text" name="host_tagline" class="form-control" value="<?= esc($user['host_tagline'] ?? '') ?>" placeholder="e.g. Award-winning caterer serving the South West">
                    </div>
                    <div style="margin-bottom:16px">
                        <label style="font-size:12.5px;font-weight:700;margin-bottom:6px;display:block">Bio</label>
                        <textarea name="host_bio" class="form-control" rows="4" placeholder="Tell customers about your business, experience, and what makes you special…"><?= esc($user['host_bio'] ?? '') ?></textarea>
                    </div>
                    <div style="margin-bottom:16px">
                        <label style="font-size:12.5px;font-weight:700;margin-bottom:6px;display:block">Quote / tagline</label>
                        <input type="text" name="host_quote" class="form-control" value="<?= esc($user['host_quote'] ?? '') ?>" placeholder="A short memorable phrase">
                    </div>
                    <div style="margin-bottom:20px">
                        <label style="font-size:12.5px;font-weight:700;margin-bottom:6px;display:block">Specialisms <span class="fye-muted">(comma-separated)</span></label>
                        <?php
                        $plays = json_decode($user['host_plays'] ?? '[]', true);
                        $playsStr = is_array($plays) ? implode(', ', $plays) : '';
                        ?>
                        <input type="text" name="host_plays" class="form-control" value="<?= esc($playsStr) ?>" placeholder="e.g. Weddings, Corporate, Outdoor events">
                    </div>
                    <div class="fye-actions">
                        <button type="submit" class="fye-btn primary"><i class="fa-solid fa-check"></i> Save profile</button>
                        <button type="button" class="fye-btn ghost" data-bs-toggle="collapse" data-bs-target="#hostEditForm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Services on profile -->
        <?php if (!empty($services)): ?>
            <div class="fye-sec-h">
                <h3>Services on your profile</h3>
                <span class="grow"></span>
                <a href="/profile/services" class="fye-faint" style="font-size:12px;font-weight:700;text-decoration:none">Manage</a>
            </div>
            <div class="fye-gal">
                <?php foreach ($services as $svc): ?>
                    <a href="/service/edit/<?= (int)$svc['id'] ?>" class="gcard">
                        <?php if (!empty($svc['images'])): ?>
                            <img src="<?= base_url(esc($svc['images'][0]['thumbnail_path'] ?? $svc['images'][0]['image_path'] ?? '')) ?>" class="gc-img" alt="<?= esc($svc['title']) ?>">
                        <?php else: ?>
                            <div class="gc-ph">service photo</div>
                        <?php endif; ?>
                        <div class="gc-body">
                            <div class="gn"><?= esc($svc['title']) ?></div>
                            <div class="gc"><?= !empty($svc['price']) ? 'from £' . number_format((float)$svc['price'], 2) : 'Price on request' ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>
</div>
</main>
<?= $this->include('footer') ?>
