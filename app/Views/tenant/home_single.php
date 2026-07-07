<?php
/**
 * Landing mode A — single-service vendor: the service IS the homepage
 * (frames 1b/1c, weak-input variant 1m). Hero (or framed-photo fallback),
 * availability checker submitting to the service page, trust chips,
 * what's-included, one review (or the honest zero-reviews card), sticky
 * book bar on mobile.
 */
?>
<?= $this->include('tenant_header') ?>
<?php
$bn        = $site['business_name'] ?? 'Storefront';
$rating    = $trust['rating'] ?? null;
$bookCnt   = (int) ($trust['bookings'] ?? 0);
$from      = $from ?? ['amount' => 0, 'per' => ''];
$fromStr   = $from['amount'] > 0
    ? 'from £' . number_format($from['amount'], $from['amount'] == (int) $from['amount'] ? 0 : 2) . ($from['per'] !== '' ? '/' . $from['per'] : '')
    : '';
$deposit   = \App\Libraries\DepositCalculator::percentDisplay();
$checkHref = '/service/' . (int) $service['id'];
$included  = array_values(array_filter(array_map('trim', preg_split('/\n+/', (string) ($service['short_description'] ?? '')))));
$catShort  = trim(explode('·', (string) ($categoryName ?? ''))[0] ?? '');
?>

<?php if ($photos['mode'] === 'framed'): ?>
    <div class="sf-framed-hero">
        <img src="<?= esc($photos['urls'][0], 'attr') ?>" alt="<?= esc($service['title'], 'attr') ?>">
    </div>
<?php elseif ($photos['mode'] === 'filmstrip'): ?>
    <div class="sf-shell" style="padding-top: 12px;">
        <div class="sf-filmstrip">
            <?php foreach ($photos['urls'] as $u): ?>
                <img src="<?= esc($u, 'attr') ?>" alt="">
            <?php endforeach; ?>
        </div>
    </div>
<?php elseif ($photos['mode'] !== 'none'): ?>
    <div class="sf-hero">
        <img src="<?= esc($photos['urls'][0], 'attr') ?>" alt="">
        <div class="sf-hero-scrim">
            <div class="sf-shell" style="width: 100%;">
                <h1 class="sf-hero-h"><?= esc($service['title']) ?></h1>
                <div class="sf-hero-meta">
                    <?php if ($rating !== null): ?>
                        <span class="sf-rating-chip"><i class="fas fa-star" aria-hidden="true"></i><?= esc(number_format($rating, 1)) ?> · <?= $bookCnt ?> booking<?= $bookCnt === 1 ? '' : 's' ?></span>
                    <?php endif; ?>
                    <?php if ($fromStr !== ''): ?>
                        <span class="sf-fromprice-pill"><?= esc($fromStr) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="sf-shell">
    <div class="sf-cols">
        <div>
            <?php if ($photos['mode'] === 'framed' || $photos['mode'] === 'filmstrip' || $photos['mode'] === 'none'): ?>
                <div class="sf-sec" style="padding-bottom: 0;">
                    <?php if ($catShort !== ''): ?><p class="sf-eyebrow"><?= esc($catShort) ?></p><?php endif; ?>
                    <h1 style="font-size: 20px; font-weight: 700; margin: 0 0 6px; letter-spacing: -0.01em;"><?= esc($service['title']) ?></h1>
                    <?php if ($rating !== null): ?>
                        <span class="sf-rating-chip" style="color: var(--sf-ink);"><i class="fas fa-star" aria-hidden="true"></i><?= esc(number_format($rating, 1)) ?> · <?= $bookCnt ?> booking<?= $bookCnt === 1 ? '' : 's' ?></span>
                    <?php elseif (! empty($newVendor['isNew'])): ?>
                        <span class="sf-newbadge"><i class="fas fa-seedling" aria-hidden="true"></i>New on PartySmith</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="sf-flash error" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <!-- Availability checker (submits to the service page = real quote) -->
            <form class="sf-check-card" method="get" action="<?= esc($checkHref, 'attr') ?>" style="margin-top: <?= $photos['mode'] === 'mosaic' || ($photos['mode'] !== 'none' && $photos['mode'] !== 'framed' && $photos['mode'] !== 'filmstrip') ? '-12px' : '14px' ?>;">
                <h2>Check your date</h2>
                <div class="sf-2col">
                    <label class="sf-field">
                        <span>Date</span>
                        <input class="sf-input" type="date" name="date" required min="<?= date('Y-m-d') ?>">
                    </label>
                    <label class="sf-field">
                        <span>Postcode</span>
                        <input class="sf-input" type="text" name="postcode" maxlength="10" placeholder="e.g. DA7" autocomplete="postal-code">
                    </label>
                </div>
                <button type="submit" class="sf-btn block">Check availability &amp; price</button>
                <p class="sf-microcopy">Exact travel cost shown next — no surprises</p>
            </form>

            <div class="sf-chips">
                <span class="sf-chip"><i class="fas fa-bolt" aria-hidden="true"></i>Usually confirms fast</span>
                <span class="sf-chip"><i class="fas fa-shield-halved" aria-hidden="true"></i>Insured</span>
                <span class="sf-chip"><i class="fas fa-rotate-left" aria-hidden="true"></i>Free 14-day cancellation</span>
            </div>

            <?php if (! empty($newVendor['isNew']) && $photos['mode'] !== 'framed' && $rating === null): ?>
                <p style="margin: 0 0 14px;"><span class="sf-newbadge"><i class="fas fa-seedling" aria-hidden="true"></i>New on PartySmith</span></p>
            <?php endif; ?>

            <?php if ($included !== []): ?>
                <section class="sf-sec">
                    <h2 class="sf-sec-h">What's included</h2>
                    <ul class="sf-included">
                        <?php foreach (array_slice($included, 0, 5) as $line): ?>
                            <li><i class="fas fa-check" aria-hidden="true"></i><?= esc($line) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php if (! empty($service['description'])): ?>
                <section class="sf-sec" style="padding-top: 0;">
                    <p style="font-size: 13.5px; color: var(--sf-muted); max-width: 60ch; margin: 0;"><?= esc($service['description']) ?></p>
                </section>
            <?php endif; ?>

            <section class="sf-sec">
                <?php if ($reviews !== []): $r = $reviews[0]; ?>
                    <div class="sf-card sf-review">
                        <span class="sf-stars"><?php for ($i = 1; $i <= 5; $i++): ?><i class="fas fa-star<?= $i > (int) $r['rating'] ? ' off' : '' ?>" aria-hidden="true"></i><?php endfor; ?></span>
                        <span class="ctx"><?= ! empty($r['created_at']) ? esc(date('M Y', strtotime($r['created_at']))) : '' ?></span>
                        <blockquote>&ldquo;<?= esc($r['comment'] ?? $r['title'] ?? '') ?>&rdquo;</blockquote>
                        <span class="who"><?= esc($r['reviewer'] ?? 'Verified customer') ?></span>
                    </div>
                <?php elseif (! empty($newVendor['isNew'])): ?>
                    <div class="sf-card sf-noreviews">
                        <b>No reviews yet</b> — <?= esc(strtok($bn, ' ')) ?> joined in <?= esc($newVendor['joined']) ?>.
                        Every booking is protected the same way: your deposit is held by PartySmith until they confirm,
                        and cancellation is free for 14 days.
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- Laptop: sticky booking panel -->
        <aside class="sf-panel sticky" style="display: none;" data-sf-desktop>
            <div class="price-head">
                <?php if ($from['amount'] > 0): ?>
                    <span class="amt">£<?= esc(number_format($from['amount'], $from['amount'] == (int) $from['amount'] ? 0 : 2)) ?></span>
                    <?php if ($from['per'] !== ''): ?><span class="per">/<?= esc($from['per']) ?></span><?php endif; ?>
                <?php endif; ?>
            </div>
            <p style="font-size: 12px; color: var(--sf-muted); margin: 0 0 12px;"><?= $deposit ?>% deposit holds it</p>
            <form method="get" action="<?= esc($checkHref, 'attr') ?>">
                <label class="sf-field"><span>Date</span><input class="sf-input" type="date" name="date" required min="<?= date('Y-m-d') ?>"></label>
                <label class="sf-field"><span>Postcode</span><input class="sf-input" type="text" name="postcode" maxlength="10" placeholder="e.g. DA7"></label>
                <button type="submit" class="sf-btn block">Check availability &amp; price</button>
            </form>
            <ul class="reassure">
                <li><i class="fas fa-location-dot" aria-hidden="true"></i>Exact travel cost calculated — no surprises</li>
                <li><i class="fas fa-lock" aria-hidden="true"></i>Secure card payment, deposit protected</li>
                <li><i class="fas fa-bolt" aria-hidden="true"></i>You get a confirmation text</li>
            </ul>
        </aside>
    </div>
</div>

<!-- Mobile sticky bar -->
<div class="sf-stickybar">
    <div class="figures">
        <div class="t"><?= esc($service['title']) ?></div>
        <?php if ($fromStr !== ''): ?><div class="d"><?= esc($fromStr) ?></div><?php endif; ?>
    </div>
    <a class="sf-btn" href="<?= esc($checkHref, 'attr') ?>">Book this date</a>
</div>

<style>@media (min-width: 980px) { [data-sf-desktop] { display: block !important; } }</style>

<?= $this->include('tenant_footer') ?>
