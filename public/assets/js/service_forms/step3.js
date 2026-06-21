/**
 * step3.js (completed)
 * - Uses <template> cloning for dynamic rows (no PHP in JS)
 * - Normalises eventTypes once and reuses everywhere
 * - Initialises rows from session (step3Data)
 * - Single submit handler runs all validations (guests, hours/days, attendance, corporate)
 * - Corporate section is capability + modifiers (no duplicate pricing tables)
 */

let eventTypes = [];

// Counts (session)
const numberOfHours = step3Data?.hour_number ? step3Data.hour_number.length : 0;
const numberOfDays = step3Data?.day_number ? step3Data.day_number.length : 0;
const numberOfGuests = step3Data?.min_guest ? step3Data.min_guest.length : 0;
const numberOfPackages = step3Data?.package_name ? step3Data.package_name.length : 0;
const numberOfPitchFees = step3Data?.min_attendance ? step3Data.min_attendance.length : 0;

function cloneTemplate(id) {
    const tpl = document.getElementById(id);
    return tpl ? tpl.content.firstElementChild.cloneNode(true) : null;
}

/* ---------------- Public ---------------- */

function addCommission(commission = null) {
    const commissionContainer = document.getElementById('commissionContainer');
    if (!commissionContainer) return;

    // Show it (rather than injecting)
    commissionContainer.style.display = 'block';

    // Set value if provided
    const input = commissionContainer.querySelector('#commission_percentage');
    if (input && commission !== null && commission !== undefined) {
        input.value = commission;
    }
}



function addPitchFee(pitch = {}) {
    const container = document.getElementById('pitchFeeContainer');
    if (!container) return;

    const node = cloneTemplate('pitchFeeTemplate');
    if (!node) return;

    node.querySelector('input[name="min_attendance[]"]').value = pitch.min_attendance ?? '';
    node.querySelector('input[name="max_attendance[]"]').value = pitch.max_attendance ?? '';
    node.querySelector('input[name="max_pitch_fee[]"]').value = pitch.max_pitch_fee ?? '';

    container.appendChild(node);
}

/* ---------------- Private: guest pricing ---------------- */

function addGuestPricingRow(guest = {}) {
    const list = document.getElementById('guestPricingList');
    if (!list) return;

    const node = cloneTemplate('guestRowTemplate');
    if (!node) return;

    node.querySelector('input[name="min_guest[]"]').value = guest.min_guest ?? '';
    node.querySelector('input[name="max_guest[]"]').value = guest.max_guest ?? '';
    node.querySelector('input[name="guest_price[]"]').value = guest.guest_price ?? '';

    list.appendChild(node);
}

/* ---------------- Private: duration pricing ---------------- */

function addHourRow(hourValue = 1, priceValue = '') {
    const list = document.getElementById('hoursList');
    if (!list) return;

    const node = cloneTemplate('hourRowTemplate');
    if (!node) return;

    node.querySelector('input[name="hour_number[]"]').value = hourValue;
    node.querySelector('input[name="hour_price[]"]').value = priceValue;

    list.appendChild(node);
}

function addDayRow(dayValue = 1, priceValue = '') {
    const list = document.getElementById('daysList');
    if (!list) return;

    const node = cloneTemplate('dayRowTemplate');
    if (!node) return;

    node.querySelector('input[name="day_number[]"]').value = dayValue;
    node.querySelector('input[name="day_price[]"]').value = priceValue;

    list.appendChild(node);
}

/* ---------------- Private: tiered packages ---------------- */

function addPackageRow(packageData = {}) {
    const list = document.getElementById('tieredPackageList');
    if (!list) return;

    const node = cloneTemplate('packageRowTemplate');
    if (!node) return;

    node.querySelector('input[name="package_name[]"]').value = packageData.package_name ?? '';
    node.querySelector('input[name="package_description[]"]').value = packageData.package_description ?? '';
    node.querySelector('input[name="package_price[]"]').value = packageData.package_price ?? '';

    list.appendChild(node);
}

/* ---------------- Normalise event types + show relevant sections ---------------- */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('publicEventForm');
    if (!form) return;

    const raw = step2Data?.event_types ?? [];
    eventTypes = Array.isArray(raw)
        ? raw.map(t => t.trim()).filter(Boolean)
        : String(raw).split(',').map(t => t.trim()).filter(Boolean);

    const eventForms = {
        public: 'public-event-form',
        private: 'private-event-form',
        corporate: 'corporate-event-form',
    };

    // Hide all initially
    Object.values(eventForms).forEach(formId => {
        const el = document.getElementById(formId);
        if (el) el.style.display = 'none';
    });

    // Show selected
    eventTypes.forEach(t => {
        const id = eventForms[t];
        if (id) {
            const el = document.getElementById(id);
            if (el) el.style.display = 'block';
        }
    });
});

/* ---------------- Initialise rows + button handlers + remove handlers ---------------- */

document.addEventListener('DOMContentLoaded', () => {
    // PUBLIC initialisation
    if (eventTypes.includes('public')) {
        if (numberOfPitchFees > 0) {
            step3Data.min_attendance.forEach((min, index) => {
                addPitchFee({
                    min_attendance: min,
                    max_attendance: step3Data.max_attendance?.[index] ?? '',
                    max_pitch_fee: step3Data.max_pitch_fee?.[index] ?? '',
                });
            });
        } else {
            addPitchFee();
        }
        addCommission(step3Data.commission_percentage ?? null);
    }

    // PRIVATE initialisation
    if (eventTypes.includes('private') && step2Data.pricing_type === 'guest_based_pricing') {
        if (numberOfGuests > 0) {
            step3Data.min_guest.forEach((minGuest, index) => {
                addGuestPricingRow({
                    min_guest: minGuest,
                    max_guest: step3Data.max_guest?.[index] ?? '',
                    guest_price: step3Data.guest_price?.[index] ?? '',
                });
            });
        } else {
            addGuestPricingRow();
        }
    }

    if (eventTypes.includes('private') && step2Data.pricing_type === 'custom_duration_pricing') {
        if (numberOfHours > 0) {
            step3Data.hour_number.forEach((hour, index) => {
                addHourRow(hour, step3Data.hour_price?.[index] ?? '');
            });
        } else {
            addHourRow();
        }

        if (numberOfDays > 0) {
            step3Data.day_number.forEach((day, index) => {
                addDayRow(day, step3Data.day_price?.[index] ?? '');
            });
        } else {
            addDayRow();
        }
    }

    if (eventTypes.includes('private') && step2Data.pricing_type === 'tiered_packages_pricing') {
        if (numberOfPackages > 0) {
            step3Data.package_name.forEach((name, index) => {
                addPackageRow({
                    package_name: name,
                    package_description: step3Data.package_description?.[index] ?? '',
                    package_price: step3Data.package_price?.[index] ?? '',
                });
            });
        } else {
            addPackageRow();
        }
    }

    // Add-button handlers
    document.getElementById('addPitchFee')?.addEventListener('click', () => addPitchFee());
    document.getElementById('addGuestPricing')?.addEventListener('click', () => addGuestPricingRow());
    document.getElementById('addHourRow')?.addEventListener('click', () => addHourRow());
    document.getElementById('addDayRow')?.addEventListener('click', () => addDayRow());
    document.getElementById('addPackage')?.addEventListener('click', () => addPackageRow());

    // Delegated remove handlers
    document.addEventListener('click', (e) => {
        if (e.target.closest('.remove-pitch-fee')) e.target.closest('.pitch-group')?.remove();
        if (e.target.closest('.remove-guest-range')) e.target.closest('.pitch-group')?.remove();
        if (e.target.closest('.remove-hour')) e.target.closest('.form-group')?.remove();
        if (e.target.closest('.remove-day')) e.target.closest('.form-group')?.remove();
        if (e.target.closest('.remove-package')) e.target.closest('.pitch-group')?.remove();
    });
});

/* ---------------- Duration toggle logic (fixed for template rows) ---------------- */

document.addEventListener('DOMContentLoaded', () => {
    const enableHoursCheckbox = document.getElementById('enableHours');
    const enableDaysCheckbox = document.getElementById('enableDays');
    const hoursSection = document.getElementById('hoursSection');
    const daysSection = document.getElementById('daysSection');
    const addHourButton = document.getElementById('addHourRow');
    const addDayButton = document.getElementById('addDayRow');

    // Only applicable when the duration UI exists
    if (!enableHoursCheckbox || !enableDaysCheckbox || !hoursSection || !daysSection) return;

    const hoursList = document.getElementById('hoursList');
    const daysList = document.getElementById('daysList');

    function pruneListToOne(listEl) {
        if (!listEl) return;
        while (listEl.children.length > 1) {
            listEl.removeChild(listEl.lastElementChild);
        }
    }

    function toggleSection(checkbox, section, listEl, addButton) {
        const isEnabled = checkbox.checked;
        const inputs = section.querySelectorAll('input');

        if (isEnabled) {
            inputs.forEach(input => {
                input.removeAttribute('disabled');
                input.setAttribute('required', 'required');
            });
            section.style.display = 'block';
            addButton?.removeAttribute('disabled');
        } else {
            inputs.forEach(input => {
                input.setAttribute('disabled', 'disabled');
                input.removeAttribute('required');
            });
            addButton?.setAttribute('disabled', 'disabled');

            // keep only one row (template-based)
            pruneListToOne(listEl);
        }
    }

    function enforceAtLeastOne(event) {
        if (!enableHoursCheckbox.checked && !enableDaysCheckbox.checked) {
            if (event.target === enableHoursCheckbox) {
                enableDaysCheckbox.checked = true;
            } else {
                enableHoursCheckbox.checked = true;
            }
        }
    }

    // Prefill from session
    enableHoursCheckbox.checked = !!step3Data.enableHours;
    enableDaysCheckbox.checked = !!step3Data.enableDays;

    // Apply initial
    toggleSection(enableHoursCheckbox, hoursSection, hoursList, addHourButton);
    toggleSection(enableDaysCheckbox, daysSection, daysList, addDayButton);

    enableHoursCheckbox.addEventListener('change', (event) => {
        enforceAtLeastOne(event);
        toggleSection(enableHoursCheckbox, hoursSection, hoursList, addHourButton);
        toggleSection(enableDaysCheckbox, daysSection, daysList, addDayButton);
    });

    enableDaysCheckbox.addEventListener('change', (event) => {
        enforceAtLeastOne(event);
        toggleSection(enableHoursCheckbox, hoursSection, hoursList, addHourButton);
        toggleSection(enableDaysCheckbox, daysSection, daysList, addDayButton);
    });
});

/* ---------------- Corporate (capability + modifiers) ---------------- */

document.addEventListener('DOMContentLoaded', () => {
    if (!eventTypes.includes('corporate')) return;

    const enabled = document.getElementById('corporate_enabled');
    const billingSection = document.getElementById('corporateBillingSection');
    const complianceSection = document.getElementById('corporateComplianceSection');
    const modifiersSection = document.getElementById('corporateModifiersSection');

    // If markup isn't present yet, do nothing safely
    if (!enabled) return;

    const vatRegistered = document.getElementById('corporate_vat_registered');
    const vatWrap = document.getElementById('corporate_vat_number_wrap');
    const vatNumber = document.getElementById('corporate_vat_number');

    const surchargeType = document.getElementById('corporate_surcharge_type');
    const surchargeWrap = document.getElementById('corporate_surcharge_value_wrap');
    const surchargeValue = document.getElementById('corporate_surcharge_value');

    const invoiceSupported = document.getElementById('corporate_invoice_supported');
    const accountsEmail = document.getElementById('corporate_accounts_email');

    const billingErrors = document.getElementById('corporateErrorsBilling');
    const modifiersErrors = document.getElementById('corporateErrorsModifiers');

    function setVisible(el, visible) {
        if (!el) return;
        el.style.display = visible ? 'block' : 'none';
        el.querySelectorAll('input, select, textarea').forEach(i => {
            if (visible) i.removeAttribute('disabled');
            else i.setAttribute('disabled', 'disabled');
        });
    }

    function updateVatUI() {
        if (!vatRegistered || !vatWrap || !vatNumber) return;
        const show = vatRegistered.checked;
        vatWrap.style.display = show ? 'block' : 'none';
        if (!show) vatNumber.value = '';
    }

    function updateSurchargeUI() {
        if (!surchargeType || !surchargeWrap || !surchargeValue) return;
        const show = surchargeType.value !== 'none';
        surchargeWrap.style.display = show ? 'block' : 'none';
        if (!show) surchargeValue.value = '';
    }

    function updateCorporateUI() {
        const on = !!enabled.checked;
        setVisible(billingSection, on);
        setVisible(complianceSection, on);
        setVisible(modifiersSection, on);
        updateVatUI();
        updateSurchargeUI();
    }

    // Prefill from session (flat keys)
    enabled.checked = !!step3Data.corporate_enabled;

    document.getElementById('corporate_invoice_supported') && (document.getElementById('corporate_invoice_supported').checked = !!step3Data.corporate_invoice_supported);
    document.getElementById('corporate_po_supported') && (document.getElementById('corporate_po_supported').checked = !!step3Data.corporate_po_supported);

    if (accountsEmail && step3Data.corporate_accounts_email) accountsEmail.value = step3Data.corporate_accounts_email;

    if (vatRegistered) vatRegistered.checked = !!step3Data.corporate_vat_registered;
    if (vatNumber && step3Data.corporate_vat_number) vatNumber.value = step3Data.corporate_vat_number;

    const pli = document.getElementById('corporate_pli_level');
    if (pli && step3Data.corporate_pli_level) pli.value = step3Data.corporate_pli_level;

    const ra = document.getElementById('corporate_risk_assessment');
    if (ra) ra.checked = !!step3Data.corporate_risk_assessment;

    const ms = document.getElementById('corporate_method_statement');
    if (ms) ms.checked = !!step3Data.corporate_method_statement;

    const pat = document.getElementById('corporate_pat_testing');
    if (pat && step3Data.corporate_pat_testing) pat.value = step3Data.corporate_pat_testing;

    const dbs = document.getElementById('corporate_dbs');
    if (dbs && step3Data.corporate_dbs) dbs.value = step3Data.corporate_dbs;

    if (surchargeType && step3Data.corporate_surcharge_type) surchargeType.value = step3Data.corporate_surcharge_type;
    if (surchargeValue && step3Data.corporate_surcharge_value) surchargeValue.value = step3Data.corporate_surcharge_value;

    const invoiceFee = document.getElementById('corporate_invoice_fee');
    if (invoiceFee && step3Data.corporate_invoice_fee) invoiceFee.value = step3Data.corporate_invoice_fee;

    const minSpend = document.getElementById('corporate_min_spend');
    if (minSpend && step3Data.corporate_min_spend) minSpend.value = step3Data.corporate_min_spend;

    // Payment terms checkboxes
    const terms = step3Data.corporate_payment_terms ?? [];
    if (Array.isArray(terms)) {
        document.querySelectorAll('input[name="corporate_payment_terms[]"]').forEach(cb => {
            cb.checked = terms.includes(cb.value);
        });
    }

    enabled.addEventListener('change', updateCorporateUI);
    vatRegistered?.addEventListener('change', updateVatUI);
    surchargeType?.addEventListener('change', updateSurchargeUI);

    updateCorporateUI();

    // Expose a validator function for the main submit handler
    window.__validateCorporate = function () {
        if (!enabled.checked) return true;

        if (billingErrors) billingErrors.innerHTML = '';
        if (modifiersErrors) modifiersErrors.innerHTML = '';

        let ok = true;

        if (invoiceSupported?.checked && !(accountsEmail?.value || '').trim()) {
            ok = false;
            billingErrors.innerHTML += `<p class="text-danger">Accounts payable email is required if you support invoicing.</p>`;
        }

        if (vatRegistered?.checked && !(vatNumber?.value || '').trim()) {
            ok = false;
            billingErrors.innerHTML += `<p class="text-danger">VAT number is required if VAT registered.</p>`;
        }

        if (surchargeType?.value && surchargeType.value !== 'none') {
            const n = parseFloat(surchargeValue?.value || '');
            if (!Number.isFinite(n) || n <= 0) {
                ok = false;
                modifiersErrors.innerHTML += `<p class="text-danger">Enter a valid corporate surcharge value.</p>`;
            }
            if (surchargeType.value === 'percent' && n > 100) {
                ok = false;
                modifiersErrors.innerHTML += `<p class="text-danger">Corporate surcharge percentage cannot exceed 100%.</p>`;
            }
        }

        return ok;
    };
});

/* ---------------- Validations (single submit handler) ---------------- */

function validateGuestRanges() {
    const errorContainer = document.getElementById('guestPricingErrors');
    const minInputs = document.querySelectorAll('input[name="min_guest[]"]');
    const maxInputs = document.querySelectorAll('input[name="max_guest[]"]');

    // No guest pricing on page
    if (!errorContainer || minInputs.length === 0 || maxInputs.length === 0) return true;

    errorContainer.innerHTML = '';
    const minGuests = Array.from(minInputs).map(i => parseInt(i.value, 10) || 0);
    const maxGuests = Array.from(maxInputs).map(i => parseInt(i.value, 10) || 0);

    let ok = true;

    // Overlap check
    for (let i = 0; i < minGuests.length; i++) {
        for (let j = i + 1; j < minGuests.length; j++) {
            const overlap =
                (minGuests[i] <= maxGuests[j] && maxGuests[i] >= minGuests[j]) ||
                (minGuests[j] <= maxGuests[i] && maxGuests[j] >= minGuests[i]);

            if (overlap) {
                ok = false;
                errorContainer.innerHTML += `<p class="text-danger">Overlap detected between ranges ${minGuests[i]}-${maxGuests[i]} and ${minGuests[j]}-${maxGuests[j]}.</p>`;
            }
        }
    }

    return ok;
}

function validateHourDayDuplicates() {
    const hourErrorContainer = document.getElementById('hourPricingErrors');
    const dayErrorContainer = document.getElementById('dayPricingErrors');

    const hourInputs = document.querySelectorAll('input[name="hour_number[]"]');
    const dayInputs = document.querySelectorAll('input[name="day_number[]"]');

    if (hourErrorContainer) hourErrorContainer.innerHTML = '';
    if (dayErrorContainer) dayErrorContainer.innerHTML = '';

    // If neither exists, nothing to validate
    if (hourInputs.length === 0 && dayInputs.length === 0) return true;

    let ok = true;

    const hours = Array.from(hourInputs).map(i => parseInt(i.value, 10) || 0).filter(n => n > 0);
    const days = Array.from(dayInputs).map(i => parseInt(i.value, 10) || 0).filter(n => n > 0);

    // duplicates in hours
    const seenH = new Set();
    for (const h of hours) {
        if (seenH.has(h)) {
            ok = false;
            if (hourErrorContainer) hourErrorContainer.innerHTML += `<p class="text-danger">Duplicate hour value: ${h}.</p>`;
        }
        seenH.add(h);
    }

    // duplicates in days
    const seenD = new Set();
    for (const d of days) {
        if (seenD.has(d)) {
            ok = false;
            if (dayErrorContainer) dayErrorContainer.innerHTML += `<p class="text-danger">Duplicate day value: ${d}.</p>`;
        }
        seenD.add(d);
    }

    return ok;
}

function validateAttendanceRanges() {
    const errorContainer = document.getElementById('attendancePricingErrors');
    const minInputs = document.querySelectorAll('input[name="min_attendance[]"]');
    const maxInputs = document.querySelectorAll('input[name="max_attendance[]"]');
    const feeInputs = document.querySelectorAll('input[name="max_pitch_fee[]"]');

    // No attendance pricing on page
    if (!errorContainer || minInputs.length === 0 || maxInputs.length === 0 || feeInputs.length === 0) return true;

    errorContainer.innerHTML = '';
    let ok = true;

    const min = Array.from(minInputs).map(i => parseInt(i.value, 10));
    const max = Array.from(maxInputs).map(i => parseInt(i.value, 10));

    // required check (keep it basic)
    minInputs.forEach((input, idx) => {
        if (!String(input.value).trim() || !String(maxInputs[idx].value).trim() || !String(feeInputs[idx].value).trim()) {
            ok = false;
            errorContainer.innerHTML += `<p class="text-danger">Attendance range and pitch fee are required.</p>`;
        }
    });

    // overlap check
    for (let i = 0; i < min.length; i++) {
        for (let j = i + 1; j < min.length; j++) {
            const overlap =
                (min[i] <= max[j] && max[i] >= min[j]) ||
                (min[j] <= max[i] && max[j] >= min[i]);

            if (overlap) {
                ok = false;
                errorContainer.innerHTML += `<p class="text-danger">Overlap between ranges ${min[i]}-${max[i]} and ${min[j]}-${max[j]}.</p>`;
            }
        }
    }

    return ok;
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('publicEventForm');
    if (!form) return;

    form.addEventListener('submit', (e) => {
        const okGuests = validateGuestRanges();
        const okDur = validateHourDayDuplicates();
        const okAttendance = validateAttendanceRanges();

        // Corporate validator (only exists when corporate markup is present)
        const okCorp = typeof window.__validateCorporate === 'function' ? window.__validateCorporate() : true;

        if (!(okGuests && okDur && okAttendance && okCorp)) {
            e.preventDefault();
        }
    });
});
