<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table         = 'reviews';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';
    protected $allowedFields = [
        'booking_item_id',
        'customer_id',
        'vendor_id',
        'service_id',
        'rating',
        'title',
        'comment',
        'flagged',
    ];

    /**
     * Vendor-wide rating summary (average + count across ALL of a vendor's services).
     *
     * @return array{avg: float|null, count: int}
     */
    public function vendorRatingSummary(int $vendorId): array
    {
        $row = $this->db->table($this->table)
            ->select('AVG(rating) AS avg_rating, COUNT(*) AS cnt')
            ->where('vendor_id', $vendorId)
            ->get()
            ->getRowArray();

        $count = (int) ($row['cnt'] ?? 0);

        return [
            'avg'   => $count > 0 ? round((float) $row['avg_rating'], 1) : null,
            'count' => $count,
        ];
    }

    /**
     * Written reviews for a single service (comments are service-specific).
     *
     * @return list<array<string, mixed>>
     */
    public function serviceReviews(int $serviceId, ?int $limit = null): array
    {
        $builder = $this->db->table($this->table)
            ->select('reviews.*, users.name AS customer_name, events.title AS event_title, events.event_type AS event_type')
            ->join('users', 'users.id = reviews.customer_id', 'left')
            ->join('booking_items', 'booking_items.id = reviews.booking_item_id', 'left')
            ->join('bookings', 'bookings.id = booking_items.booking_id', 'left')
            ->join('events', 'events.id = bookings.event_id', 'left')
            ->where('reviews.service_id', $serviceId)
            ->orderBy('reviews.created_at', 'DESC');

        if ($limit !== null) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * All reviews for a vendor across every service, including which service each was for.
     *
     * @return list<array<string, mixed>>
     */
    public function vendorReviews(int $vendorId): array
    {
        return $this->db->table($this->table)
            ->select('reviews.*, users.name AS customer_name, services.title AS service_title, events.event_type AS event_type')
            ->join('users', 'users.id = reviews.customer_id', 'left')
            ->join('services', 'services.id = reviews.service_id', 'left')
            ->join('booking_items', 'booking_items.id = reviews.booking_item_id', 'left')
            ->join('bookings', 'bookings.id = booking_items.booking_id', 'left')
            ->join('events', 'events.id = bookings.event_id', 'left')
            ->where('reviews.vendor_id', $vendorId)
            ->orderBy('reviews.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function hasReviewedBookingItem(int $bookingItemId): bool
    {
        return $this->db->table($this->table)
            ->where('booking_item_id', $bookingItemId)
            ->countAllResults() > 0;
    }
}
