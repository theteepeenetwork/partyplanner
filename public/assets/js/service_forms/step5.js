const extrasContainer = document.getElementById('optionalExtrasContainer');
let extraCount = 0;

function addOptionalExtra(extra = { name: '', description: '', price: '', quantity: '' }, isFirstRow = false) {
    if (!extrasContainer) return;

    extraCount++; // Increment the counter
    const extraHTML = `
        <div class="form-group optional-extra mb-3 optional-extras-row" id="optional_extra_${extraCount}">
        <h4>Optional Extra ${extraCount}</h4>
            <div class="row g-3 align-items-center">
                <!-- Extra Name -->
                <div class="">
    <label>Extra Name</label>
    <input 
        type="text" 
        id="extra_name_${extraCount}" 
        class="form-control extra-name"
        name="extra_name[]" 
        placeholder="e.g. Extra staff member, Premium equipment, Delivery service"
        value="${extra.name || ''}" 
    >
</div>

<!-- Extra Description -->
<div class="">
    <label>Description</label>
    <textarea 
        id="extra_description_${extraCount}" 
        class="form-control extra-description" 
        name="extra_description[]" 
        placeholder="Briefly explain what the customer gets, including duration, quantity, or any limits"
        rows="2"
    >${extra.description || ''}</textarea>
</div>

                <!-- Price, Quantity, and Remove Button -->
                <div class=" d-flex align-items-start">
                    <div class="input-group me-2">
                        <span class="input-group-text">£</span>
                        <input 
                            type="number" 
                            id="extra_price_${extraCount}" 
                            class="form-control extra-price" 
                            name="extra_price[]" 
                            placeholder="Price" 
                            value="${extra.price || ''}" 
                            min="0" 
                            step="0.01"
                        >
                    </div>

                    <div class="me-2">
                        <input 
                            type="number" 
                            id="extra_quantity_${extraCount}" 
                            class="form-control extra-quantity" 
                            name="extra_quantity[]" 
                            placeholder="Qty" 
                            value="${extra.quantity || ''}" 
                            min="0"
                        >
                        <span class="form-text text-muted" style="font-size: 0.85em;">Optional</span>
                    </div>

                    <button 
                        type="button" 
                        class="btn btn-danger remove-extra" 
                        data-first-row="${isFirstRow ? 'true' : 'false'}"
                    >
                        <span class="bi bi-x-lg"></span>
                    </button>
                </div>
            </div>
        </div>
    `;

    extrasContainer.insertAdjacentHTML('beforeend', extraHTML);
    attachRemoveListeners(); // Attach remove functionality
}


function attachRemoveListeners() {
    document.querySelectorAll('.remove-extra').forEach(button => {
        button.onclick = function () {
            const rowElement = this.closest('.optional-extra'); // Use .optional-extra instead of tr
            if (!rowElement) return; // Ensure the row exists before proceeding

            const isFirstRow = this.dataset.firstRow === 'true';
            const isLastRow = document.querySelectorAll('.optional-extra').length === 1;

            const extraNameInput = rowElement.querySelector('.extra-name');
            const extraDescriptionInput = rowElement.querySelector('.extra-description');
            const extraPriceInput = rowElement.querySelector('.extra-price');
            const extraQuantityInput = rowElement.querySelector('.extra-quantity');

            const extraName = extraNameInput?.value || '';

            if (isFirstRow || isLastRow) {
                // Clear fields for the first row or the last remaining row
                extraNameInput.value = '';
                extraDescriptionInput.value = '';
                extraPriceInput.value = '';
                extraQuantityInput.value = '';

                // Remove from session and local view data if a name exists
                if (extraName) {
                    fetch('/service/remove-optional-extra', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?= csrf_hash() ?>', // Include CSRF token
                        },
                        body: JSON.stringify({ extra_name: extraName })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Optional extra cleared from session.');
                            } else {
                                alert(data.error || 'Error clearing optional extra.');
                            }
                        })
                        .catch(error => console.error('Error:', error));

                    // Remove from local optionalExtrasData if applicable
                    if (typeof optionalExtrasData !== 'undefined') {
                        optionalExtrasData = optionalExtrasData.filter(
                            extra => extra.name !== extraName
                        );
                    }
                }
            } else {
                // Remove the row if it's not the first or last remaining row
                if (extraName) {
                    fetch('/service/remove-optional-extra', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?= csrf_hash() ?>', // Include CSRF token
                        },
                        body: JSON.stringify({ extra_name: extraName })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Optional extra removed from session.');
                                rowElement.remove();

                                // Update local count
                                extraCount--;

                                // Remove from local optionalExtrasData
                                if (typeof optionalExtrasData !== 'undefined') {
                                    optionalExtrasData = optionalExtrasData.filter(
                                        extra => extra.name !== extraName
                                    );
                                }
                            } else {
                                alert(data.error || 'Error removing optional extra.');
                            }
                        })
                        .catch(error => console.error('Error:', error));
                } else {
                    rowElement.remove();
                    extraCount--;
                }
            }
        };
    });
}






document.addEventListener('DOMContentLoaded', function () {
    const extrasContainer = document.getElementById('optionalExtrasContainer');
    const addExtraButton = document.getElementById('addOptionalExtra');
    const nextButton = document.querySelector('.next-btn[data-next="step8"]');
    let extraCount = 1;

    // Ensure the container exists before proceeding
    if (!extrasContainer) {
        console.error("The element with ID 'optionalExtrasContainer' is not found.");
        return;
    }

    // Populate extras from session data (step5Data)
    if (step5Data.length > 0) {
        step5Data.forEach((extra, index) => {
            const extraName = extra.name || '';
            const extraDescription = extra.description || '';
            const extraPrice = extra.price || '';
            addOptionalExtra({ name: extraName, description: extraDescription, price: extraPrice }, index === 0);
        });
    } else {
        // Add a default row if no session data exists
        addOptionalExtra({}, true);
    }

    // Add new optional extra row on button click
    if (addExtraButton) {
        addExtraButton.addEventListener('click', function () {
            addOptionalExtra({}, false);
        });
    }

    // Validate inputs on "Next" button click
    if (nextButton) {
        nextButton.addEventListener('click', function (e) {
            let valid = true;

            document.querySelectorAll('.optional-extra').forEach(row => {
                const nameInput = row.querySelector('.extra-name');
                const descInput = row.querySelector('.extra-description');
                const priceInput = row.querySelector('.extra-price');

                // Reset validation styles
                nameInput.classList.remove('is-invalid');
                descInput.classList.remove('is-invalid');
                priceInput.classList.remove('is-invalid');

                if (!nameInput.value.trim()) {
                    nameInput.classList.add('is-invalid');
                    valid = false;
                }
                if (!descInput.value.trim()) {
                    descInput.classList.add('is-invalid');
                    valid = false;
                }
                if (!priceInput.value || priceInput.value <= 0) {
                    priceInput.classList.add('is-invalid');
                    valid = false;
                }
            });

            if (!valid) {
                e.preventDefault();
                alert('Please correct the highlighted errors before proceeding.');
            }
        });
    }
});
