<?= $this->include('header') ?>

<main class="container mt-4">
    <h2>Welcome to Our Event Marketplace</h2>

    <div class="row">
        <?php foreach ($services as $service): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <!-- Display the first thumbnail image -->
                    <?php if (!empty($service['images'])): ?>
                        <img src="<?= base_url(esc($service['images'][0]['thumbnail_path'])) ?>" class="card-img-top" alt="<?= esc($service['title']) ?>" style="object-fit: cover; height: 200px; width: 100%;">
                    <?php else: ?>
                        <img src="<?= base_url('uploads/default-thumbnail.jpg') ?>" class="card-img-top" alt="<?= esc($service['title']) ?>" style="object-fit: cover; height: 200px; width: 100%;">
                    <?php endif; ?>

                    <div class="card-body">
                        <h5 class="card-title"><?= esc($service['title']) ?></h5>
                        <p class="card-text"><?= esc($service['short_description']) ?></p>
                        <p class="card-text">Price: $<?= esc($service['price']) ?></p>
                        <a href="<?= base_url('service/view/' . esc($service['id'])) ?>" class="btn btn-primary btn-sm">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?= $this->include('footer') ?>
