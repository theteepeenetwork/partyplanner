<?php
$fallbackImage = base_url('assets/images/' . esc($serviceFallbackImage ?? 'fallback-service-card.jpg'));
$heroBg        = base_url('assets/images/' . esc($heroImage ?? 'hero-event-planning.jpg'));
$vendorImg     = base_url('assets/images/' . esc($vendorCtaImage ?? 'vendor-cta.jpg'));
$planUrl       = session()->has('user_id') ? base_url('event/create') : base_url('register');
?>
<?= $this->include('header') ?>

<main class="full-width-container home-page home-marketplace">
    <section class="hero-section" aria-labelledby="home-hero-heading">
        <div class="hero-image" style="background-image: url('<?= $heroBg ?>');">
            <div class="overlay hero-overlay d-flex align-items-center justify-content-center">
                <div class="text-center text-white hero-copy">
                    <p class="hero-eyebrow">UK event supplier marketplace</p>
                    <h1 id="home-hero-heading" class="hero-headline">
                        Plan your perfect event in one organised place
                    </h1>
                    <p class="hero-sublead mx-auto">
                        Find trusted suppliers, compare services, request quotes and keep every booking beautifully organised.
                    </p>
                    <div class="hero-cta-group d-flex flex-wrap gap-2 justify-content-center">
                        <a href="<?= $planUrl ?>" class="btn btn-home-primary btn-lg">Start planning</a>
                        <a href="<?= base_url('browse-services') ?>" class="btn btn-home-outline-light btn-lg">Browse suppliers</a>
                    </div>

                    <form class="search-form hero-search d-none d-lg-flex justify-content-center"
                        action="<?= base_url('search') ?>" method="get" role="search">
                        <div class="hero-search-inner hero-search-inner--simple">
                            <label class="visually-hidden" for="home-search-q">Search suppliers</label>
                            <input type="search" class="form-control hero-search-q" id="home-search-q" name="q"
                                placeholder="Search photographers, venues, catering…" autocomplete="off">
                            <button class="btn btn-primary hero-search-btn" type="submit">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="search-form-container d-lg-none">
            <form class="search-form py-3 px-2" action="<?= base_url('search') ?>" method="get" role="search">
                <div class="hero-search-inner hero-search-inner--simple hero-search-inner--stacked mx-auto">
                    <label class="visually-hidden" for="home-search-q-mobile">Search suppliers</label>
                    <input type="search" class="form-control" id="home-search-q-mobile" name="q"
                        placeholder="Search photographers, venues, catering…" autocomplete="off">
                    <button class="btn btn-primary w-100 hero-search-btn" type="submit">Search</button>
                </div>
            </form>
        </div>
    </section>

    <section class="trust-strip-compact home-trust-strip" aria-label="Why planners trust us">
        <div class="container">
            <ul class="home-trust-grid">
                <li class="home-trust-item">
                    <span class="home-trust-icon" aria-hidden="true"><i class="fas fa-circle-check"></i></span>
                    <span>Verified suppliers</span>
                </li>
                <li class="home-trust-item">
                    <span class="home-trust-icon" aria-hidden="true"><i class="fas fa-file-lines"></i></span>
                    <span>Quotes in one place</span>
                </li>
                <li class="home-trust-item">
                    <span class="home-trust-icon" aria-hidden="true"><i class="fas fa-lock"></i></span>
                    <span>Secure messaging</span>
                </li>
                <li class="home-trust-item">
                    <span class="home-trust-icon" aria-hidden="true"><i class="fas fa-calendar-days"></i></span>
                    <span>Weddings to corporate events</span>
                </li>
            </ul>
        </div>
    </section>

    <?php if (! empty($cmsHome['content'])): ?>
    <section class="home-section section-surface text-center">
        <div class="container cms-intro">
            <?= $cmsHome['content'] ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="home-section section-surface text-center" aria-labelledby="home-categories-heading">
        <div class="container">
            <p class="section-eyebrow mb-2">Browse by category</p>
            <h2 id="home-categories-heading" class="section-heading mb-2">What does your event need?</h2>
            <p class="section-lead mx-auto mb-4">
                From intimate celebrations to large corporate gatherings—explore trusted UK suppliers by service type.
            </p>
        </div>
        <?php if (! empty($homeCategoryTiles)): ?>
        <div class="category-card-container">
            <?php foreach ($homeCategoryTiles as $tile): ?>
                <a class="category-card category-card-link"
                    href="<?= esc($tile['href']) ?>"
                    aria-label="Browse <?= esc($tile['name']) ?>">
                    <div class="category-card-media">
                        <img src="<?= base_url('assets/images/' . esc($tile['image'])) ?>"
                            alt="<?= esc($tile['name']) ?> suppliers"
                            class="category-card-image"
                            width="400"
                            height="400"
                            loading="lazy"
                            decoding="async"
                            onerror="this.onerror=null;this.src='<?= base_url('assets/images/fallback-service-card.jpg') ?>';">
                        <div class="category-card-gradient" aria-hidden="true"></div>
                        <div class="category-card-category"><?= esc($tile['name']) ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="container mt-4">
            <a href="<?= base_url('browse-services') ?>" class="btn btn-home-secondary">View all categories</a>
        </div>
    </section>

    <section class="home-section how-it-works" aria-labelledby="home-how-heading">
        <div class="container text-center">
            <p class="section-eyebrow mb-2">Simple process</p>
            <h2 id="home-how-heading" class="section-heading mb-4">How it works</h2>
            <div class="how-it-works-steps">
                <div class="how-it-works-card">
                    <div class="icon-wrapper">
                        <i class="fas fa-calendar-plus" aria-hidden="true"></i>
                    </div>
                    <span class="step-number">Step 1</span>
                    <h3 class="card-title">Create your event</h3>
                    <p class="card-description">Add your date, location, and occasion so suppliers understand your brief.</p>
                </div>
                <div class="how-it-works-card">
                    <div class="icon-wrapper">
                        <i class="fas fa-compass" aria-hidden="true"></i>
                    </div>
                    <span class="step-number">Step 2</span>
                    <h3 class="card-title">Discover suppliers</h3>
                    <p class="card-description">Browse verified listings, compare services, and save your favourites.</p>
                </div>
                <div class="how-it-works-card">
                    <div class="icon-wrapper">
                        <i class="fas fa-comments" aria-hidden="true"></i>
                    </div>
                    <span class="step-number">Step 3</span>
                    <h3 class="card-title">Request quotes</h3>
                    <p class="card-description">Message vendors and receive quotes in one organised inbox.</p>
                </div>
                <div class="how-it-works-card">
                    <div class="icon-wrapper">
                        <i class="fas fa-shield-halved" aria-hidden="true"></i>
                    </div>
                    <span class="step-number">Step 4</span>
                    <h3 class="card-title">Book with confidence</h3>
                    <p class="card-description">Confirm bookings and keep every detail in your planning dashboard.</p>
                </div>
            </div>
            <a href="<?= $planUrl ?>" class="btn btn-home-primary btn-lg mt-4">Start planning</a>
        </div>
    </section>

    <section class="home-section section-surface-alt" aria-labelledby="home-services-heading">
        <div class="container d-flex flex-column flex-md-row align-items-md-end justify-content-md-between gap-3 mb-4 text-md-start text-center">
            <div>
                <p class="section-eyebrow mb-2">Featured listings</p>
                <h2 id="home-services-heading" class="section-heading mb-0">Popular services</h2>
                <p class="section-lead mt-2 mb-0">Hand-picked suppliers to inspire your next celebration.</p>
            </div>
            <a href="<?= base_url('browse-services') ?>" class="btn btn-home-primary align-self-center align-self-md-end">See all services</a>
        </div>
        <div class="service-card-container">
            <?php foreach ($services as $service): ?>
                <?php
                $serviceUrl = base_url('service/view/' . (int) $service['id']);
                $thumb      = ! empty($service['images'])
                    ? base_url(esc($service['images'][0]['thumbnail_path']))
                    : $fallbackImage;
                $location = trim((string) ($service['service_location'] ?? ''));
                if ($location === '') {
                    $location = 'UK-wide';
                }
                $price = isset($service['price']) ? (float) $service['price'] : null;
                $categoryLabel = trim((string) ($service['category_label'] ?? ''));
                if ($categoryLabel === '') {
                    $categoryLabel = 'Event services';
                }
                $isVerified = ! empty($service['license']);
                ?>
                <article class="service-card">
                    <a href="<?= $serviceUrl ?>" class="service-card-media d-block text-decoration-none">
                        <img src="<?= $thumb ?>"
                            alt="<?= esc($service['title']) ?>"
                            class="service-card-image"
                            width="400"
                            height="300"
                            loading="lazy"
                            decoding="async"
                            onerror="this.onerror=null;this.src='<?= $fallbackImage ?>';">
                        <?php if ($isVerified): ?>
                            <span class="service-card-verified"><i class="fas fa-circle-check" aria-hidden="true"></i> Verified</span>
                        <?php endif; ?>
                    </a>
                    <div class="service-card-content text-start">
                        <span class="service-card-category"><?= esc($categoryLabel) ?></span>
                        <h3 class="service-card-title">
                            <a href="<?= $serviceUrl ?>" class="text-decoration-none text-reset"><?= esc($service['title']) ?></a>
                        </h3>
                        <div class="service-card-meta">
                            <span class="service-card-location">
                                <i class="fas fa-location-dot" aria-hidden="true"></i> <?= esc($location) ?>
                            </span>
                            <?php if ($price !== null && $price > 0): ?>
                                <span class="service-card-price">From £<?= number_format($price, 0) ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="service-card-description">
                            <?= esc($service['short_description'] ?? 'View full details and request a quote from this supplier.') ?>
                        </p>
                    </div>
                    <div class="service-card-footer text-start">
                        <a href="<?= $serviceUrl ?>" class="service-card-cta">
                            View details <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="home-section vendor-cta-section" aria-labelledby="home-vendor-heading">
        <div class="container px-0 px-lg-3">
            <div class="vendor-cta-grid">
                <div class="vendor-cta-copy">
                    <p class="section-eyebrow mb-2">For suppliers</p>
                    <h2 id="home-vendor-heading" class="section-heading">Are you an event supplier?</h2>
                    <p class="section-lead mb-4">
                        List your services, receive enquiries and manage bookings from one simple dashboard.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= base_url('register/vendor') ?>" class="btn btn-home-primary">Become a vendor</a>
                        <a href="<?= base_url('vendor-info') ?>" class="btn btn-home-secondary">How it works for vendors</a>
                    </div>
                </div>
                <div class="vendor-cta-media">
                    <img src="<?= $vendorImg ?>"
                        alt="Event supplier preparing a celebration"
                        width="600"
                        height="400"
                        loading="lazy"
                        decoding="async"
                        onerror="this.onerror=null;this.src='<?= $fallbackImage ?>';">
                </div>
            </div>
        </div>
    </section>

    <section class="home-section social-proof-section text-center" aria-labelledby="home-proof-heading">
        <div class="container">
            <p class="section-eyebrow mb-2" style="color: var(--home-gold, #c4a574);">Social proof</p>
            <h2 id="home-proof-heading" class="section-heading">Trusted by planners across the UK</h2>
            <p class="section-lead mx-auto">Couples, families, and corporate teams use one calm space to plan with confidence.</p>

            <?php /* TEMP: placeholder stats until live metrics are wired — replace with real counts */ ?>
            <div class="proof-stats" data-stats-temporary="true">
                <div class="proof-stat-card">
                    <p class="proof-stat-value">500+</p>
                    <p class="proof-stat-label">supplier listings</p>
                </div>
                <div class="proof-stat-card">
                    <p class="proof-stat-value">UK-wide</p>
                    <p class="proof-stat-label">coverage</p>
                </div>
                <div class="proof-stat-card">
                    <p class="proof-stat-value">One place</p>
                    <p class="proof-stat-label">organised planning space</p>
                </div>
            </div>

            <div class="testimonial-grid">
                <blockquote class="testimonial-card">
                    <p class="testimonial-quote">“We compared photographers and caterers without endless spreadsheets—everything stayed in one place.”</p>
                    <footer><p class="testimonial-author">Emma &amp; James, wedding</p></footer>
                </blockquote>
                <blockquote class="testimonial-card">
                    <p class="testimonial-quote">“Our team event came together quickly. Quotes arrived in the inbox and we booked with clear pricing.”</p>
                    <footer><p class="testimonial-author">Priya K., corporate organiser</p></footer>
                </blockquote>
                <blockquote class="testimonial-card">
                    <p class="testimonial-quote">“Listing our DJ service was straightforward. Enquiries land in the dashboard and we reply the same day.”</p>
                    <footer><p class="testimonial-author">Marcus T., entertainment vendor</p></footer>
                </blockquote>
            </div>
        </div>
    </section>

    <section class="home-final-cta text-white home-section" aria-labelledby="home-cta-heading">
        <div class="container text-center cta-inner">
            <h2 id="home-cta-heading" class="cta-heading">Ready to start planning?</h2>
            <p class="cta-lead mb-4">Create your event, browse suppliers, and keep every quote and message beautifully organised.</p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="<?= base_url('event/create') ?>" class="btn btn-light btn-lg px-4">Create your event</a>
                <a href="<?= base_url('browse-services') ?>" class="btn btn-home-outline-light btn-lg px-4">Browse suppliers</a>
            </div>
        </div>
    </section>
</main>

<?= $this->include('footer') ?>
