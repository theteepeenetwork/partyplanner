<?php

/**
 * White-label tenant layout — header (T4).
 *
 * Expects $site (vendor_sites row) from TenantController; falls back to the
 * shared tenant service. Optional: $pageTitle (prefixed to the business name).
 *
 * Branding: primary_color/secondary_color are injected as CSS custom
 * properties over the :root palette that style.css defines and the .ps-app
 * design system consumes. No marketplace nav and no PartySmith branding here
 * — the only PartySmith reference is the "Powered by" line in tenant_footer.
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
     * Darken a hex colour by $amount (0–1) — derives the deep/darkest tones
     * (button hover, footer ground) from the vendor's primary colour.
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
}

$primaryColor   = tenant_hex_color($site['primary_color'] ?? null);
$secondaryColor = tenant_hex_color($site['secondary_color'] ?? null);

$phone     = trim((string) ($site['phone'] ?? ''));
$phoneHref = $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '';

$logoPath = trim((string) ($site['logo_path'] ?? ''));
$logoUrl  = $logoPath !== '' ? '/' . ltrim($logoPath, '/') : '';

// Monogram fallback (first letters of the first two words) when there's no logo.
$initials = '';
foreach (preg_split('/\s+/', $businessName) as $word) {
    if ($word !== '' && strlen($initials) < 2) {
        $initials .= strtoupper(mb_substr($word, 0, 1));
    }
}
if ($initials === '') {
    $initials = 'P';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($headTitle) ?></title>
    <?php if (! empty($metaDescription)): ?>
        <meta name="description" content="<?= esc($metaDescription) ?>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Newsreader:ital,opsz,wght@0,16..72,400;0,16..72,500;0,16..72,600;1,16..72,400;1,16..72,500&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Newsreader:ital,opsz,wght@0,16..72,400;0,16..72,500;0,16..72,600;1,16..72,400;1,16..72,500&display=swap" rel="stylesheet">
    </noscript>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- Partysmith design system, scoped under .ps-app -->
    <link rel="stylesheet" href="/assets/css/partysmith-app.css">
    <!-- White-label storefront components, themed by the palette below -->
    <link rel="stylesheet" href="/assets/css/tenant-storefront.css">

    <?php if ($primaryColor !== null || $secondaryColor !== null): ?>
    <!-- Tenant brand palette: overrides the :root tokens the design system reads -->
    <style>
        :root {
            <?php if ($primaryColor !== null): ?>
            --green: <?= $primaryColor ?>;
            --green-bright: <?= $primaryColor ?>;
            --green-deep: <?= tenant_darken_hex($primaryColor, 0.18) ?>;
            --green-darkest: <?= tenant_darken_hex($primaryColor, 0.38) ?>;
            --bs-primary: <?= $primaryColor ?>;
            <?php endif; ?>
            <?php if ($secondaryColor !== null): ?>
            --gold: <?= $secondaryColor ?>;
            --gold-bright: <?= $secondaryColor ?>;
            <?php endif; ?>
        }
    </style>
    <?php endif; ?>
</head>

<body>

    <a href="#main-content" class="skip-link">Skip to main content</a>

    <header>
        <nav class="navbar fixed-top shadow-sm">
            <div class="container d-flex align-items-center justify-content-between flex-nowrap">
                <a class="sf-brandwrap" href="/">
                    <?php if ($logoUrl !== ''): ?>
                        <img src="<?= esc($logoUrl, 'attr') ?>" alt="<?= esc($businessName, 'attr') ?>" style="height: 44px; width: auto;">
                    <?php else: ?>
                        <span class="sf-monogram" aria-hidden="true"><?= esc($initials) ?></span>
                    <?php endif; ?>
                    <span>
                        <span class="sf-bn"><?= esc($businessName) ?></span>
                        <?php if ($phone !== ''): ?>
                            <span class="sf-bsub d-none d-sm-block"><?= esc($phone) ?></span>
                        <?php endif; ?>
                    </span>
                </a>

                <?php if ($phone !== ''): ?>
                    <a class="btn btn-nav-cta" href="<?= esc($phoneHref, 'attr') ?>">
                        <i class="fas fa-phone" aria-hidden="true"></i>&nbsp;<?= esc($phone) ?>
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <div id="main-content" tabindex="-1">
    <div class="ps-app">
