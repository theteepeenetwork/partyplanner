<?= $this->include('tenant_header') ?>

<main class="section" style="padding-top: clamp(44px, 6vw, 78px);">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb" style="margin-bottom: 18px;">
            <a href="/"><?= esc($site['business_name']) ?></a><span class="sep">/</span>
            <span class="cur"><?= esc($service['title']) ?></span>
        </nav>

        <div class="section-head" style="margin-bottom: 28px;">
            <?php if (! empty($categoryName)): ?>
                <p class="eyebrow"><?= esc($categoryName) ?></p>
            <?php endif; ?>
            <h1 class="heading"><?= esc($service['title']) ?></h1>
            <?php if (! empty($service['short_description'])): ?>
                <p class="lead"><?= esc($service['short_description']) ?></p>
            <?php endif; ?>
        </div>

        <?php if (! empty($service['images'])): ?>
            <div class="row g-3" style="margin-bottom: 32px;">
                <?php foreach (array_slice($service['images'], 0, 3) as $image):
                    $imgSrc = '/' . ltrim((string) ($image['image_path'] ?? ''), '/');
                    ?>
                    <div class="col-md-4">
                        <img src="<?= esc($imgSrc, 'attr') ?>" alt="<?= esc($service['title'], 'attr') ?>"
                            style="width: 100%; height: 240px; object-fit: cover; border-radius: var(--r-img);"
                            loading="lazy" onerror="this.closest('.col-md-4').remove();">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (! empty($service['description'])): ?>
            <div style="max-width: 70ch;">
                <p class="lead" style="font-size: 16.5px;"><?= nl2br(esc($service['description'])) ?></p>
            </div>
        <?php endif; ?>

        <?php if (! empty($site['phone'])):
            $phoneHref = 'tel:' . preg_replace('/[^0-9+]/', '', (string) $site['phone']);
            ?>
            <div style="margin-top: 36px;">
                <a class="btn btn-primary btn-lg" href="<?= esc($phoneHref, 'attr') ?>">
                    <i class="fas fa-phone" aria-hidden="true"></i> Call <?= esc($site['phone']) ?> to book
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?= $this->include('tenant_footer') ?>
