<?php namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table            = 'bookings';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['user_id', 'event_id', 'status'];

    // Define validation rules (optional)
    protected $validationRules = [
        'user_id' => 'required|integer',
        'event_id' => 'required|integer',
        'status' => 'required|in_list[pending,accepted,rejected]'
    ];

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
    public function countBookingItems($eventId) {
        $bookingItemModel = new BookingItemModel();
        return $bookingItemModel->where('booking_id', $eventId)->countAllResults();
    }
}











/*namespace App\Models;

use CodeIgniter\Model;

class BookingItemModel extends Model
{
    protected $table = 'booking_items';
    protected $allowedFields = ['booking_id', 'service_id', 'quantity', 'status']; 
}*/