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
use App\Models\OptionalExtraModel;
use App\Models\ServiceTagsModel;
use App\Models\TagsModel;
use App\Models\ServiceEventTypeModel;
use App\Models\ServicePrivatePricingModel;
use App\Models\ServiceCustomDurationPricingModel;
use App\Models\ServiceTieredPackagesPricingModel;
use App\Models\ServiceGuestBasedPricingModel;
use App\Models\ServiceCancellationPolicyModel;


use App\Models\ServiceLocationModel;
use App\Models\ServiceOptionalExtrasModel;
use App\Models\CartModel;
use CodeIgniter\Controller;
use Config\Services;
use DateTime;


class Service_Controller extends BaseController
{
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
                $rules['pricing_type'] = 'required|in_list[guest_based_pricing,custom_duration_pricing,tiered_packages_pricing]';
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
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            /* ---------------- BUILD STEP3 SESSION PAYLOAD SAFELY ---------------- */
            $post = $this->request->getPost();

            // Start with existing step3_data so you can revisit step3 without losing data
            $step3 = session('step3_data') ?? [];

            // Always store fields you use for review/pricing tables
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
                }

                if ($pricingType === 'tiered_packages_pricing') {
                    $step3['package_name'] = $post['package_name'] ?? [];
                    $step3['package_description'] = $post['package_description'] ?? [];
                    $step3['package_price'] = $post['package_price'] ?? [];

                    unset($step3['min_guest'], $step3['max_guest'], $step3['guest_price']);
                    unset($step3['enableHours'], $step3['enableDays'], $step3['hour_number'], $step3['hour_price'], $step3['day_number'], $step3['day_price']);
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
                    $step3['package_price']
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

            session()->set('step3_data', $step3);

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
            // Define validation rules
            $rules = [
                'service_location' => 'required',
                'latitude' => 'required|decimal',
                'longitude' => 'required|decimal',
                'free_coverage_radius' => 'permit_empty|integer|greater_than_equal_to[0]',
                'paid_coverage_radius' => 'permit_empty|integer|greater_than_equal_to[0]',
                'travel_fee_per_km' => 'permit_empty|decimal|greater_than_equal_to[0]',
            ];

            // Validate form data
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            // Retrieve form inputs
            $allTravelIncluded = $this->request->getPost('all_travel_included') ? true : false;
            $noTravelLimit = $this->request->getPost('no_travel_limit') ? true : false;
            $freeCoverageRadius = $this->request->getPost('free_coverage_radius');
            $paidCoverageRadius = $this->request->getPost('paid_coverage_radius');
            $travelFeePerKm = $this->request->getPost('travel_fee_per_km');

            // Validate combinations
            if ($allTravelIncluded && $noTravelLimit) {
                // If both options are selected, no other inputs should have values
                if (!empty($freeCoverageRadius) || !empty($paidCoverageRadius) || !empty($travelFeePerKm)) {
                    return redirect()->back()->withInput()->with('error', 'When both "All Travel Included" and "No Travel Limit" are selected, no additional inputs are allowed.');
                }
            } elseif ($allTravelIncluded) {
                // If "All Travel Included" is selected, no paid coverage radius or travel fee per km is allowed
                if (!empty($paidCoverageRadius) || !empty($travelFeePerKm)) {
                    return redirect()->back()->withInput()->with('error', 'When "All Travel Included" is selected, paid coverage radius and travel fee per km must be empty.');
                }
            } elseif ($noTravelLimit) {
                // If "No Travel Limit" is selected, paid coverage radius must be empty
                if (!empty($paidCoverageRadius)) {
                    return redirect()->back()->withInput()->with('error', 'When "No Travel Limit" is selected, paid coverage radius must be empty.');
                }
                // Travel fee per km is required
                if (empty($travelFeePerKm)) {
                    return redirect()->back()->withInput()->with('error', '"No Travel Limit" requires a travel fee per km.');
                }
            } else {
                // If neither option is selected, ensure valid free and paid coverage radii

                if ($freeCoverageRadius >= $paidCoverageRadius) {
                    //return redirect()->back()->withInput()->with('error', 'Paid coverage radius must be greater than the free coverage radius.');
                }
            }

            // Save validated data to the session
            $step4Data = [
                'service_location' => $this->request->getPost('service_location'),
                'latitude' => $this->request->getPost('latitude'),
                'longitude' => $this->request->getPost('longitude'),
                'all_travel_included' => $allTravelIncluded ? 1 : 0,
                'no_travel_limit' => $noTravelLimit ? 1 : 0,
                'free_coverage_radius' => $freeCoverageRadius,
                'paid_coverage_radius' => $paidCoverageRadius,
                'travel_fee_per_km' => $travelFeePerKm,
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
            // Retrieve optional extras data from POST
            $extraNames = $this->request->getPost('extra_name');
            $extraPrices = $this->request->getPost('extra_price');
            $extraDescriptions = $this->request->getPost('extra_description');
            $extraQuantity = $this->request->getPost('extra_quantity');

            // Validation
            if (is_array($extraNames) && is_array($extraPrices) && is_array($extraDescriptions)) {
                foreach ($extraNames as $index => $extraName) {
                    // Ensure name, description, and price are provided
                    if (empty(trim($extraName)) || empty(trim($extraDescriptions[$index])) || empty(trim($extraPrices[$index]))) {
                        //return redirect()->back()->withInput()->with('error', 'All optional extras, their descriptions, and prices must be filled out.');
                    }

                    // Ensure price is a valid number
                    if (!is_numeric($extraPrices[$index]) || $extraPrices[$index] < 0) {
                        //return redirect()->back()->withInput()->with('error', 'Prices must be valid positive numbers.');
                    }
                }
            } else {
                return redirect()->back()->withInput()->with('error', 'Invalid input format for optional extras.');
            }

            // Save optional extras to session
            $optionalExtras = [];
            foreach ($extraNames as $index => $extraName) {
                $optionalExtras[] = [
                    'name' => trim($extraName),
                    'description' => trim($extraDescriptions[$index]),
                    'price' => (float) trim($extraPrices[$index]),
                    'quantity' => trim($extraQuantity[$index]),
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

                $serviceId = $serviceModel->insert([
                    'vendor_id' => $step1Data['vendor_id'],
                    'title' => $step1Data['title'],
                    'short_description' => $step1Data['short_description'],
                    'description' => $step1Data['description'],
                    'category_id' => $step1Data['category_id'],
                    'subcategory_id' => $step1Data['subcategory_id'] ?? null,
                    'third_category_id' => $step1Data['third_category_id'] ?? null,
                ]);

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
                    'service_id' => $serviceId,
                    'service_location' => $step4Data['service_location'],
                    'latitude' => $step4Data['latitude'],
                    'longitude' => $step4Data['longitude'],
                    'all_travel_included' => !empty($step4Data['all_travel_included']) ? 1 : 0,
                    'no_travel_limit' => !empty($step4Data['no_travel_limit']) ? 1 : 0,
                    'free_coverage_radius' => $step4Data['free_coverage_radius'],
                    'paid_coverage_radius' => $step4Data['paid_coverage_radius'],
                    'travel_fee_per_km' => $step4Data['travel_fee_per_km'],
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
                            $res = $optionalExtrasModel->insert([
                                'service_id' => $serviceId,
                                'name' => trim($extra['name']),
                                'description' => trim($extra['description'] ?? ''),
                                'price' => (float) trim($extra['price']),
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
        $optionalExtraModel = new OptionalExtraModel();

        // Prepare the data
        $extras = [];
        foreach ($extraNames as $index => $name) {
            $extras[] = [
                'name' => $name,
                'price' => $extraPrices[$index]
            ];
        }

        // Save the extras to the database
        return $optionalExtraModel->saveExtras($serviceId, $extras);
    }
    public function update($id = null)
    {
        if (!session()->has('user_id') || session()->get('role') !== 'vendor') {
            return redirect()->to('/')->with('error', 'You are not authorized to edit services.');
        }

        $serviceModel = new ServiceModel();
        $service = $serviceModel->getServiceWithImages($id);

        if (!$service || $service['vendor_id'] != session()->get('user_id')) {
            return redirect()->to('/profile')->with('error', 'Service not found or you are not authorized to edit it.');
        }

        // Retrieve the time blocks associated with the service



        $data['categories'] = $this->buildCategoryTree();
        $data['service'] = $service;


        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'title' => 'required|min_length[3]|max_length[255]',
                'short_description' => 'required',
                'description' => 'required',
                'price' => 'required|decimal',
                'category_id' => 'required|is_natural_no_zero',
                'subcategory_id' => 'required|is_natural_no_zero',
                'images.*' => 'max_size[images,10240]|is_image[images]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            } else {
                $serviceModel->transStart();

                $serviceData = [
                    'title' => $this->request->getPost('title'),
                    'description' => $this->request->getPost('description'),
                    'short_description' => $this->request->getPost('short_description'),
                    'price' => $this->request->getPost('price'),
                    'category_id' => $this->request->getPost('category_id'),
                    'subcategory_id' => $this->request->getPost('subcategory_id'),
                ];

                if ($serviceModel->update($id, $serviceData)) {
                    $this->handleImages($id);

                    // Update time blocks

                    $serviceModel->transComplete();
                    return redirect()->to('/service/edit/' . $id)->with('success', 'Service updated successfully.');
                } else {
                    $serviceModel->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Failed to update service in the database.');
                }
            }
        }

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
        $serviceLocationModel = new ServiceLocationModel();
        $optionalExtrasModel = new ServiceOptionalExtrasModel();

        // Fetch the service details
        $service = $serviceModel
            ->select('services.*, categories.name as category_name')
            ->join('categories', 'categories.id = services.category_id', 'left')
            ->find($id);

        if (!$service) {
            return redirect()->to('/service')->with('error', 'Service not found.');
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

        // Fetch service location details
        $location = $serviceLocationModel->where('service_id', $id)->first();

        // Fetch optional extras
        $optionalExtras = $optionalExtrasModel->where('service_id', $id)->findAll();

        // Compile data for the view
        $data = [
            'service' => $service,
            'images' => $images,
            'eventTypes' => $eventTypes,
            'privatePricing' => $privatePricing,
            'guestPricing' => $guestPricing,
            'durationPricing' => $durationPricing,
            'tieredPackages' => $tieredPackages,
            'location' => $location,
            'optionalExtras' => $optionalExtras,
        ];

        // Render the view
        return view('service_view', $data);
    }

    private function validateSessionData($key)
    {
        return session()->has($key) && !empty(session($key));
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

    private function saveTimeBlocks($serviceId, $timeBlocks)
    {
        $serviceTimeBlockModel = new ServiceTimeBlockModel();

        // Clear existing time blocks before saving new ones
        $serviceTimeBlockModel->where('service_id', $serviceId)->delete();

        foreach ($timeBlocks as $timeBlock) {
            $serviceTimeBlockModel->save([
                'service_id' => $serviceId,
                'time_length' => trim($timeBlock)
            ]);
        }
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




}
