<style>
    /* ============================================================
       PartyPlanner · shared style include for service-create steps
       Drop-in replacement for app/Views/service_create/css.php
       Rebranded to the forest-green / terracotta system to match
       service-form.css. Keeps the modal / tooltip / tag structural
       rules the JS relies on.
       ============================================================ */

    :root {
        --pp-green-dark: #2D4A3E;
        --pp-green-darker: #143729;
        --pp-green-light: #E8F0EC;
        --pp-green-tint: #F1F6F2;
        --pp-terracotta: #B98C2A;
        --pp-terracotta-deep: #A9794E;
        --pp-terracotta-tint: #F6ECE0;
        --pp-cream: #FAF7F2;
        --pp-cream-warm: #F5EFE6;
        --pp-ink: #21302A;
        --pp-muted: #6B6560;
        --pp-line: #E7E1D7;
        --pp-line-strong: #D8D0C3;
        --pp-danger: #B4543F;
    }

    html, body {
        margin: 0;
        padding: 0;
        font-family: 'DM Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
        background: var(--pp-cream);
        color: var(--pp-ink);
    }

    main { margin: 0 auto; padding-top: 25px; }

    /* sections render as cards — separated by shadow, not a border */
    section {
        padding: 26px 28px;
        margin-bottom: 20px;
        margin-top: 0;
        border-radius: 18px;
        background-color: #fff;
        box-shadow: 0 10px 40px rgba(26, 46, 39, .09);
    }

    section h4 {
        font-family: 'Fraunces', Georgia, serif;
        font-size: 1.45rem;
        font-weight: 560;
        margin-bottom: 20px;
        display: block;
        color: var(--pp-green-darker);
    }
    section h4::after {
        content: "";
        display: block;
        width: 46px;
        height: 3px;
        margin-top: 11px;
        border-radius: 2px;
        background: var(--pp-terracotta);
    }

    .form-group { margin-bottom: 18px; }

    .divider { height: 1px; background-color: var(--pp-line); margin: 20px 0; }

    /* info icon */
    .info-icon {
        display: inline-grid;
        place-items: center;
        width: 18px;
        height: 18px;
        font-size: 11px;
        font-weight: 700;
        border-radius: 50%;
        background: var(--pp-terracotta-tint);
        color: var(--pp-terracotta-deep);
        border: 1px solid #EAD6BF;
        cursor: help;
        margin-left: 5px;
        position: relative;
    }
    .info-icon:hover { background: var(--pp-green-light); color: var(--pp-green-dark); }

    /* modal box (kept for JS) */
    .my-modal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        background: #fff;
        box-shadow: 0 14px 40px rgba(26, 46, 39, .2);
        border-radius: 16px;
        padding: 24px;
        z-index: 1000;
    }
    .my-modal-overlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(26, 46, 39, .45);
        z-index: 999;
        display: none;
    }
    .close-btn { cursor: pointer; float: right; font-size: 18px; font-weight: bold; color: var(--pp-muted); }

    /* tag input → pills */
    .tag-container {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        border: 1.5px solid var(--pp-line-strong);
        padding: 9px 10px;
        min-height: 48px;
        border-radius: 11px;
        background: #fff;
        cursor: text;
    }
    .tag-container:focus-within {
        border-color: var(--pp-green-dark);
        box-shadow: 0 0 0 3.5px rgba(45, 74, 62, .14);
    }
    .tag {
        background-color: var(--pp-green-light);
        color: var(--pp-green-dark);
        border-radius: 999px;
        padding: 5px 12px;
        margin: 0;
        display: inline-flex;
        align-items: center;
        font-size: .82rem;
        font-weight: 600;
    }
    .tag .remove-tag {
        margin-left: 6px;
        cursor: pointer;
        font-weight: bold;
        width: 16px; height: 16px;
        border-radius: 50%;
        background: rgba(45, 74, 62, .14);
        display: grid; place-items: center;
        font-size: 11px; line-height: 1;
    }
    .tag-input { border: none; outline: none; flex: 1; min-width: 120px; margin: 0; background: transparent; font-size: .95rem; }

    /* server-side validation */
    .is-invalid { border: 1.5px solid var(--pp-danger) !important; }
    .invalid-feedback { color: var(--pp-danger); font-size: .82rem; }

    .custom-tooltip {
        position: absolute;
        background-color: var(--pp-green-darker);
        color: #fff;
        padding: 6px 11px;
        border-radius: 8px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
    }

    /* pricing sections */
    .pricing-section h4 {
        margin-bottom: 1rem;
        color: var(--pp-green-darker);
        font-family: 'Fraunces', Georgia, serif;
        font-size: 1.45rem;
        font-weight: 560;
        display: block;
    }
    .pricing-section h4::after {
        content: "";
        display: block;
        width: 46px;
        height: 3px;
        margin-top: 11px;
        border-radius: 2px;
        background: var(--pp-terracotta);
    }

    .input-group { display: flex; align-items: stretch; width: 100%; flex-wrap: nowrap; }
    .input-group .form-row { align-items: center; width: 100%; margin-bottom: 10px; flex-wrap: nowrap; }
    .input-group .form-row .input-group-text { margin-right: 10px; }
    .input-group .form-row .form-control { min-width: 70px; max-width: 100%; }

    .pitch-group {
        border: 1.5px solid var(--pp-line);
        border-radius: 13px;
        margin-bottom: 12px;
        padding: 16px;
        background: var(--pp-cream);
    }

    @media (min-width: 751px) {
        .input-group .form-row { flex: 1; margin-bottom: 0; }
        .input-group .form-row:last-child { margin-right: 0; }
    }

    .all-rows-container {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
    }

    .input-group { flex: 1; min-width: 150px; max-width: 320px; }

    @media (max-width: 768px) {
        .all-rows-container { flex-direction: column; align-items: stretch; }
        .input-group { max-width: none; }
    }

    /* buttons */
    .btn-primary {
        background-color: var(--pp-green-dark);
        color: #fff;
        border-color: var(--pp-green-dark);
        border-radius: 11px;
        font-weight: 600;
    }
    .btn-primary:hover {
        background-color: var(--pp-green-darker);
        border-color: var(--pp-green-darker);
    }

    .btn-danger {
        background-color: #fff;
        color: var(--pp-muted-soft);
        border: 1.5px solid var(--pp-line-strong);
        border-radius: 9px;
        margin-top: 0 !important;
        margin-left: 10px;
    }
    .btn-danger:hover { background-color: #fff; border-color: var(--pp-danger); color: var(--pp-danger); }

    .remove-guest-range {
        background-color: #fff;
        color: var(--pp-muted-soft);
        border: 1.5px solid var(--pp-line-strong);
        padding: 6px 11px;
        border-radius: 9px;
        font-size: 14px;
        cursor: pointer;
    }
    .remove-guest-range:hover { border-color: var(--pp-danger); color: var(--pp-danger); }
</style>
