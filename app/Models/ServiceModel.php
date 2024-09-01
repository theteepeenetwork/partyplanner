<?php namespace App\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    protected $table = 'services';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'description', 'short_description', 'price', 'category_id', 'subcategory_id', 'vendor_id', 'status'];

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
