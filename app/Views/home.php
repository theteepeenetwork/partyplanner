<?php
$fallbackImage = base_url('assets/images/' . esc($serviceFallbackImage ?? 'fallback-service-card.jpg'));
$heroBg        = base_url('assets/images/' . esc($heroImage ?? 'hero-event-planning.jpg'));
$vendorImg     = base_url('assets/images/' . esc($vendorCtaImage ?? 'vendor-cta.jpg'));
$planUrl       = session()->has('user_id') ? base_url('event/create') : base_url('register');
$inspirationCards = $inspirationCards ?? [];
$inspirationBrowseUrl = $inspirationBrowseUrl ?? base_url('browse-services');
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
                        <a href="<?= base_url('browse-services') ?>" class="btn btn-home-outline-light btn-lg">Browse services</a>
                    </div>

                    <form class="search-form hero-search d-none d-lg-flex justify-content-center"
                        action="<?= base_url('search') ?>" method="get" role="search">
                        <div class="hero-search-inner hero-search-inner--simple">
                            <label class="visually-hidden" for="home-search-q">Search services</label>
                            <input type="search" class="form-control hero-search-q" id="home-search-q" name="q"
                                placeholder="Search photographers, venues, catering…" autocomplete="off">
                            <button class="btn btn-primary hero-search-btn" type="submit">Search services</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="search-form-container d-lg-none">
            <form class="search-form py-3 px-2" action="<?= base_url('search') ?>" method="get" role="search">
                <div class="hero-search-inner hero-search-inner--simple hero-search-inner--stacked mx-auto">
                    <label class="visually-hidden" for="home-search-q-mobile">Search services</label>
                    <input type="search" class="form-control" id="home-search-q-mobile" name="q"
                        placeholder="Search photographers, venues, catering…" autocomplete="off">
                    <button class="btn btn-primary w-100 hero-search-btn" type="submit">Search services</button>
                </div>
            </form>
        </div>
    </section>

    <section class="home-trust-strip" aria-label="Marketplace benefits">
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
            <p class="section-eyebrow mb-2">Explore services</p>
            <h2 id="home-categories-heading" class="section-heading mb-2">Everything you need for your event</h2>
            <p class="section-lead mx-auto mb-4">
                From photographers and catering to venues, styling and entertainment, discover trusted suppliers for every kind of celebration.
            </p>
        </div>
        <?php if (! empty($homeCategoryTiles)): ?>
        <div class="category-card-container">
            <?php foreach ($homeCategoryTiles as $tile): ?>
                <a class="category-card category-card-link"
                    href="<?= esc($tile['href']) ?>"
                    aria-label="Browse <?= esc($tile['name']) ?> services">
                    <div class="category-card-media">
                        <img src="<?= base_url('assets/images/' . esc($tile['image'])) ?>"
                            alt="<?= esc($tile['name']) ?> services"
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
            <a href="<?= base_url('browse-services') ?>" class="btn btn-home-secondary">View all services</a>
        </div>
    </section>

    <section class="home-section how-it-works" aria-labelledby="home-how-heading">
        <div class="container text-center">
            <p class="section-eyebrow mb-2">How it works</p>
            <h2 id="home-how-heading" class="section-heading mb-2">Plan with less back-and-forth</h2>
            <p class="section-lead mx-auto mb-4">
                Create your event once, then discover suitable suppliers, request quotes and manage your bookings from one calm planning space.
            </p>
            <div class="how-it-works-steps">
                <div class="how-it-works-card">
                    <div class="icon-wrapper">
                        <i class="fas fa-calendar-plus" aria-hidden="true"></i>
                    </div>
                    <span class="step-number">Step 1</span>
                    <h3 class="card-title">Create your event</h3>
                    <p class="card-description">Tell us the date, location, guest numbers and type of event.</p>
                </div>
                <div class="how-it-works-card">
                    <div class="icon-wrapper">
                        <i class="fas fa-compass" aria-hidden="true"></i>
                    </div>
                    <span class="step-number">Step 2</span>
                    <h3 class="card-title">Discover services</h3>
                    <p class="card-description">Browse suppliers that match what you need.</p>
                </div>
                <div class="how-it-works-card">
                    <div class="icon-wrapper">
                        <i class="fas fa-comments" aria-hidden="true"></i>
                    </div>
                    <span class="step-number">Step 3</span>
                    <h3 class="card-title">Request quotes</h3>
                    <p class="card-description">Send enquiries and compare responses in one place.</p>
                </div>
                <div class="how-it-works-card">
                    <div class="icon-wrapper">
                        <i class="fas fa-clipboard-list" aria-hidden="true"></i>
                    </div>
                    <span class="step-number">Step 4</span>
                    <h3 class="card-title">Manage bookings</h3>
                    <p class="card-description">Keep services, messages and payments organised as your plans come together.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section section-surface-alt" aria-labelledby="home-services-heading">
        <div class="container d-flex flex-column flex-md-row align-items-md-end justify-content-md-between gap-3 mb-4 text-md-start text-center">
            <div>
                <p class="section-eyebrow mb-2">Featured services</p>
                <h2 id="home-services-heading" class="section-heading mb-0">Popular services for upcoming events</h2>
                <p class="section-lead mt-2 mb-0">Browse a selection of suppliers ready to help with weddings, private parties and corporate occasions.</p>
            </div>
            <a href="<?= base_url('browse-services') ?>" class="btn btn-home-primary align-self-center align-self-md-end">View all services</a>
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
                    <a href="<?= $serviceUrl ?>" class="service-card-media">
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
                            <?php else: ?>
                                <span class="service-card-quote">Request a quote</span>
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
                        <a href="<?= base_url('vendor-info') ?>" class="btn btn-home-secondary">Learn how it works</a>
                    </div>
                </div>
                <div class="vendor-cta-media">
                    <img src="<?= $vendorImg ?>"
                        alt="Event supplier preparing services for a celebration"
                        width="600"
                        height="400"
                        loading="lazy"
                        decoding="async"
                        onerror="this.onerror=null;this.src='<?= $fallbackImage ?>';">
                </div>
            </div>
        </div>
    </section>

    <section class="home-section trust-value-section text-center" aria-labelledby="home-trust-heading">
        <div class="container">
            <p class="section-eyebrow mb-2">Why use us</p>
            <h2 id="home-trust-heading" class="section-heading">A calmer way to organise important events</h2>
            <div class="trust-value-grid">
                <article class="trust-value-card">
                    <h3>Trusted suppliers</h3>
                    <p>Browse services from event professionals across the UK.</p>
                </article>
                <article class="trust-value-card">
                    <h3>One organised space</h3>
                    <p>Keep quotes, messages and bookings together.</p>
                </article>
                <article class="trust-value-card">
                    <h3>Built for every occasion</h3>
                    <p>Plan weddings, christenings, birthdays, parties and corporate events.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="home-section inspiration-section text-center" aria-labelledby="home-inspiration-heading">
        <div class="container">
            <p class="section-eyebrow mb-2">Inspiration</p>
            <h2 id="home-inspiration-heading" class="section-heading">Ideas for your next event</h2>
            <div class="inspiration-grid">
                <?php foreach ($inspirationCards as $card): ?>
                    <a href="<?= esc($card['href']) ?>" class="inspiration-card">
                        <span class="inspiration-card-icon" aria-hidden="true"><i class="<?= esc($card['icon']) ?>"></i></span>
                        <h3 class="inspiration-card-title"><?= esc($card['title']) ?></h3>
                        <p class="inspiration-card-text"><?= esc($card['text']) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php /* No dedicated inspiration/blog route yet — links to browse-services filtered views */ ?>
            <a href="<?= esc($inspirationBrowseUrl) ?>" class="btn btn-home-secondary mt-4">Read inspiration</a>
        </div>
    </section>

    <section class="home-final-cta home-section" aria-labelledby="home-cta-heading">
        <div class="container text-center cta-inner">
            <h2 id="home-cta-heading" class="cta-heading">Ready to start planning?</h2>
            <p class="cta-lead mb-4">
                Create your event, discover services and keep every supplier enquiry in one beautifully organised place.
            </p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="<?= base_url('event/create') ?>" class="btn btn-light btn-lg px-4">Create your event</a>
                <a href="<?= base_url('browse-services') ?>" class="btn btn-home-ghost-light btn-lg px-4">Browse services</a>
            </div>
        </div>
    </section>
</main>

<?= $this->include('footer') ?>
