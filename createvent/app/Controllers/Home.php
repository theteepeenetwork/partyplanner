<?php namespace App\Controllers;

use App\Models\ServiceModel;

class Home extends BaseController
{
    public function index()
    {
        $serviceModel = new ServiceModel();
        
        // Retrieve 9 random services
        $data['services'] = $serviceModel->orderBy('rand()')->limit(9)->findAll();

        return view('home', $data); 
    }
}