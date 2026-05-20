<?= $this->include('header') ?>

<?php if (!empty($cmsHome)): ?>
<section class="border-bottom bg-white py-3">
    <div class="container cms-intro">
        <?= $cmsHome['content'] ?>
    </div>
</section>
<?php endif; ?>

<main class="full-width-container home-page">
    <!-- Hero -->
    <section class="hero-section d-flex flex-column" aria-labelledby="home-hero-heading">
        <div class="hero-image" style="background-image: url('<?= base_url('assets/images/' . esc($heroImage ?? 'hero_wedding_evening_v1.webp')) ?>');">
            <div class="overlay hero-overlay d-flex align-items-center justify-content-center">
                <div class="text-center text-white hero-copy">
                    <p class="hero-eyebrow mb-2">Event planning &amp; booking</p>
                    <div class="display-4-container">
                        <h1 id="home-hero-heading" class="display-4 mb-0">
                            <span class="visually-hidden">For Your </span>
                            <div class="flex-wrapper">
                                <span class="static-text" aria-hidden="true">For Your</span>
                                <span id="rotating-words" class="rotating-words" aria-hidden="true">Event</span>
                            </div>
                        </h1>
                    </div>
                    <p class="hero-sublead mx-auto mt-3">
                        <?= esc($heroSubtitle ?? 'Plan your entire event in one organised place.') ?>
                    </p>
                    <div class="hero-cta-group d-flex flex-wrap gap-2 justify-content-center mt-3 mb-4">
                        <a href="<?= base_url('register') ?>" class="btn btn-light btn-lg px-4 hero-cta-primary">Get Started Free</a>
                        <a href="<?= base_url('browse-services') ?>" class="btn btn-outline-light btn-lg px-4">Browse Suppliers</a>
                    </div>

                    <form class="search-form mt-2 d-none d-lg-flex justify-content-center hero-search" action="<?= base_url('search') ?>"
                        method="get" role="search">
                        <div class="hero-search-inner hero-search-inner--simple">
                            <label class="visually-hidden" for="home-search-q">Search suppliers</label>
                            <input type="search" class="form-control hero-search-q" id="home-search-q" name="q"
                                placeholder="Search suppliers, services, or keywords" autocomplete="off">
                            <button class="btn btn-primary hero-search-btn" type="submit">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="search-form-container d-lg-none mt-0 text-center">
            <form class="search-form py-3 px-2" action="<?= base_url('search') ?>" method="get" role="search">
                <div class="hero-search-inner hero-search-inner--simple hero-search-inner--stacked mx-auto">
                    <label class="visually-hidden" for="home-search-q-mobile">Search suppliers</label>
                    <input type="search" class="form-control" id="home-search-q-mobile" name="q"
                        placeholder="Search suppliers or keywords" autocomplete="off">
                    <button class="btn btn-primary w-100" type="submit">Search</button>
                </div>
            </form>
        </div>
    </section>

    <section class="trust-strip-compact" aria-label="Why planners trust us">
        <div class="container">
            <ul class="trust-strip-compact-list">
                <li>
                    <i class="fas fa-circle-check" aria-hidden="true"></i>
                    <span>Verified suppliers</span>
                </li>
                <li>
                    <i class="fas fa-calendar-check" aria-hidden="true"></i>
                    <span>Weddings to corporate events</span>
                </li>
                <li>
                    <i class="fas fa-lock" aria-hidden="true"></i>
                    <span>Secure messaging</span>
                </li>
                <li>
                    <i class="fas fa-layer-group" aria-hidden="true"></i>
                    <span>Organised in one place</span>
                </li>
            </ul>
        </div>
    </section>

    <section class="home-section section-surface text-center">
        <div class="container">
            <p class="section-eyebrow mb-2">Explore the catalogue</p>
            <h2 class="section-heading mb-2">Popular categories</h2>
            <p class="section-lead mx-auto mb-4">Find trusted suppliers by category—each opens filtered results tailored to your event.</p>
        </div>
        <?php if (!empty($homeCategoryTiles)): ?>
        <div class="category-card-container">
            <?php foreach ($homeCategoryTiles as $tile): ?>
                <a class="category-card category-card-link"
                    href="<?= base_url('browse-services?category=' . (int) $tile['id']) ?>"
                    aria-label="Browse <?= esc($tile['name']) ?>">
                    <div class="category-card-media">
                        <img src="<?= base_url('assets/images/' . esc($tile['image'])) ?>"
                            alt=""
                            class="category-card-image"
                            width="400"
                            height="400"
                            loading="lazy"
                            decoding="async">
                        <div class="category-card-gradient" aria-hidden="true"></div>
                        <div class="category-card-category"><?= esc($tile['name']) ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted">Categories will appear here once they are configured in the catalogue.</p>
        <?php endif; ?>
        <div class="container mt-4">
            <a href="<?= base_url('browse-services') ?>" class="btn btn-outline-primary">View full catalogue</a>
        </div>
    </section>

    <section class="home-section how-it-works">
        <div class="container text-center">
            <p class="section-eyebrow mb-2">Simple flow</p>
            <h2 class="section-heading mb-5">How it works</h2>
            <div class="how-it-works-timeline row justify-content-center g-4">
                <div class="col-md-4">
                    <div class="how-it-works-card h-100">
                        <div class="icon-wrapper">
                            <i class="fas fa-calendar-plus fa-2x" aria-hidden="true"></i>
                        </div>
                        <h3 class="card-title mt-3 h5">Create your event</h3>
                        <p class="card-description">Add your date, location, and style so suppliers know exactly what you need.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="how-it-works-card h-100">
                        <div class="icon-wrapper">
                            <i class="fas fa-search fa-2x" aria-hidden="true"></i>
                        </div>
                        <h3 class="card-title mt-3 h5">Browse trusted suppliers</h3>
                        <p class="card-description">Compare services, prices, and reviews in one organised place.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="how-it-works-card h-100">
                        <div class="icon-wrapper">
                            <i class="fas fa-handshake fa-2x" aria-hidden="true"></i>
                        </div>
                        <h3 class="card-title mt-3 h5">Book with confidence</h3>
                        <p class="card-description">Message vendors, manage quotes, and keep everything beautifully organised.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section section-surface-alt text-center">
        <div class="container d-flex flex-column flex-md-row align-items-md-end justify-content-md-between gap-3 mb-4 text-md-start">
            <div>
                <p class="section-eyebrow mb-2">Hand-picked variety</p>
                <h2 class="section-heading mb-0">Popular services</h2>
            </div>
            <a href="<?= base_url('browse-services') ?>" class="btn btn-primary align-self-center align-self-md-end">See all services</a>
        </div>
        <div class="service-card-container">
            <?php foreach ($services as $service): ?>
                <?php
                $thumb = !empty($service['images'])
                    ? base_url(esc($service['images'][0]['thumbnail_path']))
                    : base_url('assets/images/category_default_v1.webp');
                $location = trim((string) ($service['service_location'] ?? ''));
                if ($location === '') {
                    $location = 'UK-wide';
                }
                $price = isset($service['price']) ? (float) $service['price'] : null;
                $isVerified = !empty($service['license']);
                ?>
                <a href="<?= base_url('service/view/' . (int) $service['id']) ?>" class="service-card service-card-link">
                    <div class="service-card-media">
                        <img src="<?= $thumb ?>"
                            alt=""
                            class="service-card-image"
                            width="400"
                            height="260"
                            loading="lazy"
                            decoding="async">
                        <?php if ($isVerified): ?>
                            <span class="service-card-verified"><i class="fas fa-circle-check" aria-hidden="true"></i> Verified</span>
                        <?php endif; ?>
                    </div>
                    <div class="service-card-content text-start">
                        <h3 class="service-card-title"><?= esc($service['title']) ?></h3>
                        <div class="service-card-meta">
                            <span class="service-card-location"><i class="fas fa-location-dot" aria-hidden="true"></i> <?= esc($location) ?></span>
                            <?php if ($price !== null && $price > 0): ?>
                                <span class="service-card-price">From £<?= number_format($price, 0) ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="service-card-description">
                            <?= esc($service['short_description'] ?? 'View full details and request a quote.') ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="cta-section text-white home-section">
        <div class="container text-center cta-inner">
            <h2 class="cta-heading">Ready when you are</h2>
            <p class="cta-lead mb-4">Create a free account to save events, message suppliers, and keep every booking organised.</p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="<?= base_url('register') ?>" class="btn btn-light btn-lg px-4">Get Started Free</a>
                <a href="<?= base_url('browse-services') ?>" class="btn btn-outline-light btn-lg px-4">Browse Suppliers</a>
            </div>
        </div>
    </section>
</main>

<?= $this->include('footer') ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const words = ['Wedding', 'Birthday', 'Christening', 'Bat Mitzvah', 'Meeting', 'Events'];
        const rotatingWords = document.getElementById('rotating-words');
        if (!rotatingWords) {
            return;
        }
        let index = 0;
        let wordCount = 0;
        let interval = 3000;

        const updateWord = () => {
            rotatingWords.textContent = words[index];
            index++;
            wordCount++;

            if (wordCount > 1) {
                interval = 3000;
            }

            if (wordCount >= words.length) {
                rotatingWords.style.animation = 'none';
                return;
            }

            setTimeout(updateWord, interval);
        };

        updateWord();
    });
</script>
