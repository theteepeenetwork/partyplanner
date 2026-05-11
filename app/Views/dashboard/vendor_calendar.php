<?= $this->include('header') ?>
<!-- FullCalendar CSS & JS (lightweight) -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">
        <?= $this->include('dashboard/_vendor_tabs') ?>

        <h4 class="mb-4">Calendar</h4>

        <div class="dash-card">
            <div class="d-flex gap-3 mb-3 small">
                <span><span class="badge bg-success">&nbsp;</span> Confirmed</span>
                <span><span class="badge bg-warning">&nbsp;</span> Pending</span>
            </div>
            <div id="vendor-calendar"></div>
        </div>

        <!-- Booking detail modal -->
        <div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Booking Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm table-borderless">
                            <tr><th>Event</th><td id="modal-event"></td></tr>
                            <tr><th>Customer</th><td id="modal-customer"></td></tr>
                            <tr><th>Event Type</th><td id="modal-type"></td></tr>
                            <tr><th>Date</th><td id="modal-date"></td></tr>
                            <tr><th>Location</th><td id="modal-location"></td></tr>
                            <tr><th>Service</th><td id="modal-service"></td></tr>
                            <tr><th>Status</th><td id="modal-status"></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('vendor-calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listWeek'
        },
        events: '/profile/calendar-data',
        eventClick: function(info) {
            var props = info.event.extendedProps;
            document.getElementById('modal-event').textContent = info.event.title;
            document.getElementById('modal-customer').textContent = props.customer || '';
            document.getElementById('modal-type').textContent = props.event_type || '';
            document.getElementById('modal-date').textContent = info.event.startStr;
            document.getElementById('modal-location').textContent = props.location || '';
            document.getElementById('modal-service').textContent = props.service || '';
            document.getElementById('modal-status').textContent = props.status || '';
            var modal = new bootstrap.Modal(document.getElementById('bookingModal'));
            modal.show();
        },
        height: 'auto',
        eventDisplay: 'block',
    });
    calendar.render();
});
</script>

</main>

<?= $this->include('footer') ?>
