<?= $this->include('tenant_header') ?>

<main class="section" style="padding-top: clamp(44px, 6vw, 78px);">
    <div class="container">
        <div class="section-head">
            <p class="eyebrow">What we do</p>
            <h1 class="heading"><?= esc($site['business_name']) ?></h1>
            <?php if (! empty($site['about_text'])): ?>
                <p class="lead"><?= esc($site['about_text']) ?></p>
            <?php endif; ?>
        </div>

        <?php if (empty($services)): ?>
            <p class="lead">No services are available right now — please check back soon.</p>
        <?php else: ?>
            <div class="sup-grid">
                <?php foreach ($services as $service):
                    $serviceUrl = '/service/' . (int) $service['id'];
                    $thumb      = ! empty($service['images'])
                        ? '/' . ltrim((string) ($service['images'][0]['thumbnail_path'] ?? $service['images'][0]['image_path'] ?? ''), '/')
                        : '/assets/images/fallback-service-card.jpg';
                    $category = trim((string) ($service['category_name'] ?? ''));
                    ?>
                    <article class="sup-card">
                        <a class="sup-media" href="<?= $serviceUrl ?>">
                            <img src="<?= esc($thumb, 'attr') ?>" alt="<?= esc($service['title'], 'attr') ?>" loading="lazy"
                                onerror="this.onerror=null;this.src='/assets/images/fallback-service-card.jpg';">
                        </a>
                        <div class="sup-body">
                            <?php if ($category !== ''): ?>
                                <span class="sup-cat"><?= esc($category) ?></span>
                            <?php endif; ?>
                            <h3><a href="<?= $serviceUrl ?>"><?= esc($service['title']) ?></a></h3>
                            <?php if (! empty($service['short_description'])): ?>
                                <p class="sup-meta"><?= esc($service['short_description']) ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?= $this->include('tenant_footer') ?>
