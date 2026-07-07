<?php
/**
 * Landing mode B — multi-service vendor (frames 1d/1e).
 * Hero with scrim (business name + rating + coverage), "What we offer"
 * cards → service page, neutral trust chips, dark footer.
 */
$headerSubline = '';
?>
<?= $this->include('tenant_header') ?>
<?php
$bn      = $site['business_name'] ?? 'Storefront';
$rating  = $trust['rating'] ?? null;
$reviews = (int) ($trust['reviews'] ?? 0);
$bookCnt = (int) ($trust['bookings'] ?? 0);
?>

<?php if ($heroImage !== ''): ?>
    <div class="sf-hero">
        <img src="<?= esc($heroImage, 'attr') ?>" alt="">
        <div class="sf-hero-scrim">
            <div class="sf-shell" style="width: 100%;">
                <h1 class="sf-hero-h"><?= esc($bn) ?></h1>
                <div class="sf-hero-meta">
                    <?php if ($rating !== null): ?>
                        <span class="sf-rating-chip"><i class="fas fa-star" aria-hidden="true"></i><?= esc(number_format($rating, 1)) ?> · <?= $bookCnt ?> verified booking<?= $bookCnt === 1 ? '' : 's' ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="sf-shell">
    <?php if ($heroImage === ''): ?>
        <div class="sf-sec" style="padding-bottom: 0;">
            <h1 style="font-size: 20px; font-weight: 700; margin: 0 0 4px;"><?= esc($bn) ?></h1>
            <?php if ($rating !== null): ?>
                <span class="sf-rating-chip" style="color: var(--sf-ink);"><i class="fas fa-star" aria-hidden="true"></i><?= esc(number_format($rating, 1)) ?> · <?= $bookCnt ?> verified booking<?= $bookCnt === 1 ? '' : 's' ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="sf-flash error" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('info')): ?>
        <div class="sf-flash info"><?= esc(session()->getFlashdata('info')) ?></div>
    <?php endif; ?>

    <section class="sf-sec">
        <h2 class="sf-sec-h">What we offer</h2>
        <p class="sf-sec-sub">Pick a service — you'll get an exact price for your date.</p>

        <div class="sf-svc-list">
            <?php foreach ($services as $service):
                $img  = ! empty($service['images']) ? '/' . ltrim((string) ($service['images'][0]['thumbnail_path'] ?? $service['images'][0]['image_path'] ?? ''), '/') : '';
                $from = $service['from'] ?? ['amount' => 0, 'per' => ''];
            ?>
                <a class="sf-svc-card" href="/service/<?= (int) $service['id'] ?>">
                    <?php if (! empty($mostBookedId) && (int) $service['id'] === (int) $mostBookedId && count($services) > 1): ?>
                        <span class="sf-badge-most">Most booked</span>
                    <?php endif; ?>
                    <span class="img">
                        <?php if ($img !== ''): ?>
                            <img src="<?= esc($img, 'attr') ?>" alt="" loading="lazy">
                        <?php else: ?>
                            <span style="display: block; width: 100%; height: 100%; min-height: 96px; background: var(--sf-tint-12);"></span>
                        <?php endif; ?>
                    </span>
                    <span class="body">
                        <?php if (! empty($service['category_name'])): ?>
                            <span class="sf-eyebrow"><?= esc(trim(explode('·', $service['category_name'])[0])) ?></span>
                        <?php endif; ?>
                        <h3 class="t"><?= esc($service['title']) ?></h3>
                        <?php if ($from['amount'] > 0): ?>
                            <span class="price">from £<?= esc(number_format($from['amount'], $from['amount'] == (int) $from['amount'] ? 0 : 2)) ?><?php if ($from['per'] !== ''): ?><span class="per">/<?= esc($from['per']) ?></span><?php endif; ?></span>
                        <?php endif; ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="sf-chips">
        <span class="sf-chip"><i class="fas fa-shield-halved" aria-hidden="true"></i><?= (int) \App\Libraries\DepositCalculator::percentDisplay() ?>% deposit holds your date</span>
        <span class="sf-chip"><i class="fas fa-lock" aria-hidden="true"></i>Secure card payment</span>
        <span class="sf-chip"><i class="fas fa-rotate-left" aria-hidden="true"></i>Free 14-day cancellation</span>
    </div>

    <?php if (! empty($aboutLine)): ?>
        <p style="font-size: 13px; color: var(--sf-muted); margin: 4px 0 18px;"><?= esc($aboutLine) ?></p>
    <?php endif; ?>
</div>

<?= $this->include('tenant_footer') ?>
