function getCsrfToken() {
    const meta = document.querySelector('meta[name="X-CSRF-TOKEN"]');
    return meta ? meta.getAttribute('content') || '' : '';
}

const extrasContainer = document.getElementById('optionalExtrasContainer');
let extraCount = 0;

function addOptionalExtra(extra = {}, isFirstRow = false) {
    if (!extrasContainer) return;

    extraCount++;
    const pricingType = extra.pricing_type || 'flat';
    const isPerItem = pricingType === 'per_item';

    const extraHTML = `
        <div class="form-group optional-extra mb-4 p-3 border rounded bg-white" id="optional_extra_${extraCount}">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="mb-0 fw-semibold">Extra ${extraCount}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-extra" data-first-row="${isFirstRow ? 'true' : 'false'}">
                    Remove
                </button>
            </div>

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" class="form-control extra-name" name="extra_name[]"
                    placeholder="e.g. Custom engraving, Extra colour option, Gift wrapping"
                    value="${esc(extra.name || '')}">
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control extra-description" name="extra_description[]"
                    placeholder="What does the customer get? Include any limits or conditions."
                    rows="2">${esc(extra.description || '')}</textarea>
            </div>

            <div class="row g-3 align-items-start mb-2">
                <div class="col-md-4">
                    <label class="form-label">Pricing type</label>
                    <select class="form-select pricing-type-select" name="extra_pricing_type[]">
                        <option value="flat" ${!isPerItem ? 'selected' : ''}>Flat fee — one fixed price</option>
                        <option value="per_item" ${isPerItem ? 'selected' : ''}>Per item / per guest — price × quantity</option>
                    </select>
                    <div class="form-text flat-hint ${isPerItem ? 'd-none' : ''}">Charged once regardless of order size.</div>
                    <div class="form-text per-item-hint ${isPerItem ? '' : 'd-none'}">Customer chooses how many they want.</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Price</label>
                    <div class="input-group">
                        <span class="input-group-text">£</span>
                        <input type="number" class="form-control extra-price" name="extra_price[]"
                            placeholder="0.00" value="${extra.price || ''}" min="0" step="0.01">
                    </div>
                    <div class="form-text per-item-hint ${isPerItem ? '' : 'd-none'}">Price per unit</div>
                </div>

                <div class="col-md-5 per-item-fields ${isPerItem ? '' : 'd-none'}">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label">Unit label <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="text" class="form-control" name="extra_unit_label[]"
                                placeholder="e.g. per bag, per guest, each"
                                value="${esc(extra.unit_label || '')}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Min qty</label>
                            <input type="number" class="form-control" name="extra_min_quantity[]"
                                placeholder="e.g. 10" value="${extra.min_quantity || ''}" min="1" step="1">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Max qty</label>
                            <input type="number" class="form-control" name="extra_max_quantity[]"
                                placeholder="e.g. 500" value="${extra.max_quantity || ''}" min="1" step="1">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    extrasContainer.insertAdjacentHTML('beforeend', extraHTML);
    attachRowListeners(extrasContainer.lastElementChild);
}

function esc(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function attachRowListeners(row) {
    // Pricing type toggle
    const select = row.querySelector('.pricing-type-select');
    if (select) {
        select.addEventListener('change', function () {
            const isPerItem = this.value === 'per_item';
            row.querySelectorAll('.per-item-fields, .per-item-hint').forEach(el => el.classList.toggle('d-none', !isPerItem));
            row.querySelectorAll('.flat-hint').forEach(el => el.classList.toggle('d-none', isPerItem));
        });
    }

    // Remove button
    const removeBtn = row.querySelector('.remove-extra');
    if (!removeBtn) return;

    removeBtn.addEventListener('click', function () {
        const isFirstRow = this.dataset.firstRow === 'true';
        const isLastRow = document.querySelectorAll('.optional-extra').length === 1;
        const nameInput = row.querySelector('.extra-name');
        const extraName = nameInput?.value || '';

        if (isFirstRow || isLastRow) {
            // Clear fields rather than remove
            row.querySelectorAll('input[type="text"], input[type="number"], textarea').forEach(i => i.value = '');
            const sel = row.querySelector('.pricing-type-select');
            if (sel) sel.value = 'flat';
            row.querySelectorAll('.per-item-fields, .per-item-hint').forEach(el => el.classList.add('d-none'));
            row.querySelectorAll('.flat-hint').forEach(el => el.classList.remove('d-none'));
            if (extraName) removeFromSession(extraName);
        } else {
            if (extraName) {
                removeFromSession(extraName, () => {
                    row.remove();
                    extraCount--;
                });
            } else {
                row.remove();
                extraCount--;
            }
        }
    });
}

function removeFromSession(extraName, callback) {
    fetch('/service/remove-optional-extra', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({ extra_name: extraName })
    })
        .then(r => r.json())
        .then(data => {
            if (!data.success) console.warn('Could not remove extra from session:', data.error);
            if (callback) callback();
        })
        .catch(err => {
            console.error('Error removing extra from session:', err);
            if (callback) callback();
        });
}

document.addEventListener('DOMContentLoaded', function () {
    const addExtraButton = document.getElementById('addOptionalExtra');

    if (!extrasContainer) {
        console.error("Element 'optionalExtrasContainer' not found.");
        return;
    }

    if (Array.isArray(step5Data) && step5Data.length > 0) {
        step5Data.forEach((extra, index) => addOptionalExtra(extra, index === 0));
    } else {
        addOptionalExtra({}, true);
    }

    if (addExtraButton) {
        addExtraButton.addEventListener('click', () => addOptionalExtra({}, false));
    }
});
