<?php
namespace App\Controllers;

use App\Models\ServiceModel;
use App\Models\ServiceImageModel;
use App\Models\CategoryModel;
use App\Models\CmsPageModel;

class Home extends BaseController
{
    /**
     * Editorial category tiles for the homepage (fixed labels + art; IDs resolved from DB).
     *
     * @var list<array{label: string, image: string, keywords: list<string>}>
     */
    private const HOMEPAGE_CATEGORY_TILES = [
        ['label' => 'Photography & Video', 'image' => 'category-photography-video.jpg', 'keywords' => ['photo', 'photograph', 'video']],
        ['label' => 'Catering & Drinks', 'image' => 'category-catering-drinks.jpg', 'keywords' => ['cater', 'food', 'drink', 'kitchen']],
        ['label' => 'Venues', 'image' => 'category-venues.jpg', 'keywords' => ['venue', 'hall', 'space']],
        ['label' => 'Flowers & Styling', 'image' => 'category-flowers-styling.jpg', 'keywords' => ['flor', 'flower', 'styl', 'decor']],
        ['label' => 'Entertainment', 'image' => 'category-entertainment.jpg', 'keywords' => ['music', 'dj', 'entertain']],
        ['label' => 'Cakes & Desserts', 'image' => 'category-cakes-desserts.jpg', 'keywords' => ['cake', 'dessert', 'sweet', 'baker']],
        ['label' => 'Beauty & Personal Care', 'image' => 'category-beauty-personal-care.jpg', 'keywords' => ['hair', 'makeup', 'beauty']],
        ['label' => 'Event Planning Support', 'image' => 'category-event-planning-support.jpg', 'keywords' => ['plan', 'coord', 'planner']],
    ];

    public function index()
    {
        $serviceModel      = new ServiceModel();
        $serviceImageModel = new ServiceImageModel();
        $categoryModel     = new CategoryModel();

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

        // A rejected/pending vendor's services must not be spotlighted on the homepage.
        $builder = $builder->approvedVendorOnly();

        $services = $builder
            ->orderBy('rand()')
            ->limit(9)
            ->findAll();

        foreach ($services as &$service) {
            $service['images']           = $serviceImageModel
                ->where(['service_id' => $service['id'], 'is_primary' => 1])
                ->findAll();
            $service['category_label'] = $categoryModel->getServiceCategoryLabel($service);
        }
        unset($service);

        $categories = $categoryModel->getRootCategories();

        $data = [
            'services'             => $services,
            'categories'           => $categories,
            'homeCategoryTiles'    => $this->buildCategoryTiles($categories),
            'cmsHome'              => $cmsHome,
            'heroImage'            => 'hero-event-planning.jpg',
            'serviceFallbackImage' => 'fallback-service-card.jpg',
            'vendorCtaImage'       => 'vendor-cta.jpg',
            'isHomePage'           => true,
            'inspirationBrowseUrl' => base_url('browse-services'),
            'inspirationCards'     => [
                [
                    'title' => 'Wedding planning essentials',
                    'text'  => 'Photography, venues, catering and more.',
                    'href'  => base_url('browse-services?q=wedding'),
                    'icon'  => 'fas fa-heart',
                ],
                [
                    'title' => 'Birthday party supplier ideas',
                    'text'  => 'Entertainment, cakes and styling.',
                    'href'  => base_url('browse-services?q=birthday'),
                    'icon'  => 'fas fa-cake-candles',
                ],
                [
                    'title' => 'Corporate event services',
                    'text'  => 'Venues, catering and AV support.',
                    'href'  => base_url('browse-services?q=corporate'),
                    'icon'  => 'fas fa-briefcase',
                ],
                [
                    'title' => 'Christening celebration inspiration',
                    'text'  => 'Catering, photography and venues.',
                    'href'  => base_url('browse-services?q=christening'),
                    'icon'  => 'fas fa-champagne-glasses',
                ],
            ],
        ];

        return view('home', $data);
    }

    /**
     * @param list<array<string, mixed>> $categories
     *
     * @return list<array{id: int|null, name: string, image: string, href: string}>
     */
    private function buildCategoryTiles(array $categories): array
    {
        $tiles   = [];
        $usedIds = [];

        foreach (self::HOMEPAGE_CATEGORY_TILES as $def) {
            $matchedId = null;
            foreach ($categories as $cat) {
                $name = strtolower((string) ($cat['name'] ?? ''));
                foreach ($def['keywords'] as $kw) {
                    if ($kw !== '' && str_contains($name, strtolower($kw))) {
                        $cid = (int) $cat['id'];
                        if (! isset($usedIds[$cid])) {
                            $matchedId     = $cid;
                            $usedIds[$cid] = true;
                        }
                        break 2;
                    }
                }
            }

            $href = $matchedId !== null
                ? base_url('browse-services?category=' . $matchedId)
                : base_url('browse-services');

            $tiles[] = [
                'id'    => $matchedId,
                'name'  => $def['label'],
                'image' => $def['image'],
                'href'  => $href,
            ];
        }

        return $tiles;
    }
}
