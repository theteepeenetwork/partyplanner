<?php
/**
 * Tenant storefront home: the vendor's own catalogue. Layout/branding lands
 * with T4 (tenant_header/tenant_footer); this stage renders the catalogue.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($site['business_name']) ?></title>
</head>
<body>
    <header>
        <h1><?= esc($site['business_name']) ?></h1>
        <?php if (! empty($site['phone'])): ?>
            <p><a href="tel:<?= esc(preg_replace('/\s+/', '', $site['phone']), 'attr') ?>"><?= esc($site['phone']) ?></a></p>
        <?php endif; ?>
    </header>

    <main>
        <?php if (! empty($site['about_text'])): ?>
            <p><?= esc($site['about_text']) ?></p>
        <?php endif; ?>

        <?php if (empty($services)): ?>
            <p>No services are available right now — please check back soon.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($services as $service): ?>
                    <li>
                        <a href="/service/<?= (int) $service['id'] ?>"><?= esc($service['title']) ?></a>
                        <?php if (! empty($service['short_description'])): ?>
                            — <?= esc($service['short_description']) ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>
</html>
