<?= $this->include('header') ?>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/service-view.css">
<style>body { background: #F6F1EB; }</style>

<?php
/* ── Inline SVG icon helper ─────────────────────────────────────────────── */
function svIcon(string $name, string $class = '', string $style = ''): string
{
    static $icons = [
        'check'    => ['fill' => 'none',         'd' => '<path d="M20 6L9 17l-5-5"/>'],
        'checkbold'=> ['fill' => 'none',         'd' => '<path d="M20 6L9 17l-5-5" stroke-width="3"/>'],
        'star'     => ['fill' => 'currentColor', 'stroke' => 'none', 'd' => '<path d="M12 2l2.9 6.3 6.8.7-5.1 4.6 1.4 6.7L12 17.8 6 20.6l1.4-6.7L2.3 9l6.8-.7z"/>'],
        'users'    => ['fill' => 'none',         'd' => '<circle cx="9" cy="8" r="3.2"/><path d="M3.5 19a5.5 5.5 0 0111 0M16 6.2a3 3 0 010 5.6M21 19a5 5 0 00-3.5-4.8"/>'],
        'clock'    => ['fill' => 'none',         'd' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'],
        'pin'      => ['fill' => 'none',         'd' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0z"/><circle cx="12" cy="10" r="2.6"/>'],
        'shield'   => ['fill' => 'none',         'd' => '<path d="M12 2l8 3v6c0 5-3.5 8.5-8 11-4.5-2.5-8-6-8-11V5z"/><path d="M9 12l2 2 4-4"/>'],
        'bolt'     => ['fill' => 'none',         'd' => '<path d="M13 2L4 14h7l-1 8 9-12h-7z"/>'],
        'chat'     => ['fill' => 'none',         'd' => '<path d="M21 12a8 8 0 01-11.5 7.2L4 21l1.8-5A8 8 0 1121 12z"/>'],
        'heart'    => ['fill' => 'none',         'd' => '<path d="M12 20s-7-4.5-9.5-9C1 8 2.5 4.5 6 4.5c2 0 3.2 1.2 4 2.3.8-1.1 2-2.3 4-2.3 3.5 0 5 3.5 3.5 6.5C19 15.5 12 20 12 20z"/>'],
        'share'    => ['fill' => 'none',         'd' => '<circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="6" r="2.5"/><circle cx="18" cy="18" r="2.5"/><path d="M8.2 10.9l7.6-3.8M8.2 13.1l7.6 3.8"/>'],
        'calendar' => ['fill' => 'none',         'd' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/>'],
        'tool'     => ['fill' => 'none',         'd' => '<path d="M14 7a4 4 0 01-5 5l-5 5 2 2 5-5a4 4 0 005-5l-2.5 2.5L13 11l1.5-1.5z"/>'],
        'sun'      => ['fill' => 'none',         'd' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4 12H2M22 12h-2M5 5l1.5 1.5M17.5 17.5L19 19M19 5l-1.5 1.5M6.5 17.5L5 19"/>'],
        'truck'    => ['fill' => 'none',         'd' => '<path d="M3 7h11v8H3zM14 10h4l3 3v2h-7"/><circle cx="7" cy="18" r="1.8"/><circle cx="17" cy="18" r="1.8"/>'],
        'music'    => ['fill' => 'none',         'd' => '<path d="M9 18V5l11-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="17" cy="16" r="3"/>'],
        'arrow'    => ['fill' => 'none',         'd' => '<path d="M5 12h14M13 6l6 6-6 6"/>'],
    ];
    $p      = $icons[$name] ?? $icons['check'];
    $fill   = $p['fill'];
    $stroke = ($p['stroke'] ?? 'currentColor') !== 'none' ? ' stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"' : '';
    $ca     = $class ? ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"' : '';
    $sa     = $style ? ' style="' . htmlspecialchars($style, ENT_QUOTES) . '"' : '';
    return '<svg' . $ca . $sa . ' viewBox="0 0 24 24" fill="' . $fill . '"' . $stroke . '>' . $p['d'] . '</svg>';
}
?>

<?php if (session()->getFlashdata('error')): ?>
    <div style="max-width:1140px;margin:0 auto;padding:16px 40px 0">
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('success')): ?>
    <div style="max-width:1140px;margin:0 auto;padding:16px 40px 0">
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    </div>
<?php endif; ?>

<?php if (isset($service)): ?>
<?php
/* ── Pricing type detection ─────────────────────────────────────────────── */
$pricingType = $privatePricing['pricing_type'] ?? null;
if ($pricingType === null) {
    if (!empty($guestPricing))        $pricingType = 'guest_based_pricing';
    elseif (!empty($durationPricing)) $pricingType = 'custom_duration_pricing';
    elseif (!empty($tieredPackages))  $pricingType = 'tiered_packages_pricing';
    elseif (!empty($quantityTiers ?? $quantityPricing ?? null)) $pricingType = 'quantity_based_pricing';
}
$quantityTiers  = $quantityTiers ?? (isset($quantityPricing) && is_array($quantityPricing) ? [$quantityPricing] : []);
$showGuest      = $pricingType === 'guest_based_pricing';
$showDuration   = $pricingType === 'custom_duration_pricing';
$showPackages   = $pricingType === 'tiered_packages_pricing';
$showQuantity   = $pricingType === 'quantity_based_pricing' && $quantityTiers !== [];
$isCustomQuote  = (($privatePricing['pricing_type'] ?? '') === 'custom_quote');
$isOwnService   = session()->has('user_id') && session()->get('role') === 'vendor'
                  && (int) $service['vendor_id'] === (int) session()->get('user_id');

/* ── Capacity string ────────────────────────────────────────────────────── */
$cap = '';
if (!empty($service['min_capacity']) && !empty($service['max_capacity'])) {
    $cap = (int) $service['min_capacity'] . '–' . (int) $service['max_capacity'] . ' guests';
} elseif (!empty($service['max_capacity'])) {
    $cap = 'Up to ' . (int) $service['max_capacity'] . ' guests';
} elseif (!empty($service['min_capacity'])) {
    $cap = 'From ' . (int) $service['min_capacity'] . ' guests';
}

/* ── Quantity limits ────────────────────────────────────────────────────── */
$qtyMin = 1; $qtyMax = null;
foreach ($quantityTiers as $qt) {
    $tMin   = max(1, (int) ($qt['min_quantity'] ?? 1));
    $qtyMin = min($qtyMin, $tMin);
    $tMaxRaw = $qt['max_quantity'] ?? null;
    if ($tMaxRaw !== null && $tMaxRaw !== '') {
        $tMax   = max($tMin, (int) $tMaxRaw);
        $qtyMax = $qtyMax === null ? $tMax : max($qtyMax, $tMax);
    } else {
        $qtyMax = null;
    }
}
$qtyDefault = $qtyMin;

/* ── Fulfillment type ───────────────────────────────────────────────────── */
$fulfillmentType = $location['fulfillment_type'] ?? 'in_person';

/* ── Specs for "Good to know" grid ─────────────────────────────────────── */
$io    = $service['indoor_outdoor'] ?? 'both';
$specs = [];
if ($cap !== '') {
    $specs[] = ['k' => 'Capacity', 'v' => $cap, 'icon' => 'users'];
}
if ($io === 'indoor') {
    $specs[] = ['k' => 'Setting', 'v' => 'Indoor only', 'icon' => 'sun'];
} elseif ($io === 'outdoor') {
    $specs[] = ['k' => 'Setting', 'v' => 'Outdoor only', 'icon' => 'sun'];
} else {
    $specs[] = ['k' => 'Setting', 'v' => 'Indoor & outdoor', 'icon' => 'sun'];
}
if (!empty($service['setup_minutes'])) {
    $sv = (int) $service['setup_minutes'] . ' min setup';
    if (!empty($service['breakdown_minutes'])) {
        $sv .= ' · ' . (int) $service['breakdown_minutes'] . ' min breakdown';
    }
    $specs[] = ['k' => 'Setup', 'v' => $sv, 'icon' => 'tool'];
}
if (!empty($service['min_notice_days'])) {
    $nd      = (int) $service['min_notice_days'];
    $specs[] = ['k' => 'Min. notice', 'v' => $nd . ' day' . ($nd === 1 ? '' : 's'), 'icon' => 'calendar'];
}
if ($fulfillmentType === 'postal' || $fulfillmentType === 'both') {
    $pf      = (float) ($location['postal_fee'] ?? 0);
    $specs[] = ['k' => 'Postage', 'v' => $pf === 0.0 ? 'Free' : '£' . number_format($pf, 2), 'icon' => 'truck'];
}

/* ── Requirements list ──────────────────────────────────────────────────── */
$reqs = [];
if (!empty($service['power_required']))          $reqs[] = 'Mains power required';
if (!empty($service['water_required']))          $reqs[] = 'Water access required';
if (!empty($service['vehicle_access_required'])) $reqs[] = 'Vehicle access required';
if (!empty($service['equipment_provided']))      $reqs[] = 'Supplier provides their own equipment';

/* ── Initial price for sidebar ──────────────────────────────────────────── */
$initialPrice = 0.0;
if ($showPackages && !empty($tieredPackages)) {
    $initialPrice = (float) ($tieredPackages[0]['package_price'] ?? $tieredPackages[0]['price'] ?? 0);
} elseif (!$isCustomQuote) {
    $initialPrice = (float) ($service['price'] ?? 0);
}

/* ── Gallery primary image ──────────────────────────────────────────────── */
$primaryImage = null;
foreach ($images as $img) {
    if (!empty($img['is_primary'])) { $primaryImage = $img; break; }
}
if ($primaryImage === null && !empty($images)) {
    $primaryImage = $images[0];
}
$fallback = base_url('assets/images/fallback-service-card.jpg');
?>

<div class="sv">
  <div class="sv-page-inner" style="max-width:1140px;margin:0 auto;padding:26px 40px 80px">

    <!-- ── Breadcrumb ── -->
    <div class="sv-crumb">
      <a href="/">Home</a><span class="sep">/</span>
      <?php if (!empty($category_names['main']) && $category_names['main'] !== 'Not Selected'): ?>
        <a href="/browse-services"><?= esc($category_names['main']) ?></a><span class="sep">/</span>
      <?php endif; ?>
      <?php if (!empty($category_names['sub'])): ?>
        <a href="/browse-services"><?= esc($category_names['sub']) ?></a><span class="sep">/</span>
      <?php endif; ?>
      <b><?= esc($service['title']) ?></b>
    </div>

    <!-- ── Title block ── -->
    <div class="sv-title-row" style="display:flex;align-items:flex-end;justify-content:space-between;gap:24px;margin:18px 0 22px">
      <div>
        <?php
        $eyebrow = $category_names['third'] ?? ($category_names['sub'] ?? '');
        if (!empty($eyebrow)):
        ?>
          <div class="sv-eyebrow" style="margin-bottom:10px"><?= esc($eyebrow) ?></div>
        <?php endif; ?>
        <h1 class="sv-h1" style="font-size:42px"><?= esc($service['title']) ?></h1>
        <?php if (!empty($vendor_rating) && $vendor_rating['count'] > 0): ?>
          <div class="sv-rate-row" style="margin-top:10px">
            <span class="sv-stars"><?= view('partials/sv_stars', ['rating' => $vendor_rating['avg']]) ?></span>
            <b><?= esc(number_format((float) $vendor_rating['avg'], 1)) ?></b>
            <span>·</span>
            <span><?= (int) $vendor_rating['count'] ?> review<?= $vendor_rating['count'] === 1 ? '' : 's' ?></span>
          </div>
        <?php endif; ?>
        <?php if (!empty($service['short_description'])): ?>
          <p class="sv-lead" style="margin:10px 0 0;max-width:620px"><?= esc($service['short_description']) ?></p>
        <?php endif; ?>
        <?php
        $locationText = '';
        if (!empty($location['city'])) {
            $locationText = $location['city'];
        } elseif ($fulfillmentType === 'postal') {
            $locationText = 'Ships nationwide';
        } elseif (!empty($location['travel_radius_miles'])) {
            $locationText = 'Travels up to ' . (int) $location['travel_radius_miles'] . ' miles';
        }
        ?>
        <?php if ($locationText !== ''): ?>
          <div style="margin-top:16px">
            <span class="sv-chip"><?= svIcon('pin') ?><?= esc($locationText) ?></span>
          </div>
        <?php endif; ?>
      </div>
      <?php if (!$isOwnService): ?>
      <div style="display:flex;gap:8px;flex:0 0 auto">
        <button type="button" class="sv-btn sv-btn-ghost" style="padding:11px 15px">
          <?= svIcon('heart', '', 'width:17px;height:17px') ?>Save
        </button>
        <button type="button" class="sv-btn sv-btn-ghost" style="padding:11px 15px">
          <?= svIcon('share', '', 'width:17px;height:17px') ?>Share
        </button>
      </div>
      <?php endif; ?>
    </div>

    <!-- ── Gallery ── -->
    <?php if (!empty($images)): ?>
    <?php
    $thumb1     = $images[1] ?? null;
    $thumb2     = $images[2] ?? null;
    $extraCount = max(0, count($images) - 3);
    ?>
    <div class="sv-gallery-grid" style="display:grid;grid-template-columns:2fr 1fr;gap:12px;height:420px;margin-bottom:44px">
      <div class="sv-gallery-main" style="height:100%">
        <img id="sv-main-img"
             src="<?= base_url(esc($primaryImage['image_path'])) ?>"
             alt="<?= esc($service['title']) ?>"
             onerror="this.onerror=null;this.src='<?= $fallback ?>'">
      </div>
      <div class="sv-gallery-thumbs" style="display:grid;grid-template-rows:1fr 1fr;gap:12px">
        <div class="sv-thumb" onclick="svSwapMain(this,'<?= base_url(esc($thumb1['image_path'] ?? $primaryImage['image_path'])) ?>')" style="height:100%">
          <?php if ($thumb1): ?>
            <img src="<?= base_url(esc($thumb1['thumbnail_path'] ?? $thumb1['image_path'])) ?>"
                 alt=""
                 onerror="this.onerror=null;this.src='<?= $fallback ?>'">
          <?php else: ?>
            <div style="width:100%;height:100%;background:#EDE5D8"></div>
          <?php endif; ?>
        </div>
        <div class="sv-thumb" onclick="svSwapMain(this,'<?= base_url(esc($thumb2 ? $thumb2['image_path'] : $primaryImage['image_path'])) ?>')" style="height:100%;position:relative">
          <?php if ($thumb2): ?>
            <img src="<?= base_url(esc($thumb2['thumbnail_path'] ?? $thumb2['image_path'])) ?>"
                 alt=""
                 onerror="this.onerror=null;this.src='<?= $fallback ?>'">
          <?php else: ?>
            <div style="width:100%;height:100%;background:#E3D3BE"></div>
          <?php endif; ?>
          <?php if ($extraCount > 0): ?>
            <div style="position:absolute;inset:0;background:rgba(34,27,24,.45);color:#fff;display:grid;place-items:center;font-weight:700;font-size:15px;font-family:var(--font-sans)">
              +<?= $extraCount ?> photos
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- ── 2-column split ── -->
    <div class="sv-split" style="display:grid;grid-template-columns:1fr 384px;gap:56px;align-items:start">

      <!-- ───────── LEFT: description content ───────── -->
      <div>

        <!-- About -->
        <?php if (!empty($service['description']) || !empty($service['short_description'])): ?>
        <section>
          <h2 class="sv-section-label">About this service</h2>
          <div class="sv-body" style="font-size:16.5px">
            <?php if (!empty($service['description'])): ?>
              <?php foreach (array_filter(preg_split('/\n{2,}/', $service['description'])) as $para): ?>
                <p><?= nl2br(esc(trim($para))) ?></p>
              <?php endforeach; ?>
            <?php else: ?>
              <p><?= esc($service['short_description']) ?></p>
            <?php endif; ?>
          </div>
        </section>
        <?php endif; ?>

        <!-- Delivery (postal) -->
        <?php if ($fulfillmentType === 'postal' || $fulfillmentType === 'both'): ?>
        <section style="margin-top:40px">
          <h2 class="sv-section-label">Delivery</h2>
          <div class="sv-body">
            <p>
              <?= $fulfillmentType === 'both' ? 'Available to post or attend in person.' : 'Posted / delivered to you.' ?>
              <?php if (isset($location['postal_fee'])): ?>
                <?php $postalFee = (float) $location['postal_fee']; ?>
                Postage: <?= $postalFee === 0.0 ? 'Free' : '£' . number_format($postalFee, 2) ?>.
                <?php if (!empty($location['free_postage_above'])): ?>
                  Free on orders over £<?= number_format((float) $location['free_postage_above'], 2) ?>.
                <?php endif; ?>
              <?php endif; ?>
              <?php if (!empty($location['delivery_lead_time_days'])): ?>
                Typical dispatch: <?= (int) $location['delivery_lead_time_days'] ?> working day<?= (int) $location['delivery_lead_time_days'] === 1 ? '' : 's' ?>.
              <?php endif; ?>
            </p>
          </div>
        </section>
        <?php endif; ?>

        <!-- Good to know -->
        <?php if (!empty($specs) || !empty($reqs)): ?>
        <section style="margin-top:40px">
          <h2 class="sv-section-label">Good to know</h2>
          <?php if (!empty($specs)): ?>
          <div class="sv-specs">
            <?php foreach ($specs as $spec): ?>
            <div class="sv-spec">
              <span class="k"><?= svIcon($spec['icon']) ?><?= esc($spec['k']) ?></span>
              <span class="v"><?= esc($spec['v']) ?></span>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <?php if (!empty($reqs)): ?>
          <ul class="sv-incl" style="margin-top:14px">
            <?php foreach ($reqs as $req): ?>
              <li><?= svIcon('check') ?><?= esc($req) ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </section>
        <?php endif; ?>

        <!-- Guest pricing table (informational, shown on left) -->
        <?php if ($showGuest && !empty($guestPricing)): ?>
        <section style="margin-top:40px">
          <h2 class="sv-section-label">Pricing by guest count</h2>
          <div class="sv-specs">
            <?php foreach ($guestPricing as $gp): ?>
            <div class="sv-spec">
              <span class="k">
                <?= svIcon('users') ?>
                <?= esc($gp['min_guest'] ?? $gp['min_guests'] ?? '') ?>–<?= esc($gp['max_guest'] ?? $gp['max_guests'] ?? '') ?> guests
              </span>
              <span class="v">£<?= esc(number_format((float) ($gp['guest_price'] ?? $gp['price'] ?? 0), 2)) ?> per person</span>
            </div>
            <?php endforeach; ?>
          </div>
          <p class="sv-body" style="font-size:13.5px;margin-top:12px">
            The rate matching your guest count is applied automatically when you add this to an event.
          </p>
        </section>
        <?php endif; ?>

        <!-- Meet your host -->
        <?php if (!empty($vendor_profile) && !empty($vendor_profile['name'])): ?>
        <section style="margin-top:40px">
          <h2 class="sv-section-label">Meet your host</h2>
          <div class="sv-panel sv-host">
            <div class="sv-host-head">
              <?php if (!empty($vendor_profile['photo_path'])): ?>
                <img src="<?= base_url(esc($vendor_profile['photo_path'])) ?>"
                     alt="<?= esc($vendor_profile['name']) ?>"
                     class="sv-host-ava"
                     onerror="this.style.display='none'">
              <?php else: ?>
                <div class="sv-host-ava-initials">
                  <?= esc(strtoupper(substr($vendor_profile['name'], 0, 1))) ?>
                </div>
              <?php endif; ?>

              <div class="sv-host-id">
                <div class="sv-host-name"><?= esc($vendor_profile['name']) ?></div>
                <?php if (!empty($vendor_profile['tagline'])): ?>
                  <div class="sv-host-role"><?= esc($vendor_profile['tagline']) ?></div>
                <?php endif; ?>
                <?php if (!empty($vendor_profile['since'])): ?>
                  <div class="sv-host-meta">
                    <?= svIcon('calendar', '', 'width:14px;height:14px') ?>
                    Member since <?= esc((string) $vendor_profile['since']) ?>
                  </div>
                <?php endif; ?>
              </div>

              <a href="<?= base_url('vendor/' . esc($service['vendor_id'])) ?>" class="sv-host-link">
                View full profile<?= svIcon('arrow', '', 'width:15px;height:15px') ?>
              </a>
            </div>

            <?php if (!empty($vendor_profile['bio'])): ?>
              <p class="sv-host-bio"><?= nl2br(esc($vendor_profile['bio'])) ?></p>
            <?php endif; ?>

            <?php if (!empty($vendor_profile['plays'])): ?>
              <div class="sv-host-plays">
                <span class="sv-host-plays-label">Plays</span>
                <?php foreach ($vendor_profile['plays'] as $playTag): ?>
                  <span class="sv-tag"><?= esc($playTag) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php if (!empty($vendor_profile['quote'])): ?>
              <blockquote class="sv-host-quote"><?= esc($vendor_profile['quote']) ?></blockquote>
            <?php endif; ?>

            <?php if (!empty($message_vendor_eligible) && !empty($message_vendor_url)): ?>
              <a href="<?= esc($message_vendor_url) ?>" class="sv-btn sv-btn-ghost" style="margin-top:18px;align-self:flex-start;text-decoration:none">
                <?= svIcon('chat', '', 'width:16px;height:16px') ?>Message <?= esc($vendor_profile['name']) ?>
              </a>
            <?php elseif (!session()->has('user_id')): ?>
              <a href="/login" class="sv-btn sv-btn-ghost" style="margin-top:18px;align-self:flex-start;text-decoration:none">
                <?= svIcon('chat', '', 'width:16px;height:16px') ?>Log in to message
              </a>
            <?php endif; ?>
          </div>
        </section>
        <?php endif; ?>

        <!-- Recent reviews (service-specific written comments) -->
        <?php if (!empty($service_reviews)): ?>
        <section style="margin-top:40px">
          <h2 class="sv-section-label">Recent reviews</h2>
          <div style="display:flex;flex-direction:column;gap:14px">
            <?php foreach ($service_reviews as $rv): ?>
              <div class="sv-review">
                <div class="sv-stars"><?= view('partials/sv_stars', ['rating' => (int) $rv['rating']]) ?></div>
                <?php if (!empty($rv['title'])): ?>
                  <div style="font-weight:700;margin:8px 0 4px"><?= esc($rv['title']) ?></div>
                <?php endif; ?>
                <p class="sv-review-quote">"<?= esc($rv['comment']) ?>"</p>
                <div class="sv-review-by">
                  <b><?= esc($rv['customer_name'] ?? 'Verified customer') ?></b>
                  <?php
                  $context = $rv['event_type'] ?? ($rv['event_title'] ?? '');
                  if (!empty($context)):
                  ?>
                    · <?= esc($context) ?>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

      </div><!-- /left column -->

      <!-- ───────── RIGHT: sticky booking panel ───────── -->
      <aside style="position:sticky;top:88px">
        <form action="<?= site_url('event/add-to-event/' . (int) $service['id']) ?>" method="post" id="booking-form">
          <?= csrf_field() ?>
          <div class="sv-panel" style="overflow:hidden">

            <!-- Panel header -->
            <div style="padding:18px 22px;border-bottom:1px solid var(--line-soft);background:var(--warm)">
              <div class="sv-eyebrow">Your booking</div>
              <div style="display:flex;align-items:baseline;gap:8px;margin-top:6px">
                <?php if ($isCustomQuote): ?>
                  <span class="sv-h2" style="font-size:22px">Price on request</span>
                <?php elseif ($initialPrice > 0): ?>
                  <span class="sv-h2" style="font-size:26px" id="sv-price-display">
                    £<?= number_format((int) $initialPrice) ?>
                  </span>
                  <span class="sv-total-sub">est. total</span>
                <?php endif; ?>
              </div>
            </div>

            <div style="padding:20px 22px">

              <?php if ($isCustomQuote): ?>
                <!-- Custom quote notice -->
                <div style="font-size:14px;color:var(--muted);line-height:1.6;margin-bottom:16px;display:flex;gap:10px;align-items:flex-start">
                  <?= svIcon('bolt', '', 'width:16px;height:16px;color:var(--accent);flex-shrink:0;margin-top:2px') ?>
                  <span>This supplier prices each event individually. Add it to your event to <strong style="color:var(--ink)">request a bespoke quote</strong> — there's no charge until you accept.</span>
                </div>

              <?php elseif ($showPackages && !empty($tieredPackages)): ?>
                <!-- Tiered package radio cards -->
                <div style="font-size:12.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:12px">
                  Choose a package
                </div>
                <div style="display:grid;gap:10px">
                  <?php foreach ($tieredPackages as $pkgIdx => $pkg):
                    $pkgPrice  = (float) ($pkg['package_price'] ?? $pkg['price'] ?? 0);
                    $pkgActive = ($pkgIdx === 0);
                  ?>
                  <button type="button" class="sv-pkg<?= $pkgActive ? ' is-active' : '' ?>"
                          data-pkg-id="<?= (int) $pkg['id'] ?>"
                          data-pkg-price="<?= (int) $pkgPrice ?>"
                          onclick="svSelectPkg(this)">
                    <span class="sv-pkg-radio"></span>
                    <span>
                      <span class="sv-pkg-name">
                        <?= esc($pkg['package_name']) ?>
                        <?php if (!empty($pkg['tag'])): ?>
                          <span class="sv-pkg-tag"><?= esc($pkg['tag']) ?></span>
                        <?php endif; ?>
                      </span>
                      <?php if (!empty($pkg['description'])): ?>
                        <span class="sv-pkg-desc"><?= esc($pkg['description']) ?></span>
                      <?php endif; ?>
                    </span>
                    <span class="sv-pkg-price">£<?= number_format((int) $pkgPrice) ?><small>from</small></span>
                  </button>
                  <?php endforeach; ?>
                </div>
                <input type="hidden" name="pricing_option" id="sv-pkg-input"
                       value="package_<?= !empty($tieredPackages) ? (int) $tieredPackages[0]['id'] : '' ?>">

              <?php elseif ($showDuration): ?>
                <!-- Duration selector -->
                <div style="font-size:12.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:10px">
                  Choose duration
                </div>
                <select class="form-control" id="durationPricing" name="pricing_option" required style="margin-bottom:16px">
                  <?php if (!empty($timeBlocks)): ?>
                    <optgroup label="Time slots">
                      <?php foreach ($timeBlocks as $block):
                        $start = preg_match('/^(\d{1,2}:\d{2})/', (string) ($block['start_time'] ?? ''), $sm) ? $sm[1] : '';
                        $end   = preg_match('/^(\d{1,2}:\d{2})/', (string) ($block['end_time']   ?? ''), $em) ? $em[1] : '';
                      ?>
                        <option value="timeblock_<?= esc($block['id']) ?>">
                          <?= esc($start) ?> – <?= esc($end) ?>: £<?= number_format((float) ($block['price'] ?? 0), 2) ?>
                        </option>
                      <?php endforeach; ?>
                    </optgroup>
                  <?php endif; ?>
                  <?php if (!empty($durationPricing)): ?>
                    <optgroup label="Duration">
                      <?php foreach ($durationPricing as $dp): ?>
                        <option value="duration_<?= esc($dp['id']) ?>">
                          <?= esc($dp['duration_hours'] ?? $dp['duration'] ?? '') ?>
                          <?= (($dp['duration_type'] ?? '') === 'day') ? 'Day(s)' : 'Hour(s)' ?>:
                          £<?= number_format((float) ($dp['price'] ?? 0), 2) ?>
                        </option>
                      <?php endforeach; ?>
                    </optgroup>
                  <?php endif; ?>
                </select>

              <?php elseif ($showQuantity && !empty($quantityTiers)): ?>
                <!-- Quantity input -->
                <?php $qtyUnitLabel = esc($quantityTiers[0]['unit_label'] ?? 'items'); ?>
                <div style="font-size:12.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:10px">
                  Order quantity (<?= $qtyUnitLabel ?>)
                </div>
                <?php if (count($quantityTiers) > 1): ?>
                  <div style="margin-bottom:12px">
                    <?php foreach ($quantityTiers as $qt):
                      $qMin2   = (int) ($qt['min_quantity'] ?? 1);
                      $qMaxRaw2 = $qt['max_quantity'] ?? null;
                    ?>
                      <div style="font-size:13px;color:var(--muted);padding:4px 0">
                        <?= ($qMaxRaw2 !== null && $qMaxRaw2 !== '') ? $qMin2 . '–' . (int) $qMaxRaw2 : $qMin2 . '+' ?>:
                        <strong style="color:var(--ink)">£<?= number_format((float) ($qt['unit_price'] ?? 0), 2) ?></strong> each
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                <input type="number" class="form-control quote-refresh-input" id="orderQuantity"
                       name="order_quantity"
                       value="<?= (int) $qtyDefault ?>"
                       min="<?= (int) $qtyMin ?>"
                       <?= $qtyMax !== null ? 'max="' . (int) $qtyMax . '"' : '' ?>
                       required style="margin-bottom:8px">
                <input type="hidden" name="pricing_option" id="qtyPricingOption" value="qty_<?= (int) $qtyDefault ?>">
                <?php if (count($quantityTiers) === 1 && !empty($quantityTiers[0]['unit_price'])): ?>
                  <p style="font-size:13px;color:var(--muted);margin-bottom:16px">
                    £<?= number_format((float) $quantityTiers[0]['unit_price'], 2) ?> per <?= $qtyUnitLabel ?>
                  </p>
                <?php endif; ?>

              <?php elseif (!$isCustomQuote && !$showGuest): ?>
                <!-- Fixed price service — nothing to select -->
                <?php if ($initialPrice > 0): ?>
                  <p style="font-size:14px;color:var(--muted);margin-bottom:16px">
                    Fixed price — no options to choose.
                  </p>
                <?php endif; ?>
              <?php endif; ?>

              <!-- Optional extras -->
              <?php if (!empty($optional_extras)): ?>
                <div style="font-size:12.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin:22px 0 4px">
                  Add optional extras
                </div>
                <div id="sv-extras-list">
                  <?php foreach ($optional_extras as $extra):
                    $isPerItem      = ($extra['pricing_type'] ?? 'flat') === 'per_item';
                    $extraPrice     = (float) ($extra['price'] ?? 0);
                    $extraUnitLabel = esc($extra['unit_label'] ?? 'per item');
                  ?>
                  <div class="sv-extra"
                       data-extra-id="<?= esc($extra['id']) ?>"
                       data-extra-price="<?= (int) $extraPrice ?>"
                       onclick="svToggleExtra(this)">
                    <!-- Hidden real checkbox for form submission -->
                    <input type="checkbox"
                           class="extra-checkbox"
                           id="extra_<?= esc($extra['id']) ?>"
                           name="extras[]"
                           value="<?= esc($extra['id']) ?>"
                           <?= $isPerItem ? 'data-per-item="1"' : '' ?>
                           onclick="event.stopPropagation()"
                           onchange="svSyncExtra(this)"
                           style="position:absolute;opacity:0;pointer-events:none">
                    <span class="sv-check"><?= svIcon('checkbold') ?></span>
                    <span class="sv-extra-body">
                      <span class="sv-extra-name"><?= esc($extra['name']) ?></span>
                      <?php if (!empty($extra['description'])): ?>
                        <span class="sv-extra-desc"><?= esc($extra['description']) ?></span>
                      <?php endif; ?>
                      <?php if ($isPerItem): ?>
                        <div class="extra-qty-wrap" id="qty_wrap_<?= esc($extra['id']) ?>" style="display:none;margin-top:6px" onclick="event.stopPropagation()">
                          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                            <label style="font-size:12px;color:var(--muted);margin:0">Qty (optional):</label>
                            <input type="number" class="form-control form-control-sm quote-refresh-input"
                                   style="width:80px"
                                   name="extra_qty[<?= esc($extra['id']) ?>]"
                                   value=""
                                   placeholder="Auto"
                                   min="<?= (int) ($extra['min_quantity'] ?? 1) ?>"
                                   <?= !empty($extra['max_quantity']) ? 'max="' . (int) $extra['max_quantity'] . '"' : '' ?>
                                   title="Leave blank to use your event's guest count">
                            <?php if (!empty($extra['min_quantity']) || !empty($extra['max_quantity'])): ?>
                              <span style="font-size:11px;color:var(--muted)">
                                <?= !empty($extra['min_quantity']) ? 'Min: ' . (int) $extra['min_quantity'] : '' ?>
                                <?= (!empty($extra['min_quantity']) && !empty($extra['max_quantity'])) ? ' · ' : '' ?>
                                <?= !empty($extra['max_quantity']) ? 'Max: ' . (int) $extra['max_quantity'] : '' ?>
                              </span>
                            <?php endif; ?>
                          </div>
                          <p style="font-size:11.5px;color:var(--muted);margin:4px 0 0">
                            <?= !empty($showQuantity)
                                ? 'Blank = use order quantity above.'
                                : 'Blank = use event guest count.' ?>
                          </p>
                        </div>
                      <?php endif; ?>
                    </span>
                    <span class="sv-extra-price">+£<?= number_format((int) $extraPrice) ?></span>
                  </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <!-- Estimated total -->
              <?php if (!$isCustomQuote && $initialPrice > 0): ?>
              <div class="sv-total-row" style="margin-top:20px;padding-top:18px;border-top:1px solid var(--line)">
                <span class="lbl">Estimated total</span>
                <span class="amt" id="sv-total-amt">£<?= number_format((int) $initialPrice) ?></span>
              </div>
              <p class="sv-total-sub" style="margin-top:4px">
                Final price confirmed by <?= esc($vendor_profile['name'] ?? 'the supplier') ?> for your date &amp; guest count.
              </p>
              <?php endif; ?>

              <!-- Live quote preview (AJAX) -->
              <?php if (!empty($preview_event_id) && session()->get('role') === 'customer'): ?>
              <div id="live-quote-preview"
                   style="margin-top:12px;padding:10px 12px;background:var(--warm);border-radius:8px;border:1px solid var(--line-soft);font-size:13px;color:var(--muted)"
                   data-service-id="<?= (int) $service['id'] ?>"
                   data-event-id="<?= (int) $preview_event_id ?>">
                <strong style="color:var(--ink)">Your event price:</strong>
                <span id="live-quote-total" style="color:var(--ink);font-weight:700;margin-left:4px">—</span>
                <div id="live-quote-errors" class="text-danger mt-1"></div>
                <ul id="live-quote-lines" class="mb-0" style="padding-left:1rem;margin-top:4px"></ul>
              </div>
              <?php endif; ?>

              <!-- CTAs -->
              <div style="margin-top:16px;display:flex;flex-direction:column;gap:10px">
                <?php if ($isOwnService): ?>
                  <a href="<?= base_url('service/edit/' . (int) $service['id']) ?>"
                     class="sv-btn sv-btn-primary sv-btn-lg"
                     style="text-decoration:none">
                    Edit Service
                  </a>
                <?php elseif (!session()->has('user_id')): ?>
                  <a href="/login" class="sv-btn sv-btn-primary sv-btn-lg" style="text-decoration:none">
                    <?= svIcon('calendar', '', 'width:18px;height:18px') ?>Log in to book
                  </a>
                <?php else: ?>
                  <button type="submit" class="sv-btn sv-btn-primary sv-btn-lg">
                    <?= svIcon('calendar', '', 'width:18px;height:18px') ?>
                    <?= $isCustomQuote ? 'Request a quote' : 'Add to my event' ?>
                  </button>
                <?php endif; ?>

                <?php if (!empty($message_vendor_eligible) && !empty($message_vendor_url)): ?>
                  <a href="<?= esc($message_vendor_url) ?>" class="sv-btn sv-btn-ghost sv-btn-lg" style="text-decoration:none">
                    <?= svIcon('chat', '', 'width:17px;height:17px') ?>Message <?= esc($vendor_profile['name'] ?? 'vendor') ?>
                  </a>
                <?php elseif (session()->has('user_id') && session()->get('role') === 'customer' && !$isOwnService): ?>
                  <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted);padding:4px 0">
                    <?= svIcon('bolt', '', 'width:14px;height:14px;color:var(--accent)') ?>
                    Messaging unlocks after you book
                  </div>
                <?php endif; ?>
              </div>

              <!-- Cancellation note -->
              <div style="display:flex;align-items:flex-start;gap:8px;margin-top:16px;font-size:12.5px;color:var(--muted);line-height:1.5">
                <?= svIcon('shield', '', 'width:15px;height:15px;color:var(--accent);flex-shrink:0;margin-top:1px') ?>
                <?php if (!empty($cancellation_policy)): ?>
                  <?= esc($cancellation_policy) ?>
                <?php else: ?>
                  Free cancellation — see supplier policy
                <?php endif; ?>
              </div>

            </div><!-- /panel body -->
          </div><!-- /sv-panel -->
        </form>
      </aside>

    </div><!-- /sv-split -->
  </div><!-- /sv-page-inner -->
</div><!-- /sv -->

<?php else: ?>
<main class="container">
  <section class="py-5">
    <p>Service not found.</p>
    <a href="/browse-services" class="btn btn-outline-secondary">Back to Services</a>
  </section>
</main>
<?php endif; ?>

<?= $this->include('footer') ?>

<script>
/* ── Gallery ──────────────────────────────────────────────────────────── */
function svSwapMain(thumbEl, url) {
    var img = document.getElementById('sv-main-img');
    if (img) img.src = url;
    document.querySelectorAll('.sv-thumb').forEach(function(t) { t.style.borderColor = 'transparent'; });
    thumbEl.style.borderColor = '#B66A4D';
}

/* ── Package selection ────────────────────────────────────────────────── */
function svSelectPkg(btn) {
    document.querySelectorAll('.sv-pkg').forEach(function(b) { b.classList.remove('is-active'); });
    btn.classList.add('is-active');
    var inp = document.getElementById('sv-pkg-input');
    if (inp) inp.value = 'package_' + btn.dataset.pkgId;
    svUpdateTotal();
    svTriggerLiveQuote();
}

/* ── Extra toggle ─────────────────────────────────────────────────────── */
function svToggleExtra(el) {
    var isOn = !el.classList.contains('is-on');
    el.classList.toggle('is-on', isOn);
    var cb = el.querySelector('.extra-checkbox');
    if (cb) cb.checked = isOn;
    var qtyWrap = cb ? document.getElementById('qty_wrap_' + cb.value) : null;
    if (qtyWrap) qtyWrap.style.display = isOn ? '' : 'none';
    svUpdateTotal();
    svTriggerLiveQuote();
}

/* Called when hidden checkbox changes (e.g. keyboard activation) */
function svSyncExtra(cb) {
    var el = cb.closest('.sv-extra');
    if (!el) return;
    el.classList.toggle('is-on', cb.checked);
    var qtyWrap = document.getElementById('qty_wrap_' + cb.value);
    if (qtyWrap) qtyWrap.style.display = cb.checked ? '' : 'none';
    svUpdateTotal();
    svTriggerLiveQuote();
}

/* ── Price total update ───────────────────────────────────────────────── */
function svUpdateTotal() {
    var pkgBtn = document.querySelector('.sv-pkg.is-active');
    var base   = pkgBtn ? parseInt(pkgBtn.dataset.pkgPrice || 0, 10) : 0;
    var extras = 0;
    document.querySelectorAll('.sv-extra.is-on').forEach(function(el) {
        extras += parseInt(el.dataset.extraPrice || 0, 10);
    });
    var total = base + extras;
    var fmt = '£' + total.toLocaleString('en-GB');
    var hdr = document.getElementById('sv-price-display');
    var amt = document.getElementById('sv-total-amt');
    if (hdr) hdr.textContent = fmt;
    if (amt) amt.textContent = fmt;
}

/* ── Live quote preview (AJAX) ────────────────────────────────────────── */
var _liveQuoteTimer = null;
function svTriggerLiveQuote() {
    clearTimeout(_liveQuoteTimer);
    _liveQuoteTimer = setTimeout(svRunLiveQuote, 300);
}

function svRunLiveQuote() {
    var preview = document.getElementById('live-quote-preview');
    if (!preview) return;
    var form = document.getElementById('booking-form');
    if (!form) return;
    var serviceId = preview.dataset.serviceId;
    var eventId   = preview.dataset.eventId;
    var fd     = new FormData(form);
    var params = new URLSearchParams();
    var po = fd.get('pricing_option');
    if (po) params.set('pricing_option', po);
    fd.getAll('extras[]').forEach(function(id) { params.append('extras[]', id); });
    var oq = fd.get('order_quantity');
    if (oq) params.set('order_quantity', oq);
    fetch('/event/quote-preview/' + serviceId + '/' + eventId + '?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('live-quote-total').textContent =
            data.total != null ? '£' + Number(data.total).toFixed(2) : '—';
        var errEl = document.getElementById('live-quote-errors');
        errEl.textContent = (data.errors || []).join(' ');
        var ul = document.getElementById('live-quote-lines');
        ul.innerHTML = '';
        (data.lines || []).forEach(function(line) {
            if (line.code === 'platform_commission') return;
            var li = document.createElement('li');
            li.textContent = line.label + ': £' + Number(line.amount).toFixed(2);
            ul.appendChild(li);
        });
    })
    .catch(function() {});
}

/* ── Quantity pricing option sync ─────────────────────────────────────── */
(function() {
    var qtyInput  = document.getElementById('orderQuantity');
    var qtyHidden = document.getElementById('qtyPricingOption');
    if (qtyInput && qtyHidden) {
        qtyInput.addEventListener('input', function() {
            qtyHidden.value = 'qty_' + this.value;
        });
        qtyInput.addEventListener('change', function() {
            qtyHidden.value = 'qty_' + this.value;
            svTriggerLiveQuote();
        });
    }

    var durationSelect = document.getElementById('durationPricing');
    if (durationSelect) {
        durationSelect.addEventListener('change', svTriggerLiveQuote);
    }
})();

/* ── Run live quote on page load ──────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function() {
    svRunLiveQuote();
});
</script>
