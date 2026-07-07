<?= $this->include('tenant_header') ?>
<?php
$images  = $service['images'] ?? [];
$imgCount = count($images);
$mainImg = $imgCount > 0
    ? '/' . ltrim((string) ($images[0]['image_path'] ?? $images[0]['thumbnail_path'] ?? ''), '/')
    : '/assets/images/fallback-service-card.jpg';

$rating   = $trust['rating'] ?? null;
$bookings = (int) ($trust['bookings'] ?? 0);
$phone     = trim((string) ($site['phone'] ?? ''));
$phoneHref = $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '';
$price     = isset($service['price']) && $service['price'] !== null ? (float) $service['price'] : null;

// Spec chips from whichever service fields are populated.
$chips = [];
$minCap = (int) ($service['min_capacity'] ?? 0);
$maxCap = (int) ($service['max_capacity'] ?? 0);
if ($maxCap > 0) {
    $chips[] = ['fa-users', $minCap > 0 && $minCap !== $maxCap ? "{$minCap}–{$maxCap} guests" : "Up to {$maxCap} guests"];
}
$io = strtolower(trim((string) ($service['indoor_outdoor'] ?? '')));
if ($io === 'both') {
    $chips[] = ['fa-house', 'Indoor & outdoor'];
} elseif ($io === 'indoor') {
    $chips[] = ['fa-house', 'Indoor'];
} elseif ($io === 'outdoor') {
    $chips[] = ['fa-tree', 'Outdoor'];
}
if (! empty($service['equipment_provided'])) {
    $chips[] = ['fa-box-open', 'Equipment provided'];
}
if ((int) ($service['setup_minutes'] ?? 0) > 0) {
    $chips[] = ['fa-screwdriver-wrench', 'We set up & pack down'];
}
?>

<div class="ps-storefront">
<main>
    <section class="sf-sec" style="padding-top: clamp(20px, 3vw, 32px);">
        <div class="container" style="max-width: 820px;">
            <a class="sf-back" href="/"><span aria-hidden="true">‹</span> <?= esc($site['business_name']) ?></a>

            <!-- Gallery -->
            <div class="sf-gallery">
                <div class="sf-gallery-main">
                    <img src="<?= esc($mainImg, 'attr') ?>" alt="<?= esc($service['title'], 'attr') ?>"
                        onerror="this.onerror=null;this.src='/assets/images/fallback-service-card.jpg';">
                    <?php if ($imgCount > 1): ?>
                        <span class="sf-gallery-count">1 / <?= $imgCount ?> photos</span>
                    <?php endif; ?>
                </div>
                <?php if ($imgCount > 1): ?>
                    <div class="sf-thumbs">
                        <?php foreach (array_slice($images, 1, 6) as $img):
                            $t = '/' . ltrim((string) ($img['thumbnail_path'] ?? $img['image_path'] ?? ''), '/');
                        ?>
                            <img src="<?= esc($t, 'attr') ?>" alt="" loading="lazy"
                                onerror="this.closest('img').remove();">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <h1 class="heading" style="font-size: clamp(26px,4vw,38px); margin: 18px 0 8px;"><?= esc($service['title']) ?></h1>

            <?php if (! empty($categoryName) || $rating !== null): ?>
                <div class="sf-rating">
                    <?php if ($rating !== null): ?>
                        <span class="sf-stars" aria-hidden="true">★★★★★</span> <b><?= esc(number_format((float) $rating, 1)) ?></b>
                        <?php if ($bookings > 0): ?>· booked <?= esc(number_format($bookings)) ?> time<?= $bookings === 1 ? '' : 's' ?> through this site<?php endif; ?>
                    <?php elseif (! empty($categoryName)): ?>
                        <?= esc($categoryName) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (! empty($chips)): ?>
                <div class="sf-badges">
                    <?php foreach ($chips as [$icon, $label]): ?>
                        <span class="sf-chip"><i class="fas <?= esc($icon, 'attr') ?>" aria-hidden="true"></i> <?= esc($label) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (! empty($service['short_description'])): ?>
                <p class="lead" style="margin: 6px 0 14px;"><?= esc($service['short_description']) ?></p>
            <?php endif; ?>
            <?php if (! empty($service['description'])): ?>
                <div style="max-width: 66ch; color: var(--ink-soft); line-height: 1.65;"><?= nl2br(esc($service['description'])) ?></div>
            <?php endif; ?>

            <?php if ($price !== null && $price > 0): ?>
                <p class="sf-detail-price">from £<?= esc(number_format($price, ($price == (int) $price) ? 0 : 2)) ?> <small>· get an exact price for your date</small></p>
            <?php endif; ?>

            <?php if (! empty($extras)): ?>
                <h2 style="font-family: var(--serif); font-weight: 500; font-size: 20px; margin: 26px 0 4px; color: var(--ink);">Add extras</h2>
                <ul class="sf-extras">
                    <?php foreach ($extras as $x):
                        $xp = isset($x['price']) && $x['price'] !== null ? (float) $x['price'] : null;
                    ?>
                        <li>
                            <span>
                                <span class="x-name"><?= esc($x['name'] ?? 'Extra') ?></span>
                                <?php if (! empty($x['description'])): ?><br><span class="x-desc"><?= esc($x['description']) ?></span><?php endif; ?>
                            </span>
                            <?php if ($xp !== null): ?>
                                <span class="x-price">+£<?= esc(number_format($xp, ($xp == (int) $xp) ? 0 : 2)) ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <!-- Instant quote (comp screen 02 → 03): date + location + options,
                 priced by the same engine as the marketplace. -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="sf-flash error" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('info')): ?>
                <div class="sf-flash info"><?= esc(session()->getFlashdata('info')) ?></div>
            <?php endif; ?>

            <form class="sf-quote-form" method="post" action="/quote">
                <?= csrf_field() ?>
                <input type="hidden" name="service_id" value="<?= (int) $service['id'] ?>">

                <h2 class="sf-form-title">Get an instant quote</h2>
                <p class="sf-form-sub">A real price in seconds — no waiting for a call back.</p>

                <div class="sf-field-row">
                    <label class="sf-field">
                        <span>Party date</span>
                        <input type="date" name="event_date" required min="<?= date('Y-m-d') ?>">
                    </label>
                    <label class="sf-field">
                        <span>Postcode</span>
                        <input type="text" name="postcode" maxlength="10" placeholder="e.g. SK7 2AA" autocomplete="postal-code">
                    </label>
                </div>

                <?php if (! empty($pricing['needsGuests'])): ?>
                    <label class="sf-field">
                        <span>Guests</span>
                        <input type="number" name="guest_count" min="1" max="10000" required placeholder="e.g. 45">
                    </label>
                <?php endif; ?>

                <?php if (! empty($pricing['needsQuantity'])): ?>
                    <label class="sf-field">
                        <span>How many?</span>
                        <input type="number" name="order_quantity" min="<?= (int) $pricing['minQuantity'] ?>" max="100000"
                            value="<?= (int) $pricing['minQuantity'] ?>" required>
                    </label>
                <?php endif; ?>

                <?php if (! empty($pricing['options'])): ?>
                    <fieldset class="sf-options">
                        <legend>Pick your option</legend>
                        <?php foreach ($pricing['options'] as $i => $opt): ?>
                            <label class="sf-option">
                                <input type="radio" name="pricing_option" value="<?= esc($opt['token'], 'attr') ?>" <?= $i === 0 ? 'checked' : '' ?>>
                                <span class="o-label"><?= esc($opt['label']) ?></span>
                                <span class="o-price"><?= esc($opt['sub']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                <?php endif; ?>

                <?php if (! empty($extras)): ?>
                    <fieldset class="sf-options">
                        <legend>Add extras</legend>
                        <?php foreach ($extras as $x):
                            $xp      = isset($x['price']) && $x['price'] !== null ? (float) $x['price'] : null;
                            $perItem = strtolower((string) ($x['pricing_type'] ?? 'flat')) === 'per_item';
                        ?>
                            <label class="sf-option">
                                <input type="checkbox" name="extras[]" value="<?= (int) $x['id'] ?>">
                                <span class="o-label"><?= esc($x['name'] ?? 'Extra') ?></span>
                                <?php if ($xp !== null): ?>
                                    <span class="o-price">+£<?= esc(number_format($xp, ($xp == (int) $xp) ? 0 : 2)) ?><?= $perItem && ! empty($x['unit_label']) ? '/' . esc($x['unit_label']) : '' ?></span>
                                <?php endif; ?>
                                <?php if ($perItem): ?>
                                    <input class="o-qty" type="number" name="extra_qty[<?= (int) $x['id'] ?>]"
                                        min="<?= max(1, (int) ($x['min_quantity'] ?? 1)) ?>"
                                        value="<?= max(1, (int) ($x['min_quantity'] ?? 1)) ?>" aria-label="Quantity">
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                <?php endif; ?>

                <button type="submit" class="sf-btn block">Get instant quote</button>
                <p class="sf-book-note"><?= (int) $depositPercent ?>% deposit holds your date · free 14-day cancellation</p>
            </form>

            <?php if ($phone !== ''): ?>
                <p class="sf-book-note" style="margin-top: 14px;">Prefer to talk it through?
                    <a href="<?= esc($phoneHref, 'attr') ?>" style="color: var(--sf-primary); font-weight: 700;"><?= esc($phone) ?></a>
                </p>
            <?php endif; ?>
        </div>
    </section>
</main>
</div>

<?= $this->include('tenant_footer') ?>
