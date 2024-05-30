<?= $this->include('header') ?>

<main class="container mt-4">
    <?php if (isset($service)): ?>
        <div class="card">
            <?php if (!empty($service['image'])): ?>
                <img src="<?= base_url('uploads/' . esc($service['image'])) ?>" class="card-img-top img-fluid" alt="<?= esc($service['title']) ?>" style="max-width: 500px;"> 
            <?php endif; ?>

            <div class="card-body">
                <h2 class="card-title"><?= esc($service['title']) ?></h2>
                <h5 class="card-subtitle mb-2 text-muted"><?= esc($service['short_description']) ?></h5>
                <p class="card-text"><?= nl2br(esc($service['description'])) ?></p>
                <p class="card-text">Category: <?= esc($service['category_name']) ?></p>
                <?php if (isset($service['subcategory_name'])): ?> 
                    <p class="card-text">Subcategory: <?= esc($service['subcategory_name']) ?></p>
                <?php endif; ?>
                <p class="card-text"><strong>Price:</strong> $<?= esc($service['price']) ?></p>
                
            </div>
            <div class="card-body">
            <form action="<?= base_url('cart/add/' . $service['id']) ?>" method="post">
                <input type="hidden" name="service_id" value="<?= $service['id'] ?>"> 
                <?php if (session()->has('user_id') && session()->get('role') == 'vendor' && $service['vendor_id'] == session()->get('user_id')): ?>
                        <a href="/service/edit/<?= esc($service['id']) ?>" class="btn btn-secondary btn-sm">Edit</a>
                    <?php endif; ?>
                <button type="submit" class="btn btn-success">Add to Cart</button>
            </form>
        </div>
    <?php else: ?>
        <p>Service not found.</p>
    <?php endif; ?>
</main>

<footer class="footer mt-5 py-3 bg-light">
</footer>
