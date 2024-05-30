<?= $this->include('header') ?>

<main class="container mt-4">
    <h2>Add New Service</h2>

    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/service/create" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= old('title') ?>">
        </div>

        <div class="form-group">
            <label for="short_description">Short Description:</label>
            <input type="text" class="form-control" id="short_description" name="short_description" value="<?= old('short_description') ?>">
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea class="form-control" id="description" name="description"><?= old('description') ?></textarea>
        </div>

        <div class="form-group">
            <label for="image">Image:</label>
            <input type="file" class="form-control-file" id="image" name="image"> </div>

        <div class="form-group">
            <label for="price">Price:</label>
            <input type="text" class="form-control" id="price" name="price" value="<?= old('price') ?>">
        </div>
       

        <div class="form-group">
    <label for="category_id">Category:</label>
    <select class="form-control" id="category_id" name="category_id">
        <?php foreach ($categories as $category): ?>
            <option value="<?= esc($category['id']) ?>"><?= esc($category['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group">
    <label for="subcategory_id">Subcategory:</label>
    <select class="form-control" id="subcategory_id" name="subcategory_id">
        <?php foreach ($subcategories as $subcategory): ?>
            <option value="<?= esc($subcategory['id']) ?>" 
                    data-category="<?= esc($subcategory['category_id']) ?>" 
                    <?= (old('subcategory_id') == $subcategory['id']) ? 'selected' : '' ?>>
                <?= esc($subcategory['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<script>
const categorySelect = document.getElementById('category_id');
const subcategorySelect = document.getElementById('subcategory_id');

// Parse the subcategories JSON data passed from the controller
const subcategories = JSON.parse('<?= $subcategoriesJson ?>');

categorySelect.addEventListener('change', function() {
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

        <button type="submit" class="btn btn-primary">Add Service</button>
    </form>
</main>

