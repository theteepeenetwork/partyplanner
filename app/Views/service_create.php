<?= $this->include('header') ?>

<main class="container mt-4">
    <h2>Create Service</h2>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success">
            <?= session('success') ?>
        </div>
    <?php elseif (session()->has('error')): ?>
        <div class="alert alert-danger">
            <?= session('error') ?>
        </div>
    <?php endif; ?>

    <form action="/service/create" method="POST" enctype="multipart/form-data" id="serviceForm">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" class="form-control <?= session('errors.title') ? 'is-invalid' : '' ?>"
                id="title" name="title" value="<?= old('title') ?>">
            <div class="invalid-feedback"><?= session('errors.title') ?></div>
        </div>

        <div class="form-group">
            <label for="short_description">Short Description:</label>
            <input type="text" class="form-control <?= session('errors.short_description') ? 'is-invalid' : '' ?>"
                id="short_description" name="short_description" value="<?= old('short_description') ?>">
            <div class="invalid-feedback"><?= session('errors.short_description') ?></div>
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea class="form-control <?= session('errors.description') ? 'is-invalid' : '' ?>"
                id="description" name="description"><?= old('description') ?></textarea>
            <div class="invalid-feedback"><?= session('errors.description') ?></div>
        </div>

        <div class="form-group">
            <label for="price">Price:</label>
            <input type="text" class="form-control <?= session('errors.price') ? 'is-invalid' : '' ?>"
                id="price" name="price" value="<?= old('price') ?>">
            <div class="invalid-feedback"><?= session('errors.price') ?></div>
        </div>

        <!-- Multiple Images Upload with Primary Image Selector -->
        <div class="form-group">
            <label for="images">Upload Images:</label>
            <input type="file" class="form-control-file <?= session('errors.images') ? 'is-invalid' : '' ?>"
                id="images" name="images[]" multiple>
            <div class="invalid-feedback"><?= session('errors.images') ?></div>
            <label for="primary_image">Set Primary Image:</label>
            <select class="form-control" id="primary_image" name="primary_image">
                <option value="">Select the primary image after upload</option>
            </select>
        </div>

        <!-- Category Dropdowns -->
        <div class="form-group">
            <label for="category_id">Category:</label>
            <select class="form-control" id="category_id" name="category_id" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <?php if ($category['level'] === 0): ?>
                        <option value="<?= esc($category['id']) ?>"><?= esc($category['name']) ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Please select a category.</div>
        </div>

        <div class="form-group">
            <label for="subcategory_id">Subcategory:</label>
            <select class="form-control" id="subcategory_id" name="subcategory_id" disabled required>
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

        <!-- Hire Duration Settings -->
        <div class="form-group">
            <label for="hire_durations">Hire Durations (in hours):</label>
            <div id="hire_durations">
                <?php if (!empty($timeBlocks)): ?>
                    <?php foreach ($timeBlocks as $timeBlock): ?>
                        <div class="d-flex align-items-center mb-2">
                            <select class="form-control" name="hire_durations[]">
                                <?php for ($i = 1; $i <= 24; $i++): ?>
                                    <option value="<?= $i ?>" <?= $timeBlock['time_length'] == $i ? 'selected' : '' ?>>
                                        <?= $i ?> <?= $i == 24 ? 'hours (All Day)' : 'hour' . ($i > 1 ? 's' : '') ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <button type="button" class="btn btn-danger ml-2 remove-duration">Remove</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="d-flex align-items-center mb-2">
                        <select class="form-control" name="hire_durations[]">
                            <?php for ($i = 1; $i <= 24; $i++): ?>
                                <option value="<?= $i ?>">
                                    <?= $i ?> <?= $i == 24 ? 'hours (All Day)' : 'hour' . ($i > 1 ? 's' : '') ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <button type="button" class="btn btn-danger ml-2 remove-duration">Remove</button>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" class="btn btn-secondary mt-2" id="add_duration">Add Duration</button>
        </div>

        <!-- All Day Option -->
        <div class="form-check mb-4">
            <input type="checkbox" class="form-check-input" id="all_day_option" name="all_day_option" value="1"></input>
            <label class="form-check-label" for="all_day_option">Include an "All Day" option</label>
        </div>

        <button type="submit" class="btn btn-primary">Create Service</button>
    </form>
</main>

<footer class="footer mt-5 py-3 bg-light">
</footer>

<?= $this->include('footer') ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        var categories = <?= json_encode($categories) ?>;
        var primaryImageDropdown = $('#primary_image');

        $('#category_id').change(function() {
            var categoryId = $(this).val();
            $('#subcategory_id').empty().append('<option value="">Select Subcategory</option>').prop('disabled', true);
            $('#third_category_id').empty().append('<option value="">Select Further Subcategory</option>').prop('disabled', true);

            if (categoryId) {
                $.each(categories, function(index, category) {
                    if (category.parent_id == categoryId) {
                        $('#subcategory_id').append('<option value="' + category.id + '">' + category.name + '</option>');
                    }
                });
                $('#subcategory_id').prop('disabled', false);
            }
        });

        $('#subcategory_id').change(function() {
            var subcategoryId = $(this).val();
            $('#third_category_id').empty().append('<option value="">Select Further Subcategory</option>').prop('disabled', true);

            if (subcategoryId) {
                $.each(categories, function(index, category) {
                    if (category.parent_id == subcategoryId) {
                        $('#third_category_id').append('<option value="' + category.id + '">' + category.name + '</option>');
                    }
                });
                $('#third_category_id').prop('disabled', false);
            }
        });

        $('#images').change(function() {
            primaryImageDropdown.empty().append('<option value="">Select the primary image after upload</option>');
            $.each(this.files, function(index, file) {
                primaryImageDropdown.append('<option value="' + index + '">' + file.name + '</option>');
            });
        });


        $(document).ready(function() {
            $('#add_duration').click(function() {
                $('#hire_durations').append(`
                <div class="d-flex align-items-center mb-2">
                    <select class="form-control" name="hire_durations[]">
                        ${[...Array(24)].map((_, i) => `<option value="${i+1}">${i+1} ${i+1 == 24 ? 'hours (All Day)' : 'hour' + (i > 0 ? 's' : '')}</option>`).join('')}
                    </select>
                    <button type="button" class="btn btn-danger ml-2 remove-duration">Remove</button>
                </div>
            `);
            });

            $(document).on('click', '.remove-duration', function() {
                $(this).closest('.d-flex').remove();
            });
        });



        $(document).on('click', '.remove-duration', function() {
            $(this).parent().remove();
        });
    });
</script>