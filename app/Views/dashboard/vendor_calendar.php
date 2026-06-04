<?= $this->include('header') ?>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>

<main class="page-main">
<div class="dashboard-wrapper">
<div class="container">
    <?= $this->include('dashboard/_vendor_tabs') ?>
    <?= $this->include('dashboard/_flash_alerts') ?>
    <div class="fye-page">
        <div class="fye-page-head">
            <div>
                <h1 class="fye-page-title">Calendar &amp; availability</h1>
                <p class="fye-page-sub">Confirmed bookings and pending enquiries. Block dates when you're unavailable.</p>
            </div>
            <button type="button" class="fye-btn ghost" data-bs-toggle="modal" data-bs-target="#blockDatesModal">
                <i class="fa-solid fa-ban"></i> Block dates
            </button>
        </div>

        <div style="display:flex;gap:18px;margin-bottom:18px;font-size:12.5px;flex-wrap:wrap">
            <span style="display:inline-flex;align-items:center;gap:7px">
                <span style="width:12px;height:12px;border-radius:4px;background:var(--fye-terra);display:inline-block"></span>
                Confirmed booking
            </span>
            <span style="display:inline-flex;align-items:center;gap:7px">
                <span style="width:12px;height:12px;border-radius:4px;background:var(--fye-gold-tint);border:1px solid var(--fye-gold);display:inline-block"></span>
                Pending enquiry
            </span>
            <span style="display:inline-flex;align-items:center;gap:7px">
                <span style="width:12px;height:12px;border-radius:4px;background:var(--fye-plum-tint);display:inline-block"></span>
                Blocked
            </span>
        </div>

        <div class="fye-cal" style="padding:24px">
            <div id="vendor-calendar"></div>
        </div>
    </div>
</div>
</div>

<!-- Block dates modal -->
<div class="modal fade" id="blockDatesModal" tabindex="-1" aria-labelledby="blockDatesLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" style="font-family:var(--fye-display);font-weight:600" id="blockDatesLabel">Block dates</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:13.5px;color:var(--fye-ink-2)">Select dates to mark yourself unavailable. Customers won't be able to request bookings on these days.</p>
                <p style="font-size:12.5px;color:var(--fye-ink-3)">Use the calendar to click dates directly, or contact support to bulk-block ranges.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="fye-btn ghost" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Booking detail modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" style="font-family:var(--fye-display);font-weight:600">Booking details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="kv"><span class="k">Event</span><span class="v" id="modal-event"></span></div>
                <div class="kv"><span class="k">Customer</span><span class="v" id="modal-customer"></span></div>
                <div class="kv"><span class="k">Event type</span><span class="v" id="modal-type"></span></div>
                <div class="kv"><span class="k">Date</span><span class="v" id="modal-date"></span></div>
                <div class="kv"><span class="k">Location</span><span class="v" id="modal-location"></span></div>
                <div class="kv"><span class="k">Service</span><span class="v" id="modal-service"></span></div>
                <div class="kv"><span class="k">Status</span><span class="v" id="modal-status"></span></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('vendor-calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listWeek' },
        events: '/profile/calendar-data',
        eventClick: function (info) {
            var props = info.event.extendedProps;
            document.getElementById('modal-event').textContent     = info.event.title;
            document.getElementById('modal-customer').textContent  = props.customer || '';
            document.getElementById('modal-type').textContent      = props.event_type || '';
            document.getElementById('modal-date').textContent      = info.event.startStr;
            document.getElementById('modal-location').textContent  = props.location || '';
            document.getElementById('modal-service').textContent   = props.service || '';
            document.getElementById('modal-status').textContent    = props.status || '';
            new bootstrap.Modal(document.getElementById('bookingModal')).show();
        },
        eventColor: 'var(--fye-terra)',
        height: 'auto',
        eventDisplay: 'block',
    });
    calendar.render();
});
</script>

</main>
<?= $this->include('footer') ?>
