<?php

namespace App\Controllers;

use App\Models\ServiceModel;
use App\Models\EventModel;
use App\Models\CategoryModel;
use App\Models\ServiceImageModel;
use App\Models\UserModel;
use App\Models\BookingModel;
use App\Models\ServiceTimeBlockModel;  // Add this line
use App\Models\ServiceAvailabilityModel;
use App\Models\ServicePublicEventModel;
use App\Models\BookingItemModel;
use App\Models\ServiceTagsModel;
use App\Models\TagsModel;
use App\Models\ServiceEventTypeModel;
use App\Models\ServicePrivatePricingModel;
use App\Models\ServiceCustomDurationPricingModel;
use App\Models\ServiceTieredPackagesPricingModel;
use App\Models\ServiceGuestBasedPricingModel;
use App\Models\ServiceQuantityPricingModel;
use App\Models\ServiceCancellationPolicyModel;


use App\Models\ServiceLocationModel;
use App\Models\ServiceOptionalExtrasModel;
use App\Models\ReviewModel;
use App\Models\CartModel;
use App\Libraries\CustomerEventSummary;
use App\Libraries\EventBookingQuote;
use App\Libraries\EventQuoteBuilder;
use CodeIgniter\Controller;
use Config\Services;
use DateTime;


class Service_Controller extends BaseController
{
    /** @var list<string>|null */
    private ?array $servicesTableColumns = null;

    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login')->with('error', 'You must be logged in to view your profile.');
        }

        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/')->with('error', 'User not found.');
        }

        $data['user'] = $user;

        if ($user['role'] == 'vendor') {
            $serviceModel = new ServiceModel();
            $services = $serviceModel->where('vendor_id', $userId)->findAll();

            $data['services'] = $services;
            return view('profile_vendor', $data);
        } else {
            return redirect()->to('/')->with('error', 'You are not authorized to view this page.');
        }
    }

    public function browse()
    {
        $this->setPreferredBasketEventFromQuery($this->request->getGet('event_id'));

        $serviceModel = new ServiceModel();
        $serviceImageModel = new ServiceImageModel();
        $categoryModel = new CategoryModel();

        $categoryFilter = $this->request->getGet('category');
        $subcategoryFilter = $this->request->getGet('subcategory');
        $thirdCategoryFilter = $this->request->getGet('third_category');
        $searchQuery = trim((string) $this->request->getGet('q'));
        $sort = (string) $this->request->getGet('sort');
        if (! in_array($sort, ['newest', 'price_asc', 'price_desc', 'title'], true)) {
            $sort = 'newest';
        }

        // Attribute filters (price / capacity / event type).
        $priceMin  = $this->request->getGet('price_min');
        $priceMax  = $this->request->getGet('price_max');
        $guests    = $this->request->getGet('guests');
        $eventType = (string) $this->request->getGet('event_type');
        if (! in_array($eventType, ['public', 'private', 'corporate'], true)) {
            $eventType = '';
        }

        // Hero search fields: free-text location and an event date (vendor must not be blocked).
        $location  = trim((string) $this->request->getGet('location'));
        $date      = trim((string) $this->request->getGet('date'));
        if ($date !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = '';
        }

        $cols = $this->getServicesTableColumns();

        $builder = $this->applyPublicServiceCatalogFilters($serviceModel, $cols);

        $builder = $this->applyBrowseCategoryFilters(
            $builder,
            $cols,
            $categoryFilter !== null ? (string) $categoryFilter : '',
            $subcategoryFilter !== null ? (string) $subcategoryFilter : '',
            $thirdCategoryFilter !== null ? (string) $thirdCategoryFilter : ''
        );

        $builder = $this->applyBrowseAttributeFilters($builder, $cols, [
            'price_min'  => $priceMin,
            'price_max'  => $priceMax,
            'guests'     => $guests,
            'event_type' => $eventType,
            'location'   => $location,
            'date'       => $date,
        ]);

        if ($searchQuery !== '') {
            $builder = $this->applyBrowseSearch($builder, $searchQuery, $cols);
        }

        $builder = $this->applyBrowseSort($builder, $sort, $cols);

        // Resolve active event ID before filtering (URL param takes precedence over session).
        $queryEventId = $this->request->getGet('event_id');
        $basketEventId = null;
        if ($queryEventId !== null && $queryEventId !== '') {
            $basketEventId = (int) $queryEventId;
        } elseif (session()->get('preferred_basket_event_id') !== null) {
            $basketEventId = (int) session()->get('preferred_basket_event_id');
        }

        $showUnavailable = $this->request->getGet('show_unavailable') === '1';

        $services = $builder->findAll();
        $services = $this->filterAndTagServicesByActiveEvent($services, $basketEventId, $showUnavailable);

        // Bulk-fetch vendor ratings to avoid N+1 queries.
        $vendorIds = array_unique(array_column($services, 'vendor_id'));
        $ratingsByVendor = [];
        if (!empty($vendorIds)) {
            $db = \Config\Database::connect();
            $rows = $db->table('reviews')
                ->select('vendor_id, AVG(rating) AS avg_rating, COUNT(*) AS cnt')
                ->whereIn('vendor_id', $vendorIds)
                ->groupBy('vendor_id')
                ->get()->getResultArray();
            foreach ($rows as $r) {
                $ratingsByVendor[(int) $r['vendor_id']] = [
                    'avg' => round((float) $r['avg_rating'], 1),
                    'cnt' => (int) $r['cnt'],
                ];
            }
        }

        foreach ($services as &$service) {
            $service['images'] = $serviceImageModel
                ->where(['service_id' => $service['id'], 'is_primary' => 1])
                ->findAll();
            $service['category_name'] = $categoryModel->getServiceCategoryLabel($service);
            $vid = (int) $service['vendor_id'];
            $service['avg_rating']   = $ratingsByVendor[$vid]['avg'] ?? null;
            $service['review_count'] = $ratingsByVendor[$vid]['cnt'] ?? 0;
        }
        unset($service);

        // Which services are already in the active event's basket?
        $basketServiceIds = [];
        $messageEligibleByServiceId = [];
        if (session()->has('user_id') && session()->get('role') === 'customer') {
            $serviceIds = array_column($services, 'id');
            $bookingItemModel = new BookingItemModel();
            $eligible = $bookingItemModel->eligibleServiceIdsForCustomer((int) session()->get('user_id'), $serviceIds);
            foreach ($eligible as $sid) {
                $messageEligibleByServiceId[$sid] = true;
            }
        }

        $customerEventContext = $this->customerEventContextForBrowse();

        // Which service IDs are already in the active event's basket?
        $activeEventId = $customerEventContext['active']['id'] ?? $basketEventId;
        if ($activeEventId && session()->has('user_id')) {
            $db = \Config\Database::connect();
            $rows = $db->table('event_basket_items')
                ->select('service_id')
                ->where('event_id', (int) $activeEventId)
                ->where('user_id', (int) session()->get('user_id'))
                ->get()->getResultArray();
            $basketServiceIds = array_column($rows, 'service_id');
        }

        $data = [
            'services' => $services,
            'categories' => $this->buildCategoryTree(),
            'rootCategories' => $categoryModel->getRootCategories(),
            'selectedCategory' => $categoryFilter,
            'selectedSubcategory' => $subcategoryFilter,
            'selectedThirdCategory' => $thirdCategoryFilter,
            'selectedSort' => $sort,
            'selectedPriceMin' => $priceMin,
            'selectedPriceMax' => $priceMax,
            'selectedGuests' => $guests,
            'selectedEventType' => $eventType,
            'selectedLocation' => $location,
            'selectedDate' => $date,
            'searchQuery' => $searchQuery ?? '',
            'basketEventId' => $basketEventId,
            'showUnavailable' => $showUnavailable,
            'message_eligible_by_service_id' => $messageEligibleByServiceId,
            'customerEvents' => $customerEventContext['events'],
            'activeEvent'    => $customerEventContext['active'],
            'basketServiceIds' => $basketServiceIds,
        ];

        return view('browse_services', $data);
    }

    /**
     * Column names on `services` (cached per request) for schema-safe filters.
     *
     * @return list<string>
     */
    private function getServicesTableColumns(): array
    {
        if ($this->servicesTableColumns !== null) {
            return $this->servicesTableColumns;
        }

        $db = \Config\Database::connect();
        $this->servicesTableColumns = $db->getFieldNames('services');

        return $this->servicesTableColumns;
    }

    /**
     * Public catalogue: active, non-soft-deleted listings when those columns exist.
     *
     * @param list<string> $cols
     */
    private function applyPublicServiceCatalogFilters(ServiceModel $serviceModel, array $cols): ServiceModel
    {
        $builder = $serviceModel;
        if (in_array('status', $cols, true)) {
            $builder = $builder->where('status', 'active');
        }
        if (in_array('deleted_at', $cols, true)) {
            $builder = $builder->where('deleted_at', null);
        }

        return $builder;
    }

    /**
     * @param list<string> $cols
     */
    private function applyBrowseSearch(ServiceModel $builder, string $searchQuery, array $cols): ServiceModel
    {
        $fields = array_values(array_filter(
            ['title', 'short_description', 'description', 'service_tags'],
            static fn ($f) => in_array($f, $cols, true)
        ));

        $canJoinCats = in_array('category_id', $cols, true);
        if ($canJoinCats) {
            $builder->join('categories cat_browse_main', 'cat_browse_main.id = services.category_id', 'left');
            if (in_array('subcategory_id', $cols, true)) {
                $builder->join('categories cat_browse_sub', 'cat_browse_sub.id = services.subcategory_id', 'left');
            }
            if (in_array('third_category_id', $cols, true)) {
                $builder->join('categories cat_browse_third', 'cat_browse_third.id = services.third_category_id', 'left');
            }
            $builder->select('services.*')->groupBy('services.id');
        }

        if ($fields === [] && ! $canJoinCats) {
            return $builder;
        }

        $builder->groupStart();
        $isFirst = true;
        foreach ($fields as $field) {
            if ($isFirst) {
                $builder->like('services.' . $field, $searchQuery);
                $isFirst = false;
            } else {
                $builder->orLike('services.' . $field, $searchQuery);
            }
        }
        if ($canJoinCats) {
            if ($isFirst) {
                $builder->like('cat_browse_main.name', $searchQuery);
                $isFirst = false;
            } else {
                $builder->orLike('cat_browse_main.name', $searchQuery);
            }
            if (in_array('subcategory_id', $cols, true)) {
                $builder->orLike('cat_browse_sub.name', $searchQuery);
            }
            if (in_array('third_category_id', $cols, true)) {
                $builder->orLike('cat_browse_third.name', $searchQuery);
            }
        }
        $builder->groupEnd();

        return $builder;
    }

    /**
     * Narrow catalogue by root / sub / optional third category GET params.
     *
     * @param list<string> $cols
     */
    private function applyBrowseCategoryFilters(
        ServiceModel $builder,
        array $cols,
        string $rootId,
        string $subId,
        string $thirdId
    ): ServiceModel {
        $third = trim($thirdId);
        $sub = trim($subId);
        $root = trim($rootId);

        if ($third !== '' && in_array('third_category_id', $cols, true)) {
            $builder->where('third_category_id', (int) $third);
            if ($sub !== '' && in_array('subcategory_id', $cols, true)) {
                $builder->where('subcategory_id', (int) $sub);
            }
            if ($root !== '' && in_array('category_id', $cols, true)) {
                $builder->where('category_id', (int) $root);
            }

            return $builder;
        }

        if ($sub !== '' && in_array('subcategory_id', $cols, true)) {
            if ($third === '') {
                $categoryModel = new CategoryModel();
                $treeIds       = $categoryModel->getSelfAndDescendantIds((int) $sub);
                $builder       = $this->applyBrowseCategoryIdSet($builder, $cols, $treeIds);
                if ($root !== '' && in_array('category_id', $cols, true)) {
                    $builder->where('category_id', (int) $root);
                }

                return $builder;
            }

            $builder->where('subcategory_id', (int) $sub);
            if ($root !== '' && in_array('category_id', $cols, true)) {
                $builder->where('category_id', (int) $root);
            }

            return $builder;
        }

        if ($root !== '' && in_array('category_id', $cols, true)) {
            $builder->where('category_id', (int) $root);
        }

        return $builder;
    }

    /**
     * @param list<string> $cols
     */
    private function applyBrowseSort(ServiceModel $builder, string $sort, array $cols): ServiceModel
    {
        switch ($sort) {
            case 'price_asc':
                if (in_array('price', $cols, true)) {
                    return $builder->orderBy('services.price', 'ASC');
                }
                break;
            case 'price_desc':
                if (in_array('price', $cols, true)) {
                    return $builder->orderBy('services.price', 'DESC');
                }
                break;
            case 'title':
                if (in_array('title', $cols, true)) {
                    return $builder->orderBy('services.title', 'ASC');
                }
                break;
            case 'newest':
            default:
                if (in_array('created_at', $cols, true)) {
                    return $builder->orderBy('services.created_at', 'DESC');
                }
                if (in_array('id', $cols, true)) {
                    return $builder->orderBy('services.id', 'DESC');
                }
                break;
        }

        return $builder;
    }

    /**
     * Apply price / capacity / event-type attribute filters to the browse query.
     * Each filter is skipped when its value is empty, and capacity/event-type
     * filters are only applied when the supporting columns/tables exist, so the
     * method is safe on older schemas.
     *
     * @param list<string>         $cols
     * @param array<string,mixed>  $filters
     */
    private function applyBrowseAttributeFilters(ServiceModel $builder, array $cols, array $filters): ServiceModel
    {
        $db = \Config\Database::connect();

        // Price range (ignore non-numeric input).
        if (in_array('price', $cols, true)) {
            $min = $filters['price_min'];
            $max = $filters['price_max'];
            if ($min !== null && $min !== '' && is_numeric($min)) {
                $builder->where('services.price >=', (float) $min);
            }
            if ($max !== null && $max !== '' && is_numeric($max)) {
                $builder->where('services.price <=', (float) $max);
            }
        }

        // Capacity: a service "fits" N guests when its min/max capacity bracket
        // covers N. NULL bounds mean "no limit", so unset services are kept.
        $guests = $filters['guests'];
        if ($guests !== null && $guests !== '' && is_numeric($guests)
            && in_array('min_capacity', $cols, true) && in_array('max_capacity', $cols, true)) {
            $g = (int) $guests;
            $builder->groupStart()
                ->where('services.min_capacity IS NULL')
                ->orWhere('services.min_capacity <=', $g)
            ->groupEnd();
            $builder->groupStart()
                ->where('services.max_capacity IS NULL')
                ->orWhere('services.max_capacity >=', $g)
            ->groupEnd();
        }

        // Event type: service must opt in via services_event_types.
        $eventType = $filters['event_type'];
        if ($eventType !== '' && $db->tableExists('services_event_types')) {
            $builder->where(
                'services.id IN (SELECT service_id FROM services_event_types WHERE event_type = ' . $db->escape($eventType) . ')',
                null,
                false
            );
        }

        // Location: free-text match against the service's stated area.
        $location = $filters['location'] ?? '';
        if ($location !== '' && in_array('service_location', $cols, true)) {
            $builder->like('services.service_location', $location);
        }

        // Date: exclude services whose vendor has marked the date unavailable.
        $date = $filters['date'] ?? '';
        if ($date !== '' && in_array('vendor_id', $cols, true) && $db->tableExists('unavailable_dates')) {
            $builder->where(
                'services.vendor_id NOT IN (SELECT vendor_id FROM unavailable_dates WHERE date = ' . $db->escape($date) . ')',
                null,
                false
            );
        }

        return $builder;
    }

    /**
     * Legacy route `/service/search` (still linked from older views). Delegates to the
     * same catalogue as browse-services; optional `event_id` scopes the add-to-basket flow.
     */
    public function search()
    {
        $this->setPreferredBasketEventFromQuery($this->request->getGet('event_id'));

        $q = $this->request->getGet('q');
        $cuisine = $this->request->getGet('cuisine');
        $category = $this->request->getGet('category');
        $subcategory = $this->request->getGet('subcategory');
        $thirdCategory = $this->request->getGet('third_category');

        $params = [];
        if ($q !== null && $q !== '') {
            $params['q'] = $q;
        }
        if ($cuisine !== null && $cuisine !== '') {
            $params['category'] = $cuisine;
        }
        if ($category !== null && $category !== '') {
            $params['category'] = $category;
        }
        if ($subcategory !== null && $subcategory !== '') {
            $params['subcategory'] = $subcategory;
        }
        if ($thirdCategory !== null && $thirdCategory !== '') {
            $params['third_category'] = $thirdCategory;
        }

        $url = '/browse-services';
        if ($params !== []) {
            $url .= '?' . http_build_query($params);
        }

        return redirect()->to($url);
    }

    /**
     * When customers browse with ?event_id=, remember it so "Add to event" can skip the picker.
     */
    /**
     * @return array{events: list<array<string,mixed>>, active: array<string,mixed>|null}
     */
    private function customerEventContextForBrowse(): array
    {
        if (!session()->has('user_id') || session()->get('role') !== 'customer') {
            return ['events' => [], 'active' => null];
        }

        $eventModel = new EventModel();
        $userId = (int) session()->get('user_id');
        $events = $eventModel->where('user_id', $userId)->orderBy('date', 'ASC')->findAll();
        $summary = new CustomerEventSummary();
        $events = $summary->enrichMany($userId, $events);
        $preferred = session()->get('preferred_basket_event_id');
        $preferredId = $preferred !== null ? (int) $preferred : null;
        $active = $summary->resolveActiveEvent($events, $preferredId);

        // Add basket item counts and estimated totals per event.
        $db = \Config\Database::connect();
        foreach ($events as &$ev) {
            $rows = $db->table('event_basket_items')
                ->select('COUNT(*) as cnt, SUM(estimated_total) as total')
                ->where('event_id', $ev['id'])
                ->where('user_id', $userId)
                ->get()->getRowArray();
            $ev['basket_count'] = (int) ($rows['cnt'] ?? 0);
            $ev['basket_total'] = (float) ($rows['total'] ?? 0);
        }
        unset($ev);

        return ['events' => $events, 'active' => $active];
    }

    private function setPreferredBasketEventFromQuery(?string $eventId): void
    {
        if ($eventId === null || $eventId === '' || !session()->has('user_id')) {
            return;
        }

        $eventModel = new EventModel();
        $event = $eventModel->find((int) $eventId);
        if (
            $event
            && isset($event['user_id'])
            && (int) $event['user_id'] === (int) session()->get('user_id')
        ) {
            session()->set('preferred_basket_event_id', (int) $eventId);
        }
    }




    /**
     * Partysmith adaptive "List your service" onboarding (single-page builder).
     *
     * This is the single, canonical service-creation flow. The view is a
     * self-contained SPA (assets/js/onboarding.js) that POSTs the assembled
     * listing to publishListing() below. (The legacy step1–6 wizard was
     * removed in the Phase 3 cleanup.)
     */
    public function listService()
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to add services.');
        }

        return view('service_create/list_your_service');
    }

    /**
     * Persist a listing built in the adaptive onboarding flow.
     *
     * Receives the front-end state as JSON (in the `payload` POST field) and
     * maps it onto the existing service schema/models, mirroring the insert
     * shapes used by saveService(). Returns JSON for the SPA to act on.
     */
    public function publishListing()
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return $this->response->setStatusCode(403)->setJSON([
                'success'  => false,
                'error'    => 'You must be signed in as a vendor to publish a listing.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $payload = json_decode((string) $this->request->getPost('payload'), true);
        if (!is_array($payload)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'  => false,
                'error'    => 'We could not read your listing. Please try again.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $title     = trim((string) ($payload['title'] ?? ''));
        $shortDesc = trim((string) ($payload['shortDesc'] ?? ''));
        $fullDesc  = trim((string) ($payload['fullDesc'] ?? ''));
        $location  = trim((string) ($payload['location'] ?? ''));
        $typeId    = (string) ($payload['typeId'] ?? '');
        $pricing   = (string) ($payload['pricing'] ?? '');

        if ($title === '' || $shortDesc === '' || $location === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'success'  => false,
                'error'    => 'Please add a title, a short description and where you are based before publishing.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        // Resolve a top-level category from the chosen supplier type (best match by name).
        $categoryId = $this->resolveCategoryForType($typeId);
        if ($categoryId === null) {
            return $this->response->setStatusCode(422)->setJSON([
                'success'  => false,
                'error'    => 'No service categories are set up yet — please contact support.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        // Map the plain-language pricing model onto event type + structured pricing type.
        $eventType   = ($pricing === 'pitch') ? 'public' : 'private';
        $pricingType = [
            'packages' => 'tiered_packages_pricing',
            'fixed'    => 'tiered_packages_pricing',
            'guest'    => 'guest_based_pricing',
            'duration' => 'custom_duration_pricing',
            'quantity' => 'quantity_based_pricing',
            'quote'    => 'custom_quote',
        ][$pricing] ?? 'custom_quote';

        $reqs       = is_array($payload['reqs'] ?? null) ? $payload['reqs'] : [];
        $startPrice = $this->toNumber($payload['startPrice'] ?? '');
        $setupMap   = ['30min' => 30, '1hr' => 60, '2hr' => 150, 'halfday' => 240, 'fullday' => 480, 'multiday' => 1440];
        $indoor     = in_array($payload['indoorOutdoor'] ?? 'both', ['indoor', 'outdoor', 'both'], true)
            ? $payload['indoorOutdoor'] : 'both';
        $footW = trim((string) ($payload['footW'] ?? ''));
        $footD = trim((string) ($payload['footD'] ?? ''));
        $space = ($footW !== '' && $footD !== '') ? ($footW . 'm x ' . $footD . 'm') : null;

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            /* ---- services row (incl. capacity / logistics / requirements) ---- */
            $serviceModel = new ServiceModel();
            $serviceInsert = [
                'vendor_id'               => session('user_id'),
                'title'                   => $title,
                'short_description'       => $shortDesc,
                'description'             => $fullDesc !== '' ? $fullDesc : $shortDesc,
                'category_id'             => $categoryId,
                'min_capacity'            => ((int) ($payload['capMin'] ?? 0)) ?: null,
                'max_capacity'            => ((int) ($payload['capMax'] ?? 0)) ?: null,
                'setup_minutes'           => $setupMap[$payload['setupTime'] ?? ''] ?? null,
                'min_notice_days'         => (($payload['leadTime'] ?? '') !== '') ? (int) $payload['leadTime'] : null,
                'space_required'          => $space,
                'indoor_outdoor'          => $indoor,
                'power_required'          => in_array('power', $reqs, true) ? 1 : 0,
                'water_required'          => in_array('water', $reqs, true) ? 1 : 0,
                'vehicle_access_required' => in_array('vehicle', $reqs, true) ? 1 : 0,
                'equipment_provided'      => in_array('own_equip', $reqs, true) ? 1 : 0,
            ];
            if ($startPrice !== null && $startPrice > 0) {
                $serviceInsert['price'] = $startPrice;
            }

            $serviceId = $serviceModel->insert($serviceInsert);
            if (!$serviceId) {
                throw new \Exception('Could not create the service record.');
            }

            /* ---- tags ---- */
            $tags = is_array($payload['tags'] ?? null) ? $payload['tags'] : [];
            if ($tags) {
                $tagsModel        = new TagsModel();
                $serviceTagsModel = new ServiceTagsModel();
                foreach ($tags as $tagName) {
                    $tagName = trim((string) $tagName);
                    if ($tagName === '') {
                        continue;
                    }
                    $existing = $tagsModel->where('name', $tagName)->first();
                    $tagId    = $existing ? $existing['id'] : $tagsModel->insert(['name' => $tagName]);
                    if ($tagId) {
                        $serviceTagsModel->insert(['service_id' => $serviceId, 'tag_id' => $tagId]);
                    }
                }
            }

            /* ---- event type + private pricing row ---- */
            $serviceEventTypeModel = new ServiceEventTypeModel();
            if (!$serviceEventTypeModel->insert(['service_id' => $serviceId, 'event_type' => $eventType])) {
                throw new \Exception('Could not save the event type.');
            }

            $privateEventPricingId = null;
            if ($eventType === 'private') {
                $servicePrivatePricingModel = new ServicePrivatePricingModel();
                $privateEventPricingId = $servicePrivatePricingModel->insert([
                    'service_id'   => $serviceId,
                    'pricing_type' => $pricingType,
                ]);
                if (!$privateEventPricingId) {
                    throw new \Exception('Could not save the pricing type.');
                }
            }

            /* ---- structured pricing rows ---- */
            if ($eventType === 'public') {
                // Event pitch fee → public event pricing.
                if ($startPrice !== null && $startPrice > 0) {
                    $maxAttendance = ((int) ($payload['capMax'] ?? 0)) ?: 1000;
                    $commission    = $this->toNumber($payload['pctTakings'] ?? '');
                    $ok = $db->table('services_public_event_pricing')->insert([
                        'service_id'            => $serviceId,
                        'commission_percentage' => $commission,
                        'min_attendance'        => 1,
                        'max_attendance'        => $maxAttendance,
                        'max_pitch_fee'         => $startPrice,
                    ]);
                    if (!$ok) {
                        throw new \Exception('Could not save pitch pricing.');
                    }
                }
            } elseif ($pricingType === 'tiered_packages_pricing') {
                $tieredModel = new ServiceTieredPackagesPricingModel();
                $rows = [];
                if ($pricing === 'packages' && is_array($payload['tiers'] ?? null)) {
                    foreach ($payload['tiers'] as $tier) {
                        $name  = trim((string) ($tier['name'] ?? ''));
                        $price = $this->toNumber($tier['price'] ?? '');
                        if ($name === '' || $price === null || $price <= 0) {
                            continue;
                        }
                        $rows[] = ['name' => $name, 'desc' => $name . ' package', 'price' => $price];
                    }
                }
                if (!$rows && $startPrice !== null && $startPrice > 0) {
                    // "One flat price" → a single package.
                    $rows[] = ['name' => 'Standard', 'desc' => 'Flat-rate booking', 'price' => $startPrice];
                }
                foreach ($rows as $r) {
                    $tieredModel->insert([
                        'service_id'               => $serviceId,
                        'private_event_pricing_id' => $privateEventPricingId,
                        'package_name'             => $r['name'],
                        'package_description'      => $r['desc'],
                        'package_price'            => $r['price'],
                    ]);
                }
            } elseif ($pricingType === 'guest_based_pricing' && $startPrice !== null && $startPrice > 0) {
                $minGuest = max(1, (int) ($payload['minGuests'] ?? 0) ?: 1);
                $maxCap   = (int) ($payload['capMax'] ?? 0);
                $maxGuest = $maxCap > $minGuest ? $maxCap : max($minGuest, 500);
                (new ServiceGuestBasedPricingModel())->insert([
                    'service_id'               => $serviceId,
                    'private_event_pricing_id' => $privateEventPricingId,
                    'min_guest'                => $minGuest,
                    'max_guest'                => $maxGuest,
                    'guest_price'              => $startPrice,
                ]);
            } elseif ($pricingType === 'custom_duration_pricing' && $startPrice !== null && $startPrice > 0) {
                $unit = ($payload['durationUnit'] ?? 'hour') === 'day' ? 'day' : 'hour';
                (new ServiceCustomDurationPricingModel())->insert([
                    'service_id'               => $serviceId,
                    'private_event_pricing_id' => $privateEventPricingId,
                    'duration_type'            => $unit,
                    'duration'                 => max(1, (int) ($payload['minDuration'] ?? 1)),
                    'price'                    => $startPrice,
                ]);
            } elseif ($pricingType === 'quantity_based_pricing' && $startPrice !== null && $startPrice > 0) {
                (new ServiceQuantityPricingModel())->insert([
                    'service_id'               => $serviceId,
                    'private_event_pricing_id' => $privateEventPricingId,
                    'unit_price'               => $startPrice,
                    'min_quantity'             => max(1, (int) ($payload['minQty'] ?? 1)),
                    'max_quantity'             => null,
                    'unit_label'               => trim((string) ($payload['unitLabel'] ?? '')) ?: 'items',
                ]);
            }
            // 'custom_quote' intentionally inserts no pricing rows (price on request).

            /* ---- coverage / location ---- */
            $nationwide = !empty($payload['nationwide']);
            (new ServiceLocationModel())->insert([
                'service_id'           => $serviceId,
                'fulfillment_type'     => 'in_person',
                'service_location'     => $location,
                'no_travel_limit'      => $nationwide ? 1 : 0,
                'all_travel_included'  => 0,
                'free_coverage_radius' => $nationwide ? null : (((int) ($payload['freeRadius'] ?? 0)) ?: null),
                'paid_coverage_radius' => $nationwide ? null : (((int) ($payload['radius'] ?? 0)) ?: null),
                'travel_fee_per_km'    => $nationwide ? null : $this->toNumber($payload['travelFee'] ?? ''),
            ]);

            $db->transCommit();

            return $this->response->setJSON([
                'success'  => true,
                'service_id' => $serviceId,
                'viewUrl'  => base_url('service/view/' . $serviceId),
                'csrfHash' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }

            return $this->response->setStatusCode(500)->setJSON([
                'success'  => false,
                'error'    => 'Sorry — we could not publish your listing: ' . $e->getMessage(),
                'csrfHash' => csrf_hash(),
            ]);
        }
    }

    /**
     * Best-effort map from an onboarding supplier-type id to a top-level
     * category id (matched by keyword against category names), falling back
     * to the first available root category.
     */
    private function resolveCategoryForType(string $typeId): ?int
    {
        $keywords = [
            'dj' => 'entertainment', 'band' => 'entertainment', 'magician' => 'entertainment',
            'dancefloor' => 'entertainment', 'av' => 'entertainment', 'inflatable' => 'entertainment',
            'workshop' => 'entertainment',
            'caterer' => 'catering', 'foodtruck' => 'catering', 'bar' => 'catering',
            'photographer' => 'photo', 'videographer' => 'photo', 'booth' => 'photo',
            'florist' => 'flower', 'hire' => 'flower',
            'marquee' => 'venue',
            'transport' => 'transport',
            'planner' => 'planning', 'security' => 'planning',
        ];

        $categoryModel = new CategoryModel();
        $roots = $categoryModel->getRootCategories();
        if (!$roots) {
            return null;
        }

        $keyword = $keywords[$typeId] ?? '';
        if ($keyword !== '') {
            foreach ($roots as $root) {
                if (stripos((string) ($root['name'] ?? ''), $keyword) !== false) {
                    return (int) $root['id'];
                }
            }
        }

        return (int) $roots[0]['id'];
    }

    /**
     * Parse a user-entered money/number string (e.g. "2,500" or "3.50") to a
     * float, or null when blank/invalid.
     */
    private function toNumber($value): ?float
    {
        $clean = preg_replace('/[^0-9.]/', '', str_replace(',', '', (string) $value));
        if ($clean === '' || !is_numeric($clean)) {
            return null;
        }

        return (float) $clean;
    }



    public function deleteImage($formId)
    {

        log_message('debug', 'Form ID received: ' . $formId);


        $uploadedImages = session()->get('uploaded_images');
        log_message('debug', 'Uploaded images in session: ' . json_encode($uploadedImages));

        foreach ($uploadedImages as $key => $image) {
            log_message('debug', 'Checking image: ' . json_encode($image));

            if ($image['formId'] === $formId) {
                log_message('debug', 'Matching image found: ' . json_encode($image));

                $filePath = ROOTPATH . 'public/' . $image['image_path']; // Correct path

                // Check if file exists
                if (file_exists($filePath)) {

                    unlink($filePath); // Delete file
                    log_message('debug', 'File deleted: ' . $filePath);
                } else {
                    log_message('error', 'File not found: ' . $filePath);
                }
                unset($uploadedImages[$key]); // Remove from session
                session()->set('uploaded_images', array_values($uploadedImages)); // Re-index session

                return $this->response->setJSON(['success' => true]);
            }
        }

        log_message('error', 'Image not found in session for formId: ' . $formId);
        return $this->response->setJSON(['success' => false, 'error' => 'Image not found']);
    }





    public function update($id = null)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to edit services.');
        }

        $serviceModel = new ServiceModel();
        $service = $serviceModel->getServiceWithImages($id);

        if (!$service || $service['vendor_id'] != session()->get('user_id')) {
            return redirect()->to('/profile/services')->with('error', 'Service not found.');
        }

        $categoryModel = new CategoryModel();
        $serviceImageModel = new ServiceImageModel();
        $servicePrivatePricingModel = new ServicePrivatePricingModel();
        $guestPricingModel = new ServiceGuestBasedPricingModel();
        $durationPricingModel = new ServiceCustomDurationPricingModel();
        $tieredPricingModel = new ServiceTieredPackagesPricingModel();
        $quantityPricingModel = new ServiceQuantityPricingModel();
        $serviceLocationModel = new ServiceLocationModel();
        $optionalExtrasModel = new ServiceOptionalExtrasModel();
        $cancellationModel = new ServiceCancellationPolicyModel();
        $eventTypeModel = new ServiceEventTypeModel();

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'title' => 'required|min_length[3]|max_length[255]',
                'short_description' => 'required|max_length[200]',
                'description' => 'required',
                // Capacity, logistics & requirements (all optional, additive).
                'min_capacity'      => 'permit_empty|is_natural',
                'max_capacity'      => 'permit_empty|is_natural',
                'setup_minutes'     => 'permit_empty|is_natural',
                'breakdown_minutes' => 'permit_empty|is_natural',
                'min_notice_days'   => 'permit_empty|is_natural',
                'space_required'    => 'permit_empty|max_length[120]',
                'indoor_outdoor'    => 'permit_empty|in_list[indoor,outdoor,both]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            // Capacity sanity: max must not be below min when both are given.
            $minCap = $this->request->getPost('min_capacity');
            $maxCap = $this->request->getPost('max_capacity');
            if ($minCap !== null && $minCap !== '' && $maxCap !== null && $maxCap !== ''
                && (int) $maxCap < (int) $minCap) {
                return redirect()->back()->withInput()
                    ->with('errors', ['max_capacity' => 'Maximum capacity cannot be lower than minimum capacity.']);
            }

            $catErr = $categoryModel->validateAssignment(
                $this->request->getPost('category_id'),
                $this->request->getPost('subcategory_id'),
                $this->request->getPost('third_category_id')
            );
            if ($catErr !== null) {
                return redirect()->back()->withInput()->with('errors', ['category' => $catErr]);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            $serviceData = [
                'title' => $this->request->getPost('title'),
                'short_description' => $this->request->getPost('short_description'),
                'description' => $this->request->getPost('description'),
                'price' => $this->request->getPost('price') ?: 0,
                'category_id' => $this->request->getPost('category_id') ?: null,
                'subcategory_id' => $this->request->getPost('subcategory_id') ?: null,
                'third_category_id' => $this->request->getPost('third_category_id') ?: null,
                'service_tags' => $this->request->getPost('service_tags'),
                // Capacity, logistics & on-site requirements.
                'min_capacity'            => $this->request->getPost('min_capacity') !== '' ? ($this->request->getPost('min_capacity') ?? null) : null,
                'max_capacity'            => $this->request->getPost('max_capacity') !== '' ? ($this->request->getPost('max_capacity') ?? null) : null,
                'setup_minutes'           => $this->request->getPost('setup_minutes') !== '' ? ($this->request->getPost('setup_minutes') ?? null) : null,
                'breakdown_minutes'       => $this->request->getPost('breakdown_minutes') !== '' ? ($this->request->getPost('breakdown_minutes') ?? null) : null,
                'min_notice_days'         => $this->request->getPost('min_notice_days') !== '' ? ($this->request->getPost('min_notice_days') ?? null) : null,
                'space_required'          => $this->request->getPost('space_required') ?: null,
                'indoor_outdoor'          => in_array($this->request->getPost('indoor_outdoor'), ['indoor', 'outdoor', 'both'], true) ? $this->request->getPost('indoor_outdoor') : 'both',
                'power_required'          => $this->request->getPost('power_required') ? 1 : 0,
                'water_required'          => $this->request->getPost('water_required') ? 1 : 0,
                'vehicle_access_required' => $this->request->getPost('vehicle_access_required') ? 1 : 0,
                'equipment_provided'      => $this->request->getPost('equipment_provided') ? 1 : 0,
            ];
            $serviceModel->update($id, $serviceData);

            // Handle image uploads
            $imageFiles = $this->request->getFiles();
            if (!empty($imageFiles['images'])) {
                foreach ($imageFiles['images'] as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move(FCPATH . 'uploads/services', $newName);

                        $thumbPath = 'uploads/services/thumbnails/thumb_' . $newName;
                        $imgService = \Config\Services::image();
                        $imgService->withFile(FCPATH . 'uploads/services/' . $newName)
                            ->resize(300, 200, true, 'width')
                            ->save(FCPATH . $thumbPath);

                        $serviceImageModel->insert([
                            'service_id' => $id,
                            'image_path' => 'uploads/services/' . $newName,
                            'thumbnail_path' => $thumbPath,
                            'is_primary' => 0,
                        ]);
                    }
                }
            }

            // Update location
            $location = $serviceLocationModel->where('service_id', $id)->first();
            $locationData = [
                'fulfillment_type' => $this->request->getPost('fulfillment_type') ?: 'in_person',
                'postal_fee' => $this->request->getPost('postal_fee') !== '' && $this->request->getPost('postal_fee') !== null ? $this->request->getPost('postal_fee') : null,
                'free_postage_above' => $this->request->getPost('free_postage_above') ?: null,
                'delivery_lead_time_days' => $this->request->getPost('delivery_lead_time_days') ? (int) $this->request->getPost('delivery_lead_time_days') : null,
                'service_location' => $this->request->getPost('service_location'),
                'latitude' => $this->request->getPost('latitude') ?: null,
                'longitude' => $this->request->getPost('longitude') ?: null,
                'free_coverage_radius' => $this->request->getPost('free_coverage_radius') ?: null,
                'paid_coverage_radius' => $this->request->getPost('paid_coverage_radius') ?: null,
                'travel_fee_per_km' => $this->request->getPost('travel_fee_per_km') ?: null,
                'all_travel_included' => !empty($this->request->getPost('all_travel_included')) ? 1 : 0,
                'no_travel_limit' => !empty($this->request->getPost('no_travel_limit')) ? 1 : 0,
            ];
            if ($location) {
                $serviceLocationModel->update($location['id'], $locationData);
            } else {
                $locationData['service_id'] = $id;
                $serviceLocationModel->insert($locationData);
            }

            // Update cancellation policy
            $cancellation = $cancellationModel->where('service_id', $id)->first();
            $policyText = $this->request->getPost('cancellation_policy');
            if ($cancellation) {
                $cancellationModel->update($cancellation['id'], ['cancellation_policy' => $policyText]);
            } elseif (!empty($policyText)) {
                $cancellationModel->insert(['service_id' => $id, 'cancellation_policy' => $policyText]);
            }

            // Update optional extras — delete all and re-insert
            $optionalExtrasModel->where('service_id', $id)->delete();
            $extraNames        = $this->request->getPost('extra_name') ?? [];
            $extraPrices       = $this->request->getPost('extra_price') ?? [];
            $extraDescs        = $this->request->getPost('extra_description') ?? [];
            $extraPricingTypes = $this->request->getPost('extra_pricing_type') ?? [];
            $extraUnitLabels   = $this->request->getPost('extra_unit_label') ?? [];
            $extraMinQtys      = $this->request->getPost('extra_min_quantity') ?? [];
            $extraMaxQtys      = $this->request->getPost('extra_max_quantity') ?? [];
            foreach ($extraNames as $i => $name) {
                $name = trim((string) ($name ?? ''));
                if ($name === '') continue;
                $pricingType = in_array($extraPricingTypes[$i] ?? '', ['flat', 'per_item'], true)
                    ? $extraPricingTypes[$i] : 'flat';
                $optionalExtrasModel->insert([
                    'service_id'   => $id,
                    'name'         => $name,
                    'price'        => (float) ($extraPrices[$i] ?? 0),
                    'description'  => trim($extraDescs[$i] ?? ''),
                    'pricing_type' => $pricingType,
                    'unit_label'   => trim($extraUnitLabels[$i] ?? '') ?: null,
                    'min_quantity' => ($pricingType === 'per_item' && !empty($extraMinQtys[$i])) ? max(1, (int) $extraMinQtys[$i]) : null,
                    'max_quantity' => ($pricingType === 'per_item' && !empty($extraMaxQtys[$i])) ? max(1, (int) $extraMaxQtys[$i]) : null,
                ]);
            }

            $privatePricing = $servicePrivatePricingModel->where('service_id', $id)->first();
            if (($privatePricing['pricing_type'] ?? '') === 'custom_duration_pricing') {
                $starts = $this->request->getPost('timeblock_start') ?? [];
                $ends = $this->request->getPost('timeblock_end') ?? [];
                $prices = $this->request->getPost('timeblock_price') ?? [];
                $blocks = [];
                if (is_array($starts)) {
                    foreach ($starts as $i => $start) {
                        $blocks[] = [
                            'start_time' => $start,
                            'end_time' => $ends[$i] ?? '',
                            'price' => $prices[$i] ?? '',
                        ];
                    }
                }
                $this->saveTimeBlocks((int) $id, $blocks);
            }

            if (($privatePricing['pricing_type'] ?? '') === 'quantity_based_pricing') {
                $unitPrice = $this->request->getPost('quantity_unit_price');
                $minQty = $this->request->getPost('quantity_min_quantity');
                if ($unitPrice !== null && $unitPrice !== '' && $minQty !== null && $minQty !== '') {
                    $minQty = max(1, (int) $minQty);
                    $maxRaw = $this->request->getPost('quantity_max_quantity');
                    $maxQty = ($maxRaw !== null && $maxRaw !== '') ? max($minQty, (int) $maxRaw) : null;
                    $unitLabel = trim((string) ($this->request->getPost('quantity_unit_label') ?? 'items')) ?: 'items';
                    $existing = $quantityPricingModel->where('service_id', $id)->first();
                    $row = [
                        'service_id' => $id,
                        'private_event_pricing_id' => $privatePricing['id'] ?? null,
                        'unit_price' => (float) $unitPrice,
                        'min_quantity' => $minQty,
                        'max_quantity' => $maxQty,
                        'unit_label' => $unitLabel,
                    ];
                    if ($existing) {
                        $quantityPricingModel->update($existing['id'], $row);
                    } else {
                        $quantityPricingModel->insert($row);
                    }
                }
            }

            $db->transComplete();

            return redirect()->to('/service/edit/' . $id)->with('success', 'Service updated successfully.');
        }

        // Load all service data for the edit form
        $images = $serviceImageModel->where('service_id', $id)->findAll();
        $location = $serviceLocationModel->where('service_id', $id)->first();
        $optionalExtras = $optionalExtrasModel->where('service_id', $id)->findAll();
        $cancellation = $cancellationModel->where('service_id', $id)->first();
        $eventTypes = $eventTypeModel->where('service_id', $id)->findAll();
        $privatePricing = $servicePrivatePricingModel->where('service_id', $id)->first();

        $guestPricing = [];
        $durationPricing = [];
        $tieredPackages = [];
        $quantityPricing = null;
        $timeBlocks = [];
        if ($privatePricing) {
            $guestPricing = $guestPricingModel->where('private_event_pricing_id', $privatePricing['id'])->findAll();
            $durationPricing = $durationPricingModel->where('private_event_pricing_id', $privatePricing['id'])->findAll();
            $tieredPackages = $tieredPricingModel->where('private_event_pricing_id', $privatePricing['id'])->findAll();
            $quantityPricing = $quantityPricingModel->where('private_event_pricing_id', $privatePricing['id'])->first();
            if (($privatePricing['pricing_type'] ?? '') === 'custom_duration_pricing') {
                $timeBlocks = (new ServiceTimeBlockModel())->getByServiceId((int) $id);
            }
        }

        $categories = $this->buildCategoryTree();

        $data = [
            'service' => $service,
            'categories' => $categories,
            'images' => $images,
            'location' => $location,
            'optionalExtras' => $optionalExtras,
            'cancellation' => $cancellation,
            'eventTypes' => $eventTypes,
            'privatePricing' => $privatePricing,
            'guestPricing' => $guestPricing,
            'durationPricing' => $durationPricing,
            'tieredPackages' => $tieredPackages,
            'quantityPricing' => $quantityPricing,
            'timeBlocks' => $timeBlocks,
        ];

        return view('service_edit', $data);
    }
    private function buildCategoryTree($parentId = null, $level = 0)
    {
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->where('parent_id', $parentId)->findAll();
        $result = [];

        foreach ($categories as $category) {
            $category['level'] = $level;
            $result[] = $category;
            $children = $this->buildCategoryTree($category['id'], $level + 1);
            $result = array_merge($result, $children);
        }

        return $result;
    }
    public function view($id)
    {
        // Initialize necessary models
        $serviceModel = new ServiceModel();
        $categoryModel = new CategoryModel();
        $serviceImageModel = new ServiceImageModel();
        $serviceTagsModel = new ServiceTagsModel();
        $tagsModel = new TagsModel();
        $serviceEventTypeModel = new ServiceEventTypeModel();
        $servicePrivatePricingModel = new ServicePrivatePricingModel();
        $guestPricingModel = new ServiceGuestBasedPricingModel();
        $durationPricingModel = new ServiceCustomDurationPricingModel();
        $tieredPricingModel = new ServiceTieredPackagesPricingModel();
        $quantityPricingModel = new ServiceQuantityPricingModel();
        $serviceLocationModel = new ServiceLocationModel();
        $optionalExtrasModel = new ServiceOptionalExtrasModel();
        $timeBlockModel = new ServiceTimeBlockModel();

        // Fetch the service details
        $service = $serviceModel->find($id);

        if (!$service) {
            return redirect()->to('/browse-services')->with('error', 'Service not found.');
        }

        // Fetch associated images
        $images = $serviceImageModel->where('service_id', $id)->findAll();

        // Fetch associated tags


        // Fetch associated event types
        $eventTypes = $serviceEventTypeModel->where('service_id', $id)->findAll();

        // Fetch private pricing type
        $privatePricing = $servicePrivatePricingModel->where('service_id', $id)->first();
        $privatePricingId = $privatePricing['id'] ?? null;

        // Fetch guest-based pricing
        $guestPricing = $privatePricingId
            ? $guestPricingModel->where('private_event_pricing_id', $privatePricingId)->findAll()
            : [];

        // Fetch duration-based pricing
        $durationPricing = $privatePricingId
            ? $durationPricingModel->where('private_event_pricing_id', $privatePricingId)->findAll()
            : [];

        // Fetch tiered packages
        $tieredPackages = $privatePricingId
            ? $tieredPricingModel->where('private_event_pricing_id', $privatePricingId)->findAll()
            : [];

        $quantityTiers = $privatePricingId
            ? $quantityPricingModel->where('private_event_pricing_id', $privatePricingId)->orderBy('min_quantity', 'ASC')->findAll()
            : [];
        $quantityPricing = $quantityTiers[0] ?? null;
        $showQuantity = ($privatePricing['pricing_type'] ?? '') === 'quantity_based_pricing' && $quantityTiers !== [];
        $timeBlocks = ($privatePricing['pricing_type'] ?? '') === 'custom_duration_pricing'
            ? $timeBlockModel->getByServiceId((int) $id)
            : [];

        // Fetch service location details
        $location = $serviceLocationModel->where('service_id', $id)->first();

        // Fetch optional extras
        $optionalExtras = $optionalExtrasModel->where('service_id', $id)->findAll();

        // Fetch cancellation policy
        $cancellationModel = new ServiceCancellationPolicyModel();
        $cancellationRecord = $cancellationModel->where('service_id', $id)->first();
        $cancellationPolicy = $cancellationRecord['policy'] ?? '';

        // Build category names
        $category_names = [
            'main' => 'Not Selected',
            'sub' => '',
            'third' => '',
        ];
        if (! empty($service['category_id'])) {
            $mainCategory = $categoryModel->find($service['category_id']);
            $category_names['main'] = $mainCategory['name'] ?? 'Not Selected';
        }
        if (! empty($service['subcategory_id'])) {
            $subCategory = $categoryModel->find($service['subcategory_id']);
            $category_names['sub'] = $subCategory['name'] ?? '';
        }
        if (! empty($service['third_category_id'])) {
            $thirdCategory = $categoryModel->find($service['third_category_id']);
            $category_names['third'] = $thirdCategory['name'] ?? '';
        }

        // Compile data for the view
        $messageVendorEligible = false;
        $messageVendorUrl = null;
        if (session()->has('user_id') && session()->get('role') === 'customer') {
            $bookingItemModel = new BookingItemModel();
            if ($bookingItemModel->customerHasEligibleBookingForService((int) session()->get('user_id'), (int) $id)) {
                $messageVendorEligible = true;
                $messageVendorUrl = base_url('profile/messages/start/' . $id);
            }
        }

        // Load vendor host profile
        $vendorUser = (new UserModel())->find($service['vendor_id']);
        $vendorProfile = null;
        if ($vendorUser) {
            $playsArr = [];
            if (!empty($vendorUser['host_plays'])) {
                $decoded = json_decode($vendorUser['host_plays'], true);
                $playsArr = is_array($decoded) ? $decoded : [];
            }
            $memberSince = !empty($vendorUser['created_at']) ? (int) date('Y', strtotime($vendorUser['created_at'])) : null;
            $vendorProfile = [
                'name'       => $vendorUser['name'],
                'tagline'    => $vendorUser['host_tagline'] ?? '',
                'bio'        => $vendorUser['host_bio'] ?? '',
                'quote'      => $vendorUser['host_quote'] ?? '',
                'plays'      => $playsArr,
                'photo_path' => $vendorUser['host_photo_path'] ?? '',
                'since'      => $memberSince,
            ];
        }

        // Reviews: vendor-wide rating + this service's written reviews.
        $reviewModel    = new ReviewModel();
        $vendorRating   = $reviewModel->vendorRatingSummary((int) $service['vendor_id']);
        $serviceReviews = $reviewModel->serviceReviews((int) $id, 6);

        // Load customer events for the inline event selector and guest-aware pricing.
        $customerEvents = [];
        $activeEvent    = null;
        if (session()->has('user_id') && session()->get('role') === 'customer') {
            $eventModel     = new EventModel();
            $customerEvents = $eventModel
                ->where('user_id', session()->get('user_id'))
                ->where('status', 'active')
                ->orderBy('date', 'ASC')
                ->findAll();
            $preferredId = (int) (session()->get('preferred_basket_event_id') ?? 0);
            foreach ($customerEvents as $ev) {
                if ((int) $ev['id'] === $preferredId) {
                    $activeEvent = $ev;
                    break;
                }
            }
            if ($activeEvent === null && ! empty($customerEvents)) {
                $activeEvent = $customerEvents[0];
            }
        }

        $data = [
            'service' => $service,
            'images' => $images,
            'eventTypes' => $eventTypes,
            'privatePricing' => $privatePricing,
            'guestPricing' => $guestPricing,
            'durationPricing' => $durationPricing,
            'tieredPackages' => $tieredPackages,
            'quantityPricing' => $quantityPricing,
            'quantityTiers' => $quantityTiers,
            'timeBlocks' => $timeBlocks,
            'showQuantity' => $showQuantity,
            'location' => $location,
            'optional_extras' => $optionalExtras,
            'cancellation_policy' => $cancellationPolicy,
            'category_names' => $category_names,
            'message_vendor_eligible' => $messageVendorEligible,
            'message_vendor_url' => $messageVendorUrl,
            'preview_event_id' => session()->get('preferred_basket_event_id'),
            'vendor_profile' => $vendorProfile,
            'vendor_id' => (int) $service['vendor_id'],
            'vendor_rating' => $vendorRating,
            'service_reviews' => $serviceReviews,
            'customerEvents' => $customerEvents,
            'activeEvent'    => $activeEvent,
        ];

        // Render the view
        return view('service_view', $data);
    }

    /**
     * Public vendor profile / storefront — host header plus a grid of the
     * vendor's active services. Linked from the "Meet your host" card on every
     * service view.
     */
    public function vendorProfile($id)
    {
        $userModel  = new UserModel();
        $vendorUser = $userModel->find((int) $id);

        if (! $vendorUser || ($vendorUser['role'] ?? '') !== 'vendor') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Vendor not found.');
        }

        $playsArr = [];
        if (! empty($vendorUser['host_plays'])) {
            $decoded  = json_decode($vendorUser['host_plays'], true);
            $playsArr = is_array($decoded) ? $decoded : [];
        }
        $memberSince = ! empty($vendorUser['created_at']) ? (int) date('Y', strtotime($vendorUser['created_at'])) : null;

        $vendorProfile = [
            'name'       => $vendorUser['name'],
            'tagline'    => $vendorUser['host_tagline'] ?? '',
            'bio'        => $vendorUser['host_bio'] ?? '',
            'quote'      => $vendorUser['host_quote'] ?? '',
            'plays'      => $playsArr,
            'photo_path' => $vendorUser['host_photo_path'] ?? '',
            'since'      => $memberSince,
        ];

        $serviceModel      = new ServiceModel();
        $serviceImageModel = new ServiceImageModel();
        $categoryModel     = new CategoryModel();

        $services = $serviceModel
            ->where('vendor_id', (int) $id)
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->findAll();

        foreach ($services as &$service) {
            $service['images'] = $serviceImageModel
                ->where(['service_id' => $service['id'], 'is_primary' => 1])
                ->findAll();
            $service['category_name'] = $categoryModel->getServiceCategoryLabel($service);
        }
        unset($service);

        return view('vendor_profile_public', [
            'vendor_profile' => $vendorProfile,
            'services'       => $services,
        ]);
    }

    private function validateSessionData(string ...$keys)
    {
        // Every requested step must be present and non-empty. Previously this only
        // accepted a single key, so the review page's six-argument call silently
        // checked just the first one and let users reach review with missing steps
        // (e.g. no step6), causing a fatal "array offset on null".
        foreach ($keys as $key) {
            if (!session()->has($key) || empty(session($key))) {
                return false;
            }
        }

        return true;
    }

    public function removeOptionalExtra()
    {
        $input = $this->request->getJSON();
        $extraName = $input->extra_name ?? null;

        if (!$extraName) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid optional extra name.']);
        }

        // Get the current session data
        $optionalExtras = session()->get('optional_extras') ?? [];

        // Filter out the extra by name
        $updatedExtras = array_filter($optionalExtras, function ($extra) use ($extraName) {
            return $extra['name'] !== $extraName;
        });

        // Save updated session data
        session()->set('optional_extras', array_values($updatedExtras));

        return $this->response->setJSON(['success' => true]);
    }















    public function deactivate($id = null)
    {
        try {
            if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'You are not authorized to deactivate services.']);
            }

            $serviceModel = new ServiceModel();
            $service = $serviceModel->find($id);

            if (!$service || $service['vendor_id'] != session()->get('user_id')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Service not found or you are not authorized to deactivate it.']);
            }

            if ($serviceModel->update($id, ['status' => 'inactive'])) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Service deactivated successfully.']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to deactivate the service.']);
            }
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'An error occurred while deactivating the service.']);
        }
    }

    public function reactivate($id = null)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'You are not authorized to reactivate services.']);
        }

        $serviceModel = new ServiceModel();
        $service = $serviceModel->find($id);

        if (!$service || $service['vendor_id'] != session()->get('user_id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Service not found or you are not authorized to reactivate it.']);
        }

        if ($serviceModel->update($id, ['status' => 'active'])) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Service reactivated successfully.']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to reactivate the service.']);
        }
    }


    public function toggleStatus($id = null)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/profile/services')->with('error', 'Unauthorized.');
        }

        $serviceModel = new ServiceModel();
        $service = $serviceModel->find($id);

        if (!$service || $service['vendor_id'] != session()->get('user_id')) {
            return redirect()->to('/profile/services')->with('error', 'Service not found.');
        }

        $newStatus = ($service['status'] === 'active') ? 'inactive' : 'active';
        $serviceModel->update($id, ['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'activated' : 'deactivated';
        return redirect()->to('/profile/services')->with('success', '"' . $service['title'] . '" has been ' . $label . '.');
    }

    public function delete($id = null)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/profile')->with('error', 'Unauthorized access.');
        }

        $serviceModel = new ServiceModel();
        $service = $serviceModel->find($id);

        if (!$service || $service['vendor_id'] != session()->get('user_id')) {
            return redirect()->to('/profile')->with('error', 'Service not found or unauthorized access.');
        }

        try {
            // Soft delete the service
            if ($serviceModel->update($id, ['deleted_at' => date('Y-m-d H:i:s')])) {
                $this->deleteServiceImages($id);
                return redirect()->to('/profile#services')->with('success', 'Service deleted successfully.');
            } else {
                return redirect()->to('/profile')->with('error', 'Failed to delete the service.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Service deletion error: ' . $e->getMessage());
            return redirect()->to('/profile')->with('error', 'An error occurred while deleting the service.');
        }
    }





    /*public function deleteImage($imageId)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized.']);
        }

        $serviceImageModel = new \App\Models\ServiceImageModel();
        $image = $serviceImageModel->find($imageId);

        if (!$image) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Image not found.']);
        }

        $serviceId = $image['service_id'];
        $serviceModel = new \App\Models\ServiceModel();
        $service = $serviceModel->find($serviceId);

        if ($service['vendor_id'] != session()->get('user_id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized to delete this image.']);
        }

        // Delete the image files
        unlink(ROOTPATH . 'public/' . $image['image_path']);
        unlink(ROOTPATH . 'public/' . $image['thumbnail_path']);

        // Delete the image record from the database
        $serviceImageModel->delete($imageId);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Image deleted successfully.']);
    }*/

    public function setPrimaryImage($imageId)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $serviceImageModel = new ServiceImageModel();
        $image = $serviceImageModel->find($imageId);

        if (!$image) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Image not found']);
        }

        // Unset previous primary image
        $serviceImageModel->where('service_id', $image['service_id'])->set(['is_primary' => 0])->update();

        // Set the selected image as primary
        if ($serviceImageModel->update($imageId, ['is_primary' => 1])) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Primary image set successfully']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to set primary image']);
        }
    }



    private function deleteServiceImages($serviceId)
    {
        $serviceImageModel = new ServiceImageModel();
        $images = $serviceImageModel->where('service_id', $serviceId)->findAll();

        foreach ($images as $image) {
            // Delete images from filesystem
            @unlink(ROOTPATH . 'public/' . $image['image_path']);
            @unlink(ROOTPATH . 'public/' . $image['thumbnail_path']);

            // Remove images from the database
            $serviceImageModel->delete($image['id']);
        }
    }



    public function bookService()
    {
        $bookingModel = new BookingModel();
        $serviceAvailabilityModel = new ServiceAvailabilityModel();

        // Get the necessary data from the POST request
        $serviceId = $this->request->getPost('service_id');
        $eventId = $this->request->getPost('event_id');
        $date = $this->request->getPost('date');
        $startTime = $this->request->getPost('start_time');
        $duration = $this->request->getPost('duration');

        // Convert the duration to minutes
        $durationMinutes = $this->convertToMinutes($duration);

        // Calculate the end time based on the start time and duration
        $startDateTime = new \DateTime("$date $startTime");
        $endDateTime = clone $startDateTime;
        $endDateTime->modify("+$durationMinutes minutes");

        // Check if the time slot is still available
        $availability = $serviceAvailabilityModel->where('service_id', $serviceId)
            ->where('date', $date)
            ->where('start_time <=', $startTime)
            ->where('end_time >=', $endDateTime->format('H:i:s'))
            ->where('is_booked', 0)
            ->first();

        if (!$availability) {
            return redirect()->back()->with('error', 'The selected time slot is no longer available.');
        }

        // Check for booking conflicts
        $conflict = $bookingModel->where('service_id', $serviceId)
            ->where('date', $date)
            ->where('start_time <', $endDateTime->format('H:i:s'))
            ->where('end_time >', $startTime)
            ->first();

        if ($conflict) {
            return redirect()->back()->with('error', 'The selected time slot conflicts with an existing booking.');
        }

        // Book the service
        $bookingModel->save([
            'user_id' => session()->get('user_id'),
            'event_id' => $eventId,
            'service_id' => $serviceId,
            'date' => $date,
            'start_time' => $startDateTime->format('H:i:s'),
            'end_time' => $endDateTime->format('H:i:s'),
            'status' => 'pending',
        ]);

        // Mark the time slot as booked
        $serviceAvailabilityModel->update($availability['id'], ['is_booked' => 1]);

        return redirect()->to('/service/view/' . $serviceId)->with('success', 'Service booked successfully!');
    }

    private function convertToMinutes($timeLength)
    {
        // Parse the time length (e.g., "1h", "2h30m") into total minutes
        $hours = 0;
        $minutes = 0;

        if (preg_match('/(\d+)h/', $timeLength, $matches)) {
            $hours = (int) $matches[1];
        }

        if (preg_match('/(\d+)m/', $timeLength, $matches)) {
            $minutes = (int) $matches[1];
        }

        return ($hours * 60) + $minutes;
    }

    /**
     * @param list<array{start_time?: string, end_time?: string, price?: float|string}> $timeBlocks
     */
    private function saveTimeBlocks(int $serviceId, array $timeBlocks): void
    {
        (new ServiceTimeBlockModel())->saveForService($serviceId, $timeBlocks);
    }


    public function addAvailability($serviceId)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to add availability.');
        }

        $serviceModel = new ServiceModel();
        $service = $serviceModel->find($serviceId);

        if (!$service || $service['vendor_id'] != session()->get('user_id')) {
            return redirect()->to('/profile')->with('error', 'Service not found or you are not authorized to add availability.');
        }

        if ($this->request->getMethod() === 'POST') {
            $availabilityModel = new ServiceAvailabilityModel();
            $date = $this->request->getPost('date');
            $startTime = $this->request->getPost('start_time');
            $endTime = $this->request->getPost('end_time');

            $availabilityData = [
                'service_id' => $serviceId,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_booked' => 0
            ];

            if ($availabilityModel->save($availabilityData)) {
                return redirect()->to('/service/edit/' . $serviceId)->with('success', 'Availability added successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to add availability.');
            }
        }

        return view('add_availability', ['service' => $service]);
    }

    private function checkAvailability($serviceId, $date)
    {
        $bookingModel = new BookingModel();

        // Fetch all bookings for the service on the selected date
        $existingBookings = $bookingModel->where('service_id', $serviceId)
            ->where('DATE(start_time)', $date)
            ->findAll();

        // If no bookings are found, the service is fully available
        if (empty($existingBookings)) {
            return 'Available';
        }

        // Calculate if the service has some availability
        // You can add logic here to check the time slots that are already booked and compare them to the service's availability.

        // For now, if there are existing bookings but not fully booked, we consider it 'Limited Availability'
        $totalSlots = $this->calculateAvailableSlots($serviceId, $date);

        if (!empty($totalSlots)) {
            return 'Limited Availability';
        }

        // If all slots are booked, mark the service as not available
        return 'Not Available';
    }



    private function timeOverlap($block, $booking)
    {
        $blockStart = strtotime($block['start_time']);
        $blockEnd = strtotime($block['end_time']);
        $bookingStart = strtotime($booking['start_time']);
        $bookingEnd = strtotime($booking['end_time']);

        // Check if time blocks overlap
        return ($blockStart < $bookingEnd && $blockEnd > $bookingStart);
    }

    /**
     * Filter or tag services based on the active event's compatibility.
     *
     * When $showUnavailable is false (default), incompatible services are removed.
     * When true, they are kept but tagged with a '_unavailable_reasons' array so
     * the view can render them as greyed-out with a tooltip.
     *
     * Checks: travel distance, guest-count capacity, and date availability.
     *
     * @param list<array<string,mixed>> $services
     * @return list<array<string,mixed>>
     */
    private function filterAndTagServicesByActiveEvent(array $services, ?int $eventId, bool $showUnavailable): array
    {
        if (!$eventId || empty($services)) {
            return $services;
        }

        $eventModel = new EventModel();
        $event = $eventModel->find($eventId);
        if (!$event) {
            return $services;
        }

        $locationModel = new ServiceLocationModel();
        $quoteBuilder  = new EventQuoteBuilder();
        $db            = \Config\Database::connect();
        $cols          = $this->getServicesTableColumns();

        $eventLat     = !empty($event['latitude'])    ? (float) $event['latitude']    : null;
        $eventLng     = !empty($event['longitude'])   ? (float) $event['longitude']   : null;
        $eventGuests  = (int) ($event['guest_count'] ?? 0);
        $eventDate    = !empty($event['date']) ? (string) $event['date'] : null;
        $eventSetting = $event['event_setting'] ?? 'private'; // 'private' or 'public'

        // Bulk-fetch service event types to avoid N+1 queries.
        $serviceIds = array_column($services, 'id');
        $typesByServiceId = [];
        if ($db->tableExists('services_event_types')) {
            $typeRows = $db->table('services_event_types')
                ->whereIn('service_id', $serviceIds)
                ->get()->getResultArray();
            foreach ($typeRows as $row) {
                $typesByServiceId[(int) $row['service_id']][] = $row['event_type'];
            }
        }

        $result = [];
        foreach ($services as $service) {
            $reasons = [];

            // Event setting compatibility: mirrors the addToBasket guard in EventController.
            $typeSlugs = $typesByServiceId[(int) $service['id']] ?? [];
            if ($eventSetting === 'public' && !in_array('public', $typeSlugs, true)) {
                $reasons[] = 'Not offered for public events';
            } elseif ($eventSetting === 'private' && !in_array('private', $typeSlugs, true)) {
                $reasons[] = 'Not offered for private events';
            }

            // Distance: only block if vendor has a strict travel radius set.
            if ($eventLat !== null && $eventLng !== null) {
                $locRow = $locationModel->where('service_id', (int) $service['id'])->first();
                $loc    = $quoteBuilder->mergeServiceLocation($service, $locRow);
                if (!empty($loc['strict_travel_radius'])) {
                    $distance = EventBookingQuote::haversineKm(
                        (float) $loc['latitude'],
                        (float) $loc['longitude'],
                        $eventLat,
                        $eventLng
                    );
                    $travel = (new EventBookingQuote())->computeTravel($distance, $loc);
                    foreach ($travel['warnings'] as $w) {
                        if (str_contains($w, 'exceeds the vendor') || str_contains($w, 'beyond the maximum')) {
                            $reasons[] = 'Too far from your event location';
                            break;
                        }
                    }
                }
            }

            // Guest count: skip when event has no guests or service has no capacity limits.
            if ($eventGuests > 0) {
                $minCap = in_array('min_capacity', $cols, true) ? $service['min_capacity'] : null;
                $maxCap = in_array('max_capacity', $cols, true) ? $service['max_capacity'] : null;
                if ($minCap !== null && $minCap !== '' && (int) $minCap > $eventGuests) {
                    $reasons[] = 'Minimum ' . (int) $minCap . ' guests required (your event has ' . $eventGuests . ')';
                } elseif ($maxCap !== null && $maxCap !== '' && (int) $maxCap < $eventGuests) {
                    $reasons[] = 'Maximum capacity is ' . (int) $maxCap . ' guests (your event has ' . $eventGuests . ')';
                }
            }

            // Date: vendor has marked the event date unavailable.
            if ($eventDate !== null && $db->tableExists('unavailable_dates')) {
                $blocked = $db->table('unavailable_dates')
                    ->where('vendor_id', (int) $service['vendor_id'])
                    ->where('date', $eventDate)
                    ->countAllResults() > 0;
                if ($blocked) {
                    $reasons[] = 'Not available on your event date';
                }
            }

            if (empty($reasons)) {
                $result[] = $service;
            } elseif ($showUnavailable) {
                $service['_unavailable_reasons'] = $reasons;
                $result[] = $service;
            }
        }

        return $result;
    }

    /**
     * Clone a service listing (pricing rules copied via DB).
     */
    public function duplicateService($serviceId)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/login');
        }

        $serviceModel = new ServiceModel();
        $service = $serviceModel->find((int) $serviceId);
        if (!$service || (int) $service['vendor_id'] !== (int) session()->get('user_id')) {
            return redirect()->to('/profile/services')->with('error', 'Service not found.');
        }

        unset($service['id']);
        $service['title'] = ($service['title'] ?? 'Service') . ' (copy)';
        $service['status'] = 'draft';
        $serviceModel->insert($service);
        $newId = (int) $serviceModel->getInsertID();

        $db = \Config\Database::connect();
        foreach ([
            'services_private_event_pricing',
            'services_corporate_event_pricing',
            'services_locations',
            'services_optional_extras',
            'services_public_event_pricing',
            'service_event_types',
        ] as $table) {
            if (!$db->tableExists($table)) {
                continue;
            }
            $rows = $db->table($table)->where('service_id', (int) $serviceId)->get()->getResultArray();
            foreach ($rows as $row) {
                unset($row['id']);
                $row['service_id'] = $newId;
                $db->table($table)->insert($row);
            }
        }

        return redirect()->to('/service/edit/' . $newId)->with('success', 'Service duplicated. Review pricing and publish when ready.');
    }
}
