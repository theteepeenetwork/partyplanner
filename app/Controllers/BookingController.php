<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\BookingItemModel;
use App\Models\EventModel;
use App\Models\ServiceModel;
use CodeIgniter\Controller;
use DateTime;
 
class BookingController extends Controller
{
    // Method to display a month-to-view calendar with bookings 
    public function calendarView($year = null, $month = null)
    {
        $serviceModel = new ServiceModel();
        $bookingItemModel = new BookingItemModel();

        $vendorId = session()->get('user_id');
        $year = $year ?: date('Y');
        $month = $month ?: date('m');

        $bookings = $bookingItemModel
            ->select('booking_items.*, events.date as event_date, services.title as service_title')
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->join('events', 'events.id = bookings.event_id')
            ->where('services.vendor_id', $vendorId)
            ->where('YEAR(events.date)', $year)
            ->where('MONTH(events.date)', $month)
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




    // Method to handle booking requests
    public function bookService()
    {
        $bookingModel = new BookingModel();
        $bookingItemModel = new BookingItemModel();
        $eventModel = new EventModel();
        $serviceAvailabilityModel = new ServiceAvailabilityModel();

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

    public function getBookingsByMonth($year, $month)
    {
        $builder = $this->db->table('bookings')
            ->select('bookings.*, events.title as event_title, events.date as event_date, booking_items.start_time, booking_items.end_time, services.title as service_title')
            ->join('booking_items', 'booking_items.booking_id = bookings.id')
            ->join('events', 'events.id = bookings.event_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->where('YEAR(events.date)', $year)
            ->where('MONTH(events.date)', $month)
            ->get();

        $results = [];
        foreach ($builder->getResultArray() as $row) {
            $date = $row['event_date'];
            if (!isset($results[$date])) {
                $results[$date] = [];
            }
            $results[$date][] = $row;
        }

        return $results;
    }

    public function calendarData($year, $month)
    {
        $bookingModel = new BookingModel();
        $bookingItemModel = new BookingItemModel();

        // Get all bookings for the specified month and year
        $startDate = "$year-$month-01";
        $endDate = date("Y-m-t", strtotime($startDate)); // Get the last day of the month

        $bookings = $bookingItemModel
            ->select('booking_items.*, bookings.event_id, bookings.status, events.date as event_date, services.title as service_title')
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('events', 'events.id = bookings.event_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->where('events.date >=', $startDate)
            ->where('events.date <=', $endDate)
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


    // Other methods related to booking management...
}
