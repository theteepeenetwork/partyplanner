
/************************************************** */
/**********************Step 1********************** */
/************************************************** */

$(document).ready(function () {


    function populateSubcategories(categoryId, preselectSubcategoryId = null) {
        $('#subcategory_id').empty().append('<option value="">Select Subcategory</option>').prop('disabled', true);
        $('#third_category_id').empty().append('<option value="">Select Further Subcategory</option>').prop('disabled', true);

        if (categoryId) {
            $.each(categories, function (index, category) {
                if (category.parent_id == categoryId) {
                    $('#subcategory_id').append('<option value="' + category.id + '"' +
                        (category.id == preselectSubcategoryId ? ' selected' : '') + '>' + category.name + '</option>');
                }
            });
            $('#subcategory_id').prop('disabled', false);

            // Populate third category if subcategory is pre-selected
            if (preselectSubcategoryId) {
                populateThirdCategories(preselectSubcategoryId, selectedThirdCategoryId);
            }
        }
    }

    function populateThirdCategories(subcategoryId, preselectThirdCategoryId = null) {
        $('#third_category_id').empty().append('<option value="">Select Further Subcategory</option>').prop('disabled', true);

        if (subcategoryId) {
            $.each(categories, function (index, category) {
                if (category.parent_id == subcategoryId) {
                    $('#third_category_id').append('<option value="' + category.id + '"' +
                        (category.id == preselectThirdCategoryId ? ' selected' : '') + '>' + category.name + '</option>');
                }
            });
            $('#third_category_id').prop('disabled', false);
        }
    }

    // Function to check and populate dynamically
    function checkAndPopulateCategories() {
        const categoryId = $('#category_id').val();

        // Populate subcategories if category_id is set
        if (categoryId && $('#subcategory_id option').length === 1) {
            populateSubcategories(categoryId, selectedSubcategoryId);
        }

        const subcategoryId = $('#subcategory_id').val();

        // Populate third categories if subcategory_id is set
        if (subcategoryId && $('#third_category_id option').length === 1) {
            populateThirdCategories(subcategoryId, selectedThirdCategoryId);
        }
    }

    // Periodically check if categories need to be populated
    const checkInterval = setInterval(() => {
        checkAndPopulateCategories();
    }, 200); // Check every 200 milliseconds

    // Stop the interval once the dropdowns are populated
    setTimeout(() => clearInterval(checkInterval), 5000); // Stop checking after 5 seconds

    // Handle dynamic changes
    $('#category_id').change(function () {
        populateSubcategories($(this).val());
    });

    $('#subcategory_id').change(function () {
        populateThirdCategories($(this).val());
    });

    // On page load, initialize dropdowns based on pre-selected values
    if (selectedCategoryId) {
        $('#category_id').val(selectedCategoryId);
        populateSubcategories(selectedCategoryId, selectedSubcategoryId);
    }
});

//Images
document.addEventListener('DOMContentLoaded', function () {
    $('.input-images').imageUploader();
});

document.addEventListener('DOMContentLoaded', function () {
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');

    // Listen for delete button clicks
    imagePreviewContainer.addEventListener('click', function (event) {
        if (event.target.classList.contains('delete-image')) {
            const formId = event.target.getAttribute('data-index');
            console.log('Form ID being sent to server:', formId); // Log the formId

            // Send an AJAX request to delete the image
            fetch(`/service/delete-image/${formId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>', // CSRF token for CodeIgniter
                },
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Server response:', data); // Log server response
                    if (data.success) {
                        // Remove the image preview
                        const imagePreview = document.querySelector(`.image-preview[data-index="${formId}"]`);
                        if (imagePreview) {
                            imagePreview.remove();
                        }
                    } else {
                        console.error('Error from server:', data.error);
                        alert(data.error || 'Error deleting image.');
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    });
});



















