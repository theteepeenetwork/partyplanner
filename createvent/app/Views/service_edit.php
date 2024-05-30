<?= $this->include('header') ?>

<main class="container mt-4">
    <h2>Edit Service</h2>

    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="/service/edit/<?= esc($service['id']) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= old('title', $service['title']) ?>">
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea class="form-control" id="description" name="description"><?= old('description', $service['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="price">Price:</label>
            <input type="text" class="form-control" id="price" name="price" value="<?= old('price', $service['price']) ?>">
        </div>

        <button type="submit" class="btn btn-primary">Update Service</button>
    </form>
</main>

<footer class="footer mt-5 py-3 bg-light">
</footer>
