<?php
namespace App\Controllers;

use App\Models\ServiceModel;
use App\Models\ServiceImageModel;
use App\Models\CategoryModel;
use App\Models\CmsPageModel;

class Home extends BaseController
{
    public function index()
    {
        $serviceModel = new ServiceModel();
        $serviceImageModel = new ServiceImageModel();
        $categoryModel = new CategoryModel();

        $db = \Config\Database::connect();

        $cmsHome = null;
        if ($db->tableExists('cms_pages')) {
            $cmsModel = new CmsPageModel();
            $cmsHome  = $cmsModel->where('slug', 'homepage')->where('status', 'published')->first();
        }

        $cols = $db->getFieldNames('services');

        $builder = $serviceModel;
        if (in_array('status', $cols, true)) {
            $builder = $builder->where('status', 'active');
        }
        if (in_array('deleted_at', $cols, true)) {
            $builder = $builder->where('deleted_at', null);
        }

        // Retrieve 9 random active services
        $services = $builder
            ->orderBy('rand()')
            ->limit(9)
            ->findAll();

        // Fetch associated images for each service
        foreach ($services as &$service) {
            $service['images'] = $serviceImageModel
                ->where(['service_id' => $service['id'], 'is_primary' => 1])
                ->findAll();
        }

        // Top-level categories only (sub-tiers load in browse / service flows)
        $categories = $categoryModel->getRootCategories();

        $data = [
            'services' => $services,
            'categories' => $categories, // Include categories in the data array
            'cmsHome' => $cmsHome,
        ];

        return view('home', $data);
    }
}
