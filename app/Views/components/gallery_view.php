<!-- APPPATH/Views/components/gallery_view.php -->

<div class="service-gallery">
    <!-- Main Image Display -->
    <div class="main-image">
        <?php if (!empty($images)): ?>
            <?php
            // Determine the primary image or default to the first image
            $primaryImage = null;
            foreach ($images as $image) {
                if ($image['is_primary']) {
                    $primaryImage = $image;
                    break;
                }
            }
            if (!$primaryImage) {
                $primaryImage = $images[0]; // Default to the first image if no primary is set
            }
            ?>
            <img id="current-image" src="<?= base_url($primaryImage['image_path']) ?>" alt="Main Service Image" style="width:100%; max-height: 500px;">
        <?php else: ?>
            <p>No images available for this service.</p>
        <?php endif;  ?>
    </div>

    <!-- Thumbnails Display -->
    <?php if (count($images) > 1): ?>
        <div class="thumbnails mt-3">
            <?php foreach ($images as $image): ?>
                
                <img class="thumbnail-image <?= $image['id'] == $primaryImage['id'] ? 'active-thumbnail' : '' ?>" 
                     src="<?= base_url($image['thumbnail_path']) ?>" 
                     data-full-image="<?= base_url($image['image_path']) ?>" 
                     alt="Thumbnail" style="width:100px; cursor:pointer; margin-right: 10px;">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .service-gallery {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .main-image {
        margin-bottom: 15px;
    }

    .thumbnails {
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .thumbnail-image {
        border: 2px solid transparent;
        transition: border 0.3s ease;
    }

    .thumbnail-image:hover {
        border: 2px solid #007bff; /* Highlight the thumbnail on hover */
    }

    .active-thumbnail {
        border: 2px solid #007bff; /* Highlight the active thumbnail */
    }
</style>

<script>
    $(document).ready(function() {
        $('.thumbnail-image').on('click', function() {
            // Get the full image URL from the data attribute
            var fullImageUrl = $(this).data('full-image');
            // Update the main image with the full-size image
            $('#current-image').attr('src', fullImageUrl);

            // Update active thumbnail
            $('.thumbnail-image').removeClass('active-thumbnail');
            $(this).addClass('active-thumbnail');
        });
    });
</script>
