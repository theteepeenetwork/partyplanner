<?= $this->include('header') ?>

<main class="container mt-4">
    <h2>Welcome to Our Event Marketplace</h2>

    <div class="row">
        <?php foreach ($services as $service): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="<?= base_url('uploads/' . esc($service['image'])) ?>" class="card-img-top" alt="<?= esc($service['title']) ?>">
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
