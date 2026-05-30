<?= $this->include('header') ?>

<main class="container browse-services-page">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <h2 class="mb-0">Browse Services</h2>
        <?php if (isset($resultsCount)): ?>
            <p class="text-muted small mb-0"><?= (int) $resultsCount ?> service<?= (int) $resultsCount === 1 ? '' : 's' ?> found</p>
        <?php endif; ?>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if (!empty($customerEvents)): ?>
        <?= view('partials/customer_event_switcher', [
            'customerEvents' => $customerEvents,
            'activeEvent' => $activeEvent ?? null,
        ]) ?>
    <?php endif; ?>

    <?php if (!empty($activeEvent)): ?>
        <div class="alert alert-info mb-3">
            <i class="fas fa-info-circle me-1" aria-hidden="true"></i>
            Services you add from this page go into the basket for
            <strong><?= esc($activeEvent['title'] ?? 'your event') ?></strong>
            <?php if (!empty($activeEvent['date'])): ?>
                (<?= date('d M Y', strtotime($activeEvent['date'])) ?>)
            <?php endif; ?>.
            <a href="/event/basket/<?= (int) $activeEvent['id'] ?>" class="alert-link">View basket</a>
        </div>
    <?php endif; ?>

    <?php
    $catById = [];
    foreach ($categories as $c) {
        $catById[(int) $c['id']] = $c['name'];
    }
    $formEventId = !empty($basketEventId)
        ? (int) $basketEventId
        : (!empty($activeEvent) && !empty($activeEvent['id']) ? (int) $activeEvent['id'] : null);
    $hasActiveFilters = !empty($searchQuery)
        || !empty($selectedCategory)
        || !empty($selectedSubcategory)
        || !empty($selectedThirdCategory)
        || (!empty($selectedSort) && $selectedSort !== 'newest');
    $clearUrl = '/browse-services' . ($formEventId ? '?event_id=' . esc($formEventId) : '');
    ?>

    <form class="browse-services-form card border-0 shadow-sm mb-3" action="<?= site_url('browse-services') ?>" method="get" id="browse-services-form">
        <div class="card-body">
            <?php if ($formEventId): ?>
                <input type="hidden" name="event_id" value="<?= esc($formEventId) ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-12">
                    <label for="browse-q" class="form-label">Keywords</label>
                    <input type="search" class="form-control" id="browse-q" name="q" placeholder="Search by title, description, or tags…"
                        value="<?= esc($searchQuery ?? '') ?>" autocomplete="off">
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <label for="browse-category" class="form-label">Category</label>
                    <select class="form-select" id="browse-category" name="category" aria-describedby="browse-category-hint">
                        <option value="">All categories</option>
                        <?php foreach ($rootCategories as $category): ?>
                            <option value="<?= esc($category['id']) ?>"
                                <?= ((string) ($selectedCategory ?? '')) === (string) $category['id'] ? 'selected' : '' ?>>
                                <?= esc($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="browse-category-hint" class="form-text">Pick a main group, then narrow down if you like.</div>
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <label for="browse-subcategory" class="form-label">
                        Subcategory <span class="text-muted fw-normal">(optional)</span>
                    </label>
                    <select class="form-select" id="browse-subcategory" name="subcategory" disabled aria-describedby="browse-sub-hint">
                        <option value="">Any subcategory</option>
                    </select>
                    <div id="browse-sub-hint" class="form-text">Leave blank to include every type under the category above.</div>
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <label for="browse-third-category" class="form-label">
                        Specific type <span class="text-muted fw-normal">(optional)</span>
                    </label>
                    <select class="form-select" id="browse-third-category" name="third_category" disabled>
                        <option value="">Any specific type</option>
                    </select>
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <label for="browse-sort" class="form-label">Sort by</label>
                    <select class="form-select" id="browse-sort" name="sort">
                        <option value="newest" <?= (($selectedSort ?? 'newest') === 'newest') ? 'selected' : '' ?>>Newest listed</option>
                        <option value="price_asc" <?= (($selectedSort ?? '') === 'price_asc') ? 'selected' : '' ?>>Price: low to high</option>
                        <option value="price_desc" <?= (($selectedSort ?? '') === 'price_desc') ? 'selected' : '' ?>>Price: high to low</option>
                        <option value="title" <?= (($selectedSort ?? '') === 'title') ? 'selected' : '' ?>>Title A–Z</option>
                    </select>
                </div>

                <div class="col-12 col-md-6 col-xl-4 browse-services-form__actions">
                    <span class="form-label d-block visually-hidden">Search actions</span>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1" aria-hidden="true"></i>Search
                        </button>
                        <?php if ($hasActiveFilters): ?>
                            <a href="<?= esc($clearUrl) ?>" class="btn btn-outline-secondary">Clear filters</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <p class="small text-muted mb-4">
        Subcategory and specific type are optional — choose only a main category to see all related listings.
        Keywords also match category names shown on each service.
    </p>

    <?php if ($hasActiveFilters): ?>
        <p class="text-muted mb-3">
            <?php
            $filterParts = [];
            if (!empty($searchQuery)) {
                $filterParts[] = 'keywords "' . esc($searchQuery) . '"';
            }
            if (!empty($selectedCategory) && isset($catById[(int) $selectedCategory])) {
                $filterParts[] = 'category "' . esc($catById[(int) $selectedCategory]) . '"';
            }
            if (!empty($selectedSubcategory) && isset($catById[(int) $selectedSubcategory])) {
                $filterParts[] = 'subcategory "' . esc($catById[(int) $selectedSubcategory]) . '"';
            }
            if (!empty($selectedThirdCategory) && isset($catById[(int) $selectedThirdCategory])) {
                $filterParts[] = 'type "' . esc($catById[(int) $selectedThirdCategory]) . '"';
            }
            if (!empty($selectedSort) && $selectedSort !== 'newest') {
                $sortLabels = [
                    'price_asc' => 'price (low to high)',
                    'price_desc' => 'price (high to low)',
                    'title' => 'title A-Z',
                ];
                $filterParts[] = 'sort: ' . esc($sortLabels[$selectedSort] ?? $selectedSort);
            }
            ?>
            Showing results for <?= implode(' &middot; ', $filterParts) ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($services)): ?>
        <div class="service-card-container">
            <?php foreach ($services as $service): ?>
                <div class="service-card">
                    <a href="<?= base_url('service/view/' . esc($service['id'])) ?>" class="service-card-link text-decoration-none text-body">
                        <?php if (!empty($service['images'])): ?>
                            <img src="<?= base_url(esc($service['images'][0]['thumbnail_path'])) ?>"
                                alt="<?= esc($service['title']) ?>" class="service-card-image" loading="lazy" decoding="async"
                                onerror="this.onerror=null;this.src='<?= base_url('assets/images/fallback-service-card.jpg') ?>';">
                        <?php else: ?>
                            <img src="<?= base_url('assets/images/fallback-service-card.jpg') ?>" alt="No image available"
                                class="service-card-image" loading="lazy" decoding="async">
                        <?php endif; ?>

                        <div class="service-card-content">
                            <h3 class="service-card-title"><?= esc($service['title']) ?></h3>
                            <?php if (!empty($service['category_name'])): ?>
                                <span class="badge bg-secondary mb-2"><?= esc($service['category_name']) ?></span>
                            <?php endif; ?>
                            <p class="service-card-description">
                                <?= esc($service['short_description'] ?? 'No description available.') ?>
                            </p>
                            <?php if (!empty($service['price'])): ?>
                                <p class="service-price">From &pound;<?= number_format((float) $service['price'], 2) ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="text-center pb-3">
                        <a href="<?= base_url('service/view/' . esc($service['id'])) ?>" class="service-card-button">View Details</a>
                        <?php if (session()->has('user_id') && session()->get('role') === 'customer' && !empty($message_eligible_by_service_id[$service['id']])): ?>
                            <a href="<?= base_url('profile/messages/start/' . esc($service['id'])) ?>" class="btn btn-sm btn-outline-primary ms-1">Message vendor</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3" aria-hidden="true"></i>
            <h4>No services found</h4>
            <p class="text-muted">Try fewer filters, a different category, or different keywords.</p>
            <?php if ($hasActiveFilters): ?>
                <a href="<?= esc($clearUrl) ?>" class="btn btn-outline-primary">View all services</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<script>
    var categories = <?= json_encode($categories ?? []) ?>;
</script>
<script src="<?= base_url('assets/js/category_cascade.js') ?>"></script>
<script>
    $(function () {
        if (typeof window.initCategoryCascade === 'function') {
            window.initCategoryCascade({
                rootSelect: '#browse-category',
                subSelect: '#browse-subcategory',
                thirdSelect: '#browse-third-category',
                categories: categories,
                preselectSub: <?= json_encode($selectedSubcategory ?? '') ?>,
                preselectThird: <?= json_encode($selectedThirdCategory ?? '') ?>,
                subPlaceholder: 'Any subcategory',
                thirdPlaceholder: 'Any specific type',
            });
        }
    });
</script>

<?= $this->include('footer') ?>
