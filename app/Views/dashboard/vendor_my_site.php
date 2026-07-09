<?= $this->include('header') ?>
<main class="page-main">
<div class="dashboard-wrapper">
<div class="container">
    <?= $this->include('dashboard/_vendor_tabs') ?>
    <?= $this->include('dashboard/_flash_alerts') ?>

    <?php if ($site === null): ?>
        <div class="fye-page">
            <div class="fye-page-head">
                <div>
                    <h1 class="fye-page-title">My site</h1>
                    <p class="fye-page-sub">Your own booking website — <em>yourname</em>.partysmith.co.uk.</p>
                </div>
            </div>
            <div class="fye-card">
                <h2><i class="fa-solid fa-globe"></i> Your storefront isn't set up yet</h2>
                <p class="sub" style="margin-bottom: 16px;">
                    A white-label site gives you your own branded page to take bookings — no marketplace,
                    just your business. We set the address up for you as part of onboarding.
                </p>
                <a href="/contact" class="fye-btn primary">Talk to us about a site</a>
            </div>
        </div>
    <?php else:
        $mono = '';
        foreach (preg_split('/\s+/', trim((string) ($site['business_name'] ?? ''))) as $w) {
            if ($w !== '' && strlen($mono) < 2) { $mono .= strtoupper(mb_substr($w, 0, 1)); }
        }
        if ($mono === '') { $mono = 'P'; }
        $themes       = \App\Libraries\StorefrontThemes::all();
        $currentTheme = \App\Libraries\StorefrontThemes::resolve($site['theme'] ?? null);
        $cur          = $themes[$currentTheme];
        $logoUrl      = ! empty($site['logo_path']) ? '/' . ltrim((string) $site['logo_path'], '/') : '';
        $siteUrl      = esc($site['subdomain']) . '.partysmith.co.uk';
    ?>
        <div class="fye-page">
            <div class="fye-page-head">
                <div>
                    <h1 class="fye-page-title">My site</h1>
                    <p class="fye-page-sub">
                        <a href="https://<?= $siteUrl ?>" target="_blank" rel="noopener" style="color:var(--fye-terra);font-weight:600;text-decoration:none;"><?= $siteUrl ?></a>
                        <span class="fye-pill" style="background:var(--fye-sage-tint);color:var(--fye-sage);margin-left:6px;">● Live</span>
                    </p>
                </div>
            </div>

            <form method="post" action="/profile/my-site" enctype="multipart/form-data" id="mySiteForm">
                <?= csrf_field() ?>
                <div class="mysite-grid">

                    <!-- ============ Editor ============ -->
                    <div class="fye-card">
                        <div class="fye-card-head">
                            <h2><i class="fa-solid fa-wand-magic-sparkles"></i> Appearance</h2>
                        </div>

                        <label class="mysite-label">Colour theme</label>
                        <p class="mysite-hint">A curated palette for your whole site — storefront through checkout.</p>
                        <div class="mysite-themes" role="radiogroup" aria-label="Colour theme">
                            <?php foreach ($themes as $key => $t): ?>
                                <label class="mysite-theme<?= $key === $currentTheme ? ' is-active' : '' ?>"
                                    data-accent="<?= esc($t['accent'], 'attr') ?>" data-bg="<?= esc($t['bg'], 'attr') ?>" data-ink="<?= esc($t['ink'], 'attr') ?>" data-border="<?= esc($t['border'], 'attr') ?>">
                                    <input type="radio" name="theme" value="<?= esc($key, 'attr') ?>" <?= $key === $currentTheme ? 'checked' : '' ?>>
                                    <span class="sw" aria-hidden="true">
                                        <span style="background: <?= esc($t['bg'], 'attr') ?>;"></span>
                                        <span style="background: <?= esc($t['accent'], 'attr') ?>;"></span>
                                        <span style="background: <?= esc($t['ink'], 'attr') ?>;"></span>
                                    </span>
                                    <span class="nm"><?= esc($t['label']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <label class="mysite-label" style="margin-top:22px;">Logo</label>
                        <div class="mysite-logo-row">
                            <div class="mysite-logo-current" id="logoPreview" style="--pv-mono-bg: <?= esc($cur['accent'], 'attr') ?>;">
                                <?php if ($logoUrl !== ''): ?>
                                    <img src="<?= esc($logoUrl, 'attr') ?>" alt="Current logo">
                                <?php else: ?>
                                    <span class="mysite-mono" id="logoMono"><?= esc($mono) ?></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="mysite-hint" style="margin:0 0 6px;">
                                    <?= $logoUrl !== '' ? 'Upload a new logo to replace it.' : 'No logo yet — we made you a monogram. Upload one any time.' ?>
                                </p>
                                <input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="mysite-file">
                            </div>
                        </div>

                        <label class="mysite-label" for="aboutText" style="margin-top:22px;">About your business</label>
                        <textarea name="about_text" id="aboutText" class="mysite-textarea" rows="4"
                            maxlength="1000" placeholder="Tell customers who you are in a sentence or two."><?= esc($site['about_text'] ?? '') ?></textarea>

                        <label class="mysite-label" for="phoneInput" style="margin-top:18px;">Phone shown on your site</label>
                        <input type="text" name="phone" id="phoneInput" class="mysite-input" maxlength="32"
                            value="<?= esc($site['phone'] ?? '', 'attr') ?>" placeholder="e.g. 07700 900123">

                        <label class="mysite-label" style="margin-top:22px;">Recent events gallery</label>
                        <p class="mysite-hint">Separate from your service photos — shown in the "Recent events" band on your storefront. The band is hidden entirely until you add at least one photo here.</p>
                        <?php if (! empty($gallery)): ?>
                            <div class="mysite-gallery">
                                <?php foreach ($gallery as $g): ?>
                                    <div class="mysite-gal-item">
                                        <img src="/<?= esc(ltrim((string) $g['image_path'], '/'), 'attr') ?>" alt="">
                                        <label class="rm"><input type="checkbox" name="remove_gallery[]" value="<?= (int) $g['id'] ?>"> Remove</label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="gallery[]" accept="image/png,image/jpeg,image/webp" multiple class="mysite-file">

                        <button type="submit" class="fye-btn primary block lg" style="margin-top:24px;">Publish changes</button>
                    </div>

                    <!-- ============ Live preview ============ -->
                    <div>
                        <div class="mysite-preview-wrap">
                            <div class="mysite-preview-head">
                                <span>Live preview</span>
                                <span class="fye-pill" style="background:var(--fye-slate-tint);color:var(--fye-slate);">Updates instantly</span>
                            </div>
                            <div class="mysite-preview" id="sitePreview"
                                style="--pv-primary: <?= esc($cur['accent'], 'attr') ?>; --pv-accent: <?= esc($cur['accent'], 'attr') ?>; --pv-bg: <?= esc($cur['bg'], 'attr') ?>; --pv-ink: <?= esc($cur['ink'], 'attr') ?>; --pv-border: <?= esc($cur['border'], 'attr') ?>;">
                                <div class="pv-topbar">
                                    <span class="pv-mono" id="pvMono"><?= esc($mono) ?></span>
                                    <span>
                                        <span class="pv-name"><?= esc($site['business_name']) ?></span>
                                        <span class="pv-phone" id="pvPhone"><?= esc($site['phone'] ?? '') ?></span>
                                    </span>
                                </div>
                                <div class="pv-body">
                                    <p class="pv-eyebrow">Bouncy castle hire · Stockport</p>
                                    <h3 class="pv-headline"><?= esc($site['business_name']) ?></h3>
                                    <p class="pv-rating"><span class="pv-stars">★★★★★</span> 4.9 · 132 bookings</p>
                                    <div class="pv-cta">See prices for this date</div>
                                    <div class="pv-card">
                                        <div class="pv-card-img"></div>
                                        <div class="pv-card-body">
                                            <span class="pv-card-name">Party Palace</span>
                                            <span class="pv-card-price">from £85</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="pv-foot">Powered by Partysmith</div>
                            </div>
                            <p class="mysite-hint" style="text-align:center;margin-top:10px;">A sample of how customers see your site.</p>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <style>
            .mysite-grid { display: grid; grid-template-columns: 1fr; gap: 22px; }
            @media (min-width: 900px) { .mysite-grid { grid-template-columns: 1.05fr 0.95fr; align-items: start; } }
            .mysite-label { display: block; font-weight: 700; font-size: 14px; color: var(--fye-ink); }
            .mysite-hint { color: var(--fye-ink-2); font-size: 12.5px; margin: 2px 0 8px; }
            .mysite-themes { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
            @media (min-width: 560px) { .mysite-themes { grid-template-columns: 1fr 1fr 1fr; } }
            .mysite-theme { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1.5px solid var(--fye-line-2); border-radius: var(--fye-r-sm); background: #fff; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
            .mysite-theme:hover { border-color: var(--fye-ink-3); }
            .mysite-theme.is-active { border-color: var(--fye-terra); box-shadow: 0 0 0 3px var(--fye-terra-tint, rgba(190,110,60,.14)); }
            .mysite-theme input { position: absolute; opacity: 0; pointer-events: none; }
            .mysite-theme .sw { display: inline-flex; border-radius: 50%; overflow: hidden; width: 22px; height: 22px; flex: none; box-shadow: inset 0 0 0 1px rgba(0,0,0,.08); }
            .mysite-theme .sw span { display: block; width: 33.34%; height: 100%; }
            .mysite-theme .nm { font-weight: 700; font-size: 12.5px; color: var(--fye-ink); line-height: 1.2; }
            .mysite-hex, .mysite-input, .mysite-textarea {
                font: inherit; padding: 10px 12px; border: 1px solid var(--fye-line-2);
                border-radius: var(--fye-r-sm); background: #fff; color: var(--fye-ink); width: 100%;
            }
            .mysite-hex { max-width: 130px; text-transform: lowercase; }
            .mysite-textarea { resize: vertical; line-height: 1.5; }
            .mysite-hex:focus, .mysite-input:focus, .mysite-textarea:focus { outline: none; border-color: var(--fye-terra); }
            .mysite-logo-row { display: flex; align-items: center; gap: 14px; }
            .mysite-logo-current { width: 56px; height: 56px; border-radius: 12px; overflow: hidden; flex: none; display: flex; align-items: center; justify-content: center; background: var(--fye-card-warm); border: 1px solid var(--fye-line); }
            .mysite-logo-current img { width: 100%; height: 100%; object-fit: cover; }
            .mysite-mono { font-weight: 800; font-size: 22px; letter-spacing: -0.02em; color: #fff; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: var(--pv-mono-bg, #1C4A36); }
            .mysite-file { font-size: 13px; color: var(--fye-ink-2); }
            .mysite-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(88px, 1fr)); gap: 10px; margin-bottom: 10px; }
            .mysite-gal-item { display: flex; flex-direction: column; gap: 5px; }
            .mysite-gal-item img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 8px; border: 1px solid var(--fye-line); }
            .mysite-gal-item .rm { display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; color: var(--fye-ink-2); cursor: pointer; }

            /* Live preview */
            .mysite-preview-wrap { position: sticky; top: 90px; }
            .mysite-preview-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; font-weight: 700; font-size: 13px; color: var(--fye-ink-2); }
            .mysite-preview { border: 1px solid var(--fye-line-2); border-radius: 20px; overflow: hidden; background: var(--pv-bg, #fff); box-shadow: var(--fye-shadow); max-width: 380px; margin: 0 auto; transition: background .2s; }
            .pv-topbar { display: flex; align-items: center; gap: 10px; padding: 14px 16px; border-bottom: 1px solid var(--pv-border, var(--fye-line)); }
            .pv-mono { width: 34px; height: 34px; border-radius: 10px; background: var(--pv-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px; flex: none; }
            .pv-name { display: block; font-weight: 700; font-size: 14px; color: var(--pv-ink, var(--fye-ink)); }
            .pv-phone { display: block; font-size: 12px; color: var(--fye-ink-2); }
            .pv-body { padding: 18px 16px 8px; }
            .pv-eyebrow { font-size: 10px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; color: var(--pv-accent); margin: 0 0 6px; }
            .pv-headline { font-weight: 700; font-size: 22px; line-height: 1.1; letter-spacing: -0.01em; margin: 0 0 8px; color: var(--pv-ink, var(--fye-ink)); }
            .pv-rating { font-size: 12.5px; color: var(--fye-ink-2); margin: 0 0 14px; }
            .pv-stars { color: var(--pv-accent); letter-spacing: 1px; }
            .pv-cta { background: var(--pv-primary); color: #fff; text-align: center; font-weight: 800; font-size: 13px; padding: 12px; border-radius: 100px; }
            .pv-card { display: flex; gap: 10px; align-items: center; margin: 16px 0 4px; border: 1px solid var(--pv-border, var(--fye-line)); border-radius: 12px; padding: 8px; }
            .pv-card-img { width: 52px; height: 52px; border-radius: 8px; background: linear-gradient(135deg, var(--pv-primary), var(--pv-accent)); flex: none; opacity: 0.85; }
            .pv-card-name { display: block; font-weight: 700; font-size: 13px; color: var(--pv-ink, var(--fye-ink)); }
            .pv-card-price { display: block; font-weight: 800; font-size: 13px; color: var(--pv-primary); }
            .pv-foot { text-align: center; font-size: 11px; color: var(--fye-ink-3); padding: 12px; border-top: 1px solid var(--pv-border, var(--fye-line)); }
        </style>

        <script>
            (function () {
                var form = document.getElementById('mySiteForm');
                if (!form) return;
                var preview = document.getElementById('sitePreview');
                var logoTile = document.getElementById('logoPreview');

                // Theme selection drives the live preview + swatch highlight.
                function applyTheme(card) {
                    document.querySelectorAll('.mysite-theme').forEach(function (c) { c.classList.toggle('is-active', c === card); });
                    var input = card.querySelector('input[type=radio]');
                    if (input) input.checked = true;
                    preview.style.setProperty('--pv-primary', card.dataset.accent);
                    preview.style.setProperty('--pv-accent', card.dataset.accent);
                    preview.style.setProperty('--pv-bg', card.dataset.bg);
                    preview.style.setProperty('--pv-ink', card.dataset.ink);
                    preview.style.setProperty('--pv-border', card.dataset.border);
                    if (logoTile) logoTile.style.setProperty('--pv-mono-bg', card.dataset.accent);
                }
                document.querySelectorAll('.mysite-theme').forEach(function (card) {
                    card.addEventListener('click', function () { applyTheme(card); });
                });

                // Live-preview the business phone and an uploaded logo.
                var phoneInput = document.getElementById('phoneInput');
                var pvPhone = document.getElementById('pvPhone');
                if (phoneInput && pvPhone) {
                    phoneInput.addEventListener('input', function () { pvPhone.textContent = phoneInput.value; });
                }
                var fileInput = form.querySelector('input[type=file][name=logo]');
                var pvMono = document.getElementById('pvMono');
                if (fileInput && pvMono) {
                    fileInput.addEventListener('change', function () {
                        var f = fileInput.files && fileInput.files[0];
                        if (!f) return;
                        var url = URL.createObjectURL(f);
                        pvMono.style.background = 'center/cover no-repeat url(' + JSON.stringify(url) + ')';
                        pvMono.textContent = '';
                    });
                }
            })();
        </script>
    <?php endif; ?>
</div>
</div>
</main>
<?= $this->include('footer') ?>
