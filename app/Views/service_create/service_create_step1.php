<?= $this->include('header') ?>

<?= $this->include('service_create/css.php'); ?>

<link rel="stylesheet" href="<?= base_url('assets/css/service-form.css'); ?>">



<style>
    #imagePreviewContainer {
        display: flex;
        flex-wrap: wrap;
        /* Allow images to wrap to the next line */
        gap: 10px;
        /* Space between images */
        justify-content: flex-start;
        /* Align images to the left */
    }

    #imagePreviewContainer .image-preview {
        width: 150px;
        /* Fixed width for each image container */
        border: 1px solid #ddd;
        padding: 10px;
        display: flex;
        flex-direction: column;
        /* Stack contents vertically inside the container */
        align-items: center;
        /* Center contents horizontally */
        justify-content: center;
        /* Center contents vertically */
        text-align: center;
        box-sizing: border-box;
        /* Include padding in width */
    }

    #imagePreviewContainer .image-preview img {
        max-width: 100%;
        /* Fit within the container */
        height: auto;
        /* Maintain aspect ratio */
        display: block;
    }
</style>




<main class="container">


    <!-- Success and Error Messages -->
    <?php if (session()->has('success')): ?>
        <div class="alert alert-success">
            <?= session('success') ?>
        </div>
    <?php elseif (session()->has('error')): ?>
        <div class="alert alert-danger">
            <?= session('error') ?>
        </div>
    <?php endif; ?>




    <form action="/service/step1" method="POST" enctype="multipart/form-data" id="serviceForm" class="service-form">
        <?= csrf_field() ?>


        <!-- Progress Bar -->


        <!-- Step 1: Basic Service Information -->


        <!-- Step 1 -->
        <!-- Step 1 -->
        <!-- Step 1 -->
        <!-- Step 1 -->
        <!-- Step 1 -->
        <!-- Step 1 -->
        <!-- Step 1 -->
        <section id="step1" class="step">
            <h4>Service Information</h4>

            <!-- Title with Info Icon -->
            <div class="form-group">
                <label for="title">Title:</label>
                <?php
                $titleValue = !empty(session('step1_data')['title']) ? esc(session('step1_data')['title']) : old('title');
                ?>
                <input type="text" class="form-control <?= session('errors.title') ? 'is-invalid' : '' ?>" id="title"
                    name="title" value="<?= $titleValue ?>" maxlength="100"
                    placeholder="E.g., Wedding Photography Package">
                </p>
                <div class="invalid-feedback"><?= session('errors.title') ?></div>
            </div>

            <!-- Short Description -->
            <div class="form-group">
                <label for="short_description">Short Description:</label>
                <?php
                $shortDescriptionValue = !empty(session('step1_data')['short_description'])
                    ? esc(session('step1_data')['short_description'])
                    : old('short_description');
                ?>
                <input type="text" class="form-control <?= session('errors.short_description') ? 'is-invalid' : '' ?>"
                    id="short_description" name="short_description" value="<?= $shortDescriptionValue ?>"
                    maxlength="200" placeholder="Provide a brief description (max 200 characters)">
                <div class="invalid-feedback"><?= session('errors.short_description') ?></div>

            </div>

            <!-- Long Description -->
            <div class="form-group">
                <label for="description">Description:</label>
                <?php
                $descriptionValue = !empty(session('step1_data')['description'])
                    ? esc(session('step1_data')['description'])
                    : old('description');
                ?>
                <textarea class="form-control <?= session('errors.description') ? 'is-invalid' : '' ?>" id="description"
                    name="description" rows="5"
                    placeholder="Detailed description of your service..."><?= $descriptionValue ?></textarea>
                <div class="invalid-feedback"><?= session('errors.description') ?></div>

            </div>

            <!-- Service Tags -->
            <div class="form-group">
                <label for="service_tags_">Service Tags:</label>
                <div class="tag-container" id="tagContainer">
                    <?php
                    $serviceTagsValue = !empty(session('step1_data')['service_tags'])
                        ? esc(session('step1_data')['service_tags'])
                        : old('service_tags');
                    ?>
                    <input type="text" id="service_tags" class="tag-input" name="service_tags"
                        placeholder="Enter tags (comma-separated)" value="<?= $serviceTagsValue ?>">

                </div>

                <small class="form-text text-muted">Example: wedding, catering, food, photography</small>
            </div>



            <!-- ===================== IMAGES ===================== -->
            <!-- ===================== IMAGES ===================== -->
            <!-- ===================== IMAGES ===================== -->
            <!-- ===================== IMAGES ===================== -->
            <!-- ===================== IMAGES ===================== -->
            <!-- ===================== IMAGES ===================== -->
            <!-- ===================== IMAGES ===================== -->



            <h4>Images</h4>
            <div class="form-group">
                <label for="images">Upload Images:</label>

                <!-- Validation Error -->
                <?php if (session('errors.images')): ?>
                    <div class="invalid-feedback" style="display: block;">
                        <?= session('errors.images') ?>
                    </div>
                <?php endif; ?>

                <div id="imageUploader" class="image-uploader">
                    <div class="input-images">
                        <script
                            src="https://cdn.jsdelivr.net/gh/christianbayer/image-uploader@master/dist/image-uploader.min.js"></script>
                    </div>
                </div>
                <small class="form-text text-muted">Accepted formats: JPG, PNG. Max size: 5MB each.</small>
            </div>


            <div id="imagePreviewContainer">
                <?php if (!empty(session('uploaded_images'))): ?>
                    <?php foreach (session('uploaded_images') as $index => $image): ?>
                        <?php $imgId = $image['formId'] ?? $index; ?>
                        <div class="image-preview" data-index="<?= esc($imgId) ?>">
                            <img src="<?= base_url($image['image_path'] ?? $image['thumbnail_path'] ?? '') ?>" alt="Uploaded Image"
                                style="max-width: 150px; height: auto;">

                            <button type="button" class="btn btn-danger btn-sm delete-image"
                                data-index="<?= esc($imgId) ?>">Delete</button>
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No images uploaded yet.</p>
                <?php endif; ?>
            </div>


            <!-- ===================== CATEGORIES ===================== -->
            <!-- ===================== CATEGORIES ===================== -->
            <!-- ===================== CATEGORIES ===================== -->
            <!-- ===================== CATEGORIES ===================== -->
            <!-- ===================== CATEGORIES ===================== -->
            <!-- ===================== CATEGORIES ===================== -->
            <!-- ===================== CATEGORIES ===================== -->





            <h4>Categories</h4>
            <!-- Main Category -->
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select class="form-control" id="category_id" name="category_id">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <?php if ($category['level'] === 0): ?>
                            <option value="<?= esc($category['id']) ?>" <?= old('category_id', session('step1_data.category_id') ?? '') == $category['id'] ? 'selected' : '' ?>>
                                <?= esc($category['name']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Please select a category.</div>
            </div>

            <!-- Subcategory -->
            <div class="form-group">
                <label for="subcategory_id">Subcategory:</label>
                <select class="form-control" id="subcategory_id" name="subcategory_id">
                    <option value="">Select Subcategory</option>
                </select>
                <div class="invalid-feedback">Please select a subcategory.</div>
            </div>

            <!-- Third Category -->
            <div class="form-group">
                <label for="third_category_id">Further Subcategory (optional):</label>
                <select class="form-control" id="third_category_id" name="third_category_id">
                    <option value="">Select Further Subcategory</option>
                </select>
                <small class="form-text text-muted">Optional selection.</small>
            </div>


            <button type="submit" class="btn btn-primary">
                <?= !isset($step2_data) ? "Next" : "Review" ?>

            </button>

        </section>

    </form>




    <!-- Step 1 -->
    <!-- Step 1 -->
    <!-- Step 1 -->
    <!-- Step 1 -->
    <!-- Step 1 -->
    <!-- Step 1 -->
    <!-- Step 1 -->

    <script>
        //Required for scripts.js
        var categories = <?= json_encode($categories) ?>;

        // Retrieve previously selected values from session or form submission
        var selectedCategoryId = <?= json_encode(old('category_id', session('step1_data.category_id') ?? '')) ?>;
        var selectedSubcategoryId = <?= json_encode(old('subcategory_id', session('step1_data.subcategory_id') ?? '')) ?>;
        var selectedThirdCategoryId =
            <?= json_encode(old('third_category_id', session('step1_data.third_category_id') ?? '')) ?>;
    </script>
    <script src="<?= base_url('assets/js/category_cascade.js') ?>"></script>
    <script src="<?= base_url('assets/js/service_forms/step1.js') ?>"></script>
    <script src="<?= base_url('assets/js/test.js') ?>"></script>