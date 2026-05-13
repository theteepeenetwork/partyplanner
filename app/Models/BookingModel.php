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
        'payment_intent_id',
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

    public function countBookingItems(int $bookingId): int
    {
        $bookingItemModel = new BookingItemModel();
        return $bookingItemModel->where('booking_id', $bookingId)->countAllResults();
    }
}