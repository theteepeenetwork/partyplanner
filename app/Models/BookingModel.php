<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table            = 'bookings';
    protected $primaryKey       = 'id';

    // Define validation rules (optional)
    protected $allowedFields = [
        'user_id',
        'event_id',
        'status',
        'start_time',
        'end_time',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    // Define validation messages (optional)
    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be a valid integer'
        ],
        'event_id' => [
            'required' => 'Event ID is required',
            'integer' => 'Event ID must be a valid integer'
        ],
        'status' => [
            'required' => 'Booking status is required',
            'in_list' => 'Invalid booking status'
        ]
    ];

    // Get booking items for a specific booking
    public function getBookingItems($bookingId)
    {
        $bookingItemModel = new BookingItemModel();
        return $bookingItemModel->where('booking_id', $bookingId)->findAll();
    }

    // Count booking items for a specific event 
    public function countBookingItems($eventId)
    {
        $bookingItemModel = new BookingItemModel();
        return $bookingItemModel->where('booking_id', $eventId)->countAllResults();
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
}











/*namespace App\Models;

use CodeIgniter\Model;

class BookingItemModel extends Model
{
    protected $table = 'booking_items';
    protected $allowedFields = ['booking_id', 'service_id', 'quantity', 'status']; 
}*/