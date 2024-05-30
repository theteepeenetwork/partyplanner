<?= $this->include('header') ?>

<main class="container mt-4">
    <h2>Search Results</h2>
    
    <form action="/service/search" method="get">
        <div class="form-row align-items-center">
            <div class="col-md-6">
                <input type="text" class="form-control" id="query" name="q" placeholder="Search..." value="<?= esc($searchQuery) ?>">
            </div>
            <div class="col-md-4">
                <select class="form-control" id="cuisine" name="cuisine">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= esc($category['id']) ?>" 
                                <?= ($cuisine == $category['id']) ? 'selected' : '' ?>>
                            <?= esc($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </div>
    </form>


    <?php if (!empty($services)): ?>
        <p>Showing results for "<?= esc($searchQuery) ?>" in cuisine: <?= esc($cuisine ?: 'All') ?>:</p>
        <ul class="list-unstyled">
            <?php foreach ($services as $service): ?>
                <li class="media mb-3">
                    <img src="<?= base_url('uploads/' . esc($service['image'])) ?>" class="mr-3" alt="<?= esc($service['title']) ?>" style="max-width: 100px;">

                    <div class="media-body">
                        <h5 class="mt-0 mb-1"><?= esc($service['title']) ?></h5>
                        <p><?= esc($service['short_description']) ?></p>
                        <p><?= esc($service['description']) ?></p>
                        <p>Price: $<?= esc($service['price']) ?></p>
                        <a href="<?= base_url('service/view/' . esc($service['id'])) ?>" class="btn btn-primary btn-sm">View Details</a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

    <?php else: ?>
        <p>No services found matching your criteria.</p>
    <?php endif; ?>
</main>

<footer class="footer mt-5 py-3 bg-light">
    </footer>
