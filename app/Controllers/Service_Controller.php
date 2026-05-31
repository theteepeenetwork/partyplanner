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
        ]);

        if ($searchQuery !== '') {
            $builder = $this->applyBrowseSearch($builder, $searchQuery, $cols);
        }

        $builder = $this->applyBrowseSort($builder, $sort, $cols);

        $services = $builder->findAll();
        $services = $this->filterServicesByEventCoverage($services);

        foreach ($services as &$service) {
            $service['images'] = $serviceImageModel
                ->where(['service_id' => $service['id'], 'is_primary' => 1])
                ->findAll();
            $service['category_name'] = $categoryModel->getServiceCategoryLabel($service);
        }

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

        $queryEventId = $this->request->getGet('event_id');
        $basketEventId = null;
        if ($queryEventId !== null && $queryEventId !== '') {
            $basketEventId = (int) $queryEventId;
        } elseif (session()->get('preferred_basket_event_id') !== null) {
            $basketEventId = (int) session()->get('preferred_basket_event_id');
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
            'searchQuery' => $searchQuery ?? '',
            'basketEventId' => $basketEventId,
            'message_eligible_by_service_id' => $messageEligibleByServiceId,
            'customerEvents' => $customerEventContext['events'],
            'activeEvent' => $customerEventContext['active'],
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

    public function destroy($step)
    {
        session()->remove('step' . $step . "_data");

        return redirect()->to('service/step' . $step);
    }

    public function step1()
    {
        // Check if the user is authorized
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to add services.');
        }

        // Load categories for dropdowns
        $data['categories'] = $this->buildCategoryTree();

        // Set the next step flag
        $data['next_step'] = true; // Indicate there is a next step after step 1

        // Handle form submission
        if ($this->request->getMethod() === 'POST') {
            // Retrieve existing images from the session
            $uploadedImages = session()->get('uploaded_images') ?? [];

            // Define base validation rules
            $rules = [
                'title' => 'required|min_length[3]|max_length[255]',
                'short_description' => 'required|max_length[200]',
                'description' => 'required',
                'category_id' => 'required|is_natural_no_zero',
            ];

            // Add validation for images only if no images are already uploaded
            if (empty($uploadedImages)) {
                $rules['images'] = 'uploaded[images]|max_size[images,10000]|is_image[images]';
            }

            // Validate the form
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $categoryModel = new CategoryModel();
            $catErr = $categoryModel->validateAssignment(
                $this->request->getPost('category_id'),
                $this->request->getPost('subcategory_id'),
                $this->request->getPost('third_category_id')
            );
            if ($catErr !== null) {
                return redirect()->back()->withInput()->with('errors', ['category' => $catErr]);
            }

            // Save step 1 data to session
            $step1Data = [
                'vendor_id' => session()->get('user_id'),
                'title' => $this->request->getPost('title'),
                'short_description' => $this->request->getPost('short_description'),
                'description' => $this->request->getPost('description'),
                'service_tags' => $this->request->getPost('service_tags'),
                'category_id' => $this->request->getPost('category_id'),
                'subcategory_id' => $this->request->getPost('subcategory_id'),
                'third_category_id' => $this->request->getPost('third_category_id'),
            ];

            session()->set('step1_data', $step1Data);

            // Handle uploaded images and save to session
            $this->handleImages();

            // Redirect to step 2
            if (session()->has('step2_data')) {
                return redirect()->to('/service/review#basicInfoSection')->with('', '');
            } else {
                return redirect()->to('/service/step2')->with('success', 'Step 1 completed successfully!');
            }

        }


        // Render the Step 1 view with categories
        return view('service_create/service_create_step1', $data);
    }

    public function step2()
    {
        // Ensure step1 data exists
        if (!session()->has('step1_data')) {
            return redirect()->to('/service/step1')->with('error', 'Please complete the previous step first.');
        }

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'event_types' => 'required',
            ];

            // Only require `pricing_type` if 'private' is one of the selected event types
            $eventTypes = $this->request->getPost('event_types');
            if (is_array($eventTypes) && in_array('private', $eventTypes)) {
                $rules['pricing_type'] = 'required|in_list[guest_based_pricing,custom_duration_pricing,tiered_packages_pricing,quantity_based_pricing,custom_quote]';
            }

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            // Save step2 data to session
            $step2Data = [
                'event_types' => implode(',', $this->request->getPost('event_types')),
            ];

            // Add pricing_type to session only if 'private' is selected
            if (in_array('private', $eventTypes)) {
                $step2Data['pricing_type'] = $this->request->getPost('pricing_type');
            }

            if (isset($_SESSION['step2_data'])) {

                // Compare the new data with the session data
                if ($_SESSION['step2_data'] !== $step2Data) {
                    // Unset the specific session variable
                    unset($_SESSION['step2_data']);
                    unset($_SESSION['step3_data']);
                }
            }


            $this->destroy('step3');


            session()->set('step2_data', $step2Data);

            return redirect()->to('/service/step3')->with('', '');
        }

        return view('service_create/service_create_step2'); // Ensure `step2.php` exists
    }
    public function step3()
    {
        if (!session()->has('step2_data')) {
            return redirect()->to('/service/step2')->with('error', 'Please complete Step 2 first.');
        }

        $arrayCounts = [
            'min_attendance' => 0,
            'min_guest' => 0,
            'hour_number' => 0,
            'day_number' => 0,
            'package_name' => 0,
        ];

        if (session()->has('step3_data')) {
            $step3Data = session()->get('step3_data');
            foreach ($arrayCounts as $key => &$count) {
                if (isset($step3Data[$key]) && is_array($step3Data[$key])) {
                    $count = count($step3Data[$key]);
                }
            }
        }

        $step2Data = session()->get('step2_data');

        // Normalise event types robustly (string CSV or array)
        $eventTypesRaw = $step2Data['event_types'] ?? [];
        if (is_array($eventTypesRaw)) {
            $selectedEventTypes = array_map('trim', $eventTypesRaw);
        } else {
            $selectedEventTypes = array_map('trim', explode(',', (string) $eventTypesRaw));
        }
        $selectedEventTypes = array_values(array_filter($selectedEventTypes));

        $pricingType = $step2Data['pricing_type'] ?? null;

        if ($this->request->getMethod() === 'POST') {

            // Persist what the vendor just entered BEFORE validating, so that a
            // validation failure (overlapping ranges, gaps, etc.) redirects back with
            // every value and dynamically added row still present instead of wiping the
            // form. The view rebuilds its rows from step3_data.
            session()->set('step3_data', $this->buildStep3Data(
                $this->request->getPost(),
                $selectedEventTypes,
                $pricingType
            ));

            $rules = [];

            /* ---------------- PUBLIC VALIDATION ---------------- */
            if (in_array('public', $selectedEventTypes, true)) {

                $rules['commission_percentage'] = 'permit_empty|numeric|greater_than_equal_to[0]';

                $rules['min_attendance.*'] = 'required';
                $rules['max_attendance.*'] = 'required|is_natural_no_zero';
                $rules['max_pitch_fee.*'] = 'required|numeric';

                $minAttendance = (array) $this->request->getPost('min_attendance');
                $maxAttendance = (array) $this->request->getPost('max_attendance');

                $ranges = [];
                foreach ($minAttendance as $index => $min) {
                    $min = (int) $min;
                    $max = isset($maxAttendance[$index]) ? (int) $maxAttendance[$index] : null;

                    if ($max === null || $min >= $max) {
                        return redirect()->back()->withInput()->with('errors', [
                            "max_attendance.$index" => "Maximum attendance must be greater than minimum attendance for range $index."
                        ]);
                    }

                    foreach ($ranges as $existingRange) {
                        $overlap =
                            ($min <= $existingRange['max'] && $min >= $existingRange['min']) ||
                            ($max <= $existingRange['max'] && $max >= $existingRange['min']) ||
                            ($min <= $existingRange['min'] && $max >= $existingRange['max']);

                        if ($overlap) {
                            return redirect()->back()->withInput()->with('errors', [
                                "min_attendance.$index" => "Attendance range {$min}-{$max} overlaps with existing range {$existingRange['min']}-{$existingRange['max']}."
                            ]);
                        }
                    }

                    $ranges[] = ['min' => $min, 'max' => $max];
                }
            }

            /* ---------------- PRIVATE VALIDATION ---------------- */
            if (in_array('private', $selectedEventTypes, true)) {
                switch ($pricingType) {

                    case 'guest_based_pricing':
                        $rules['min_guest.*'] = 'required';
                        $rules['max_guest.*'] = 'required|is_natural_no_zero';
                        $rules['guest_price.*'] = 'required|numeric|greater_than[0]';

                        $minGuests = (array) $this->request->getPost('min_guest');
                        $maxGuests = (array) $this->request->getPost('max_guest');

                        $ranges = [];
                        foreach ($minGuests as $index => $minGuest) {
                            $minGuest = (int) $minGuest;
                            $maxGuest = (int) ($maxGuests[$index] ?? 0);

                            if ($minGuest >= $maxGuest) {
                                return redirect()->back()->withInput()->with('errors', [
                                    "max_guest.$index" => "Maximum guests must be greater than minimum guests for range $index."
                                ]);
                            }

                            $ranges[] = ['min' => $minGuest, 'max' => $maxGuest];
                        }

                        foreach ($ranges as $i => $currentRange) {
                            foreach ($ranges as $j => $otherRange) {
                                if ($i === $j)
                                    continue;

                                $overlap =
                                    ($currentRange['min'] >= $otherRange['min'] && $currentRange['min'] <= $otherRange['max']) ||
                                    ($currentRange['max'] >= $otherRange['min'] && $currentRange['max'] <= $otherRange['max']) ||
                                    ($currentRange['min'] <= $otherRange['min'] && $currentRange['max'] >= $otherRange['max']);

                                if ($overlap) {
                                    return redirect()->back()->withInput()->with('errors', [
                                        "min_guest.$i" => "Guest range {$currentRange['min']}-{$currentRange['max']} overlaps with range {$otherRange['min']}-{$otherRange['max']}."
                                    ]);
                                }
                            }
                        }

                        usort($ranges, static fn (array $a, array $b): int => $a['min'] <=> $b['min']);
                        for ($i = 0, $n = count($ranges); $i < $n - 1; $i++) {
                            $hole = $ranges[$i + 1]['min'] - $ranges[$i]['max'] - 1;
                            if ($hole > 1) {
                                return redirect()->back()->withInput()->with('errors', [
                                    'min_guest.' . ($i + 1) => sprintf(
                                        'There is a gap of %d guests between band %d–%d and %d–%d. Adjacent bands must leave at most one guest between them.',
                                        $hole,
                                        $ranges[$i]['min'],
                                        $ranges[$i]['max'],
                                        $ranges[$i + 1]['min'],
                                        $ranges[$i + 1]['max']
                                    ),
                                ]);
                            }
                        }
                        break;

                    case 'custom_duration_pricing':

                        $enableHours = !empty($this->request->getPost('enableHours'));
                        $enableDays = !empty($this->request->getPost('enableDays'));

                        if ($enableHours) {
                            $rules['hour_number.*'] = 'required|is_natural_no_zero';
                            $rules['hour_price.*'] = 'required|numeric|greater_than[0]';

                            $hourNumbers = (array) $this->request->getPost('hour_number');
                            $hourNumbers = array_map('intval', $hourNumbers);
                            sort($hourNumbers);

                            for ($i = 1; $i < count($hourNumbers); $i++) {
                                if ($hourNumbers[$i] === $hourNumbers[$i - 1]) {
                                    return redirect()->back()->withInput()->with('errors', [
                                        "hour_number.$i" => "Hour values cannot be duplicated."
                                    ]);
                                }
                            }
                        }

                        if ($enableDays) {
                            $rules['day_number.*'] = 'required|is_natural_no_zero';
                            $rules['day_price.*'] = 'required|numeric|greater_than[0]';

                            $dayNumbers = (array) $this->request->getPost('day_number');
                            $dayNumbers = array_map('intval', $dayNumbers);
                            sort($dayNumbers);

                            for ($i = 1; $i < count($dayNumbers); $i++) {
                                if ($dayNumbers[$i] === $dayNumbers[$i - 1]) {
                                    return redirect()->back()->withInput()->with('errors', [
                                        "day_number.$i" => "Day values cannot be duplicated."
                                    ]);
                                }
                            }
                        }
                        break;

                    case 'tiered_packages_pricing':
                        $rules['package_name.*'] = 'required|string';
                        $rules['package_description.*'] = 'required|string';
                        $rules['package_price.*'] = 'required|numeric|greater_than[0]';
                        break;

                    case 'quantity_based_pricing':
                        $rules['unit_price'] = 'required|numeric|greater_than[0]';
                        $rules['min_quantity'] = 'required|is_natural_no_zero';
                        $rules['max_quantity'] = 'permit_empty|is_natural_no_zero';
                        $minQty = (int) $this->request->getPost('min_quantity');
                        $maxQty = $this->request->getPost('max_quantity');
                        if ($maxQty !== null && $maxQty !== '') {
                            $maxQty = (int) $maxQty;
                            if ($maxQty < $minQty) {
                                return redirect()->back()->withInput()->with('errors', [
                                    'max_quantity' => 'Maximum quantity must be greater than or equal to minimum quantity.',
                                ]);
                            }
                        }
                        break;

                    default:
                        break;
                }
            }

            /* ---------------- CORPORATE VALIDATION ---------------- */
            if (in_array('corporate', $selectedEventTypes, true)) {

                $corporateEnabled = !empty($this->request->getPost('corporate_enabled'));

                if ($corporateEnabled) {
                    // Invoice supported => accounts email required
                    if (!empty($this->request->getPost('corporate_invoice_supported'))) {
                        $rules['corporate_accounts_email'] = 'required|valid_email';
                    }

                    // VAT registered => VAT number required
                    if (!empty($this->request->getPost('corporate_vat_registered'))) {
                        $rules['corporate_vat_number'] = 'required';
                    }

                    // Surcharge type => surcharge value rules
                    $sType = (string) $this->request->getPost('corporate_surcharge_type');
                    if ($sType && $sType !== 'none') {
                        $rules['corporate_surcharge_value'] = 'required|numeric|greater_than[0]';
                        if ($sType === 'percent') {
                            $rules['corporate_surcharge_value'] .= '|less_than_equal_to[100]';
                        }
                    }
                }
            }

            /* ---------------- RUN CI VALIDATION ---------------- */
            // Some pricing modes (e.g. custom_quote / price-on-request) need no field
            // rules at all. Calling validate() with an empty ruleset returns false and
            // would bounce the user back with an empty error banner, so only validate
            // when there is something to check.
            if (!empty($rules) && !$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            // step3_data was already persisted at the top of this POST handler, so a
            // validation failure above redirects back with the user's input intact.

            // Move on
            if (session()->has('step4_data')) {
                return redirect()->to('/service/review#pricingSection');
            }

            $data = [
                'selected_event_types' => $selectedEventTypes,
                'pricingType' => $pricingType,
                'arrayCounts' => $arrayCounts,
                'step2Data' => $step2Data,
            ];

            return view('service_create/service_create_step4', $data);
        }

        $data = [
            'selected_event_types' => $selectedEventTypes,
            'pricingType' => $pricingType,
            'arrayCounts' => $arrayCounts,
            'step2Data' => $step2Data,
        ];

        return view('service_create/service_create_step3', $data);
    }

    /**
     * Build the step3 (pricing) session payload from posted data.
     *
     * Kept separate so it can run before validation — persisting the vendor's input
     * up front means a validation failure can redirect back without losing anything.
     * Fields belonging to event types / pricing models that are not in play are
     * cleared so stale values never leak into the review or saved service.
     */
    private function buildStep3Data(array $post, array $selectedEventTypes, ?string $pricingType): array
    {
        // Start with existing step3_data so you can revisit step3 without losing data
        $step3 = session('step3_data') ?? [];

        // PUBLIC
        if (in_array('public', $selectedEventTypes, true)) {
            $step3['commission_percentage'] = $post['commission_percentage'] ?? null;
            $step3['min_attendance'] = $post['min_attendance'] ?? [];
            $step3['max_attendance'] = $post['max_attendance'] ?? [];
            $step3['max_pitch_fee'] = $post['max_pitch_fee'] ?? [];
        } else {
            // Clear public fields when public not selected
            unset($step3['commission_percentage'], $step3['min_attendance'], $step3['max_attendance'], $step3['max_pitch_fee']);
        }

        // PRIVATE
        if (in_array('private', $selectedEventTypes, true)) {
            if ($pricingType === 'guest_based_pricing') {
                $step3['min_guest'] = $post['min_guest'] ?? [];
                $step3['max_guest'] = $post['max_guest'] ?? [];
                $step3['guest_price'] = $post['guest_price'] ?? [];

                unset($step3['enableHours'], $step3['enableDays'], $step3['hour_number'], $step3['hour_price'], $step3['day_number'], $step3['day_price']);
                unset($step3['package_name'], $step3['package_description'], $step3['package_price']);
                unset($step3['unit_price'], $step3['min_quantity'], $step3['max_quantity'], $step3['unit_label']);
            }

            if ($pricingType === 'custom_duration_pricing') {
                $step3['enableHours'] = !empty($post['enableHours']) ? 1 : 0;
                $step3['enableDays'] = !empty($post['enableDays']) ? 1 : 0;

                $step3['hour_number'] = $post['hour_number'] ?? [];
                $step3['hour_price'] = $post['hour_price'] ?? [];
                $step3['day_number'] = $post['day_number'] ?? [];
                $step3['day_price'] = $post['day_price'] ?? [];

                unset($step3['min_guest'], $step3['max_guest'], $step3['guest_price']);
                unset($step3['package_name'], $step3['package_description'], $step3['package_price']);
                unset($step3['unit_price'], $step3['min_quantity'], $step3['max_quantity'], $step3['unit_label']);
            }

            if ($pricingType === 'tiered_packages_pricing') {
                $step3['package_name'] = $post['package_name'] ?? [];
                $step3['package_description'] = $post['package_description'] ?? [];
                $step3['package_price'] = $post['package_price'] ?? [];

                unset($step3['min_guest'], $step3['max_guest'], $step3['guest_price']);
                unset($step3['enableHours'], $step3['enableDays'], $step3['hour_number'], $step3['hour_price'], $step3['day_number'], $step3['day_price']);
                unset($step3['unit_price'], $step3['min_quantity'], $step3['max_quantity'], $step3['unit_label']);
            }

            if ($pricingType === 'quantity_based_pricing') {
                $step3['unit_price'] = $post['unit_price'] ?? null;
                $step3['min_quantity'] = $post['min_quantity'] ?? null;
                $step3['max_quantity'] = $post['max_quantity'] ?? null;
                $step3['unit_label'] = trim((string) ($post['unit_label'] ?? 'items')) ?: 'items';

                unset($step3['min_guest'], $step3['max_guest'], $step3['guest_price']);
                unset($step3['enableHours'], $step3['enableDays'], $step3['hour_number'], $step3['hour_price'], $step3['day_number'], $step3['day_price']);
                unset($step3['package_name'], $step3['package_description'], $step3['package_price']);
            }
        } else {
            // Clear private fields when private not selected
            unset(
                $step3['min_guest'],
                $step3['max_guest'],
                $step3['guest_price'],
                $step3['enableHours'],
                $step3['enableDays'],
                $step3['hour_number'],
                $step3['hour_price'],
                $step3['day_number'],
                $step3['day_price'],
                $step3['package_name'],
                $step3['package_description'],
                $step3['package_price'],
                $step3['unit_price'],
                $step3['min_quantity'],
                $step3['max_quantity'],
                $step3['unit_label']
            );
        }

        // CORPORATE (store as flat keys to match your current form/review, OR group it later)
        if (in_array('corporate', $selectedEventTypes, true)) {
            $enabled = !empty($post['corporate_enabled']);

            $step3['corporate_enabled'] = $enabled ? 1 : 0;
            $step3['corporate_invoice_supported'] = !empty($post['corporate_invoice_supported']) ? 1 : 0;
            $step3['corporate_po_supported'] = !empty($post['corporate_po_supported']) ? 1 : 0;
            $step3['corporate_payment_terms'] = $post['corporate_payment_terms'] ?? [];
            $step3['corporate_accounts_email'] = trim((string) ($post['corporate_accounts_email'] ?? ''));
            $step3['corporate_vat_registered'] = !empty($post['corporate_vat_registered']) ? 1 : 0;
            $step3['corporate_vat_number'] = trim((string) ($post['corporate_vat_number'] ?? ''));
            $step3['corporate_pli_level'] = (string) ($post['corporate_pli_level'] ?? 'none');
            $step3['corporate_risk_assessment'] = !empty($post['corporate_risk_assessment']) ? 1 : 0;
            $step3['corporate_method_statement'] = !empty($post['corporate_method_statement']) ? 1 : 0;
            $step3['corporate_pat_testing'] = (string) ($post['corporate_pat_testing'] ?? 'na');
            $step3['corporate_dbs'] = (string) ($post['corporate_dbs'] ?? 'na');
            $step3['corporate_surcharge_type'] = (string) ($post['corporate_surcharge_type'] ?? 'none');
            $step3['corporate_surcharge_value'] = $post['corporate_surcharge_value'] ?? null;
            $step3['corporate_invoice_fee'] = $post['corporate_invoice_fee'] ?? null;
            $step3['corporate_min_spend'] = $post['corporate_min_spend'] ?? null;

            // If corporate is selected but not enabled, wipe dependent fields to avoid stale data
            if (!$enabled) {
                $step3['corporate_invoice_supported'] = 0;
                $step3['corporate_po_supported'] = 0;
                $step3['corporate_payment_terms'] = [];
                $step3['corporate_accounts_email'] = '';
                $step3['corporate_vat_registered'] = 0;
                $step3['corporate_vat_number'] = '';
                $step3['corporate_pli_level'] = 'none';
                $step3['corporate_risk_assessment'] = 0;
                $step3['corporate_method_statement'] = 0;
                $step3['corporate_pat_testing'] = 'na';
                $step3['corporate_dbs'] = 'na';
                $step3['corporate_surcharge_type'] = 'none';
                $step3['corporate_surcharge_value'] = null;
                $step3['corporate_invoice_fee'] = null;
                $step3['corporate_min_spend'] = null;
            }
        } else {
            // Clear corporate fields when corporate not selected
            unset(
                $step3['corporate_enabled'],
                $step3['corporate_invoice_supported'],
                $step3['corporate_po_supported'],
                $step3['corporate_payment_terms'],
                $step3['corporate_accounts_email'],
                $step3['corporate_vat_registered'],
                $step3['corporate_vat_number'],
                $step3['corporate_pli_level'],
                $step3['corporate_risk_assessment'],
                $step3['corporate_method_statement'],
                $step3['corporate_pat_testing'],
                $step3['corporate_dbs'],
                $step3['corporate_surcharge_type'],
                $step3['corporate_surcharge_value'],
                $step3['corporate_invoice_fee'],
                $step3['corporate_min_spend']
            );
        }

        return $step3;
    }

    public function step4()
    {
        // Check if the user is authorized
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to add services.');
        }
        /*if (!$this->validateSessionData('step3_data')) {
            return redirect()->to('/service/step3')->with('error', 'Please complete Step 3 before proceeding.');
        }*/

        // Check if the request method is POST
        if ($this->request->getMethod() === 'POST') {
            $fulfillmentType = $this->request->getPost('fulfillment_type') ?? 'in_person';
            if (!in_array($fulfillmentType, ['in_person', 'postal', 'both'], true)) {
                $fulfillmentType = 'in_person';
            }

            $requiresLocation = ($fulfillmentType !== 'postal');

            // Build validation rules conditionally
            $rules = [
                'free_coverage_radius' => 'permit_empty|integer|greater_than_equal_to[0]',
                'paid_coverage_radius' => 'permit_empty|integer|greater_than_equal_to[0]',
                'travel_fee_per_km'    => 'permit_empty|decimal|greater_than_equal_to[0]',
                'postal_fee'           => 'permit_empty|decimal|greater_than_equal_to[0]',
                // Capacity, logistics & requirements (all optional).
                'min_capacity'      => 'permit_empty|is_natural',
                'max_capacity'      => 'permit_empty|is_natural',
                'setup_minutes'     => 'permit_empty|is_natural',
                'breakdown_minutes' => 'permit_empty|is_natural',
                'min_notice_days'   => 'permit_empty|is_natural',
                'space_required'    => 'permit_empty|max_length[120]',
                'indoor_outdoor'    => 'permit_empty|in_list[indoor,outdoor,both]',
            ];
            if ($requiresLocation) {
                $rules['service_location'] = 'required';
                $rules['latitude']         = 'required|decimal';
                $rules['longitude']        = 'required|decimal';
            }

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $allTravelIncluded  = (bool) $this->request->getPost('all_travel_included');
            $noTravelLimit      = (bool) $this->request->getPost('no_travel_limit');
            $freeCoverageRadius = $this->request->getPost('free_coverage_radius');
            $paidCoverageRadius = $this->request->getPost('paid_coverage_radius');
            $travelFeePerKm     = $this->request->getPost('travel_fee_per_km');

            if ($requiresLocation) {
                if ($allTravelIncluded && $noTravelLimit) {
                    if (!empty($freeCoverageRadius) || !empty($paidCoverageRadius) || !empty($travelFeePerKm)) {
                        return redirect()->back()->withInput()->with('error', 'When both "All Travel Included" and "No Travel Limit" are selected, no additional inputs are allowed.');
                    }
                } elseif ($allTravelIncluded) {
                    if (!empty($paidCoverageRadius) || !empty($travelFeePerKm)) {
                        return redirect()->back()->withInput()->with('error', 'When "All Travel Included" is selected, paid coverage radius and travel fee per km must be empty.');
                    }
                } elseif ($noTravelLimit) {
                    if (!empty($paidCoverageRadius)) {
                        return redirect()->back()->withInput()->with('error', 'When "No Travel Limit" is selected, paid coverage radius must be empty.');
                    }
                    if (empty($travelFeePerKm)) {
                        return redirect()->back()->withInput()->with('error', '"No Travel Limit" requires a travel fee per km.');
                    }
                }
            }

            $freePostageAbove = $this->request->getPost('free_postage_above');
            $deliveryLeadTime = $this->request->getPost('delivery_lead_time_days');

            $step4Data = [
                'fulfillment_type'        => $fulfillmentType,
                'service_location'        => $this->request->getPost('service_location'),
                'latitude'                => $this->request->getPost('latitude'),
                'longitude'               => $this->request->getPost('longitude'),
                'all_travel_included'     => $allTravelIncluded ? 1 : 0,
                'no_travel_limit'         => $noTravelLimit ? 1 : 0,
                'free_coverage_radius'    => $freeCoverageRadius,
                'paid_coverage_radius'    => $paidCoverageRadius,
                'travel_fee_per_km'       => $travelFeePerKm,
                'postal_fee'              => $this->request->getPost('postal_fee') !== '' ? $this->request->getPost('postal_fee') : null,
                'free_postage_above'      => ($freePostageAbove !== '' && $freePostageAbove !== null) ? $freePostageAbove : null,
                'delivery_lead_time_days' => ($deliveryLeadTime !== '' && $deliveryLeadTime !== null) ? (int) $deliveryLeadTime : null,
                // Capacity, logistics & on-site requirements (stored on the service row).
                'min_capacity'            => $this->request->getPost('min_capacity') !== '' ? $this->request->getPost('min_capacity') : null,
                'max_capacity'            => $this->request->getPost('max_capacity') !== '' ? $this->request->getPost('max_capacity') : null,
                'setup_minutes'           => $this->request->getPost('setup_minutes') !== '' ? $this->request->getPost('setup_minutes') : null,
                'breakdown_minutes'       => $this->request->getPost('breakdown_minutes') !== '' ? $this->request->getPost('breakdown_minutes') : null,
                'min_notice_days'         => $this->request->getPost('min_notice_days') !== '' ? $this->request->getPost('min_notice_days') : null,
                'space_required'          => $this->request->getPost('space_required') ?: null,
                'indoor_outdoor'          => in_array($this->request->getPost('indoor_outdoor'), ['indoor', 'outdoor', 'both'], true) ? $this->request->getPost('indoor_outdoor') : 'both',
                'power_required'          => $this->request->getPost('power_required') ? 1 : 0,
                'water_required'          => $this->request->getPost('water_required') ? 1 : 0,
                'vehicle_access_required' => $this->request->getPost('vehicle_access_required') ? 1 : 0,
                'equipment_provided'      => $this->request->getPost('equipment_provided') ? 1 : 0,
            ];
            session()->set('step4_data', $step4Data);

            // Redirect to the next step

            if (session()->has('step5_data')) {
                return redirect()->to('/service/review#locationSection')->with('', '');
            } else {
                return redirect()->to('/service/step5')->with('', '');
            }
        }

        // Render the step 4 view
        return view('service_create/service_create_step4');
    }
    public function step5()
    {
        // Check if the user is authorized
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to add services.');
        }

        if (!$this->validateSessionData('step4_data')) {
            return redirect()->to('/service/step3')->with('error', 'Please complete Step 3 before proceeding.');
        }


        // Check if the request method is POST
        if ($this->request->getMethod() === 'POST') {
            $extraNames        = $this->request->getPost('extra_name') ?? [];
            $extraPrices       = $this->request->getPost('extra_price') ?? [];
            $extraDescriptions = $this->request->getPost('extra_description') ?? [];
            $extraPricingTypes = $this->request->getPost('extra_pricing_type') ?? [];
            $extraUnitLabels   = $this->request->getPost('extra_unit_label') ?? [];
            $extraMinQtys      = $this->request->getPost('extra_min_quantity') ?? [];
            $extraMaxQtys      = $this->request->getPost('extra_max_quantity') ?? [];

            if (!is_array($extraNames)) {
                return redirect()->back()->withInput()->with('error', 'Invalid input format for optional extras.');
            }

            $optionalExtras = [];
            foreach ($extraNames as $index => $extraName) {
                $name = trim((string) ($extraName ?? ''));
                if ($name === '') {
                    continue;
                }

                $pricingType = in_array($extraPricingTypes[$index] ?? '', ['flat', 'per_item'], true)
                    ? $extraPricingTypes[$index]
                    : 'flat';

                $minQty = ($pricingType === 'per_item' && !empty($extraMinQtys[$index]))
                    ? max(1, (int) $extraMinQtys[$index])
                    : null;
                $maxQty = ($pricingType === 'per_item' && !empty($extraMaxQtys[$index]))
                    ? max(1, (int) $extraMaxQtys[$index])
                    : null;

                $optionalExtras[] = [
                    'name'         => $name,
                    'description'  => trim((string) ($extraDescriptions[$index] ?? '')),
                    'price'        => (float) ($extraPrices[$index] ?? 0),
                    'pricing_type' => $pricingType,
                    'unit_label'   => trim((string) ($extraUnitLabels[$index] ?? '')) ?: null,
                    'min_quantity' => $minQty,
                    'max_quantity' => $maxQty,
                ];
            }

            session()->set('step5_data', $optionalExtras);

            // Redirect to the next step
            if (session()->has('step6_data')) {
                return redirect()->to('/service/review#optionalExtrasSection')->with('', '');
            } else {
                return redirect()->to('/service/step6')->with('success', 'Optional extras saved successfully!');
            }

        }

        // Prepopulate data from the session (for editing or revisiting)
        $optionalExtras = session()->get('step5_data') ?? [];

        // Pass existing optional extras to the view
        $data = [
            'optionalExtras' => $optionalExtras,
        ];

        // Render the step 5 view
        return view('service_create/service_create_step5', $data);
    }

    public function step6()
    {
        // Check if the user is authorized
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to add services.');
        }
        if (!$this->validateSessionData('step5_data')) {
            return redirect()->to('/service/step5')->with('error', 'Please complete Step 5 before proceeding.');
        }

        // Check if the request method is POST
        if ($this->request->getMethod() === 'POST') {
            // Validate cancellation policy input
            $rules = [
                'cancellation_policy' => 'max_length[5000]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            // Save cancellation policy to the session
            session()->set('step6_data', [
                'cancellation_policy' => $this->request->getPost('cancellation_policy')
            ]);

            // Redirect to the review page
            if (session()->has('step6_data')) {
                return redirect()->to('/service/review#cancelationPolicySection')->with('', '');
            } else {
                return redirect()->to('/service/step6')->with('success', 'Optional extras saved successfully!');
            }
        }

        // Render the Step 6 view if method is not POST
        return view('service_create/service_create_step6');
    }
    public function review()
    {
        // Check if the user is authorized
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to add services.');
        }

        if (!$this->validateSessionData('step1_data', 'step2_data', 'step3_data', 'step4_data', 'step5_data', 'step6_data')) {
            return redirect()->to('/service/step2')->with('error', 'Please complete the form before proceeding.');
        }

        $categoryModel = new CategoryModel();
        $categories = $categoryModel->findAll();

        // Helper function to find category name by ID
        $getCategoryNameById = function ($id, $categories) {
            foreach ($categories as $category) {
                if ($category['id'] == $id) {
                    return $category['name'];
                }
            }
            return 'Not Selected'; // Return default text if the ID is not found
        };

        // Fetch selected category names
        $mainCategoryName = $getCategoryNameById(session('step1_data.category_id'), $categories);
        $subCategoryName = $getCategoryNameById(session('step1_data.subcategory_id'), $categories);
        $thirdCategoryName = $getCategoryNameById(session('step1_data.third_category_id'), $categories);

        // Retrieve data from all steps
        $serviceData = [
            'step1' => session()->get('step1_data'),
            'step2' => session()->get('step2_data'),
            'step3' => session()->get('step3_data'),
            'step4' => session()->get('step4_data'),
            'step5' => session()->get('step5_data'),
            'step6' => session()->get('step6_data'),
            'categories' => $categories,
            'category_names' => [
                'main' => $mainCategoryName,
                'sub' => $subCategoryName,
                'third' => $thirdCategoryName,
            ]
        ];

        // Pass data to the review view
        return view('service_create/service_review', ['serviceData' => $serviceData]);
    }
    public function saveService()
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $serviceId = null;
            $pricingRow = null;
            $privateEventPricingId = null;

            /****************************************
             * STEP 1
             ****************************************/
            if (session()->has('step1_data')) {
                $step1Data = session('step1_data');

                $serviceModel = new ServiceModel();
                $tagsModel = new TagsModel();
                $serviceTagsModel = new ServiceTagsModel();

                // Capacity / logistics / requirements captured on step 4.
                $step4ForService = session('step4_data') ?? [];
                $serviceInsert = [
                    'vendor_id' => $step1Data['vendor_id'],
                    'title' => $step1Data['title'],
                    'short_description' => $step1Data['short_description'],
                    'description' => $step1Data['description'],
                    'category_id' => $step1Data['category_id'],
                    'subcategory_id' => $step1Data['subcategory_id'] ?? null,
                    'third_category_id' => $step1Data['third_category_id'] ?? null,
                ];
                foreach ([
                    'min_capacity', 'max_capacity', 'setup_minutes', 'breakdown_minutes',
                    'min_notice_days', 'space_required', 'indoor_outdoor', 'power_required',
                    'water_required', 'vehicle_access_required', 'equipment_provided',
                ] as $reqField) {
                    if (array_key_exists($reqField, $step4ForService)) {
                        $serviceInsert[$reqField] = $step4ForService[$reqField];
                    }
                }

                $serviceId = $serviceModel->insert($serviceInsert);

                if (!$serviceId) {
                    throw new \Exception('Failed to insert service (Step 1).');
                }

                if (!empty($step1Data['service_tags'])) {
                    $tags = array_map('trim', explode(',', $step1Data['service_tags']));
                    foreach ($tags as $tagName) {
                        if ($tagName === '')
                            continue;

                        $existingTag = $tagsModel->where('name', $tagName)->first();
                        $tagId = $existingTag ? $existingTag['id'] : $tagsModel->insert(['name' => $tagName]);

                        if (!$tagId) {
                            throw new \Exception("Failed to insert or retrieve tag '$tagName' (Step 1).");
                        }

                        $linkResult = $serviceTagsModel->insert([
                            'service_id' => $serviceId,
                            'tag_id' => $tagId,
                        ]);

                        if (!$linkResult) {
                            throw new \Exception("Failed to link tag '$tagName' to service (Step 1).");
                        }
                    }
                }
            }

            // Hard guard: everything else depends on serviceId
            if (!$serviceId) {
                throw new \Exception('Service ID missing. Step 1 may not have completed correctly.');
            }

            /****************************************
             * STEP 2
             ****************************************/
            if (session()->has('step2_data')) {
                $step2Data = session('step2_data');

                $eventTypesRaw = $step2Data['event_types'] ?? '';
                $eventTypes = is_array($eventTypesRaw)
                    ? array_map('trim', $eventTypesRaw)
                    : array_map('trim', explode(',', (string) $eventTypesRaw));
                $eventTypes = array_values(array_filter($eventTypes));

                $serviceEventTypeModel = new ServiceEventTypeModel();
                $servicePrivatePricingModel = new ServicePrivatePricingModel();

                foreach ($eventTypes as $eventType) {
                    $eventTypeId = $serviceEventTypeModel->insert([
                        'service_id' => $serviceId,
                        'event_type' => $eventType,
                    ]);
                    if (!$eventTypeId) {
                        throw new \Exception("Failed to insert event type '$eventType' (Step 2).");
                    }

                    if ($eventType === 'private' && isset($step2Data['pricing_type'])) {
                        $pricingTypeInsertId = $servicePrivatePricingModel->insert([
                            'service_id' => $serviceId,
                            'pricing_type' => $step2Data['pricing_type'],
                        ]);
                        if (!$pricingTypeInsertId) {
                            throw new \Exception("Failed to insert private pricing type '{$step2Data['pricing_type']}' (Step 2).");
                        }
                    }
                }

                $pricingRow = $servicePrivatePricingModel
                    ->where('service_id', $serviceId)
                    ->first();
            }

            /****************************************
             * IMAGES
             ****************************************/
            try {
                $this->finalizeImages($serviceId);
                $this->cleanupTempFiles();
            } catch (\Throwable $t) {
                throw new \Exception('Failed to finalise images: ' . $t->getMessage());
            }

            /****************************************
             * STEP 3
             ****************************************/
            if (session()->has('step3_data')) {
                $step3Data = session('step3_data');
                $step2Data = session('step2_data');

                $eventTypesRaw = $step2Data['event_types'] ?? '';
                $eventTypes = is_array($eventTypesRaw)
                    ? array_map('trim', $eventTypesRaw)
                    : array_map('trim', explode(',', (string) $eventTypesRaw));
                $eventTypes = array_values(array_filter($eventTypes));

                $privateEventPricingId = $pricingRow['id'] ?? null;

                /* ---------- PUBLIC EVENT PRICING ---------- */
                if (in_array('public', $eventTypes, true)) {
                    $minAttendance = $step3Data['min_attendance'] ?? [];
                    $maxAttendance = $step3Data['max_attendance'] ?? [];
                    $maxPitchFee = $step3Data['max_pitch_fee'] ?? [];

                    if (!empty($minAttendance)) {
                        $rows = [];

                        foreach ($minAttendance as $i => $min) {
                            $min = (int) $min;
                            $max = (int) ($maxAttendance[$i] ?? 0);
                            $fee = (float) ($maxPitchFee[$i] ?? 0);

                            // Skip obviously empty/broken rows
                            if ($min <= 0 || $max <= 0 || $fee < 0)
                                continue;

                            $rows[] = [
                                'service_id' => $serviceId,
                                'commission_percentage' => ($step3Data['commission_percentage'] ?? null) !== ''
                                    ? (float) $step3Data['commission_percentage']
                                    : null,
                                'min_attendance' => $min,
                                'max_attendance' => $max,
                                'max_pitch_fee' => $fee,
                            ];
                        }

                        if (!empty($rows)) {
                            $ok = $db->table('services_public_event_pricing')->insertBatch($rows);
                            if (!$ok) {
                                throw new \Exception('Failed to insert public event pricing (Step 3).');
                            }
                        }
                    }
                }

                /* ---------- PRIVATE EVENT PRICING ---------- */
                if ($privateEventPricingId) {

                    // Guest-based
                    if (!empty($step3Data['min_guest']) && !empty($step3Data['max_guest'])) {
                        $guestPricingModel = new ServiceGuestBasedPricingModel();

                        foreach ($step3Data['min_guest'] as $index => $minGuest) {
                            $minGuest = (int) $minGuest;
                            $maxGuest = (int) ($step3Data['max_guest'][$index] ?? 0);
                            $price = (float) ($step3Data['guest_price'][$index] ?? 0);

                            if ($minGuest <= 0 || $maxGuest <= 0 || $price <= 0)
                                continue;

                            $insertResult = $guestPricingModel->insert([
                                'service_id' => $serviceId,
                                'private_event_pricing_id' => $privateEventPricingId,
                                'min_guest' => $minGuest,
                                'max_guest' => $maxGuest,
                                'guest_price' => $price,
                            ]);

                            if (!$insertResult) {
                                throw new \Exception("Failed to insert guest-based pricing row #$index (Step 3).");
                            }
                        }
                    }

                    // Custom duration
                    $serviceDurationPricingModel = new ServiceCustomDurationPricingModel();

                    $enableHours = !empty($step3Data['enableHours']);
                    $enableDays = !empty($step3Data['enableDays']);

                    if ($enableHours && !empty($step3Data['hour_number'])) {
                        foreach ($step3Data['hour_number'] as $index => $hourNumber) {
                            $hour = (int) $hourNumber;
                            $price = (float) ($step3Data['hour_price'][$index] ?? 0);

                            if ($hour <= 0 || $price <= 0)
                                continue;

                            $res = $serviceDurationPricingModel->insert([
                                'service_id' => $serviceId,
                                'private_event_pricing_id' => $privateEventPricingId,
                                'duration_type' => 'hour',
                                'duration' => $hour,
                                'price' => $price,
                            ]);

                            if (!$res) {
                                throw new \Exception("Failed to insert hourly pricing row #$index (Step 3).");
                            }
                        }
                    }

                    if ($enableDays && !empty($step3Data['day_number'])) {
                        foreach ($step3Data['day_number'] as $index => $dayNumber) {
                            $day = (int) $dayNumber;
                            $price = (float) ($step3Data['day_price'][$index] ?? 0);

                            if ($day <= 0 || $price <= 0)
                                continue;

                            $res = $serviceDurationPricingModel->insert([
                                'service_id' => $serviceId,
                                'private_event_pricing_id' => $privateEventPricingId,
                                'duration_type' => 'day',
                                'duration' => $day,
                                'price' => $price,
                            ]);

                            if (!$res) {
                                throw new \Exception("Failed to insert daily pricing row #$index (Step 3).");
                            }
                        }
                    }

                    // Tiered packages
                    if (!empty($step3Data['package_name'])) {
                        $tieredPricingModel = new ServiceTieredPackagesPricingModel();

                        foreach ($step3Data['package_name'] as $index => $packageName) {
                            $name = trim((string) $packageName);
                            $desc = trim((string) ($step3Data['package_description'][$index] ?? ''));
                            $price = (float) ($step3Data['package_price'][$index] ?? 0);

                            if ($name === '' || $desc === '' || $price <= 0)
                                continue;

                            $res = $tieredPricingModel->insert([
                                'service_id' => $serviceId,
                                'private_event_pricing_id' => $privateEventPricingId,
                                'package_name' => $name,
                                'package_description' => $desc,
                                'package_price' => $price,
                            ]);

                            if (!$res) {
                                throw new \Exception("Failed to insert tiered package #$index (Step 3).");
                            }
                        }
                    }

                    // Quantity-based primary pricing
                    if (isset($step3Data['unit_price'], $step3Data['min_quantity'])
                        && $step3Data['unit_price'] !== ''
                        && $step3Data['min_quantity'] !== '') {
                        $quantityPricingModel = new ServiceQuantityPricingModel();
                        $unitPrice = (float) $step3Data['unit_price'];
                        $minQty = max(1, (int) $step3Data['min_quantity']);
                        $maxQtyRaw = $step3Data['max_quantity'] ?? null;
                        $maxQty = ($maxQtyRaw !== null && $maxQtyRaw !== '') ? max($minQty, (int) $maxQtyRaw) : null;
                        $unitLabel = trim((string) ($step3Data['unit_label'] ?? 'items')) ?: 'items';

                        if ($unitPrice > 0) {
                            $res = $quantityPricingModel->insert([
                                'service_id' => $serviceId,
                                'private_event_pricing_id' => $privateEventPricingId,
                                'unit_price' => $unitPrice,
                                'min_quantity' => $minQty,
                                'max_quantity' => $maxQty,
                                'unit_label' => $unitLabel,
                            ]);
                            if (!$res) {
                                throw new \Exception('Failed to insert quantity-based pricing (Step 3).');
                            }
                        }
                    }
                }

                /* ---------- CORPORATE EVENT TEST PRICING (JSON) ---------- */
                if (in_array('corporate', $eventTypes, true)) {
                    $enabled = !empty($step3Data['corporate_enabled']);

                    if ($enabled) {
                        // ✅ Whitelist so you control what ends up in JSON
                        $allowedCorporateKeys = [
                            'corporate_enabled',
                            'corporate_invoice_supported',
                            'corporate_po_supported',
                            'corporate_payment_terms',
                            'corporate_accounts_email',
                            'corporate_vat_registered',
                            'corporate_vat_number',
                            'corporate_pli_level',
                            'corporate_risk_assessment',
                            'corporate_method_statement',
                            'corporate_pat_testing',
                            'corporate_dbs',
                            'corporate_surcharge_type',
                            'corporate_surcharge_value',
                            'corporate_invoice_fee',
                            'corporate_min_spend',
                        ];

                        $corporatePayload = [];
                        foreach ($allowedCorporateKeys as $k) {
                            if (array_key_exists($k, $step3Data)) {
                                $corporatePayload[$k] = $step3Data[$k];
                            }
                        }
                        $corporatePayload['corporate_enabled'] = 1;

                        $ok = $db->table('services_corporate_event_pricing')->insert([
                            'service_id' => $serviceId,
                            'pricing_details' => json_encode($corporatePayload, JSON_UNESCAPED_UNICODE),
                        ]);

                        if (!$ok) {
                            throw new \Exception('Failed to insert corporate event pricing (Step 3).');
                        }
                    }
                }
            }

            /****************************************
             * STEP 4
             ****************************************/
            if (session()->has('step4_data')) {
                $step4Data = session('step4_data');
                $serviceLocationModel = new ServiceLocationModel();

                $locationId = $serviceLocationModel->insert([
                    'service_id'              => $serviceId,
                    'fulfillment_type'        => $step4Data['fulfillment_type'] ?? 'in_person',
                    'service_location'        => $step4Data['service_location'] ?? null,
                    'latitude'                => $step4Data['latitude'] ?? null,
                    'longitude'               => $step4Data['longitude'] ?? null,
                    'all_travel_included'     => !empty($step4Data['all_travel_included']) ? 1 : 0,
                    'no_travel_limit'         => !empty($step4Data['no_travel_limit']) ? 1 : 0,
                    'free_coverage_radius'    => $step4Data['free_coverage_radius'] ?? null,
                    'paid_coverage_radius'    => $step4Data['paid_coverage_radius'] ?? null,
                    'travel_fee_per_km'       => $step4Data['travel_fee_per_km'] ?? null,
                    'postal_fee'              => $step4Data['postal_fee'] ?? null,
                    'free_postage_above'      => $step4Data['free_postage_above'] ?? null,
                    'delivery_lead_time_days' => $step4Data['delivery_lead_time_days'] ?? null,
                ]);

                if (!$locationId) {
                    throw new \Exception("Failed to insert service location (Step 4).");
                }
            }

            /****************************************
             * STEP 5
             ****************************************/
            if (session()->has('step5_data')) {
                $optionalExtras = session('step5_data');
                if (!empty($optionalExtras)) {
                    $optionalExtrasModel = new ServiceOptionalExtrasModel();

                    foreach ($optionalExtras as $index => $extra) {
                        if (!empty($extra['name']) && isset($extra['price'])) {
                            $pricingType = in_array($extra['pricing_type'] ?? '', ['flat', 'per_item'], true)
                                ? $extra['pricing_type']
                                : 'flat';
                            $res = $optionalExtrasModel->insert([
                                'service_id'  => $serviceId,
                                'name'        => trim($extra['name']),
                                'description' => trim($extra['description'] ?? ''),
                                'price'       => (float) $extra['price'],
                                'pricing_type' => $pricingType,
                                'unit_label'  => $extra['unit_label'] ?? null,
                                'min_quantity' => $extra['min_quantity'] ?? null,
                                'max_quantity' => $extra['max_quantity'] ?? null,
                            ]);

                            if (!$res) {
                                throw new \Exception("Failed to insert optional extra #$index (Step 5).");
                            }
                        }
                    }
                }
            }

            /****************************************
             * STEP 6
             ****************************************/
            if (session()->has('step6_data')) {
                $step6Data = session('step6_data');
                if (!empty($step6Data['cancellation_policy'])) {
                    $cancellationPolicyModel = new ServiceCancellationPolicyModel();

                    $res = $cancellationPolicyModel->insert([
                        'service_id' => $serviceId,
                        'cancellation_policy' => trim($step6Data['cancellation_policy']),
                    ]);

                    if (!$res) {
                        throw new \Exception("Failed to insert cancellation policy (Step 6).");
                    }
                }
            }

            $db->transCommit();

            session()->remove([
                'step1_data',
                'step2_data',
                'step3_data',
                'step4_data',
                'optional_extras',
                'step5_data',
                'step6_data',
                'uploaded_images',
            ]);

            return redirect()->to('/service/success')->with('success', 'Service created successfully!');

        } catch (\Exception $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }

            return redirect()->back()->with('error', 'Error saving service: ' . $e->getMessage());
        }
    }



    public function success()
    {
        return view('service_create/success');
    }
    private function handleImages()
    {
        $imageFiles = $this->request->getFiles();

        if ($imageFiles && isset($imageFiles['images'])) {
            // Retrieve existing images from session
            $uploadedImages = session()->get('uploaded_images') ?? [];

            foreach ($imageFiles['images'] as $image) {
                if ($image->isValid() && !$image->hasMoved()) {
                    // Save image to a temporary folder
                    $newName = $image->getRandomName();
                    $image->move(ROOTPATH . 'public/uploads/temp/', $newName);
                    $imagePath = 'uploads/temp/' . $newName;

                    // Append the new image to the session data
                    $uploadedImages[] = [
                        'formId' => uniqid(), // Generate a unique ID for each image
                        'image_path' => $imagePath,
                        //'file_name' => $file->getName(),
                        'is_primary' => false,
                    ];
                }
            }

            // Update the session with all images
            session()->set('uploaded_images', $uploadedImages);
        }
    }


    private function finalizeImages($serviceId)
    {
        // Get uploaded images from session
        $uploadedImages = session()->get('uploaded_images') ?? [];
        $finalImages = [];

        if (empty($uploadedImages)) {
            log_message('error', 'No uploaded images found in session.');
            return;
        }

        $serviceImageModel = new \App\Models\ServiceImageModel();
        $primarySet = false; // Flag to track if a primary image has been set

        foreach ($uploadedImages as $index => $image) {
            // Check if 'image_path' key exists and construct temp path
            if (!isset($image['image_path'])) {
                log_message('error', "Image at index {$index} is missing 'image_path': " . json_encode($image));
                continue; // Skip this image
            }

            $tempPath = ROOTPATH . 'public/' . $image['image_path'];

            // Ensure the file exists at the temp location
            if (file_exists($tempPath)) {
                $newName = basename($tempPath);
                $finalPath = ROOTPATH . 'public/uploads/services/' . $newName;

                if (rename($tempPath, $finalPath)) {
                    // Optionally generate a thumbnail
                    $thumbnailPath = $this->createThumbnail('uploads/services/' . $newName);

                    // Determine if this image is primary
                    $isPrimary = $image['is_primary'] ?? false;

                    // If no primary image has been set yet, make this the primary image
                    if (!$primarySet) {
                        $isPrimary = true;
                        $primarySet = true;
                    }

                    // Prepare image metadata
                    $finalImageData = [
                        'service_id' => $serviceId,
                        'image_path' => 'uploads/services/' . $newName,
                        'thumbnail_path' => $thumbnailPath,
                        'is_primary' => $isPrimary,
                    ];

                    // Save image record in the database
                    $serviceImageModel->insert($finalImageData);

                    // Add to finalImages array
                    $finalImages[] = $finalImageData;
                } else {
                    log_message('error', "Failed to move file from {$tempPath} to {$finalPath}");
                }
            } else {
                log_message('error', "Temporary file not found at {$tempPath}");
            }
        }

        // Update session with finalized image data
        session()->set('uploaded_images', $finalImages);
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





    private function cleanupTempFiles()
    {
        $tempFolder = ROOTPATH . 'public/uploads/temp/';
        $files = glob($tempFolder . '*'); // Get all files in the temp folder

        foreach ($files as $file) {
            // Delete files older than 1 hour
            if (is_file($file) && time() - filemtime($file) > 3600) {
                if (unlink($file)) {
                    log_message('info', "Deleted temp file: {$file}");
                } else {
                    log_message('error', "Failed to delete temp file: {$file}");
                }
            }
        }
    }
    private function saveImagesToDatabase($serviceId)
    {
        $uploadedImages = session()->get('uploaded_images') ?? [];

        foreach ($uploadedImages as $index => $image) {
            $serviceImageModel = new ServiceImageModel();
            $serviceImageModel->save([
                'service_id' => $serviceId,
                'image_path' => $image['image_path'],
                'thumbnail_path' => $image['thumbnail_path'],
                'is_primary' => $index === 0, // Set the first image as primary
            ]);
        }

        // Clear images from the session after saving to the database
        session()->remove('uploaded_images');
    }
    private function createThumbnail($imagePath)
    {
        try {
            // Ensure the thumbnail directory exists
            $thumbnailDir = ROOTPATH . 'public/uploads/services/thumbnails/';
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            // Generate thumbnail
            $imageService = \Config\Services::image();
            $thumbnailName = 'thumb_' . basename($imagePath);
            $thumbnailPath = 'uploads/services/thumbnails/' . $thumbnailName;

            $imageService->withFile(ROOTPATH . 'public/' . $imagePath)
                ->fit(200, 200, 'center')
                ->save(ROOTPATH . 'public/' . $thumbnailPath);

            return $thumbnailPath;
        } catch (\Exception $e) {
            log_message('error', 'Thumbnail generation failed: ' . $e->getMessage());
            return null; // Return null on failure to indicate no thumbnail created
        }
    }
    private function savePublicEventData($serviceId)
    {
        $ServicePublicEventData = [
            'service_id' => $serviceId,
            'commission_percentage' => $this->request->getPost('commission_percentage'),
            'license' => $this->request->getFile('license')
        ];

        // Save attendance thresholds and pitch fees
        $attendanceThresholds = $this->request->getPost('attendance_threshold');
        $maxPitchFees = $this->request->getPost('max_pitch_fee');
        if ($attendanceThresholds && $maxPitchFees) {
            foreach ($attendanceThresholds as $index => $threshold) {
                $ServicePublicEventData['attendance_threshold'] = $threshold;
                $ServicePublicEventData['max_pitch_fee'] = $maxPitchFees[$index];

                // Save to database
                $ServicePublicEventModel = new ServicePublicEventModel();
                $ServicePublicEventModel->save($ServicePublicEventData);
            }
        }

        // Save license file if uploaded
        if ($ServicePublicEventData['license']->isValid() && !$ServicePublicEventData['license']->hasMoved()) {
            $newName = $publicEventData['license']->getRandomName();
            $ServicePublicEventData['license']->move(ROOTPATH . 'public/uploads/licenses/', $newName);
            $ServicePublicEventData['license'] = 'uploads/licenses/' . $newName;
        }
    }
    //SAve private event -> guest-based pricing
    private function saveGuestBasedPricing($serviceId)
    {
        $db = \Config\Database::connect();
        $guestPricingBuilder = $db->table('service_guest_pricing');

        // Retrieve data from the form
        $minGuests = $this->request->getPost('min_guest');
        $maxGuests = $this->request->getPost('max_guest');
        $guestPrices = $this->request->getPost('guest_price');

        // Validate input
        if (!$minGuests || !$maxGuests || !$guestPrices) {
            return false; // Return false if data is missing
        }

        // Clear existing entries for the service
        $guestPricingBuilder->where('service_id', $serviceId)->delete();

        // Insert new guest-based pricing ranges
        foreach ($minGuests as $index => $minGuest) {
            $guestPricingBuilder->insert([
                'service_id' => $serviceId,
                'min_guests' => $minGuest,
                'max_guests' => $maxGuests[$index],
                'price_per_guest' => $guestPrices[$index],
            ]);
        }

        return true; // Return true if data saved successfully
    }
    private function saveDurationPricing($serviceId)
    {
        // Load the request data for hours
        $hourNumbers = $this->request->getPost('hour_number');
        $hourPrices = $this->request->getPost('hour_price');

        // Load the request data for days
        $dayNumbers = $this->request->getPost('day_number');
        $dayPrices = $this->request->getPost('day_price');

        $durationPricingModel = new \App\Models\ServiceCustomDurationPricingModel();

        // Remove existing pricing for this service to prevent duplicates
        $durationPricingModel->where('service_id', $serviceId)->delete();

        // Save hours pricing
        if ($hourNumbers && $hourPrices) {
            foreach ($hourNumbers as $index => $number) {
                if (!empty($number)) {
                    $durationPricingModel->save([
                        'service_id' => $serviceId,
                        'duration_number' => (int) $number,
                        'duration_unit' => 'hours', // Set the unit to 'hours'
                        'duration_price' => (float) $hourPrices[$index],
                    ]);
                }
            }
        }

        // Save days pricing
        if ($dayNumbers && $dayPrices) {
            foreach ($dayNumbers as $index => $number) {
                if (!empty($number)) {
                    $durationPricingModel->save([
                        'service_id' => $serviceId,
                        'duration_number' => (int) $number,
                        'duration_unit' => 'days', // Set the unit to 'days'
                        'duration_price' => (float) $dayPrices[$index],
                    ]);
                }
            }
        }
    }
    private function saveTieredPackages($serviceId)
    {
        // Load request data
        $packageNames = $this->request->getPost('package_name');
        $packageDescriptions = $this->request->getPost('package_description');
        $packagePrices = $this->request->getPost('package_price');

        // Ensure all required data is provided
        if ($packageNames && $packageDescriptions && $packagePrices) {
            $tieredPackageModel = new \App\Models\ServiceTieredPackagesModel();

            // Remove existing packages for this service to prevent duplicates
            $tieredPackageModel->where('service_id', $serviceId)->delete();

            // Save new package data
            foreach ($packageNames as $index => $name) {
                if (!empty($name) && isset($packageDescriptions[$index]) && isset($packagePrices[$index])) {
                    $tieredPackageModel->save([
                        'service_id' => $serviceId,
                        'package_name' => $name,
                        'package_description' => $packageDescriptions[$index],
                        'package_price' => (float) $packagePrices[$index],
                    ]);
                }
            }
        }
    }
    private function saveOptionalExtras($serviceId, $extraNames, $extraPrices)
    {
        $optionalExtrasModel = new ServiceOptionalExtrasModel();
        $optionalExtrasModel->where('service_id', $serviceId)->delete();

        foreach ($extraNames as $index => $name) {
            $name = trim((string) ($name ?? ''));
            if ($name === '') {
                continue;
            }
            $optionalExtrasModel->insert([
                'service_id' => $serviceId,
                'name'       => $name,
                'price'      => (float) ($extraPrices[$index] ?? 0),
            ]);
        }
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
        ];

        // Render the view
        return view('service_view', $data);
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
     * When browsing with ?event_id=, hide vendors outside strict travel radius.
     *
     * @param list<array<string,mixed>> $services
     * @return list<array<string,mixed>>
     */
    private function filterServicesByEventCoverage(array $services): array
    {
        $eventId = $this->request->getGet('event_id');
        if ($eventId === null || $eventId === '') {
            return $services;
        }

        $eventModel = new EventModel();
        $event = $eventModel->find((int) $eventId);
        if (!$event || empty($event['latitude']) || empty($event['longitude'])) {
            return $services;
        }

        $locationModel = new ServiceLocationModel();
        $quoteBuilder = new EventQuoteBuilder();
        $filtered = [];

        foreach ($services as $service) {
            $locRow = $locationModel->where('service_id', (int) $service['id'])->first();
            $loc = $quoteBuilder->mergeServiceLocation($service, $locRow);
            if (empty($loc['strict_travel_radius'])) {
                $filtered[] = $service;
                continue;
            }

            $distance = EventBookingQuote::haversineKm(
                (float) $loc['latitude'],
                (float) $loc['longitude'],
                (float) $event['latitude'],
                (float) $event['longitude']
            );
            $travel = (new EventBookingQuote())->computeTravel($distance, $loc);
            $blocked = false;
            foreach ($travel['warnings'] as $w) {
                if (str_contains($w, 'exceeds the vendor') || str_contains($w, 'beyond the maximum')) {
                    $blocked = true;
                    break;
                }
            }
            if (!$blocked) {
                $filtered[] = $service;
            }
        }

        return $filtered;
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
