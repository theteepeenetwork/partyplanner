<div class="form-group guest-item">
    <div class="all-rows-container" id="hour_${hourCounter}">
        <div class="pitch-group">
            <div class="mb-2 d-flex align-items-center">

                <span class="input-group-text">Hours</span>
                <input type="number" class="form-control hour-number" name="hour_number[]" placeholder="Hours"
                    value="${hourValue}" min="1" step="1">
                <span class="input-group-text">£</span>
                <input type="number" class="form-control hour-price" name="hour_price[]" placeholder="Price (£)"
                    value="${priceValue}" min="0" step="0.01">
                ${!isFirstRow ? `<button type="button" class="btn btn-danger remove-hour remove-guest-range"
                    data-hour-id="${hourCounter}"><span class="bi bi-x-lg"></span></button>` : `<div
                    style="width: 60px;"></div>`}
            </div>
        </div>
    </div>
</div>

<div class="form-group pitch-group guest-item" id="guest_${guestCounter}">
    <label>Guest Range and Price:</label>
    <div class="all-rows-container">
        <div class="mb-2 d-flex align-items-center row-1">
            <span class="input-group-text">From</span>
            <input type="number" class="form-control min-guest" name="min_guest[]" placeholder="Min Guests"
                value="${minGuestValue}" min="0" required>
        </div>
        <div class="mb-2 d-flex align-items-center row-2">
            <span class="input-group-text">to</span>
            <input type="number" class="form-control max-guest" name="max_guest[]" placeholder="Max Guests"
                value="${minGuestValue + 1}" min="0" required>
        </div>
        <div class="mb-2 d-flex align-items-center row-3">
            <div class="input-group-prepend">
                <span class="input-group-text">£</span>

                <input type="number" class="form-control" name="guest_price[]" placeholder="Price per Guest (£)" min="0"
                    step="0.01" required>
                ${guestCounter > 1
                ? `<button type="button" class="btn btn-danger remove-guest-range"><span class="bi bi-x-lg"></button>`
                : `<div style="width: 60px;"></div>`
                }
            </div>
        </div>
    </div>
</div>

<div class="form-grou pitch-group guest-item" id="package_${packageCounter}">
    <label>Package Details:</label>
    <div class="all-rows-container">
        <!-- Package Name -->
        <div class="mb-2 d-flex align-items-center row-1">
            <input type="text" class="form-control min-guest" name="package_name[]"
                placeholder="Package Name (e.g., Standard)" value="${packageName}">
        </div>

        <!-- Package Description -->
        <div class="mb-2 d-flex align-items-center row-2">
            <input type="text" class="form-control" name="package_description[]"
                placeholder="Describe what’s included in this package" value="${packageDescription}">
        </div>

        <!-- Package Price -->
        <div class="mb-2 d-flex align-items-center row-3">
            <div class="input-group-prepend">
                <span class="input-group-text">£</span>
            </div>
            <input type="number" class="form-control package-price" name="package_price[]" placeholder="Price"
                value="${packagePrice}" min="0" step="0.01">
            <!-- Remove Button -->
            ${!isFirstRow ? `<button type="button" class="btn btn-danger remove-package ms-2"
                data-package-id="${packageCounter}"><span class="bi bi-x-lg"></button>` : `<div style="width: 60px;">
            </div>`}
        </div>
    </div>

</div>
</div>