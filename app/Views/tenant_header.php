<?php

/**
 * White-label tenant layout — header (Storefront System handoff, frames 1a–1n).
 *
 * Neutral SaaS chrome: tenant pages load ONLY the storefront stylesheet +
 * Instrument Sans + Font Awesome — no marketplace CSS, no PartySmith branding
 * (the sole PartySmith reference is the muted "Powered by" line in the
 * footer). Vendor theming = two injected custom properties (--sf-primary,
 * --sf-accent) + tints derived in CSS.
 *
 * Handoff rules enforced here:
 *  - phone appears exactly once in the header (icon button, 44px hit area)
 *  - logo below 128px wide is never scaled up → monogram tile instead
 *  - vendor colours are contrast-checked; a primary too light to carry white
 *    text falls back to its darkened form (vendor colour never carries body
 *    text — the stylesheet keeps all body copy neutral)
 *
 * Expects $site (vendor_sites row); optional $pageTitle, $metaDescription,
 * $hasStickyBar (adds bottom padding so content clears the fixed bar).
 */
$site ??= service('tenant')->site() ?? [];

$businessName = trim((string) ($site['business_name'] ?? '')) ?: 'Storefront';
$headTitle    = isset($pageTitle) && trim((string) $pageTitle) !== ''
    ? trim((string) $pageTitle) . ' — ' . $businessName
    : $businessName;

if (! function_exists('tenant_hex_color')) {
    /**
     * Only well-formed #RGB/#RRGGBB values may reach the inline <style>.
     */
    function tenant_hex_color(?string $value): ?string
    {
        $value = trim((string) $value);

        return preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value) === 1 ? $value : null;
    }

    /**
     * Darken a hex colour by $amount (0–1).
     */
    function tenant_darken_hex(string $hex, float $amount): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $out = '#';

        foreach (str_split($hex, 2) as $channel) {
            $out .= str_pad(dechex((int) round(hexdec($channel) * (1 - $amount))), 2, '0', STR_PAD_LEFT);
        }

        return $out;
    }

    /**
     * Handoff contrast rule: the primary must carry white CTA text. If its
     * YIQ luminance is too high, fall back to a darkened form (repeatedly,
     * for pathological near-white picks).
     */
    function tenant_contrast_safe(string $hex): string
    {
        for ($i = 0; $i < 4; $i++) {
            $h = ltrim($hex, '#');
            if (strlen($h) === 3) {
                $h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
            }
            [$r, $g, $b] = [hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2))];
            $yiq          = ($r * 299 + $g * 587 + $b * 114) / 1000;
            if ($yiq <= 170) {
                return $hex;
            }
            $hex = tenant_darken_hex($hex, 0.25);
        }

        return $hex;
    }
}

if (! function_exists('sf_rating_line')) {
    /**
     * Shared rating line for hero, header and cards. Verified-booking count is
     * only shown once it is meaningful (>= threshold); below that the vendor is
     * "Verified" without a number, so an almost-empty vendor never advertises
     * "1 verified booking". Single source of this rule for every surface.
     */
    function sf_rating_line(?float $rating, int $bookings, int $threshold = 10): string
    {
        if ($rating === null) {
            return '';
        }
        $line = number_format($rating, 1) . ' · ';

        return $line . ($bookings >= $threshold
            ? $bookings . ' verified booking' . ($bookings === 1 ? '' : 's')
            : 'Verified vendor');
    }
}

$rawPrimary   = tenant_hex_color($site['primary_color'] ?? null);
$primary      = $rawPrimary !== null ? tenant_contrast_safe($rawPrimary) : null;
$accent       = tenant_hex_color($site['secondary_color'] ?? null);

$phone     = trim((string) ($site['phone'] ?? ''));
$phoneHref = $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '';

// Logo fallback: missing OR too small (<128px wide) → monogram tile.
$logoPath = trim((string) ($site['logo_path'] ?? ''));
$logoUrl  = '';
if ($logoPath !== '') {
    $abs = FCPATH . ltrim($logoPath, '/');
    if (is_file($abs)) {
        $dim = @getimagesize($abs);
        if ($dim === false || (int) $dim[0] >= 128) {
            $logoUrl = '/' . ltrim($logoPath, '/');
        }
    } else {
        $logoUrl = '/' . ltrim($logoPath, '/');
    }
}

$initials = '';
foreach (preg_split('/\s+/', $businessName) as $word) {
    if ($word !== '' && strlen($initials) < 2) {
        $initials .= strtoupper(mb_substr($word, 0, 1));
    }
}
if ($initials === '') {
    $initials = 'P';
}

$headSub = trim((string) ($headerSubline ?? ''));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= esc($headTitle) ?></title>
    <?php if (! empty($metaDescription)): ?>
        <meta name="description" content="<?= esc($metaDescription) ?>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/tenant-storefront.css">

    <?php if ($primary !== null || $accent !== null): ?>
    <style>
        :root {
            <?php if ($primary !== null): ?>
            --sf-primary: <?= $primary ?>;
            --sf-primary-deep: <?= tenant_darken_hex($primary, 0.18) ?>;
            <?php endif; ?>
            <?php if ($accent !== null): ?>
            --sf-accent: <?= $accent ?>;
            <?php endif; ?>
        }
    </style>
    <?php endif; ?>
</head>

<body class="sf-body<?= ! empty($hasStickyBar) ? ' sf-has-stickybar' : '' ?>">

    <a href="#sf-main" class="sf-skip">Skip to main content</a>

    <header class="sf-head">
        <div class="sf-shell sf-head-in">
            <a class="sf-brand" href="/">
                <?php if ($logoUrl !== ''): ?>
                    <img class="sf-logo" src="<?= esc($logoUrl, 'attr') ?>" alt="<?= esc($businessName, 'attr') ?>">
                <?php else: ?>
                    <span class="sf-mono" aria-hidden="true"><?= esc($initials) ?></span>
                <?php endif; ?>
                <span style="min-width: 0;">
                    <span class="sf-bname"><?= esc($businessName) ?></span>
                    <?php if ($headSub !== ''): ?>
                        <span class="sf-bsub"><?= esc($headSub) ?></span>
                    <?php endif; ?>
                </span>
            </a>

            <div class="sf-head-actions">
                <?php // Revealed by JS (body.sf-scrolled) once the hero is scrolled past — same
                      // target as the hero's "Get an instant quote" CTA (frame 1n sticky bar). ?>
                <?php if (! empty($stickyQuote['href'])): ?>
                    <?php $stickyRating = sf_rating_line($stickyQuote['rating'] ?? null, (int) ($stickyQuote['bookings'] ?? 0)); ?>
                    <?php if ($stickyRating !== ''): ?>
                        <span class="sf-head-rating"><i class="fas fa-star" aria-hidden="true"></i><?= esc($stickyRating) ?></span>
                    <?php endif; ?>
                    <a class="sf-btn sf-btn-compact sf-headcta" href="<?= esc($stickyQuote['href'], 'attr') ?>">Get an instant quote</a>
                <?php endif; ?>
                <?php if ($phone !== ''): ?>
                    <a class="sf-phonebtn" href="<?= esc($phoneHref, 'attr') ?>" aria-label="Call <?= esc($businessName, 'attr') ?> on <?= esc($phone, 'attr') ?>">
                        <i class="fas fa-phone" aria-hidden="true"></i><span class="num"><?= esc($phone) ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main id="sf-main" tabindex="-1">
