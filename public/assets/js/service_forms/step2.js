/************************************************** */
/**********************Step 2********************** */
/************************************************** */



document.addEventListener('DOMContentLoaded', function () {
    const privateEventCheckbox = document.getElementById('event_private');
    const pricingTypeSection = document.getElementById('pricingTypeSection');

    // Show or hide the pricing type section based on checkbox state
    function togglePricingTypeSection() {
        if (privateEventCheckbox.checked) {
            pricingTypeSection.style.display = 'block';
        } else {
            pricingTypeSection.style.display = 'none';
            // Clear selected radio buttons when hiding the section
            document.querySelectorAll('input[name="pricing_type"]').forEach(radio => radio.checked = false);
        }
    }

    // Attach change event listener
    privateEventCheckbox.addEventListener('change', togglePricingTypeSection);

    // Initialize the visibility on page load
    togglePricingTypeSection();
});



document.addEventListener('DOMContentLoaded', () => {
    // Get the checkboxes and the "Next" button for Step 4
    const eventPublic = document.getElementById('event_public');
    const eventPrivate = document.getElementById('event_private');
    const eventCorporate = document.getElementById('event_corporate');
    const nextButton = document.getElementById('step2-next-btn');
    const step2Form = document.getElementById('step2Form');
    const eventTypeErrors = document.getElementById('eventTypeErrors');

    if (!nextButton || !eventPublic || !eventPrivate || !eventCorporate) {
        return;
    }

    // Function to enable or disable the "Next" button based on checkbox selection
    const toggleNextButton = () => {
        if (eventPublic.checked || eventPrivate.checked || eventCorporate.checked) {
            nextButton.disabled = false; // Enable the button if any checkbox is checked
        } else {
            nextButton.disabled = true; // Disable the button if no checkbox is selected
        }
    };

    // Clear the inline error once any event type is selected
    const clearEventTypeError = () => {
        if (eventTypeErrors && (eventPublic.checked || eventPrivate.checked || eventCorporate.checked)) {
            eventTypeErrors.classList.add('d-none');
            eventTypeErrors.innerHTML = '';
        }
    };

    // Listen for change events on the checkboxes only in Step 4
    document.querySelectorAll('.event-type').forEach(checkbox => {
        checkbox.addEventListener('change', toggleNextButton);
        checkbox.addEventListener('change', clearEventTypeError);
    });

    // Show an inline, accessible error instead of alert() if no event type is selected on submit
    if (step2Form && eventTypeErrors) {
        step2Form.addEventListener('submit', function (event) {
            if (!eventPublic.checked && !eventPrivate.checked && !eventCorporate.checked) {
                event.preventDefault();
                eventTypeErrors.innerHTML = '<p class="text-danger">Please select at least one event type before proceeding.</p>';
                eventTypeErrors.classList.remove('d-none');
            }
        });
    }

    // Initial check to disable/enable the Next button on page load for Step 4
    toggleNextButton();
});



document.addEventListener('DOMContentLoaded', function () {


    // Get the pricing type section and input fields
    const pricingTypeSection = document.getElementById('pricingTypeSection');
    const guestBasedPricing = document.getElementById('guest_based_pricing');
    const customDurationPricing = document.getElementById('custom_duration_pricing');
    const tieredPackagesPricing = document.getElementById('tiered_packages_pricing');
    const quantityBasedPricing = document.getElementById('quantity_based_pricing');

    // Determine if any pricing type is set in the session
    const pricingType = step3Data.pricing_type ?? step2Data.pricing_type ?? '';

    if (pricingType) {
        // Display the pricing type section
        pricingTypeSection.style.display = 'block';

        // Check the appropriate radio button based on the session value
        if (pricingType === 'guest_based_pricing' && guestBasedPricing) {
            guestBasedPricing.checked = true;
        } else if (pricingType === 'custom_duration_pricing' && customDurationPricing) {
            customDurationPricing.checked = true;
        } else if (pricingType === 'tiered_packages_pricing' && tieredPackagesPricing) {
            tieredPackagesPricing.checked = true;
        } else if (pricingType === 'quantity_based_pricing' && quantityBasedPricing) {
            quantityBasedPricing.checked = true;
        }
    }

    // Add event listeners to radio buttons to ensure the section stays visible when a pricing type is selected
    [guestBasedPricing, customDurationPricing, tieredPackagesPricing, quantityBasedPricing].forEach(function (radio) {
        if (radio) {
            radio.addEventListener('change', function () {
                pricingTypeSection.style.display = 'block';
            });
        }
    });
});
