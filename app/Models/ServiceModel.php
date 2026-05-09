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
        'service_location',
        'no_travel_limit',
        'all_travel_included',
        'service_tags',
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
}
