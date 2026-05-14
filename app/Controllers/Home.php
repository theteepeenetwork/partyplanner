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

        // Homepage tiles: pair stock art with real category IDs when names align
        $tileDefs = [
            ['image' => 'photo1.png', 'keywords' => ['photo', 'photograph']],
            ['image' => 'caterer.png', 'keywords' => ['cater', 'food', 'kitchen']],
            ['image' => 'planner.png', 'keywords' => ['plan', 'coord']],
            ['image' => 'florist.png', 'keywords' => ['flor', 'flower']],
            ['image' => 'dj.png', 'keywords' => ['music', 'dj', 'entertain']],
            ['image' => 'makeup.png', 'keywords' => ['hair', 'makeup', 'beauty']],
            ['image' => 'car.png', 'keywords' => ['transport', 'car', 'limo']],
            ['image' => 'venues.png', 'keywords' => ['venue', 'hall', 'space']],
            ['image' => 'supplies.png', 'keywords' => ['suppl', 'decor', 'hire']],
            ['image' => 'cakes.png', 'keywords' => ['cake', 'dessert', 'sweet']],
        ];
        $homeCategoryTiles = [];
        $usedIds           = [];
        foreach ($tileDefs as $td) {
            foreach ($categories as $cat) {
                $name = strtolower((string) ($cat['name'] ?? ''));
                foreach ($td['keywords'] as $kw) {
                    if ($kw === '') {
                        continue;
                    }
                    if (str_contains($name, strtolower($kw))) {
                        $cid = (int) $cat['id'];
                        if (! isset($usedIds[$cid])) {
                            $usedIds[$cid]           = true;
                            $homeCategoryTiles[]      = [
                                'id'    => $cid,
                                'name'  => $cat['name'],
                                'image' => $td['image'],
                            ];
                        }
                        break 2;
                    }
                }
            }
        }
        foreach ($categories as $cat) {
            if (count($homeCategoryTiles) >= 12) {
                break;
            }
            $cid = (int) $cat['id'];
            if (isset($usedIds[$cid])) {
                continue;
            }
            $usedIds[$cid]      = true;
            $homeCategoryTiles[] = [
                'id'    => $cid,
                'name'  => $cat['name'],
                'image' => 'no-image.png',
            ];
        }

        $data = [
            'services' => $services,
            'categories' => $categories, // Include categories in the data array
            'homeCategoryTiles' => $homeCategoryTiles,
            'cmsHome' => $cmsHome,
        ];

        return view('home', $data);
    }
}
