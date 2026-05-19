<?= $this->include('header') ?>

<?php if (!empty($cmsHome)): ?>
<section class="border-bottom bg-white py-3">
    <div class="container cms-intro">
        <?= $cmsHome['content'] ?>
    </div>
</section>
<?php endif; ?>

<main class="full-width-container">
    <!-- Hero -->
    <section class="hero-section d-flex flex-column">
        <div class="hero-image" style="background-image: url('<?= base_url('assets/images/hero.png') ?>');">
            <div class="overlay hero-overlay d-flex align-items-center justify-content-center">
                <div class="text-center text-white hero-copy">
                    <p class="hero-eyebrow mb-2">Event services marketplace</p>
                    <div class="display-4-container">
                        <h1 class="display-4 mb-0">
                            <span class="visually-hidden">For Your </span>
                            <div class="flex-wrapper">
                                <span class="static-text" aria-hidden="true">For Your</span>
                                <span id="rotating-words" class="rotating-words" aria-hidden="true">Event</span>
                            </div>
                        </h1>
                    </div>
                    <p class="hero-sublead mx-auto mt-3">
                        The smarter way to plan events, compare suppliers, and stay organised.
                    </p>
                    <div class="d-flex flex-wrap gap-2 justify-content-center mt-3 mb-4">
                        <a href="<?= base_url('browse-services') ?>" class="btn btn-light btn-lg px-4">Browse all services</a>
                        <?php if (session()->has('user_id') && session()->get('role') === 'customer'): ?>
                            <a href="<?= base_url('event/create') ?>" class="btn btn-outline-light btn-lg px-4">Create an event</a>
                        <?php elseif (!session()->has('user_id')): ?>
                            <a href="<?= base_url('register') ?>" class="btn btn-outline-light btn-lg px-4">Get started free</a>
                        <?php endif; ?>
                    </div>

                    <form class="search-form mt-2 d-none d-lg-flex justify-content-center hero-search" action="<?= base_url('search') ?>"
                        method="get">
                        <div class="hero-search-inner hero-search-inner--simple shadow-lg">
                            <label class="visually-hidden" for="home-search-q">Search services</label>
                            <input type="search" class="form-control hero-search-q" id="home-search-q" name="q"
                                placeholder="Search by name or keyword" autocomplete="off">
                            <button class="btn btn-primary hero-search-btn" type="submit">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="search-form-container d-lg-none mt-0 text-center">
            <form class="search-form py-3 px-2" action="<?= base_url('search') ?>" method="get">
                <div class="hero-search-inner hero-search-inner--simple hero-search-inner--stacked mx-auto">
                    <label class="visually-hidden" for="home-search-q-mobile">Search services</label>
                    <input type="search" class="form-control" id="home-search-q-mobile" name="q"
                        placeholder="Search by name or keyword" autocomplete="off">
                    <button class="btn btn-primary w-100" type="submit">Search</button>
                </div>
            </form>
        </div>
    </section>

    <section class="trust-strip py-4 py-lg-5">
        <div class="container trust-strip-container">
            <div class="row trust-strip-row justify-content-center g-2 g-md-3">
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="trust-strip-card h-100">
                        <div class="trust-strip-card-accent" aria-hidden="true"></div>
                        <div class="trust-strip-icon-wrap">
                            <i class="fas fa-layer-group trust-strip-icon" aria-hidden="true"></i>
                        </div>
                        <h3 class="trust-strip-title">Everything in one place</h3>
                        <p class="trust-strip-text">Keep bookings, messages, quotes, and events beautifully organised.</p>
                    </article>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="trust-strip-card h-100">
                        <div class="trust-strip-card-accent" aria-hidden="true"></div>
                        <div class="trust-strip-icon-wrap">
                            <i class="fas fa-clipboard-list trust-strip-icon" aria-hidden="true"></i>
                        </div>
                        <h3 class="trust-strip-title">Simple booking journey</h3>
                        <p class="trust-strip-text">Track every enquiry from first message to confirmed booking.</p>
                    </article>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="trust-strip-card h-100">
                        <div class="trust-strip-card-accent" aria-hidden="true"></div>
                        <div class="trust-strip-icon-wrap">
                            <i class="fas fa-search trust-strip-icon" aria-hidden="true"></i>
                        </div>
                        <h3 class="trust-strip-title">Find the right suppliers</h3>
                        <p class="trust-strip-text">Browse trusted vendors by category, style, and location.</p>
                    </article>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="trust-strip-card h-100">
                        <div class="trust-strip-card-accent" aria-hidden="true"></div>
                        <div class="trust-strip-icon-wrap">
                            <i class="fas fa-feather trust-strip-icon" aria-hidden="true"></i>
                        </div>
                        <h3 class="trust-strip-title">Designed for stress-free planning</h3>
                        <p class="trust-strip-text">Built to make organising events feel calm and effortless.</p>
                    </article>
                </div>
            </div>
            <p class="trust-strip-proof text-center mb-0 mt-4 mt-lg-3">
                Trusted by vendors across weddings, parties, corporate events, and celebrations.
            </p>
        </div>
    </section>

    <section class="py-5 section-surface text-center">
        <div class="container">
            <p class="section-eyebrow mb-2">Explore the catalogue</p>
            <h2 class="section-heading mb-2">Popular categories</h2>
            <p class="section-lead mx-auto mb-4">Jump straight into the part of the marketplace that matches your event—each tile opens filtered results.</p>
        </div>
        <?php if (!empty($homeCategoryTiles)): ?>
        <div class="category-card-container">
            <?php foreach ($homeCategoryTiles as $tile): ?>
                <a class="category-card category-card-link"
                    href="<?= base_url('browse-services?category=' . (int) $tile['id']) ?>"
                    aria-label="Browse <?= esc($tile['name']) ?>">
                    <img src="<?= base_url('assets/images/' . esc($tile['image'])) ?>"
                        alt="" class="category-card-image">
                    <div class="category-card-category"><?= esc($tile['name']) ?></div>
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

    <section class="how-it-works py-5">
        <div class="container text-center">
            <p class="section-eyebrow mb-2">Simple flow</p>
            <h2 class="section-heading mb-5">How it works</h2>
            <div class="row justify-content-center g-4">
                <div class="col-md-4">
                    <div class="how-it-works-card h-100">
                        <div class="icon-wrapper">
                            <i class="fas fa-calendar-plus fa-2x text-primary" aria-hidden="true"></i>
                        </div>
                        <h3 class="card-title mt-3 h5">Create your event</h3>
                        <p class="card-description">Add dates, location, and the type of celebration so vendors know what you need.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="how-it-works-card h-100">
                        <div class="icon-wrapper">
                            <i class="fas fa-search-plus fa-2x text-primary" aria-hidden="true"></i>
                        </div>
                        <h3 class="card-title mt-3 h5">Find and shortlist</h3>
                        <p class="card-description">Browse by category, compare services, and save favourites before you book.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="how-it-works-card h-100">
                        <div class="icon-wrapper">
                            <i class="fas fa-check-circle fa-2x text-primary" aria-hidden="true"></i>
                        </div>
                        <h3 class="card-title mt-3 h5">Book with confidence</h3>
                        <p class="card-description">Request bookings, message vendors, and confirm when everything feels right.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 section-surface text-center">
        <div class="container d-flex flex-column flex-md-row align-items-md-end justify-content-md-between gap-3 mb-4 text-md-start">
            <div>
                <p class="section-eyebrow mb-2">Hand-picked variety</p>
                <h2 class="section-heading mb-0">Popular services</h2>
            </div>
            <a href="<?= base_url('browse-services') ?>" class="btn btn-primary align-self-center align-self-md-end">See all services</a>
        </div>
        <div class="service-card-container">
            <?php foreach ($services as $service): ?>
                <article class="service-card">
                    <?php if (!empty($service['images'])): ?>
                        <img src="<?= base_url(esc($service['images'][0]['thumbnail_path'])) ?>"
                            alt="<?= esc($service['title']) ?> thumbnail" class="service-card-image">
                    <?php else: ?>
                        <img src="<?= base_url('assets/images/no-image.png') ?>" alt="" class="service-card-image">
                    <?php endif; ?>

                    <div class="service-card-content">
                        <h3 class="service-card-title"><?= esc($service['title']) ?></h3>
                        <p class="service-card-description">
                            <?= esc($service['short_description'] ?? 'No description available.') ?>
                        </p>
                        <a href="<?= base_url('service/view/' . (int) $service['id']) ?>" class="service-card-button">View service</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="cta-section text-white py-5">
        <div class="container text-center cta-inner">
            <h2 class="cta-heading">Ready when you are</h2>
            <p class="cta-lead mb-4">Create a free account to save events, message vendors, and keep every booking organised.</p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="<?= base_url('register') ?>" class="btn btn-light btn-lg px-4">Create account</a>
                <a href="<?= base_url('browse-services') ?>" class="btn btn-outline-light btn-lg px-4">Browse first</a>
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
