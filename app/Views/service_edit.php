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

    <div id="statusMessage" class="alert" style="display:none;"></div>

    <form action="/service/edit/<?= esc($service['id']) ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" class="form-control <?= session('errors.title') ? 'is-invalid' : '' ?>" id="title"
                name="title" value="<?= old('title', esc($service['title'])) ?>">
            <div class="invalid-feedback"><?= session('errors.title') ?></div>
        </div>

        <div class="form-group">
            <label for="short_description">Short Description:</label>
            <input type="text" class="form-control <?= session('errors.short_description') ? 'is-invalid' : '' ?>"
                id="short_description" name="short_description"
                value="<?= old('short_description', esc($service['short_description'])) ?>">
            <div class="invalid-feedback"><?= session('errors.short_description') ?></div>
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea class="form-control <?= session('errors.description') ? 'is-invalid' : '' ?>" id="description"
                name="description"><?= old('description', esc($service['description'])) ?></textarea>
            <div class="invalid-feedback"><?= session('errors.description') ?></div>
        </div>

        <div class="form-group">
            <label for="price">Price:</label>
            <input type="text" class="form-control <?= session('errors.price') ? 'is-invalid' : '' ?>" id="price"
                name="price" value="<?= old('price', esc($service['price'])) ?>">
            <div class="invalid-feedback"><?= session('errors.price') ?></div>
        </div>

        <!-- Image Upload Section -->
        <div class="form-group">
            <label for="images">Upload Images:</label>
            <input type="file" class="form-control-file" id="images" name="images[]" multiple>
            <?php if (!empty($service['images'])): ?>
                <div class="mt-3">
                    <?php foreach ($service['images'] as $image): ?>
                        <div class="image-thumbnail">
                            <img src="<?= base_url($image['thumbnail_path']) ?>" alt="Service Image" class="img-thumbnail" style="max-width: 100px;">
                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteImage(<?= $image['id'] ?>)">Delete</button>
                            <label>
                                <input type="radio" name="primary_image" value="<?= $image['id'] ?>" <?= $image['is_primary'] ? 'checked' : '' ?>>
                                Set as Primary
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Category Handling -->
        <div class="form-group">
            <label for="category_id">Category:</label>
            <select class="form-control" id="category_id" name="category_id" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <?php if ($category['level'] === 0): ?>
                        <option value="<?= esc($category['id']) ?>" <?= old('category_id', esc($service['category_id'])) == $category['id'] ? 'selected' : '' ?>>
                            <?= esc($category['name']) ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Please select a category.</div>
        </div>


        <div class="form-group">
            <label for="subcategory_id">Subcategory:</label>
            <select class="form-control" id="subcategory_id" name="subcategory_id" <?= empty(old('subcategory_id', $service['subcategory_id'] ?? '')) ? 'disabled' : '' ?> required>
                <option value="">Select Subcategory</option>
            </select>
            <div class="invalid-feedback">Please select a subcategory.</div>
        </div>


        <div class="form-group">
            <label for="third_category_id">Further Subcategory:</label>
            <select class="form-control" id="third_category_id" name="third_category_id" disabled required>
                <option value="">Select Further Subcategory</option>
            </select>
            <div class="invalid-feedback">Please select a further subcategory.</div>
        </div>

        <!-- Time Blocks for Service -->
        <div class="form-group">
            <label for="time_blocks">Time Blocks:</label>
            <div id="timeBlocksContainer">
                <!-- Existing Time Blocks -->
                <?php if (!empty($timeBlocks)): ?>
                    <?php foreach ($timeBlocks as $timeBlock): ?>
                        <div class="input-group mb-2">
                            <select name="time_blocks[]" class="form-control">
                                <?php for ($i = 1; $i <= 24; $i++): ?>
                                    <option value="<?= $i ?>" <?= $timeBlock['time_length'] == $i ? 'selected' : '' ?>>
                                        <?= $i ?> <?= $i == 24 ? 'All Day' : 'Hour(s)' ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-danger remove-time-block">Remove</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" class="btn btn-secondary" id="addTimeBlock">Add Time Block</button>
        </div>


        <button type="submit" class="btn btn-primary">Update Service</button>

        <!-- Deactivate/Reactivate Button -->
        <?php if ($service['status'] === 'active'): ?>
            <button type="button" class="btn btn-warning btn-sm" onclick="toggleServiceStatus(<?= esc($service['id']) ?>, 'deactivate')">Delist Service</button>
        <?php else: ?>
            <button type="button" class="btn btn-success btn-sm" onclick="toggleServiceStatus(<?= esc($service['id']) ?>, 'reactivate')">Relist Service</button>
        <?php endif; ?>


        <!-- Delete Button -->
        <button type="button" class="btn btn-danger btn-sm" onclick="toggleServiceStatus(<?= esc($service['id']) ?>, 'delete')">Delete Service</button>
    </form>
</main>

<footer class="footer mt-5 py-3 bg-light">
</footer>

<?= $this->include('footer') ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
    var categories = <?= json_encode($categories) ?>;
    console.log("Categories data:", categories);

    function populateSubcategories(categoryId, selectedSubcategoryId = null) {
        console.log("Selected category ID:", categoryId);

        $('#subcategory_id').empty().append('<option value="">Select Subcategory</option>').prop('disabled', true);
        $('#third_category_id').empty().append('<option value="">Select Further Subcategory</option>').prop('disabled', true);

        if (categoryId) {
            var subcategories = categories.filter(function(category) {
                return category.parent_id == categoryId;
            });

            console.log("Filtered subcategories:", subcategories);

            if (subcategories.length > 0) {
                subcategories.forEach(function(subcategory) {
                    $('#subcategory_id').append('<option value="' + subcategory.id + '"' + (subcategory.id == selectedSubcategoryId ? ' selected' : '') + '>' + subcategory.name + '</option>');
                });
                $('#subcategory_id').prop('disabled', false);
            }
        }
    }

    function populateThirdCategories(subcategoryId, selectedThirdCategoryId = null) {
        console.log("Selected subcategory ID:", subcategoryId);

        $('#third_category_id').empty().append('<option value="">Select Further Subcategory</option>').prop('disabled', true);

        if (subcategoryId) {
            var thirdCategories = categories.filter(function(category) {
                return category.parent_id == subcategoryId;
            });

            console.log("Filtered third-level categories:", thirdCategories);

            if (thirdCategories.length > 0) {
                thirdCategories.forEach(function(thirdCategory) {
                    $('#third_category_id').append('<option value="' + thirdCategory.id + '"' + (thirdCategory.id == selectedThirdCategoryId ? ' selected' : '') + '>' + thirdCategory.name + '</option>');
                });
                $('#third_category_id').prop('disabled', false);
            }
        }
    }

    // Initial population on page load
    var initialCategoryId = $('#category_id').val();
    var initialSubcategoryId = <?= json_encode($service['subcategory_id'] ?? null) ?>;
    var initialThirdCategoryId = <?= json_encode($service['third_category_id'] ?? null) ?>;

    if (initialCategoryId) {
        populateSubcategories(initialCategoryId, initialSubcategoryId);
    }

    if (initialSubcategoryId) {
        populateThirdCategories(initialSubcategoryId, initialThirdCategoryId);
    }

    // Handle category change
    $('#category_id').change(function() {
        var categoryId = $(this).val();
        populateSubcategories(categoryId);
    });

    // Handle subcategory change
    $('#subcategory_id').change(function() {
        var subcategoryId = $(this).val();
        populateThirdCategories(subcategoryId);
    });
});






    // Remove Time Block functionality
    $(document).on('click', '.remove-time-block', function() {
        $(this).closest('.input-group').remove();
    });

    // Remove Time Block functionality
    $(document).on('click', '.remove-time-block', function() {
        $(this).closest('.input-group').remove();
    });

    // Delete image function
    window.deleteImage = function(imageId) {
        if (confirm('Are you sure you want to delete this image?')) {
            $.ajax({
                url: '/service/delete-image/' + imageId,
                type: 'POST',
                data: {
                    _token: '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + xhr.responseText);
                }
            });
        }
    };

    // Set primary image function
    $('input[name="primary_image"]').change(function() {
    var imageId = $(this).val();

    $.ajax({
        url: '/service/set-primary-image/' + imageId,
        type: 'POST',
        data: {
            _token: '<?= csrf_hash() ?>'
        },
        success: function(response) {
            if (response.status === 'success') {
                alert('Primary image set successfully');
            } else {
                alert('Failed to set primary image');
            }
        },
        error: function(xhr, status, error) {
            alert('An error occurred: ' + xhr.responseText);
        }
    });
    });
    
</script>
<script type="text/javascript">
    var baseUrl = "<?= base_url() ?>"; // Define the base URL of your application
    var csrfToken = "<?= csrf_hash() ?>"; // If you are using CSRF protection
</script>
<script>
    function toggleServiceStatus(serviceId, action) {

        let url = baseUrl + 'service/' + action + '/' + serviceId;

        fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken, // If CSRF protection is enabled
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json(); // Ensure the response is parsed as JSON
            })
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    location.reload(); // Reload the page or update the UI accordingly
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
            });
    }
</script>

</main>