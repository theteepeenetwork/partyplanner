<?php
namespace App\Controllers;

use App\Models\ServiceModel;
use App\Models\ServiceImageModel;
use App\Models\CategoryModel;
use App\Models\CmsPageModel;
use Config\Branding;

class Home extends BaseController
{
    /** @var list<array{image: string, keywords: list<string>}> */
    private const CATEGORY_TILE_DEFS = [
        ['image' => 'category_photography_v1.webp', 'keywords' => ['photo', 'photograph']],
        ['image' => 'category_catering_v1.webp', 'keywords' => ['cater', 'food', 'kitchen']],
        ['image' => 'category_planning_v1.webp', 'keywords' => ['plan', 'coord']],
        ['image' => 'category_florist_v1.webp', 'keywords' => ['flor', 'flower']],
        ['image' => 'category_entertainment_v1.webp', 'keywords' => ['music', 'dj', 'entertain']],
        ['image' => 'category_beauty_v1.webp', 'keywords' => ['hair', 'makeup', 'beauty']],
        ['image' => 'category_transport_v1.webp', 'keywords' => ['transport', 'car', 'limo']],
        ['image' => 'category_venues_v1.webp', 'keywords' => ['venue', 'hall', 'space']],
        ['image' => 'category_supplies_v1.webp', 'keywords' => ['suppl', 'decor', 'hire']],
        ['image' => 'category_cakes_v1.webp', 'keywords' => ['cake', 'dessert', 'sweet']],
    ];

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
        $homeCategoryTiles = [];
        $usedIds           = [];
        foreach (self::CATEGORY_TILE_DEFS as $td) {
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
                'image' => 'category_default_v1.webp',
            ];
        }

        $branding = config(Branding::class);

        $data = [
            'services' => $services,
            'categories' => $categories,
            'homeCategoryTiles' => $homeCategoryTiles,
            'cmsHome' => $cmsHome,
            'heroSubtitle' => $branding->heroSubtitle(),
            'heroImage' => 'hero_wedding_evening_v1.webp',
        ];

        return view('home', $data);
    }
}
