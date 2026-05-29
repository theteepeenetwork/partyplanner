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
            <img id="current-image" src="<?= base_url($primaryImage['image_path']) ?>" alt="Main Service Image" class="gallery-main-image">
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
                     alt="Thumbnail">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .service-gallery {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        height: 100%;
    }

    .main-image {
        flex: 1;
        overflow: hidden;
    }

    .gallery-main-image {
        width: 100%;
        height: 100%;
        min-height: 320px;
        max-height: 520px;
        object-fit: cover;
        display: block;
    }

    .thumbnails {
        display: flex;
        justify-content: center;
        gap: 8px;
        padding: 0.75rem 1rem;
        background: rgba(255,255,255,0.6);
    }

    .thumbnail-image {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid transparent;
        cursor: pointer;
        transition: border-color 0.25s ease, transform 0.2s ease;
    }

    .thumbnail-image:hover {
        border-color: #C4956A;
        transform: translateY(-2px);
    }

    .active-thumbnail {
        border-color: #C4956A;
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
