<?= $this->include('header') ?>

<style>
    .calendar {
        display: flex;
        flex-direction: column;
    }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
    }

    .calendar-day {
        border: 1px solid #ddd;
        padding: 10px;
        min-height: 100px;
        position: relative;
    }

    .calendar-day .date {
        font-weight: bold;
        margin-bottom: 10px;
    }

    .calendar-day .booking {
        background-color: #f0f8ff;
        border: 1px solid #ddd;
        padding: 5px;
        margin-bottom: 5px;
    }

    .calendar-day.empty {
        background-color: #f9f9f9;
    }

    .calendar-days-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        text-align: center;
        font-weight: bold;
        padding-bottom: 10px;
        border-bottom: 1px solid #ddd;
        margin-bottom: 10px;
    }
</style>
<script>
    var baseUrl = "<?= base_url() ?>";
</script>

<main class="container mt-4">
    <h2 id="calendar-title"><?= date('F Y', strtotime("$year-$month-01")) ?></h2>

    <div class="calendar">
        <div class="calendar-header">
            <button id="prev-month">Previous</button>
            <span id="calendar-title"><span id="current-month" data-month="<?= $month ?>" data-year="<?= $year ?>"><?= date('F Y', strtotime("$year-$month-01")) ?></span></span>
            <button id="next-month">Next</button>
        </div>

        <div id="calendar-days-header" class="calendar-days-header">
            <div>Monday</div>
            <div>Tuesday</div>
            <div>Wednesday</div>
            <div>Thursday</div>
            <div>Friday</div>
            <div>Saturday</div>
            <div>Sunday</div>
        </div>

        <div id="calendar-grid" class="calendar-grid">
            <?php
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $firstDayOfMonth = (date('N', strtotime("$year-$month-01")) - 1);
            ?>

            <!-- Existing PHP code to generate calendar days -->
            <?php for ($i = 0; $i < $firstDayOfMonth; $i++): ?>
                <div class="calendar-day empty"></div>
            <?php endfor; ?>

            <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                <?php $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT); ?>
                <div class="calendar-day">
                    <div class="date"><?= $day ?></div>
                    <?php if (isset($bookingsByDate[$date])): ?>
                        <div class="bookings">
                            <?php foreach ($bookingsByDate[$date] as $booking): ?>
                                <div class="booking">
                                    <strong><?= esc($booking['service_title']) ?></strong><br>
                                    <?= date('H:i', strtotime($booking['start_time'])) ?> - <?= date('H:i', strtotime($booking['end_time'])) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</main>

<?= $this->include('footer') ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateCalendar(data) {
            // Ensure data has valid month and year
            if (!data.month || !data.year) {
                console.error('Invalid data:', data);
                return;
            }

            // Update the calendar header with the correct month and year
            document.getElementById('calendar-title').textContent = `${data.month_name} ${data.year}`;
            document.getElementById('current-month').textContent = `${data.month_name} ${data.year}`;
            document.getElementById('current-month').dataset.month = data.month;
            document.getElementById('current-month').dataset.year = data.year;

            const calendarGrid = document.getElementById('calendar-grid');
            calendarGrid.innerHTML = ''; // Clear existing calendar days

            // Add blank days for the first week until the first day of the month
            for (let i = 0; i < data.first_day_of_month; i++) {
                calendarGrid.innerHTML += '<div class="calendar-day empty"></div>';
            }

            // Add actual days with bookings
            data.days.forEach(day => {
                let dayHTML = `<div class="calendar-day"><div class="date">${day.day}</div>`;
                if (day.bookings.length > 0) {
                    day.bookings.forEach(booking => {
                        dayHTML += `<div class="booking"><strong>${booking.service_title}</strong><br>${booking.start_time} - ${booking.end_time}</div>`;
                    });
                }
                dayHTML += `</div>`;
                calendarGrid.innerHTML += dayHTML;
            });
        }

        function changeMonth(direction) {
            const currentMonthElem = document.getElementById('current-month');
            let currentMonth = parseInt(currentMonthElem.dataset.month, 10);
            let currentYear = parseInt(currentMonthElem.dataset.year, 10);

            // Adjust month and year based on direction
            if (direction === 'next') {
                currentMonth++;
                if (currentMonth > 12) {
                    currentMonth = 1;
                    currentYear++;
                }
            } else if (direction === 'prev') {
                currentMonth--;
                if (currentMonth < 1) {
                    currentMonth = 12;
                    currentYear--;
                }
            }

            // Fetch new calendar data for the adjusted month and year
            fetch(`${baseUrl}/calendarData/${currentYear}/${currentMonth}`)
                .then(response => response.json())
                .then(data => updateCalendar(data))
                .catch(error => console.error('Error loading calendar:', error));
        }

        // Attach event listeners to the next and previous buttons
        document.getElementById('next-month').addEventListener('click', () => changeMonth('next'));
        document.getElementById('prev-month').addEventListener('click', () => changeMonth('prev'));
    });
</script>