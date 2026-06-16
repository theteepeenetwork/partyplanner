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
    <?php
    $occasions   = ['Wedding', 'Birthday', 'Corporate', 'Christening'];
    $guestBands  = ['' => 'Any number', '50' => 'Up to 50', '100' => 'Around 100', '150' => 'Around 150', '250' => '250+'];
    $browseUrl   = base_url('browse-services');
    ?>
    <section class="hero-section" aria-labelledby="home-hero-heading">
        <div class="hero-image" style="background-image: url('<?= $heroBg ?>');">
            <div class="overlay hero-overlay d-flex align-items-center justify-content-center">
                <div class="text-center text-white hero-copy">
                    <p class="hero-eyebrow">The UK event marketplace, expertly made</p>
                    <h1 id="home-hero-heading" class="hero-headline">
                        Plan your whole event in one organised place
                    </h1>
                    <p class="hero-sublead mx-auto">
                        Find vetted suppliers, compare services and get structured quotes — booked and paid in one calm place.
                    </p>
                    <p class="hero-script">P.S. leave the planning to us.</p>

                    <form class="hero-event-search" action="<?= $browseUrl ?>" method="get" role="search"
                        aria-label="Search event suppliers">
                        <fieldset class="hero-occasions">
                            <legend class="visually-hidden">What are you planning?</legend>
                            <?php foreach ($occasions as $i => $occasion): ?>
                                <input type="radio" class="hero-occasion-input" name="occasion"
                                    id="occasion-<?= esc(strtolower($occasion), 'attr') ?>"
                                    value="<?= esc($occasion, 'attr') ?>" <?= $i === 0 ? 'checked' : '' ?>>
                                <label class="hero-occasion-pill" for="occasion-<?= esc(strtolower($occasion), 'attr') ?>">
                                    <?= esc($occasion) ?>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                        <div class="hero-search-fields">
                            <div class="hero-field">
                                <label class="hero-field-label" for="hero-date">Date</label>
                                <input type="date" class="hero-field-input" id="hero-date" name="date">
                            </div>
                            <div class="hero-field">
                                <label class="hero-field-label" for="hero-guests">Guests</label>
                                <select class="hero-field-input" id="hero-guests" name="guests">
                                    <?php foreach ($guestBands as $value => $label): ?>
                                        <option value="<?= esc($value, 'attr') ?>"><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="hero-field">
                                <label class="hero-field-label" for="hero-location">Location</label>
                                <input type="text" class="hero-field-input" id="hero-location" name="location"
                                    placeholder="e.g. Leeds" autocomplete="off">
                            </div>
                            <button class="btn btn-home-primary hero-search-submit" type="submit">Get quotes</button>
                        </div>
                    </form>

                    <p class="hero-reassurance">
                        <i class="fas fa-circle-check" aria-hidden="true"></i> Free to use
                        &nbsp;·&nbsp;
                        <i class="fas fa-circle-check" aria-hidden="true"></i> No obligation
                        &nbsp;·&nbsp;
                        <i class="fas fa-circle-check" aria-hidden="true"></i> One place for every supplier
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="home-trust-strip" aria-label="Marketplace benefits">
        <div class="container">
            <ul class="home-trust-grid">
                <li class="home-trust-item">
                    <span class="home-trust-icon" aria-hidden="true"><i class="fas fa-calendar-plus"></i></span>
                    <span>
                        <strong class="home-trust-title">Create one event</strong>
                        <span class="home-trust-text">Set it up once and contact multiple suppliers together.</span>
                    </span>
                </li>
                <li class="home-trust-item">
                    <span class="home-trust-icon" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
                    <span>
                        <strong class="home-trust-title">Compare in one place</strong>
                        <span class="home-trust-text">Line up services and quotes side by side, calmly.</span>
                    </span>
                </li>
                <li class="home-trust-item">
                    <span class="home-trust-icon" aria-hidden="true"><i class="fas fa-comments"></i></span>
                    <span>
                        <strong class="home-trust-title">Messages &amp; bookings organised</strong>
                        <span class="home-trust-text">Every quote, message and booking kept in one thread.</span>
                    </span>
                </li>
                <li class="home-trust-item">
                    <span class="home-trust-icon" aria-hidden="true"><i class="fas fa-calendar-days"></i></span>
                    <span>
                        <strong class="home-trust-title">For every occasion</strong>
                        <span class="home-trust-text">Weddings, birthdays, christenings and corporate events.</span>
                    </span>
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

    <section class="home-section how-it-works" aria-labelledby="home-how-heading">
        <div class="container text-center">
            <p class="section-eyebrow mb-2">How it works</p>
            <h2 id="home-how-heading" class="section-heading mb-2">Everything in one organised place</h2>
            <p class="section-lead mx-auto mb-4">
                Create your event once, then discover suitable suppliers, get instant quotes and manage your bookings from one calm planning space.
            </p>
            <div class="how-it-works-steps">
                <div class="how-it-works-card">
                    <div class="icon-wrapper">
                        <i class="fas fa-calendar-plus" aria-hidden="true"></i>
                    </div>
                    <span class="step-number">Step 1</span>
                    <h3 class="card-title">Create your event</h3>
                    <p class="card-description">Tell us the date, location, guest numbers and the kind of occasion.</p>
                </div>
                <div class="how-it-works-card">
                    <div class="icon-wrapper">
                        <i class="fas fa-compass" aria-hidden="true"></i>
                    </div>
                    <span class="step-number">Step 2</span>
                    <h3 class="card-title">Discover suppliers</h3>
                    <p class="card-description">Browse trusted suppliers matched to exactly what you need.</p>
                </div>
                <div class="how-it-works-card">
                    <div class="icon-wrapper">
                        <i class="fas fa-file-invoice-dollar" aria-hidden="true"></i>
                    </div>
                    <span class="step-number">Step 3</span>
                    <h3 class="card-title">Get instant quotes</h3>
                    <p class="card-description">Receive structured pricing automatically — no waiting on emails.</p>
                </div>
                <div class="how-it-works-card">
                    <div class="icon-wrapper">
                        <i class="fas fa-clipboard-list" aria-hidden="true"></i>
                    </div>
                    <span class="step-number">Step 4</span>
                    <h3 class="card-title">Book in one place</h3>
                    <p class="card-description">Keep services, messages and payments calmly organised.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section section-surface-alt" aria-labelledby="home-services-heading">
        <div class="container d-flex flex-column flex-md-row align-items-md-end justify-content-md-between gap-3 mb-4 text-md-start text-center">
            <div>
                <p class="section-eyebrow mb-2">Featured suppliers</p>
                <h2 id="home-services-heading" class="section-heading mb-0">A glimpse of who you can find</h2>
                <p class="section-lead mt-2 mb-0">A selection of suppliers ready to help with weddings, private parties and corporate occasions.</p>
            </div>
            <a href="<?= base_url('browse-services') ?>" class="btn btn-home-primary align-self-center align-self-md-end">Browse all suppliers</a>
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

    <section class="home-section section-surface text-center" aria-labelledby="home-categories-heading">
        <div class="container">
            <p class="section-eyebrow mb-2">Explore services</p>
            <h2 id="home-categories-heading" class="section-heading mb-2">Everything you need for your event</h2>
            <p class="section-lead mx-auto mb-4">
                From photographers and catering to venues, styling and entertainment — discover suppliers for every kind of celebration.
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
            <a href="<?= base_url('browse-services') ?>" class="btn btn-home-secondary">Browse all suppliers</a>
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

    <section class="home-final-cta home-section" aria-labelledby="home-cta-heading">
        <div class="container text-center cta-inner">
            <p class="final-script">P.S. you're going to love this.</p>
            <h2 id="home-cta-heading" class="cta-heading">Ready to start planning?</h2>
            <p class="cta-lead mb-4">
                Create your event, discover services and keep every supplier enquiry in one beautifully organised place.
            </p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="<?= base_url('event/create') ?>" class="btn btn-light btn-lg px-4">Create your event</a>
                <a href="<?= base_url('browse-services') ?>" class="btn btn-home-ghost-light btn-lg px-4">Browse suppliers</a>
            </div>
        </div>
    </section>
</main>

<?= $this->include('footer') ?>
