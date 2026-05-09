let map, marker, freeCircle, paidCircle, autocomplete;
const defaultLatLng = { lat: 54.9784, lng: -1.6174 }; // Example: Newcastle upon Tyne
function initMap() {
    // Default latitude and longitude
    const persistedLat = parseFloat(step4Data.latitude ?? 54.9784);
    const persistedLng = parseFloat(step4Data.longitude ?? -1.6174);
    const persistedLocation = step4Data.service_location ?? '';
    const freeCoverage = parseInt(step4Data.free_coverage_radius ?? 10) * 1000; // Default 10 km
    const paidCoverage = parseInt(step4Data.paid_coverage_radius ?? 15) * 1000; // Default 15 km

    const defaultLatLng = { lat: persistedLat, lng: persistedLng };

    // Initialize the map
    map = new google.maps.Map(document.getElementById('map'), {
        center: defaultLatLng,
        zoom: 10,
    });

    // Add a draggable marker
    marker = new google.maps.Marker({
        position: defaultLatLng,
        map: map,
        draggable: false,
    });

    // Add free coverage circle
    freeCircle = new google.maps.Circle({
        map: map,
        radius: freeCoverage,
        fillColor: '#FF6600',
        fillOpacity: 0.2,
        strokeColor: '#FF6600',
        strokeOpacity: 0.8,
        strokeWeight: 2,
    });
    freeCircle.bindTo('center', marker, 'position');

    // Add paid coverage circle
    paidCircle = new google.maps.Circle({
        map: map,
        radius: paidCoverage,
        fillColor: '#0000FF',
        fillOpacity: 0.1,
        strokeColor: '#0000FF',
        strokeOpacity: 0.6,
        strokeWeight: 2,
    });
    paidCircle.bindTo('center', marker, 'position');

    // Populate the service location input
    document.getElementById('service_location').value = persistedLocation;

    // Autocomplete for the location input
    autocomplete = new google.maps.places.Autocomplete(document.getElementById('service_location'), {
        types: ['geocode'],
    });

    // Handle place selection
    autocomplete.addListener('place_changed', function () {
        const place = autocomplete.getPlace();
        if (!place.geometry) {
            alert(`No details available for input: '${place.name}'`);
            return;
        }

        const location = place.geometry.location;
        marker.setPosition(location);
        map.setCenter(location);

        document.getElementById('latitude').value = location.lat();
        document.getElementById('longitude').value = location.lng();
        updateSummary();
    });

    // Update latitude and longitude on marker drag
    google.maps.event.addListener(marker, 'dragend', function () {
        document.getElementById('latitude').value = marker.getPosition().lat();
        document.getElementById('longitude').value = marker.getPosition().lng();
    });



    // Initialize hidden fields
    document.getElementById('latitude').value = persistedLat;
    document.getElementById('longitude').value = persistedLng;
}


// Load Google Maps and Places API script dynamically
function loadScript() {
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=AIzaSyDqhlzmqyFt4e3NxYkuS-wyha7l-oS-nLI&libraries=places&callback=initMap`;
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
}

window.onload = loadScript;


document.addEventListener("DOMContentLoaded", function () {
    // Elements
    const allTravelIncludedCheckbox = document.getElementById("all_travel_included");
    const noTravelLimitCheckbox = document.getElementById("no_travel_limit");
    const freeCoverageRadiusInput = document.getElementById("free_coverage_radius");
    const paidCoverageRadiusInput = document.getElementById("paid_coverage_radius");
    const travelFeePerKmInput = document.getElementById("travel_fee_per_km");
    const radiusWarning = document.createElement("p");

    // Add warning message to the form
    radiusWarning.classList.add("text-danger", "mt-2");
    radiusWarning.style.display = "none"; // Hidden by default
    paidCoverageRadiusInput.parentElement.appendChild(radiusWarning);

    // Function to update placeholders, disable/enable fields, and adjust the map
    function updateFormFields() {
        const allTravelIncluded = allTravelIncludedCheckbox.checked;
        const noTravelLimit = noTravelLimitCheckbox.checked;

        if (allTravelIncluded && noTravelLimit) {
            freeCoverageRadiusInput.disabled = true;
            freeCoverageRadiusInput.value = '';
            freeCoverageRadiusInput.placeholder = "National coverage";

            paidCoverageRadiusInput.disabled = true;
            paidCoverageRadiusInput.value = '';
            paidCoverageRadiusInput.placeholder = "Travel will incur no extra fees for the customer";

            travelFeePerKmInput.disabled = true;
            travelFeePerKmInput.value = '';
            travelFeePerKmInput.placeholder = "Travel will incur no extra fees for the customer";

            freeCircle.setRadius(1000000); // National coverage
            paidCircle.setRadius(0); // No paid coverage
            radiusWarning.style.display = "none"; // Hide warning
        } else if (allTravelIncluded) {
            freeCoverageRadiusInput.disabled = false;
            freeCoverageRadiusInput.placeholder = `Enter free coverage radius in km (Current: ${freeCoverageRadiusInput.value || 10} km)`;

            paidCoverageRadiusInput.disabled = true;
            paidCoverageRadiusInput.value = '';
            paidCoverageRadiusInput.placeholder = "Travel will incur no extra fees for the customer";

            travelFeePerKmInput.disabled = true;
            travelFeePerKmInput.value = '';
            travelFeePerKmInput.placeholder = "Travel will incur no extra fees for the customer";

            freeCircle.setRadius(parseInt(freeCoverageRadiusInput.value || 10) * 1000);
            paidCircle.setRadius(0);
            radiusWarning.style.display = "none"; // Hide warning
        } else if (noTravelLimit) {
            freeCoverageRadiusInput.disabled = false;
            freeCoverageRadiusInput.placeholder = `Enter free coverage radius in km (Current: ${freeCoverageRadiusInput.value || 10} km)`;

            paidCoverageRadiusInput.disabled = true;
            paidCoverageRadiusInput.value = '';
            paidCoverageRadiusInput.placeholder = "National coverage includes England, Scotland and Wales";

            travelFeePerKmInput.disabled = false;
            //travelFeePerKmInput.value = '';
            travelFeePerKmInput.placeholder = "Cost per km beyond free coverage";

            freeCircle.setRadius(parseInt(freeCoverageRadiusInput.value || 10) * 1000);
            paidCircle.setRadius(1000000);
            radiusWarning.style.display = "none"; // Hide warning
        } else {
            freeCoverageRadiusInput.disabled = false;
            freeCoverageRadiusInput.placeholder = `Enter free coverage radius in km (Current: ${freeCoverageRadiusInput.value || 10} km)`;
            freeCoverageRadiusInput.value = freeCoverageRadiusInput.value || 10;

            paidCoverageRadiusInput.disabled = false;
            paidCoverageRadiusInput.placeholder = `Enter additional paid coverage radius in km (Current: ${paidCoverageRadiusInput.value || 5} km)`;
            paidCoverageRadiusInput.value = paidCoverageRadiusInput.value || 5;

            travelFeePerKmInput.disabled = false;
            travelFeePerKmInput.placeholder = `Enter travel fee per km (Current: £${travelFeePerKmInput.value || 1})`;
            travelFeePerKmInput.value = travelFeePerKmInput.value || 1;

            const freeRadius = parseInt(freeCoverageRadiusInput.value || 0) * 1000;
            let additionalPaidRadius = parseInt(paidCoverageRadiusInput.value || 0);

            // Enforce a minimum value of 1 for paid coverage
            if (additionalPaidRadius < 1) {
                additionalPaidRadius = 1; // Set minimum value
                paidCoverageRadiusInput.value = additionalPaidRadius; // Update the input
            }

            const totalPaidRadius = freeRadius + additionalPaidRadius * 1000;

            // Show warning if additional paid coverage is invalid
            if (additionalPaidRadius < 1) {
                radiusWarning.textContent = "Additional paid coverage radius must be at least 1 km.";
                radiusWarning.style.display = "block";
            } else {
                radiusWarning.style.display = "none"; // Hide warning if valid
            }

            freeCircle.setRadius(freeRadius);
            paidCircle.setRadius(totalPaidRadius);
        }
    }

    // Event listeners for checkboxes
    allTravelIncludedCheckbox.addEventListener("change", updateFormFields);
    noTravelLimitCheckbox.addEventListener("change", updateFormFields);

    // Event listeners for radius input changes
    freeCoverageRadiusInput.addEventListener("input", function () {
        if (!freeCoverageRadiusInput.disabled) {
            freeCoverageRadiusInput.placeholder = `Enter free coverage radius in km (Current: ${this.value || 10} km)`;
            updateFormFields();
        }
    });
    paidCoverageRadiusInput.addEventListener("input", function () {
        if (!paidCoverageRadiusInput.disabled) {
            paidCoverageRadiusInput.placeholder = `Enter additional paid coverage radius in km (Current: ${this.value || 5} km)`;
            updateFormFields();
        }
    });
    travelFeePerKmInput.addEventListener("input", function () {
        if (!travelFeePerKmInput.disabled) {
            travelFeePerKmInput.placeholder = `Enter travel fee per km (Current: £${this.value || 1})`;
        }
    });

    // Initial form update
    updateFormFields();
});











document.addEventListener('DOMContentLoaded', function () {
    generateTravelSummary($step4_data);
    updateSummary();
});

//Summary

const locationInput = document.getElementById('service_location');
const allTravelIncludedCheckbox = document.getElementById('all_travel_included');
const noTravelLimitCheckbox = document.getElementById('no_travel_limit');
const freeCoverageRadiusInput = document.getElementById('free_coverage_radius');
const paidCoverageRadiusInput = document.getElementById('paid_coverage_radius');
const travelFeePerKmInput = document.getElementById('travel_fee_per_km');
const summaryElement = document.createElement('p');

// Add summary element to the form
summaryElement.classList.add('form-text', 'text-muted');
const travelCoverageSection = document.getElementById('travel-coverage');
travelCoverageSection.appendChild(summaryElement);

// Function to update the summary


// Event listeners
locationInput.addEventListener('input', updateSummary);
allTravelIncludedCheckbox.addEventListener('change', updateSummary);
noTravelLimitCheckbox.addEventListener('change', updateSummary);
freeCoverageRadiusInput.addEventListener('input', updateSummary);
paidCoverageRadiusInput.addEventListener('input', updateSummary);
travelFeePerKmInput.addEventListener('input', updateSummary);

function updateSummary() {
    const location = locationInput.value.trim();
    const freeRadius = freeCoverageRadiusInput.value;
    const paidRadius = paidCoverageRadiusInput.value;
    const travelFee = travelFeePerKmInput.value;

    let summary = '';

    if (allTravelIncludedCheckbox.checked && noTravelLimitCheckbox.checked) {
        summary = `All travel fees are included from ${location || 'your location'} and cover all of Scotland, England, and Wales.`;
    } else if (allTravelIncludedCheckbox.checked) {
        summary = `All travel fees are included from ${location || 'your location'} and will travel up to ${freeRadius} km.`;
    } else if (noTravelLimitCheckbox.checked) {
        if (freeRadius) {
            summary = `Your service covers all of Scotland, England, and Wales from ${location || 'your location'}. You will travel up to ${freeRadius} km included in the price and charge £${travelFee || 0} per km beyond that.`;
        } else {
            summary = `Your service covers all of Scotland, England, and Wales from ${location || 'your location'}. You will travel nationally and charge £${travelFee || 0} per km.`;
        }
    } else {
        // No checkboxes selected
        if (freeRadius && paidRadius && travelFee) {
            summary = `You will travel up to ${freeRadius} km from ${location || 'your specified location'} at no extra cost. Beyond this, you can travel an additional ${paidRadius} km for £${travelFee} per km, totaling up to ${parseInt(freeRadius) + parseInt(paidRadius)} km.`;

        } else if (freeRadius) {
            summary = `You will travel up to ${freeRadius} km from ${location || 'your location'} included in the price.`;
        } else if (paidRadius && travelFee) {
            summary = `You will travel up to ${paidRadius} km from ${location || 'your location'} at £${travelFee} per km.`;
        } else {
            summary = 'Please fill in your travel details to generate a summary.';
        }
    }

    if (!summary) {
        summary = 'Please fill in your travel details to generate a summary.';
    }

    function updateLocationSummary(summary) {
        const locationSummaryDiv = document.getElementById('locationSummary');
        if (locationSummaryDiv) {
            locationSummaryDiv.innerHTML = summary; // Update the content of the div
        }
    }

    // Example usage
    const travelSummary = "Your service covers all of Scotland, England, and Wales from Darlington, UK. You will travel up to 40 km included in the price and charge £2 per km beyond that.";
    updateLocationSummary(travelSummary);




    summaryElement.textContent = summary;
}






// Initialize the Google Places Autocomplete

// When the user selects a place, update the input and regenerate the summar


//Persistence


document.addEventListener('DOMContentLoaded', () => {
    // Elements
    const allTravelIncludedCheckbox = document.getElementById('all_travel_included');
    const noTravelLimitCheckbox = document.getElementById('no_travel_limit');
    const freeCoverageRadiusInput = document.getElementById('free_coverage_radius');
    const paidCoverageRadiusInput = document.getElementById('paid_coverage_radius');
    const travelFeePerKmInput = document.getElementById('travel_fee_per_km');

    // Function to update fields based on selections
    function updateFieldStates() {
        const allTravelIncluded = allTravelIncludedCheckbox.checked;
        const noTravelLimit = noTravelLimitCheckbox.checked;

        if (allTravelIncluded && noTravelLimit) {
            // Grey out all fields
            freeCoverageRadiusInput.disabled = true;
            freeCoverageRadiusInput.value = '';
            paidCoverageRadiusInput.disabled = true;
            paidCoverageRadiusInput.value = '';
            travelFeePerKmInput.disabled = true;
            travelFeePerKmInput.value = '';
        } else if (allTravelIncluded) {
            // Grey out paid coverage and travel fee fields
            freeCoverageRadiusInput.disabled = false;
            paidCoverageRadiusInput.disabled = true;
            paidCoverageRadiusInput.value = '';
            travelFeePerKmInput.disabled = true;
            travelFeePerKmInput.value = '';
        } else if (noTravelLimit) {
            // Grey out paid coverage but keep free radius and travel fee enabled
            freeCoverageRadiusInput.disabled = false;
            paidCoverageRadiusInput.disabled = true;
            paidCoverageRadiusInput.value = '';
            travelFeePerKmInput.disabled = false;
        } else {
            // Enable all fields
            freeCoverageRadiusInput.disabled = false;
            paidCoverageRadiusInput.disabled = false;
            travelFeePerKmInput.disabled = false;
        }
    }

    // Load data from step4Data if available
    if (step4Data) {
        if (step4Data.all_travel_included) {
            allTravelIncludedCheckbox.checked = true;
        }
        if (step4Data.no_travel_limit) {
            noTravelLimitCheckbox.checked = true;
        }
        if (step4Data.free_coverage_radius) {
            freeCoverageRadiusInput.value = step4Data.free_coverage_radius;
        }
        if (step4Data.paid_coverage_radius) {
            paidCoverageRadiusInput.value = step4Data.paid_coverage_radius;
        }
        if (step4Data.travel_fee_per_km) {
            travelFeePerKmInput.value = step4Data.travel_fee_per_km;
        }
    }

    // Update field states based on initial data
    updateFieldStates();

    // Add event listeners to checkboxes
    allTravelIncludedCheckbox.addEventListener('change', updateFieldStates);
    noTravelLimitCheckbox.addEventListener('change', updateFieldStates);
});


