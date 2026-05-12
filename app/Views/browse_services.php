<?= $this->include('header') ?>

<main class="container">
    <h2 class="mb-4">Browse Services</h2>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <form class="row mb-4 g-2 align-items-end" action="/browse-services" method="get" id="browse-services-form">
        <?php if (!empty($basketEventId)): ?>
            <input type="hidden" name="event_id" value="<?= esc($basketEventId) ?>">
        <?php endif; ?>
        <div class="col-lg-4 col-md-6">
            <label for="browse-q" class="form-label small text-muted mb-0">Search</label>
            <input type="text" class="form-control" id="browse-q" name="q" placeholder="Search services…"
                value="<?= esc($searchQuery ?? '') ?>">
        </div>
        <div class="col-lg-2 col-md-6">
            <label for="browse-category" class="form-label small text-muted mb-0">Category</label>
            <select class="form-control" id="browse-category" name="category">
                <option value="">All categories</option>
                <?php foreach ($rootCategories as $category): ?>
                    <option value="<?= esc($category['id']) ?>"
                        <?= ((string) ($selectedCategory ?? '')) === (string) $category['id'] ? 'selected' : '' ?>>
                        <?= esc($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-2 col-md-4">
            <label for="browse-subcategory" class="form-label small text-muted mb-0">Subcategory</label>
            <select class="form-control" id="browse-subcategory" name="subcategory">
                <option value="">All subcategories</option>
            </select>
        </div>
        <div class="col-lg-2 col-md-4">
            <label for="browse-third-category" class="form-label small text-muted mb-0">Further refine</label>
            <select class="form-control" id="browse-third-category" name="third_category">
                <option value="">All (optional)</option>
            </select>
        </div>
        <div class="col-lg-2 col-md-4">
            <label for="browse-sort" class="form-label small text-muted mb-0">Sort by</label>
            <select class="form-control" id="browse-sort" name="sort">
                <option value="newest" <?= (($selectedSort ?? 'newest') === 'newest') ? 'selected' : '' ?>>Newest listed</option>
                <option value="price_asc" <?= (($selectedSort ?? '') === 'price_asc') ? 'selected' : '' ?>>Price: low to high</option>
                <option value="price_desc" <?= (($selectedSort ?? '') === 'price_desc') ? 'selected' : '' ?>>Price: high to low</option>
                <option value="title" <?= (($selectedSort ?? '') === 'title') ? 'selected' : '' ?>>Title A–Z</option>
            </select>
        </div>
        <div class="col-12 col-lg-12">
            <button type="submit" class="btn btn-primary">Apply</button>
        </div>
    </form>
    <p class="small text-muted mb-4">Search matches service title, descriptions, free-text tags, and the names of the categories you assign to a listing. Use the three filters to narrow results from broad groups down to specific offerings (for example Catering &amp; Food → Street food vendors → optional further refinement when your taxonomy uses three levels).</p>

    <?php
    $catById = [];
    foreach ($categories as $c) {
        $catById[(int) $c['id']] = $c['name'];
    }
    $hasActiveFilters = !empty($searchQuery)
        || !empty($selectedCategory)
        || !empty($selectedSubcategory)
        || !empty($selectedThirdCategory)
        || (!empty($selectedSort) && $selectedSort !== 'newest');
    ?>
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
                $filterParts[] = 'refinement "' . esc($catById[(int) $selectedThirdCategory]) . '"';
            }
            if (!empty($selectedSort) && $selectedSort !== 'newest') {
                $sortLabels = [
                    'price_asc' => 'price (low to high)',
                    'price_desc' => 'price (high to low)',
                    'title' => 'title A–Z',
                ];
                $filterParts[] = 'sort: ' . esc($sortLabels[$selectedSort] ?? $selectedSort);
            }
            ?>
            Showing results for <?= implode(' · ', $filterParts) ?>
            <a href="/browse-services<?= !empty($basketEventId) ? '?event_id=' . esc($basketEventId) : '' ?>" class="ms-2">(Clear filters)</a>
        </p>
    <?php endif; ?>

    <?php if (!empty($services)): ?>
        <div class="service-card-container">
            <?php foreach ($services as $service): ?>
                <div class="service-card">
                    <a href="<?= base_url('service/view/' . esc($service['id'])) ?>" style="text-decoration: none; color: inherit;">
                        <?php if (!empty($service['images'])): ?>
                            <img src="<?= base_url(esc($service['images'][0]['thumbnail_path'])) ?>"
                                alt="<?= esc($service['title']) ?>" class="service-card-image">
                        <?php else: ?>
                            <img src="<?= base_url('assets/images/no-image.png') ?>" alt="No Image Available"
                                class="service-card-image">
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
                                <p class="service-price">From £<?= number_format($service['price'], 2) ?></p>
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
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h4>No services found</h4>
            <p class="text-muted">Try adjusting your search or browse all categories.</p>
            <?php if (!empty($searchQuery) || !empty($selectedCategory) || !empty($selectedSubcategory) || !empty($selectedThirdCategory)): ?>
                <a href="/browse-services<?= !empty($basketEventId) ? '?event_id=' . esc($basketEventId) : '' ?>" class="btn btn-outline-primary">View All Services</a>
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
                subPlaceholder: 'All subcategories',
                thirdPlaceholder: 'All (optional)',
            });
        }
    });
</script>

<?= $this->include('footer') ?>
