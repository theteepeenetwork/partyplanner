
/************************************************** */
/**********************Step 1********************** */
/************************************************** */

function getCsrfToken() {
    const meta = document.querySelector('meta[name="X-CSRF-TOKEN"]');
    return meta ? meta.getAttribute('content') || '' : '';
}

$(document).ready(function () {
    if (typeof window.initCategoryCascade === 'function' && $('#category_id').length) {
        window.initCategoryCascade({
            rootSelect: '#category_id',
            subSelect: '#subcategory_id',
            thirdSelect: '#third_category_id',
            categories: typeof categories !== 'undefined' ? categories : [],
            preselectSub: typeof selectedSubcategoryId !== 'undefined' ? selectedSubcategoryId : '',
            preselectThird: typeof selectedThirdCategoryId !== 'undefined' ? selectedThirdCategoryId : '',
            subPlaceholder: 'Select Subcategory',
            thirdPlaceholder: 'Select Further Subcategory (optional)',
        });
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
                    'X-CSRF-TOKEN': getCsrfToken(),
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
