<?= $this->include('header') ?>

<main class="container mt-4">
    <h2>Edit Service</h2>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success">
            <?= session('success') ?>
        </div>
    <?php elseif (session()->has('error')): ?>
        <div class="alert alert-danger">
            <?= session('error') ?>
        </div>
    <?php endif; ?>

    <form action="/service/edit/<?= esc($service['id']) ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" class="form-control <?= session('errors.title') ? 'is-invalid' : '' ?>" id="title" name="title" value="<?= old('title', esc($service['title'])) ?>">
            <div class="invalid-feedback"><?= session('errors.title') ?></div>
        </div>

        <div class="form-group">
            <label for="short_description">Short Description:</label>
            <input type="text" class="form-control <?= session('errors.short_description') ? 'is-invalid' : '' ?>" id="short_description" name="short_description" value="<?= old('short_description', esc($service['short_description'])) ?>">
            <div class="invalid-feedback"><?= session('errors.short_description') ?></div>
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea class="form-control <?= session('errors.description') ? 'is-invalid' : '' ?>" id="description" name="description"><?= old('description', esc($service['description'])) ?></textarea>
            <div class="invalid-feedback"><?= session('errors.description') ?></div>
        </div>

        <div class="form-group">
            <label for="price">Price:</label>
            <input type="text" class="form-control <?= session('errors.price') ? 'is-invalid' : '' ?>" id="price" name="price" value="<?= old('price', esc($service['price'])) ?>">
            <div class="invalid-feedback"><?= session('errors.price') ?></div>
        </div>

        <div class="form-group">
            <label for="image">Image:</label>
            <input type="file" class="form-control-file" id="image" name="image">
        </div>

        <div class="form-group">
            <label for="category_id">Category:</label>
            <select class="form-control" id="category_id" name="category_id">
                <?php foreach ($categories as $category): ?>
                    <option value="<?= esc($category['id']) ?>" <?= $service['category_id'] == $category['id'] ? 'selected' : '' ?>><?= esc($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="subcategory_id">Subcategory:</label>
            <select class="form-control" id="subcategory_id" name="subcategory_id">
                <?php foreach ($subcategories as $subcategory): ?>
                    <option value="<?= esc($subcategory['id']) ?>" data-category="<?= esc($subcategory['category_id']) ?>" <?= ($service['subcategory_id'] == $subcategory['id']) ? 'selected' : '' ?>><?= esc($subcategory['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

             <script>
            const categorySelect = document.getElementById('category_id');
            const subcategorySelect = document.getElementById('subcategory_id');

            // Parse the subcategories JSON data passed from the controller
            const subcategories = JSON.parse('<?= $subcategoriesJson ?>');

            categorySelect.addEventListener('change', function () {
                const categoryId = this.value;
                subcategorySelect.innerHTML = ''; // Clear existing options

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.text = 'Select Subcategory';
                subcategorySelect.add(defaultOption);


                // Filter subcategories based on selected category ID
                const filteredSubcategories = subcategories.filter(subcategory => subcategory.category_id == categoryId);

                filteredSubcategories.forEach(subcategory => {
                    const option = document.createElement('option');
                    option.value = subcategory.id;
                    option.text = subcategory.name;
                    subcategorySelect.add(option);
                });
            });

            // Trigger the change event on page load to populate the default subcategories
            categorySelect.dispatchEvent(new Event('change'));
        </script>

<form method="POST" action="<?= base_url('service/delete/' . esc($service['id'])) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="_method" value="DELETE">
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this service? This cannot be undone.')">Delete Service</button>
        </form>

        <button type="submit" class="btn btn-primary">Update Service</button>
    </form>
</main>

<footer class="footer mt-5 py-3 bg-light">
</footer>
