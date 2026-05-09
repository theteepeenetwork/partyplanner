<?php
namespace App\Controllers;

use App\Models\ServiceModel;
use App\Models\ServiceImageModel;
use App\Models\CategoryModel;

class Home extends BaseController
{
    public function index()
    {
        $serviceModel = new ServiceModel();
        $serviceImageModel = new ServiceImageModel();
        $categoryModel = new CategoryModel();

        // Retrieve 9 random active services
        $services = $serviceModel
            ->orderBy('rand()')
            ->limit(9)
            ->findAll();

        // Fetch associated images for each service
        foreach ($services as &$service) {
            $service['images'] = $serviceImageModel
                ->where(['service_id' => $service['id'], 'is_primary' => 1])
                ->findAll();
        }

        // Fetch all categories
        $categories = $categoryModel->findAll(); // No need for 'deleted_at'

        $data = [
            'services' => $services,
            'categories' => $categories, // Include categories in the data array
        ];

        return view('home', $data);
    }
}
