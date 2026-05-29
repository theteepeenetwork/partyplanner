<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\BookingItemModel;
use App\Models\EventModel;
use App\Models\ServiceModel;
use CodeIgniter\Controller;
use DateTime;
 
/**
 * Handles vendor booking calendar views and legacy booking submission.
 */
class BookingController extends Controller
{
    /**
     * Render the monthly calendar view showing all bookings for the authenticated vendor.
     *
     * @param int|null $year  Calendar year; defaults to the current year.
     * @param int|null $month Calendar month (1–12); defaults to the current month.
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
     */
    public function calendarView($year = null, $month = null)
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $serviceModel = new ServiceModel();
        $bookingItemModel = new BookingItemModel();

        $vendorId = session()->get('user_id');
        $year = $year ?: date('Y');
        $month = $month ?: date('m');

        $bookings = $bookingItemModel
            ->select('booking_items.*, events.`date` as event_date, services.title as service_title', false)
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->join('events', 'events.id = bookings.event_id')
            ->where('services.vendor_id', $vendorId)
            ->where('YEAR(events.`date`)', $year, false)
            ->where('MONTH(events.`date`)', $month, false)
            ->findAll();

        $bookingsByDate = [];
        foreach ($bookings as $booking) {
            $date = $booking['event_date'];
            $bookingsByDate[$date][] = [
                'service_title' => $booking['service_title'],
                'start_time' => $booking['start_time'],
                'end_time' => $booking['end_time']
            ];
        }

        return view('vendor_calendar', [
            'year' => $year,
            'month' => $month,
            'bookingsByDate' => $bookingsByDate,
        ]);
    }




    /**
     * Handle a legacy booking form submission, check for time conflicts, and create a pending booking.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function bookService()
    {
        $bookingModel = new BookingModel();
        $bookingItemModel = new BookingItemModel();
        $eventModel = new EventModel();

        $serviceId = $this->request->getPost('service_id');
        $eventId = $this->request->getPost('event_id');
        $date = $this->request->getPost('date');
        $startTime = $this->request->getPost('start_time');
        $duration = $this->request->getPost('duration');

        // Calculate end time
        $durationObj = new \DateInterval('PT' . (int)$duration . 'H');
        $startTimeObj = new \DateTime($startTime);
        $endTimeObj = (clone $startTimeObj)->add($durationObj);

        $endTime = $endTimeObj->format('H:i:s');

        // Check for booking conflicts
        $conflict = $bookingItemModel
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->where('booking_items.service_id', $serviceId)
            ->where('bookings.event_id', $eventId)
            ->where('bookings.date', $date)
            ->where('bookings.start_time <', $endTime)
            ->where('bookings.end_time >', $startTime)
            ->whereIn('bookings.status', ['accepted', 'pending'])
            ->first();

        if ($conflict) {
            return redirect()->back()->with('error', 'The selected time slot conflicts with an existing booking.');
        }

        // Create a booking
        $bookingModel->save([
            'user_id' => session()->get('user_id'),
            'event_id' => $eventId,
            'service_id' => $serviceId,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'pending',
        ]);

        return redirect()->to('/service/view/' . $serviceId)->with('success', 'Service booked successfully!');
    }

    /**
     * Return JSON calendar data for a given month, organised by date, for AJAX calendar rendering.
     *
     * @param int|string $year  The calendar year.
     * @param int|string $month The calendar month (1–12).
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function calendarData($year, $month)
    {
        if (!session()->has('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorised']);
        }

        $bookingModel = new BookingModel();
        $bookingItemModel = new BookingItemModel();

        // Get all bookings for the specified month and year
        $startDate = "$year-$month-01";
        $endDate = date("Y-m-t", strtotime($startDate)); // Get the last day of the month

        $bookings = $bookingItemModel
            ->select('booking_items.*, bookings.event_id, bookings.status, events.`date` as event_date, services.title as service_title', false)
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('events', 'events.id = bookings.event_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->where('events.`date` >=', $startDate, false)
            ->where('events.`date` <=', $endDate, false)
            ->findAll();



        // Organize bookings by date
        $bookingsByDate = [];
        foreach ($bookings as $booking) {
            $date = $booking['event_date'];
            if (!isset($bookingsByDate[$date])) {
                $bookingsByDate[$date] = [];
            }

            // Format the start_time and end_time
            $formattedStartTime = (new DateTime($booking['start_time']))->format('H:i');
            $formattedEndTime = (new DateTime($booking['end_time']))->format('H:i');

            $bookingsByDate[$date][] = [
                'service_title' => $booking['service_title'],
                'start_time' => $formattedStartTime,
                'end_time' => $formattedEndTime,
            ];
        }

        // Prepare response data
        $response = [
            'year' => $year,
            'month' => $month,
            'month_name' => date('F', strtotime($startDate)),
            'first_day_of_month' => date('w', strtotime($startDate)),
            'days' => [],
        ];

        // Generate days of the month
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
            $response['days'][] = [
                'day' => $day,
                'bookings' => $bookingsByDate[$date] ?? [],
            ];
        }

        return $this->response->setJSON($response);
    }


    /**
     * Display the post-payment success page for a completed booking.
     *
     * @param int $bookingId The booking primary key.
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
     */
    public function paymentSuccess(int $bookingId)
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $bookingModel = new BookingModel();
        $booking = $bookingModel->find($bookingId);

        if (!$booking || (int) $booking['user_id'] !== (int) session()->get('user_id')) {
            return redirect()->to('/profile/my-bookings')->with('error', 'Booking not found.');
        }

        $bookingItemModel = new BookingItemModel();
        $items = $bookingItemModel
            ->select('booking_items.*, services.title as service_title, services.vendor_id, users.name as vendor_name')
            ->join('services', 'services.id = booking_items.service_id')
            ->join('users', 'users.id = services.vendor_id')
            ->where('booking_id', $bookingId)
            ->findAll();

        $eventModel = new EventModel();
        $event = $eventModel->find($booking['event_id']);

        return view('event/checkout_success', [
            'booking'  => $booking,
            'items'    => $items,
            'event'    => $event,
        ]);
    }

    // Other methods related to booking management...
}
