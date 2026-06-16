<?= $this->include('header') ?>
<link rel="stylesheet" href="/assets/css/browse-services.css">
<?php
/* ── Category icon + image helpers ─────────────────────────────────── */
function bsCatIcon(string $name): string {
    $n = strtolower($name);
    if (str_contains($n, 'venue'))                                    return 'fa-building';
    if (str_contains($n, 'cater') || str_contains($n, 'drink') || str_contains($n, 'bar') || str_contains($n, 'food')) return 'fa-champagne-glasses';
    if (str_contains($n, 'entertain') || str_contains($n, 'music') || str_contains($n, 'band') || str_contains($n, 'dj')) return 'fa-music';
    if (str_contains($n, 'photo') || str_contains($n, 'video') || str_contains($n, 'film'))  return 'fa-camera';
    if (str_contains($n, 'flower') || str_contains($n, 'floral') || str_contains($n, 'styling') || str_contains($n, 'decor')) return 'fa-seedling';
    if (str_contains($n, 'beauty') || str_contains($n, 'hair') || str_contains($n, 'makeup')) return 'fa-wand-magic-sparkles';
    if (str_contains($n, 'transport') || str_contains($n, 'car') || str_contains($n, 'travel')) return 'fa-car-side';
    if (str_contains($n, 'plan') || str_contains($n, 'coordinat') || str_contains($n, 'support')) return 'fa-clipboard-check';
    if (str_contains($n, 'cake') || str_contains($n, 'dessert') || str_contains($n, 'baker')) return 'fa-cake-candles';
    return 'fa-star';
}
function bsCatImg(string $name): string {
    $n = strtolower($name);
    if (str_contains($n, 'venue'))                                    return 'category-venues.jpg';
    if (str_contains($n, 'cater') || str_contains($n, 'drink'))      return 'category-catering-drinks.jpg';
    if (str_contains($n, 'entertain') || str_contains($n, 'music'))  return 'category-entertainment.jpg';
    if (str_contains($n, 'photo') || str_contains($n, 'video'))      return 'category-photography-video.jpg';
    if (str_contains($n, 'flower') || str_contains($n, 'floral') || str_contains($n, 'styling')) return 'category-flowers-styling.jpg';
    if (str_contains($n, 'beauty') || str_contains($n, 'hair'))      return 'category-beauty-personal-care.jpg';
    if (str_contains($n, 'transport') || str_contains($n, 'car'))    return 'category-transport-cars.jpg';
    if (str_contains($n, 'plan') || str_contains($n, 'support'))     return 'category-event-planning-support.jpg';
    return 'fallback-service-card.jpg';
}
function bsEventTheme(string $type): array {
    return match ($type) {
        'Wedding'       => ['icon' => 'fa-heart',        'color' => '#1C4A36'],
        'Corporate Event', 'Conference' => ['icon' => 'fa-briefcase',    'color' => '#236E4E'],
        'Birthday'      => ['icon' => 'fa-cake-candles', 'color' => '#B98C2A'],
        default         => ['icon' => 'fa-calendar-day', 'color' => '#1C4A36'],
    };
}
$activeEventId   = (int) ($activeEvent['id'] ?? 0);
$basketSvcIds    = array_map('intval', $basketServiceIds ?? []);
$isCustomer      = session()->get('role') === 'customer' && session()->has('user_id');
$showUnavailable = $showUnavailable ?? false;
$csrfField       = csrf_field();
$csrfToken       = csrf_hash();
$csrfName        = csrf_token();
?>

<main class="pp-page" id="pp-page">

    <!-- ╔══════════════════════════════════════════════════════════╗ -->
    <!-- ║  STEP 1 — event picker hero                             ║ -->
    <!-- ╚══════════════════════════════════════════════════════════╝ -->
    <div class="pp-hero" id="pp-hero">
        <div class="pp-eyebrow" style="margin-bottom:6px">Step 1 · Who are we shopping for?</div>
        <h1 class="pp-h1" style="margin-bottom:18px">Pick the event you're planning.</h1>

        <?php if ($isCustomer && !empty($customerEvents)): ?>
            <div class="ppa-picker-grid" id="pp-picker">
                <?php foreach ($customerEvents as $ev):
                    $theme   = bsEventTheme($ev['event_type'] ?? 'Event');
                    $isActive = (int)$ev['id'] === $activeEventId;
                    $bCount  = (int)($ev['basket_count'] ?? 0);
                    $bTotal  = (float)($ev['basket_total'] ?? 0);
                    $dateStr = !empty($ev['date']) ? date('d M Y', strtotime($ev['date'])) : 'Date TBC';
                    $guests  = (int)($ev['guest_count'] ?? 0);
                ?>
                    <div class="ppa-pick <?= $isActive ? 'active' : '' ?>"
                         data-event-id="<?= (int)$ev['id'] ?>"
                         data-basket-count="<?= $bCount ?>"
                         data-basket-total="<?= number_format($bTotal, 2) ?>"
                         data-event-title="<?= esc($ev['title']) ?>"
                         data-event-color="<?= esc($theme['color']) ?>"
                         data-event-icon="<?= esc($theme['icon']) ?>"
                         onclick="ppPickEvent(this)">
                        <div class="ppa-pick-top">
                            <span class="ppa-icon"
                                  style="background:<?= $isActive ? $theme['color'] : 'color-mix(in srgb,' . $theme['color'] . ', #fff 85%)' ?>;color:<?= $isActive ? '#fff' : $theme['color'] ?>">
                                <i class="fa-solid <?= $theme['icon'] ?>"></i>
                            </span>
                            <div class="ppa-text">
                                <div>
                                    <span class="ppa-type"><?= esc($ev['event_type'] ?? 'Event') ?></span>
                                    <?php if ($bCount > 0): ?>
                                        <span class="ppa-badge"><?= $bCount ?> added</span>
                                    <?php endif; ?>
                                </div>
                                <div class="ppa-title"><?= esc($ev['title']) ?></div>
                                <div class="ppa-meta">
                                    <span><i class="fa-solid fa-calendar-day"></i><?= esc($dateStr) ?></span>
                                    <?php if ($guests > 0): ?>
                                        <span><i class="fa-solid fa-user-group"></i><?= $guests ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="ppa-check"><?= $isActive ? '<i class="fa-solid fa-check"></i>' : '' ?></span>
                        </div>
                        <?php if ($isActive): ?>
                            <div class="ppa-foot" id="pp-foot-<?= (int)$ev['id'] ?>">
                                <?php if ($bCount === 0): ?>
                                    <span class="ppa-foot-empty">Basket empty — add services below</span>
                                <?php else: ?>
                                    <span>est. <strong>£<?= number_format($bTotal) ?></strong></span>
                                <?php endif; ?>
                                <a href="/event/basket/<?= (int)$ev['id'] ?>"
                                   class="pp-btn pp-btn-primary pp-btn-sm <?= $bCount === 0 ? 'disabled' : '' ?>"
                                   <?= $bCount === 0 ? 'onclick="return false" style="pointer-events:none"' : '' ?>>
                                    View basket <i class="fa-solid fa-arrow-right" style="font-size:11px"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <a href="/event/create" class="ppa-new-tile">
                    <i class="fa-solid fa-plus"></i>New event
                </a>
            </div>

        <?php elseif ($isCustomer): ?>
            <div style="background:#fff;border:1.5px solid var(--pp-line);border-radius:16px;padding:22px;max-width:460px">
                <div style="font-family:var(--pp-display);font-weight:600;font-size:18px;margin-bottom:4px">No events yet</div>
                <div class="pp-sub" style="margin-bottom:14px">Create an event and every service you add will collect under its basket.</div>
                <a href="/event/create" class="pp-btn pp-btn-primary"><i class="fa-solid fa-plus"></i>Create your first event</a>
            </div>

        <?php else: ?>
            <div style="background:#fff;border:1.5px solid var(--pp-line);border-radius:16px;padding:22px;max-width:460px">
                <div style="font-family:var(--pp-display);font-weight:600;font-size:18px;margin-bottom:4px">Sign in to track your basket</div>
                <div class="pp-sub" style="margin-bottom:14px">Log in to pick an event and save services to your basket as you browse.</div>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <a href="/login" class="pp-btn pp-btn-primary"><i class="fa-solid fa-arrow-right-to-bracket"></i>Log in</a>
                    <a href="/register" class="pp-btn pp-btn-ghost">Create account</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ╔══════════════════════════════════════════════════════════╗ -->
    <!-- ║  CONDENSED STRIP — appears on scroll                    ║ -->
    <!-- ╚══════════════════════════════════════════════════════════╝ -->
    <?php if ($isCustomer && !empty($activeEvent)): ?>
    <div class="ppa-condensed" id="pp-strip" style="display:none">
        <div class="ppa-condensed-inner">
            <span class="ppa-condensed-icon" id="pp-strip-icon"
                  style="background:<?= esc(bsEventTheme($activeEvent['event_type'] ?? 'Event')['color']) ?>;color:#fff">
                <i class="fa-solid <?= esc(bsEventTheme($activeEvent['event_type'] ?? 'Event')['icon']) ?>"></i>
            </span>
            <div class="ppa-condensed-text">
                <span class="ppa-condensed-label">Shopping for</span>
                <div class="ppa-condensed-title" id="pp-strip-title"><?= esc($activeEvent['title']) ?></div>
            </div>
            <div class="ppa-condensed-right">
                <div class="ppa-condensed-count" id="pp-strip-count">
                    <strong id="pp-strip-svc"><?= (int)($activeEvent['basket_count'] ?? 0) ?> service<?= (int)($activeEvent['basket_count'] ?? 0) !== 1 ? 's' : '' ?></strong>
                    <small>est. £<span id="pp-strip-total"><?= number_format((float)($activeEvent['basket_total'] ?? 0)) ?></span></small>
                </div>
                <a href="/event/basket/<?= $activeEventId ?>" id="pp-strip-basket" class="pp-btn pp-btn-primary pp-btn-sm">
                    <i class="fa-solid fa-basket-shopping"></i>
                    <span class="d-none d-md-inline">View basket</span>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ╔══════════════════════════════════════════════════════════╗ -->
    <!-- ║  STEP 2 — browse & add services                         ║ -->
    <!-- ╚══════════════════════════════════════════════════════════╝ -->
    <div class="pp-step2" id="pp-step2">

        <div class="pp-eyebrow" style="margin-bottom:12px">
            Step 2 · Add services<?php if (!empty($activeEvent)): ?> <span style="color:var(--pp-muted);font-weight:700;letter-spacing:0;text-transform:none" id="pp-step2-to">to <?= esc($activeEvent['title']) ?></span><?php endif; ?>
        </div>

        <!-- Toolbar: search + filters + sort -->
        <form class="pp-toolbar" id="pp-search-form" action="/browse-services" method="get">
            <?php if ($activeEventId): ?>
                <input type="hidden" name="event_id" value="<?= $activeEventId ?>">
            <?php endif; ?>
            <div class="pp-search" style="flex:1;min-width:240px">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" name="q" placeholder="Search services, vendors, styles…"
                       value="<?= esc($searchQuery ?? '') ?>" autocomplete="off" id="pp-q">
            </div>
            <select name="sort" class="pp-btn pp-btn-ghost" onchange="this.form.submit()" style="appearance:none;padding-right:14px">
                <option value="newest"     <?= ($selectedSort ?? 'newest') === 'newest'     ? 'selected' : '' ?>>Recommended</option>
                <option value="price_asc"  <?= ($selectedSort ?? '') === 'price_asc'        ? 'selected' : '' ?>>Price: low → high</option>
                <option value="price_desc" <?= ($selectedSort ?? '') === 'price_desc'       ? 'selected' : '' ?>>Price: high → low</option>
                <option value="title"      <?= ($selectedSort ?? '') === 'title'            ? 'selected' : '' ?>>Title A–Z</option>
            </select>
        </form>

        <!-- Category chips -->
        <div style="margin-bottom:16px;overflow-x:auto;padding-bottom:4px">
            <div class="pp-chips" id="pp-chips">
                <button class="pp-chip <?= empty($selectedCategory) ? 'on' : '' ?>"
                        data-cat="" onclick="ppFilterCat(this)">
                    <i class="fa-solid fa-border-all"></i>All
                </button>
                <?php foreach ($rootCategories as $cat): ?>
                    <button class="pp-chip <?= ((string)($selectedCategory ?? '')) === (string)$cat['id'] ? 'on' : '' ?>"
                            data-cat="<?= (int)$cat['id'] ?>"
                            onclick="ppFilterCat(this)">
                        <i class="fa-solid <?= esc(bsCatIcon($cat['name'])) ?>"></i><?= esc($cat['name']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Results heading -->
        <div class="pp-results-head">
            <div>
                <div class="pp-h2" id="pp-results-title">
                    <?php
                    $activeChipName = 'All services';
                    if (!empty($selectedCategory)) {
                        foreach ($rootCategories as $cat) {
                            if ((string)$cat['id'] === (string)$selectedCategory) {
                                $activeChipName = esc($cat['name']);
                                break;
                            }
                        }
                    }
                    echo $activeChipName;
                    ?>
                </div>
                <div class="pp-sub"><span id="pp-count"><?= count($services) ?></span> found</div>
            </div>
            <?php if ($isCustomer && $activeEventId): ?>
            <a href="<?= '/browse-services?' . http_build_query(array_merge($_GET, ['show_unavailable' => $showUnavailable ? '0' : '1'])) ?>"
               class="pp-btn <?= $showUnavailable ? 'pp-btn-primary' : 'pp-btn-ghost' ?>"
               style="font-size:13px;white-space:nowrap"
               title="<?= $showUnavailable ? 'Hide services that don\'t match your event' : 'Show services that don\'t match your event' ?>">
                <i class="fa-solid <?= $showUnavailable ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                <?= $showUnavailable ? 'Hide unavailable' : 'Show unavailable' ?>
            </a>
            <?php endif; ?>
        </div>

        <!-- Service card grid -->
        <?php if (!empty($services)): ?>
            <div class="pp-grid c3" id="pp-grid">
                <?php foreach ($services as $svc):
                    $inBasket        = in_array((int)$svc['id'], $basketSvcIds, true);
                    $unavailReasons  = $svc['_unavailable_reasons'] ?? [];
                    $isUnavail       = !empty($unavailReasons);
                    $catName         = $svc['category_name'] ?? '';
                    $price           = (float)($svc['price'] ?? 0);
                    $rating          = $svc['avg_rating'] ?? null;
                    $reviews         = (int)($svc['review_count'] ?? 0);
                    $location        = $svc['service_location'] ?? '';
                    $imgPath         = !empty($svc['images']) ? base_url(esc($svc['images'][0]['thumbnail_path'] ?? $svc['images'][0]['image_path'] ?? '')) : base_url('assets/images/' . bsCatImg($catName));
                    $fallback        = base_url('assets/images/fallback-service-card.jpg');
                    $catId           = (int)($svc['category_id'] ?? 0);
                ?>
                    <article class="pp-card<?= $isUnavail ? ' pp-card--unavailable' : '' ?>"
                             data-cat="<?= $catId ?>"
                             data-title="<?= esc(strtolower($svc['title'])) ?>">
                        <div class="pp-card-media">
                            <img src="<?= $imgPath ?>"
                                 alt="<?= esc($svc['title']) ?>"
                                 loading="lazy"
                                 onerror="this.onerror=null;this.src='<?= $fallback ?>'">
                            <span class="pp-card-cat"><?= esc($catName) ?></span>
                            <?php if ($isUnavail): ?>
                                <span class="pp-card-unavail-badge">
                                    <i class="fa-solid fa-circle-xmark"></i> Not compatible
                                </span>
                            <?php else: ?>
                            <button class="pp-card-fav <?= 'fav-' . (int)$svc['id'] ?>"
                                    onclick="ppToggleFav(this,<?= (int)$svc['id'] ?>)"
                                    aria-label="Save to favourites">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="pp-card-body">
                            <a href="/service/view/<?= (int)$svc['id'] ?>"
                               class="pp-card-title-link"
                               aria-label="<?= esc($svc['title']) ?> — view details">
                                <h3 class="pp-card-title"><?= esc($svc['title']) ?></h3>
                            </a>
                            <?php if ($location): ?>
                                <div class="pp-card-loc"><i class="fa-solid fa-location-dot"></i><?= esc($location) ?></div>
                            <?php endif; ?>
                            <?php if ($isUnavail): ?>
                                <div class="pp-unavail-reasons">
                                    <?php foreach ($unavailReasons as $reason): ?>
                                        <div class="pp-unavail-reason"><i class="fa-solid fa-triangle-exclamation"></i><?= esc($reason) ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <?php if ($rating !== null && $reviews > 0): ?>
                                    <div class="pp-rating">
                                        <i class="fa-solid fa-star"></i>
                                        <b><?= number_format($rating, 1) ?></b>
                                        <span>(<?= $reviews ?>)</span>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <div class="pp-card-foot">
                                <div class="pp-price">
                                    <?php if ($price > 0): ?>
                                        £<?= number_format($price) ?> <small>from</small>
                                    <?php else: ?>
                                        <small style="font-size:14px">Price on request</small>
                                    <?php endif; ?>
                                </div>
                                <?php if ($isCustomer && $activeEventId && $inBasket && !$isUnavail): ?>
                                    <span class="pp-added-badge"><i class="fa-solid fa-check"></i>Added</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:60px 20px">
                <i class="fa-solid fa-magnifying-glass fa-3x" style="color:var(--pp-sand);margin-bottom:16px;display:block"></i>
                <div style="font-family:var(--pp-display);font-size:20px;font-weight:600;margin-bottom:8px">No services found</div>
                <div class="pp-sub">Try fewer filters or a different search term.</div>
                <?php if (!empty($searchQuery) || !empty($selectedCategory)): ?>
                    <a href="/browse-services<?= $activeEventId ? '?event_id=' . $activeEventId : '' ?>" class="pp-btn pp-btn-ghost" style="margin-top:18px">Clear filters</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</main>

<!-- Toast -->
<div class="pp-toast" id="pp-toast"><i class="fa-solid fa-circle-check"></i><span id="pp-toast-msg"></span></div>

<script>
/* ══ state ══════════════════════════════════════════════════════════ */
var ppActiveEventId    = <?= $activeEventId ?: 'null' ?>;
var ppActiveEventTitle = <?= $activeEventId ? json_encode($activeEvent['title'] ?? '') : 'null' ?>;
var ppBasketServiceIds = <?= json_encode(array_map('intval', $basketSvcIds)) ?>;
var ppCsrfName         = '<?= $csrfName ?>';
var ppCsrfToken        = '<?= $csrfToken ?>';
var ppEvents           = <?= json_encode(array_map(fn($e) => [
    'id'    => (int)$e['id'],
    'title' => $e['title'],
    'color' => bsEventTheme($e['event_type'] ?? 'Event')['color'],
    'icon'  => bsEventTheme($e['event_type'] ?? 'Event')['icon'],
    'basketCount' => (int)($e['basket_count'] ?? 0),
    'basketTotal' => (float)($e['basket_total'] ?? 0),
], $customerEvents ?? [])) ?>;

/* ══ event picking ══════════════════════════════════════════════════ */
function ppPickEvent(card) {
    if (card.classList.contains('active')) return;
    var eid = parseInt(card.dataset.eventId, 10);

    // Visual: deactivate old card
    document.querySelectorAll('.ppa-pick.active').forEach(function(c) {
        c.classList.remove('active');
        var icon = c.querySelector('.ppa-icon');
        var check = c.querySelector('.ppa-check');
        var foot = c.querySelector('.ppa-foot');
        if (icon) { icon.style.background = 'color-mix(in srgb,' + c.dataset.eventColor + ', #fff 85%)'; icon.style.color = c.dataset.eventColor; }
        if (check) { check.innerHTML = ''; check.style.background = ''; check.style.border = '2px solid var(--pp-line)'; }
        if (foot) foot.remove();
    });

    // Visual: activate new card
    card.classList.add('active');
    var icon = card.querySelector('.ppa-icon');
    var check = card.querySelector('.ppa-check');
    if (icon) { icon.style.background = card.dataset.eventColor; icon.style.color = '#fff'; }
    if (check) { check.innerHTML = '<i class="fa-solid fa-check"></i>'; check.style.background = 'var(--pp-terracotta)'; check.style.border = 'none'; }

    // Add basket footer
    var bCount = parseInt(card.dataset.basketCount || 0, 10);
    var bTotal = card.dataset.basketTotal || '0';
    var foot = document.createElement('div');
    foot.className = 'ppa-foot';
    foot.innerHTML = bCount === 0
        ? '<span class="ppa-foot-empty">Basket empty — add services below</span>'
        : '<span>est. <strong>£' + bTotal + '</strong></span>';
    foot.innerHTML += '<a href="/event/basket/' + eid + '" class="pp-btn pp-btn-primary pp-btn-sm' + (bCount === 0 ? ' disabled' : '') + '"' + (bCount === 0 ? ' onclick="return false" style="pointer-events:none"' : '') + '>View basket <i class="fa-solid fa-arrow-right" style="font-size:11px"></i></a>';
    card.appendChild(foot);

    // Update strip + step 2 heading
    ppActiveEventId = eid;
    ppActiveEventTitle = card.dataset.eventTitle;
    ppUpdateStrip();
    var step2to = document.getElementById('pp-step2-to');
    if (step2to) step2to.textContent = 'to ' + ppActiveEventTitle;

    // Persist to session (fire-and-forget)
    fetch('/profile/set-active-event/' + eid, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).catch(function(){});
}

/* ══ scroll strip ═══════════════════════════════════════════════════ */
var ppScrolled = false;
window.addEventListener('scroll', function() {
    var hero = document.getElementById('pp-hero');
    var strip = document.getElementById('pp-strip');
    if (!strip || !hero) return;
    var heroBottom = hero.getBoundingClientRect().bottom;
    if (!ppScrolled && heroBottom < 10) {
        ppScrolled = true;
        strip.style.display = '';
    } else if (ppScrolled && heroBottom > 50) {
        ppScrolled = false;
        strip.style.display = 'none';
    }
}, { passive: true });

function ppUpdateStrip() {
    var ev = ppEvents.find(function(e) { return e.id === ppActiveEventId; });
    if (!ev) return;
    var title  = document.getElementById('pp-strip-title');
    var icon   = document.getElementById('pp-strip-icon');
    var svc    = document.getElementById('pp-strip-svc');
    var total  = document.getElementById('pp-strip-total');
    var basket = document.getElementById('pp-strip-basket');
    if (title)  title.textContent = ev.title;
    if (icon)   { icon.style.background = ev.color; icon.innerHTML = '<i class="fa-solid ' + ev.icon + '"></i>'; }
    if (svc)    svc.textContent = ev.basketCount + ' service' + (ev.basketCount !== 1 ? 's' : '');
    if (total)  total.textContent = Math.round(ev.basketTotal).toLocaleString('en-GB');
    if (basket) basket.href = '/event/basket/' + ev.id;
}

/* ══ category chip filtering (client-side) ══════════════════════════ */
function ppFilterCat(chip) {
    document.querySelectorAll('.pp-chip').forEach(function(c) { c.classList.remove('on'); });
    chip.classList.add('on');
    var catId = chip.dataset.cat ? parseInt(chip.dataset.cat, 10) : null;
    var cards = document.querySelectorAll('#pp-grid .pp-card');
    var visible = 0;
    cards.forEach(function(card) {
        var show = !catId || parseInt(card.dataset.cat, 10) === catId;
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    var countEl = document.getElementById('pp-count');
    var titleEl = document.getElementById('pp-results-title');
    if (countEl) countEl.textContent = visible;
    if (titleEl) titleEl.textContent = chip.dataset.cat ? chip.textContent.trim() : 'All services';
}

/* ══ add to event (AJAX) ════════════════════════════════════════════ */
function ppAdd(btn) {
    if (!ppActiveEventId) { window.location.href = '/event/create'; return; }
    if (btn.classList.contains('added')) return;
    var svcId    = parseInt(btn.dataset.serviceId, 10);
    var svcTitle = btn.dataset.serviceTitle;
    btn.disabled = true;

    var body = new FormData();
    body.append(ppCsrfName, ppCsrfToken);
    body.append('event_id', ppActiveEventId);

    fetch('/event/add-to-basket/' + svcId, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: body,
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.ok) {
            btn.classList.add('added');
            btn.innerHTML = '<i class="fa-solid fa-check"></i>Added';
            ppBasketServiceIds.push(svcId);

            // Update basket count in the picked card and strip
            var ev = ppEvents.find(function(e) { return e.id === ppActiveEventId; });
            if (ev && !data.already) {
                ev.basketCount++;
                var card = document.querySelector('.ppa-pick.active');
                if (card) {
                    card.dataset.basketCount = ev.basketCount;
                    var badge = card.querySelector('.ppa-badge');
                    if (!badge) {
                        badge = document.createElement('span'); badge.className = 'ppa-badge';
                        card.querySelector('.ppa-type').after(badge);
                    }
                    badge.textContent = ev.basketCount + ' added';
                    var foot = card.querySelector('.ppa-foot');
                    if (foot) {
                        foot.querySelector('.ppa-foot-empty') && (foot.querySelector('.ppa-foot-empty').remove());
                        var span = foot.querySelector('span:not(.ppa-foot-empty)') || document.createElement('span');
                        span.innerHTML = 'est. <strong>£' + Math.round(ev.basketTotal).toLocaleString('en-GB') + '</strong>';
                        foot.prepend(span);
                        var viewBtn = foot.querySelector('.pp-btn');
                        if (viewBtn) { viewBtn.classList.remove('disabled'); viewBtn.removeAttribute('onclick'); viewBtn.style.pointerEvents = ''; }
                    }
                }
                ppUpdateStrip();
            }
            ppToast('Added "' + svcTitle + '" to ' + ppActiveEventTitle);
        } else {
            btn.disabled = false;
            ppToast(data.message || 'Could not add service.');
        }
        // Refresh CSRF token
        if (data._token) ppCsrfToken = data._token;
    })
    .catch(function() { btn.disabled = false; });
}

/* ══ favourite toggle (UI only) ════════════════════════════════════ */
function ppToggleFav(btn, svcId) {
    btn.classList.toggle('on');
    btn.innerHTML = btn.classList.contains('on')
        ? '<i class="fa-solid fa-heart"></i>'
        : '<i class="fa-regular fa-heart"></i>';
}

/* ══ toast ══════════════════════════════════════════════════════════ */
var ppToastTimer;
function ppToast(msg) {
    var t = document.getElementById('pp-toast');
    var m = document.getElementById('pp-toast-msg');
    if (!t || !m) return;
    m.textContent = msg;
    t.classList.add('show');
    clearTimeout(ppToastTimer);
    ppToastTimer = setTimeout(function() { t.classList.remove('show'); }, 2200);
}
</script>

<?= $this->include('footer') ?>
