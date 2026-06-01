<?= $this->include('header') ?>

<div class="container py-4">

    <!-- Host header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <?php if (! empty($vendor_profile['photo_path'])): ?>
                    <img src="<?= base_url(esc($vendor_profile['photo_path'])) ?>"
                         alt="<?= esc($vendor_profile['name']) ?>"
                         class="rounded-circle"
                         style="width:84px;height:84px;object-fit:cover"
                         onerror="this.style.display='none'">
                <?php else: ?>
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                         style="width:84px;height:84px;font-size:2rem">
                        <?= esc(strtoupper(substr($vendor_profile['name'], 0, 1))) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <h1 class="h3 mb-1"><?= esc($vendor_profile['name']) ?></h1>
                    <?php if (! empty($vendor_profile['tagline'])): ?>
                        <div class="text-muted"><?= esc($vendor_profile['tagline']) ?></div>
                    <?php endif; ?>
                    <?php if (! empty($vendor_profile['since'])): ?>
                        <div class="text-muted small">Member since <?= esc((string) $vendor_profile['since']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (! empty($vendor_profile['bio'])): ?>
                <p class="mt-3 mb-0"><?= nl2br(esc($vendor_profile['bio'])) ?></p>
            <?php endif; ?>

            <?php if (! empty($vendor_profile['plays'])): ?>
                <div class="mt-3">
                    <?php foreach ($vendor_profile['plays'] as $playTag): ?>
                        <span class="badge bg-light text-dark border me-1 mb-1"><?= esc($playTag) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (! empty($vendor_profile['quote'])): ?>
                <blockquote class="blockquote mt-3 fst-italic text-muted" style="font-size:1rem">
                    &ldquo;<?= esc($vendor_profile['quote']) ?>&rdquo;
                </blockquote>
            <?php endif; ?>
        </div>
    </div>

    <!-- Services -->
    <h2 class="h4 mb-3"><?= esc($vendor_profile['name']) ?>&rsquo;s services</h2>

    <?php if (! empty($services)): ?>
        <div class="service-card-container">
            <?php foreach ($services as $service): ?>
                <div class="service-card">
                    <a href="<?= base_url('service/view/' . esc($service['id'])) ?>" class="service-card-link text-decoration-none text-body">
                        <?php if (! empty($service['images'])): ?>
                            <img src="<?= base_url(esc($service['images'][0]['thumbnail_path'])) ?>"
                                 alt="<?= esc($service['title']) ?>" class="service-card-image" loading="lazy" decoding="async"
                                 onerror="this.onerror=null;this.src='<?= base_url('assets/images/fallback-service-card.jpg') ?>';">
                        <?php else: ?>
                            <img src="<?= base_url('assets/images/fallback-service-card.jpg') ?>" alt="No image available"
                                 class="service-card-image" loading="lazy" decoding="async">
                        <?php endif; ?>

                        <div class="service-card-content">
                            <h3 class="service-card-title"><?= esc($service['title']) ?></h3>
                            <?php if (! empty($service['category_name'])): ?>
                                <span class="badge bg-secondary mb-2"><?= esc($service['category_name']) ?></span>
                            <?php endif; ?>
                            <p class="service-card-description">
                                <?= esc($service['short_description'] ?? 'No description available.') ?>
                            </p>
                            <?php if ((float) ($service['price'] ?? 0) > 0): ?>
                                <p class="service-price">From &pound;<?= number_format((float) $service['price'], 2) ?></p>
                            <?php else: ?>
                                <p class="service-price text-muted">Price on request</p>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="text-center pb-3">
                        <a href="<?= base_url('service/view/' . esc($service['id'])) ?>" class="service-card-button">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5 text-muted">This supplier has no active services right now.</div>
    <?php endif; ?>

</div>

<?= $this->include('footer') ?>
