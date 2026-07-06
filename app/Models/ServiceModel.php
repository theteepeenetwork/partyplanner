<?php
namespace App\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    protected $table = 'services';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'vendor_id',
        'title',
        'short_description',
        'description',
        'price',
        'min_capacity',
        'max_capacity',
        'setup_minutes',
        'breakdown_minutes',
        'min_notice_days',
        'space_required',
        'indoor_outdoor',
        'power_required',
        'water_required',
        'vehicle_access_required',
        'equipment_provided',
        'category_id',
        'subcategory_id',
        'third_category_id',
        'latitude',
        'longitude',
        'deleted_at',
        'free_coverage_radius',
        'paid_coverage_radius',
        'travel_fee_per_km',
        'cancellation_policy',
        'service_tags',
        'service_location',
        'all_travel_included',
        'no_travel_limit',
        'event_types',
        'commission_percentage',
        'license',
        'attendance_thresholds',
        'max_pitch_fees',
        'created_at',
        'updated_at',
        'status'
    ];

    // Function to retrieve service with images, prioritizing the primary image
    public function getServiceWithImages($id)
    {
        $service = $this->find($id);

        if ($service) {
            $serviceImageModel = new ServiceImageModel();
            // Order images by `is_primary` first to get the primary image first
            $service['images'] = $serviceImageModel->where('service_id', $id)
                ->orderBy('is_primary', 'DESC')
                ->findAll();
        }

        return $service;
    }

    // Function to unset the current primary image and set a new one
    public function setPrimaryImage($serviceId, $imageId)
    {
        $serviceImageModel = new ServiceImageModel();

        // Unset the current primary image
        $serviceImageModel->where('service_id', $serviceId)
            ->set(['is_primary' => 0])
            ->update();

        // Set the selected image as the primary one
        return $serviceImageModel->update($imageId, ['is_primary' => 1]);
    }

    /**
     * Restrict a services query to listings from vendors currently approved
     * to trade (vendor_status = 'approved'). Pending/rejected vendors keep
     * their own services visible on their own dashboard (that code queries
     * ServiceModel directly, bypassing this scope) but must disappear from
     * every public storefront surface: browse/search, and the vendor
     * profile's service grid.
     *
     * A subquery (rather than a join) is used so this composes safely with
     * callers that already join/group on `services` (e.g. the browse search
     * filter's category joins + `GROUP BY services.id`).
     */
    public function approvedVendorOnly(): self
    {
        return $this->whereIn(
            'services.vendor_id',
            $this->db->table('users')->select('id')->where('vendor_status', 'approved')
        );
    }
}
