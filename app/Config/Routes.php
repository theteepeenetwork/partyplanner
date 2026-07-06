<?php
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ── White-label tenant hosts ─────────────────────────────────────────────
// A vendor subdomain (<slug>.<tenant.baseDomain>) gets ONLY the tenant
// storefront routes; every marketplace URL 404s there, so tenant traffic
// can never wander into cross-vendor surfaces. Any other host — the main
// domain, www, partyplanner.home, CLI (`spark`) — skips this block and the
// marketplace routes below are registered exactly as before.
if (\App\Libraries\TenantHost::current() !== null) {
    $routes->group('', ['filter' => 'vendortenant'], static function ($routes) {
        $routes->get('/', 'TenantController::home');
        $routes->get('service/(:num)', 'TenantController::service/$1');
    });

    return;
}

$routes->get('/', 'Home::index');

// User Routes
$routes->get('/register', 'Register::index');
$routes->post('/register/create', 'Register::create');
$routes->get('/register/success', 'Register::success');
$routes->get('/register/vendor', 'Register::vendor');
$routes->post('/register/vendor/create', 'Register::createVendor');
$routes->get('/register/vendor/success', 'Register::vendorSuccess');

$routes->get('/login', 'Login::index');
$routes->post('/login/attempt', 'Login::attempt');
$routes->get('/logout', 'Login::logout');

$routes->get('/forgot-password', 'ForgotPassword::index');
$routes->post('/forgot-password', 'ForgotPassword::send');
$routes->get('/reset-password', 'ResetPassword::index');
$routes->post('/reset-password', 'ResetPassword::submit');

// Profile / Account Routes
$routes->get('/profile', 'Profile::index');
$routes->get('/profile/main', 'Profile::index');
$routes->get('/dashboard', 'Profile::index');
$routes->get('/profile/edit', 'Profile::edit');
$routes->post('/profile/edit', 'Profile::edit');
$routes->post('/profile/update-booking-status/(:num)', 'Profile::updateBookingStatus/$1', ['filter' => 'vendorauth']);
$routes->match(['GET', 'POST'], '/profile/quote-settings', 'Profile::quoteSettings', ['filter' => 'vendorauth']);
$routes->match(['GET', 'POST'], '/profile/quote-analytics', 'Profile::quoteAnalytics', ['filter' => 'vendorauth']);
$routes->post('/profile/bookings/bulk-status', 'Profile::bulkUpdateBookingStatus', ['filter' => 'vendorauth']);
$routes->match(['GET', 'POST'], '/profile/vendor-quote/(:num)', 'Profile::vendorQuote/$1', ['filter' => 'vendorauth']);
$routes->post('/profile/vendor-quote/(:num)/send', 'Profile::sendVendorQuote/$1', ['filter' => 'vendorauth']);
$routes->post('/profile/vendor-quote/(:num)/accept', 'Profile::acceptVendorQuote/$1');

// Vendor tabs
$routes->get('/profile/services', 'Profile::services', ['filter' => 'vendorauth']);
$routes->get('/profile/bookings', 'Profile::vendorBookings', ['filter' => 'vendorauth']);
$routes->get('/profile/request/(:num)', 'Profile::vendorRequestDetail/$1', ['filter' => 'vendorauth']);
$routes->get('/profile/earnings', 'Profile::vendorEarnings', ['filter' => 'vendorauth']);
$routes->get('/profile/calendar', 'Profile::vendorCalendar', ['filter' => 'vendorauth']);
$routes->get('/profile/calendar-data', 'Profile::calendarData', ['filter' => 'vendorauth']);
$routes->post('/profile/calendar/toggle-block', 'Profile::toggleBlockDate', ['filter' => 'vendorauth']);
$routes->match(['GET', 'POST'], '/profile/host-profile', 'Profile::hostProfile', ['filter' => 'vendorauth']);

// Customer tabs
$routes->get('/profile/events', 'Profile::customerEvents');
$routes->get('/profile/events/(:num)', 'Profile::customerEventDetail/$1');
$routes->get('/profile/set-active-event/(:num)', 'Profile::setActiveEvent/$1');
$routes->get('/profile/my-bookings', 'Profile::customerBookings');
$routes->get('/profile/my-bookings/(:num)', 'Profile::customerBookingDetail/$1');
$routes->get('/profile/messages', 'Profile::customerMessages');
$routes->get('/profile/messages/start/(:num)', 'Profile::startMessageForService/$1');
$routes->get('/profile/messages/by-booking/(:num)', 'Profile::openThreadForBookingItem/$1');
$routes->get('/profile/messages/(:num)', 'Profile::customerMessageThread/$1');
$routes->post('/profile/messages/send', 'Profile::sendMessage');
$routes->get('/profile/payments', 'Profile::customerPayments');
$routes->get('/profile/favourites', 'Profile::customerFavourites');
$routes->get('/profile/favourites/remove/(:num)', 'Profile::removeFavourite/$1');

// Reviews (customer)
$routes->get('/review/create/(:num)', 'ReviewController::create/$1');
$routes->post('/review/store', 'ReviewController::store');

// Public vendor profile
$routes->get('vendor/(:num)', 'Service_Controller::vendorProfile/$1');


// Browse Services (public)
$routes->get('/browse-services', 'Service_Controller::browse');
$routes->get('/services', 'Service_Controller::browse');
// Hero / legacy search form posts to `/search` with `category` (and optional `q`)
$routes->get('search', 'Service_Controller::search');
// Legacy path used by older views; forwards to the same handler as `/search`
$routes->get('service/search', 'Service_Controller::search');

// Service Routes
$routes->get('service/view/(:num)', 'Service_Controller::view/$1');
$routes->get('/service', 'Service_Controller::index', ['filter' => 'vendorauth']);
$routes->post('/service/remove-optional-extra', 'Service_Controller::removeOptionalExtra', ['filter' => 'vendorauth']);
$routes->post('/service/duplicate/(:num)', 'Service_Controller::duplicateService/$1', ['filter' => 'vendorauth']);

$routes->post('/service/delete-image/(:any)', 'Service_Controller::deleteImage/$1', ['filter' => 'vendorauth']);


// Multi-step service-creation wizard (step1–6 → review → saveService → success).
$routes->match(['GET', 'POST'], '/service/create', 'Service_Controller::step1', ['filter' => 'vendorauth']);
$routes->match(['GET', 'POST'], '/service/step1', 'Service_Controller::step1', ['filter' => 'vendorauth']);
$routes->match(['GET', 'POST'], '/service/service_create_step2', 'Service_Controller::step2', ['filter' => 'vendorauth']);
$routes->match(['GET', 'POST'], '/service/step2', 'Service_Controller::step2', ['filter' => 'vendorauth']);
$routes->match(['GET', 'POST'], '/service/step3', 'Service_Controller::step3', ['filter' => 'vendorauth']);
$routes->match(['GET', 'POST'], '/service/step4', 'Service_Controller::step4', ['filter' => 'vendorauth']);
$routes->match(['GET', 'POST'], '/service/step5', 'Service_Controller::step5', ['filter' => 'vendorauth']);
$routes->match(['GET', 'POST'], '/service/step6', 'Service_Controller::step6', ['filter' => 'vendorauth']);
$routes->match(['GET', 'POST'], '/service/review', 'Service_Controller::review', ['filter' => 'vendorauth']);
$routes->match(['GET', 'POST'], '/service/saveService', 'Service_Controller::saveService', ['filter' => 'vendorauth']);
$routes->get('/service/success', 'Service_Controller::success', ['filter' => 'vendorauth']);

$routes->match(['GET', 'POST'], '/service/edit/(:num)', 'Service_Controller::update/$1', ['filter' => 'vendorauth']);
$routes->post('/service/update/(:num)', 'Service_Controller::update/$1', ['filter' => 'vendorauth']);
$routes->post('/service/set-primary-image/(:num)', 'Service_Controller::setPrimaryImage/$1', ['filter' => 'vendorauth']);

$routes->post('service/book', 'Service_Controller::bookService');



// Service status management
$routes->match(['GET', 'POST'], 'service/deactivate/(:num)', 'Service_Controller::deactivate/$1', ['filter' => 'vendorauth']);
$routes->match(['GET', 'POST'], 'service/reactivate/(:num)', 'Service_Controller::reactivate/$1', ['filter' => 'vendorauth']);
$routes->match(['GET', 'POST'], 'service/delete/(:num)', 'Service_Controller::delete/$1', ['filter' => 'vendorauth']);
$routes->post('service/toggle-status/(:num)', 'Service_Controller::toggleStatus/$1', ['filter' => 'vendorauth']);


// Cart (retired) — redirect legacy GET entry points to the event-basket flow.
// The former POST money-path routes (cart/submit, cart/submitToVendors,
// cart/processPayment) and the cart/update route (never implemented) are
// intentionally not defined here; they now 404.
$routes->get('/cart', static function () {
    session()->setFlashdata('info', 'The cart has been retired. You can now add services directly to your events.');

    return redirect()->to('/profile/events');
});
$routes->match(['GET', 'POST'], '/cart/add/(:num)', static function ($serviceId = null) {
    session()->setFlashdata('info', 'The cart has been retired. Please select an event and add services directly.');

    return redirect()->to('/profile/events');
});

$routes->get('booking/success/(:num)', 'BookingController::paymentSuccess/$1');
$routes->post('webhook/stripe', 'WebhookController::stripe');
$routes->post('webhooks/stripe', 'WebhookController::stripe');



// Event Creation Flow
$routes->get('/event/create', 'EventController::create');
$routes->match(['GET', 'POST'], '/event/create/step1', 'EventController::createStep1');
$routes->match(['GET', 'POST'], '/event/create/step2', 'EventController::createStep2');
$routes->match(['GET', 'POST'], '/event/create/step3', 'EventController::createStep3');
$routes->get('/event/create/review', 'EventController::createReview');
$routes->post('/event/store', 'EventController::store');

// Add to Event Flow
$routes->match(['GET', 'POST'], '/event/add-to-event/(:num)', 'EventController::addToEvent/$1');
$routes->match(['GET', 'POST'], '/event/add-to-basket/(:num)', 'EventController::addToBasket/$1');
$routes->get('/event/quote-preview/(:num)/(:num)', 'EventController::quotePreview/$1/$2');

// Event Basket
$routes->get('/event/basket/(:num)', 'EventController::basket/$1');
$routes->get('/event/basket/remove/(:num)', 'EventController::removeFromBasket/$1');

// Checkout
$routes->get('/event/checkout/(:num)', 'EventController::checkout/$1');
$routes->post('/event/checkout/process/(:num)', 'EventController::processCheckout/$1');
$routes->get('/event/checkout/success/(:num)', 'EventController::checkoutSuccess/$1');

// Chat Routes
$routes->get('/chat/start/(:num)/(:num)', 'ChatController::startChat/$1/$2');
$routes->get('/chat/view/(:num)', 'ChatController::viewChat/$1');
$routes->post('/chat/sendMessage', 'ChatController::sendMessage');
$routes->get('/chat/checkNewMessages', 'ChatController::checkNewMessages');



//booking routes
$routes->get('calendarView/(:num)/(:num)', 'BookingController::calendarView/$1/$2');
$routes->get('calendarView', 'BookingController::calendarView');
$routes->get('calendarData/(:num)/(:num)', 'BookingController::calendarData/$1/$2');

// Public CMS pages (published only)
$routes->get('about', 'PublicPage::about');
$routes->get('how-it-works', 'PublicPage::howItWorks');
$routes->get('contact', 'PublicPage::contact');
$routes->post('contact', 'PublicPage::submitContact');
$routes->get('vendor-info', 'PublicPage::vendorInfo');
$routes->get('faq', 'PublicPage::faq');
$routes->get('sitemap', 'PublicPage::sitemap');
$routes->get('page/(:segment)', 'PublicPage::show/$1');

// Admin backend
$routes->group('admin', ['filter' => ['adminauth', 'csrf']], static function ($routes) {
    $routes->get('/', 'Admin\Dashboard::index');

    $routes->get('customers', 'Admin\Customers::index');
    $routes->get('customers/(:num)', 'Admin\Customers::show/$1');
    $routes->match(['GET', 'POST'], 'customers/(:num)/edit', 'Admin\Customers::edit/$1');
    $routes->get('customers/(:num)/delete', 'Admin\Customers::deleteConfirm/$1');
    $routes->post('customers/(:num)/delete', 'Admin\Customers::delete/$1');

    $routes->get('vendors', 'Admin\Vendors::index');
    $routes->get('vendors/(:num)', 'Admin\Vendors::show/$1');
    $routes->match(['GET', 'POST'], 'vendors/(:num)/edit', 'Admin\Vendors::edit/$1');
    $routes->get('vendors/(:num)/delete', 'Admin\Vendors::deleteConfirm/$1');
    $routes->post('vendors/(:num)/delete', 'Admin\Vendors::delete/$1');
    $routes->post('vendors/(:num)/approve', 'Admin\Vendors::approve/$1');
    $routes->post('vendors/(:num)/reject', 'Admin\Vendors::reject/$1');

    $routes->get('bookings', 'Admin\Bookings::index');
    $routes->get('bookings/(:num)', 'Admin\Bookings::show/$1');
    $routes->post('bookings/(:num)/status', 'Admin\Bookings::updateStatus/$1');
    $routes->get('bookings/(:num)/delete', 'Admin\Bookings::deleteConfirm/$1');
    $routes->post('bookings/(:num)/delete', 'Admin\Bookings::delete/$1');

    $routes->post('messages/moderate/(:num)/approve', 'Admin\Messages::approveMessage/$1');
    $routes->post('messages/moderate/(:num)/reject', 'Admin\Messages::rejectMessage/$1');
    $routes->get('messages', 'Admin\Messages::index');
    $routes->get('messages/(:num)', 'Admin\Messages::thread/$1');
    $routes->post('messages/delete/(:num)', 'Admin\Messages::deleteMessage/$1');
    $routes->post('messages/(:num)/flag', 'Admin\Messages::flagRoom/$1');
    $routes->post('messages/(:num)/unflag', 'Admin\Messages::unflagRoom/$1');

    $routes->get('services', 'Admin\Services::index');
    $routes->get('services/(:num)', 'Admin\Services::show/$1');
    $routes->match(['GET', 'POST'], 'services/(:num)/edit', 'Admin\Services::edit/$1');
    $routes->post('services/(:num)/toggle', 'Admin\Services::toggleStatus/$1');
    $routes->get('services/(:num)/delete', 'Admin\Services::deleteConfirm/$1');
    $routes->post('services/(:num)/delete', 'Admin\Services::delete/$1');

    $routes->get('events', 'Admin\Events::index');
    $routes->get('events/(:num)', 'Admin\Events::show/$1');
    $routes->match(['GET', 'POST'], 'events/(:num)/edit', 'Admin\Events::edit/$1');
    $routes->get('events/(:num)/delete', 'Admin\Events::deleteConfirm/$1');
    $routes->post('events/(:num)/delete', 'Admin\Events::delete/$1');

    $routes->get('pages', 'Admin\Pages::index');
    $routes->match(['GET', 'POST'], 'pages/edit/(:segment)', 'Admin\Pages::edit/$1');

    $routes->get('reviews', 'Admin\Reviews::index');
    $routes->get('reviews/(:num)', 'Admin\Reviews::show/$1');
    $routes->match(['GET', 'POST'], 'reviews/(:num)/edit', 'Admin\Reviews::edit/$1');
    $routes->get('reviews/(:num)/delete', 'Admin\Reviews::deleteConfirm/$1');
    $routes->post('reviews/(:num)/delete', 'Admin\Reviews::delete/$1');
});

