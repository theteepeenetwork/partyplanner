<?php

namespace App\Controllers;

use App\Models\ServiceModel;
use App\Models\ServiceImageModel;

class Home extends BaseController
{
    public function index()
    {
        $serviceModel = new ServiceModel();
        $serviceImageModel = new ServiceImageModel();

        // Retrieve 9 random active services (where deleted_at is null)
        $services = $serviceModel
            ->where('deleted_at', null)
            ->orderBy('rand()')
            ->limit(9)
            ->findAll();

        // Fetch associated images for each service
        foreach ($services as &$service) {
            $service['images'] = $serviceImageModel->where('service_id', $service['id'])->findAll();
        }

        $data['services'] = $services;

        return view('home', $data);
    }
}
