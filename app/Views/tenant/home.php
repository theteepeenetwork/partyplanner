<?= $this->include('tenant_header') ?>
<?php
$rating   = $trust['rating'] ?? null;
$bookings = (int) ($trust['bookings'] ?? 0);
$phone    = trim((string) ($site['phone'] ?? ''));
$phoneHref = $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '';
// The hero eyebrow uses the first service's category as a plain-language label.
$heroCat = '';
foreach ($services as $s) {
    if (! empty($s['category_name'])) { $heroCat = (string) $s['category_name']; break; }
}
?>

<div class="ps-storefront">
<main>
    <!-- Hero -->
    <section class="sf-hero">
        <div class="container">
            <?php if ($heroCat !== ''): ?>
                <p class="sf-eyebrow"><?= esc($heroCat) ?></p>
            <?php endif; ?>
            <h1><?= esc($site['business_name']) ?></h1>

            <?php if ($rating !== null): ?>
                <span class="sf-rating">
                    <span class="sf-stars" aria-hidden="true">★★★★★</span>
                    <b><?= esc(number_format((float) $rating, 1)) ?></b>
                    <?php if ($bookings > 0): ?>· <?= esc(number_format($bookings)) ?> verified booking<?= $bookings === 1 ? '' : 's' ?><?php endif; ?>
                </span>
            <?php elseif ($bookings > 0): ?>
                <span class="sf-rating"><b><?= esc(number_format($bookings)) ?></b> verified booking<?= $bookings === 1 ? '' : 's' ?></span>
            <?php endif; ?>

            <div class="sf-cta-row">
                <a class="sf-btn" href="#catalogue">See what we offer</a>
                <?php if ($phone !== ''): ?>
                    <a class="sf-btn ghost" href="<?= esc($phoneHref, 'attr') ?>"><i class="fas fa-phone" aria-hidden="true"></i> Call to book</a>
                <?php endif; ?>
            </div>

            <ul class="sf-trust">
                <li><span class="sf-tick" aria-hidden="true">✓</span> 10% deposit holds your date</li>
                <li><span class="sf-tick" aria-hidden="true">✓</span> Secure card payment</li>
                <li><span class="sf-tick" aria-hidden="true">✓</span> Free 14-day cancellation</li>
            </ul>
        </div>
    </section>

    <!-- Catalogue -->
    <section class="sf-sec" id="catalogue">
        <div class="container">
            <div class="sf-sec-head">
                <h2>What we offer</h2>
                <?php if (! empty($services)): ?>
                    <p>Pick a service to see the detail and get a price for your date.</p>
                <?php endif; ?>
            </div>

            <?php if (empty($services)): ?>
                <p class="lead">No services are listed right now — please check back soon.</p>
            <?php else: ?>
                <div class="sf-catalogue<?= count($services) % 3 === 0 ? ' cols-3' : '' ?>">
                    <?php foreach ($services as $i => $service):
                        $serviceUrl = '/service/' . (int) $service['id'];
                        $thumb      = ! empty($service['images'])
                            ? '/' . ltrim((string) ($service['images'][0]['thumbnail_path'] ?? $service['images'][0]['image_path'] ?? ''), '/')
                            : '/assets/images/fallback-service-card.jpg';
                        $category = trim((string) ($service['category_name'] ?? ''));
                        $price    = isset($service['price']) && $service['price'] !== null ? (float) $service['price'] : null;
                    ?>
                        <a class="sf-card" href="<?= $serviceUrl ?>">
                            <div class="sf-card-media">
                                <img src="<?= esc($thumb, 'attr') ?>" alt="<?= esc($service['title'], 'attr') ?>" loading="lazy"
                                    onerror="this.onerror=null;this.src='/assets/images/fallback-service-card.jpg';">
                                <?php if ($i === 0 && count($services) > 1): ?>
                                    <span class="sf-badge">Most booked</span>
                                <?php endif; ?>
                            </div>
                            <div class="sf-card-body">
                                <?php if ($category !== ''): ?>
                                    <span class="sf-card-cat"><?= esc($category) ?></span>
                                <?php endif; ?>
                                <h3><?= esc($service['title']) ?></h3>
                                <?php if (! empty($service['short_description'])): ?>
                                    <p class="sf-card-desc"><?= esc($service['short_description']) ?></p>
                                <?php endif; ?>
                                <?php if ($price !== null && $price > 0): ?>
                                    <span class="sf-price">from £<?= esc(number_format($price, ($price == (int) $price) ? 0 : 2)) ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- About -->
    <?php if (! empty($site['about_text'])): ?>
        <section class="sf-sec" style="padding-top: 0;">
            <div class="container">
                <div class="sf-about">
                    <h2>About <?= esc($site['business_name']) ?></h2>
                    <p><?= nl2br(esc($site['about_text'])) ?></p>
                    <?php if ($phone !== ''): ?>
                        <a class="sf-callline" href="<?= esc($phoneHref, 'attr') ?>">Questions? Call <?= esc($phone) ?> →</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>
</div>

<?= $this->include('tenant_footer') ?>
