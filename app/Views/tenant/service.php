<?php
/**
 * Tenant storefront service detail. Layout/branding lands with T4
 * (tenant_header/tenant_footer); this stage renders the service.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($service['title']) ?> — <?= esc($site['business_name']) ?></title>
</head>
<body>
    <header>
        <p><a href="/">&larr; <?= esc($site['business_name']) ?></a></p>
    </header>

    <main>
        <h1><?= esc($service['title']) ?></h1>
        <?php if (! empty($categoryName)): ?>
            <p><?= esc($categoryName) ?></p>
        <?php endif; ?>
        <?php if (! empty($service['short_description'])): ?>
            <p><?= esc($service['short_description']) ?></p>
        <?php endif; ?>
        <?php if (! empty($service['description'])): ?>
            <p><?= esc($service['description']) ?></p>
        <?php endif; ?>
        <?php if (! empty($site['phone'])): ?>
            <p>Call us on <a href="tel:<?= esc(preg_replace('/\s+/', '', $site['phone']), 'attr') ?>"><?= esc($site['phone']) ?></a> to book.</p>
        <?php endif; ?>
    </main>
</body>
</html>
