<?php
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
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

// Profile / Account Routes
$routes->get('/profile', 'Profile::index');
$routes->get('/profile/main', 'Profile::index');
$routes->get('/dashboard', 'Profile::index');
$routes->get('/profile/edit', 'Profile::edit');
$routes->post('/profile/edit', 'Profile::edit');
$routes->post('/profile/update-booking-status/(:num)', 'Profile::updateBookingStatus/$1');

// Vendor tabs
$routes->get('/profile/services', 'Profile::services');
$routes->get('/profile/bookings', 'Profile::vendorBookings');
$routes->get('/profile/calendar', 'Profile::vendorCalendar');
$routes->get('/profile/calendar-data', 'Profile::calendarData');

// Customer tabs
$routes->get('/profile/events', 'Profile::customerEvents');
$routes->get('/profile/my-bookings', 'Profile::customerBookings');
$routes->get('/profile/messages', 'Profile::customerMessages');
$routes->get('/profile/messages/(:num)', 'Profile::customerMessageThread/$1');
$routes->post('/profile/messages/send', 'Profile::sendMessage');
$routes->get('/profile/payments', 'Profile::customerPayments');
$routes->get('/profile/favourites', 'Profile::customerFavourites');
$routes->get('/profile/favourites/remove/(:num)', 'Profile::removeFavourite/$1');


// Browse Services (public)
$routes->get('/browse-services', 'Service_Controller::browse');
$routes->get('/services', 'Service_Controller::browse');

// Service Routes
$routes->get('test', 'Service_Controller::test');
$routes->get('service/view/(:num)', 'Service_Controller::view/$1');
$routes->get('/service', 'Service_Controller::index');
$routes->get('service/destroy/(:num)', 'Service_Controller::destroy/$1');
$routes->post('/service/remove-optional-extra', 'Service_Controller::removeOptionalExtra');

$routes->post('/service/delete-image/(:any)', 'Service_Controller::deleteImage/$1');


$routes->match(['GET', 'POST'], '/service/create', 'Service_Controller::step1');
$routes->match(['GET', 'POST'], '/service/step1', 'Service_Controller::step1');
$routes->match(['GET', 'POST'], '/service/service_create_step2', 'Service_Controller::step2');
$routes->match(['GET', 'POST'], '/service/step2', 'Service_Controller::step2');
$routes->match(['GET', 'POST'], '/service/step3', 'Service_Controller::step3');
$routes->match(['GET', 'POST'], '/service/step4', 'Service_Controller::step4');
$routes->match(['GET', 'POST'], '/service/step5', 'Service_Controller::step5');
$routes->match(['GET', 'POST'], '/service/step6', 'Service_Controller::step6');
$routes->match(['GET', 'POST'], '/service/review', 'Service_Controller::review');
$routes->match(['GET', 'POST'], '/service/saveService', 'Service_Controller::saveService');



$routes->get('/service/success', 'Service_Controller::success');

$routes->get('/service/view/(:num)', 'Service_Controller::view/$1');
$routes->match(['GET', 'POST'], '/service/edit/(:num)', 'Service_Controller::update/$1');
$routes->get('/service/search', 'Service_Controller::search');
$routes->post('/service/update/(:num)', 'Service_Controller::update/$1');
//$routes->post('/service/delete-image/(:num)', 'Service_Controller::deleteImage/$1');
$routes->post('/service/set-primary-image/(:num)', 'Service_Controller::setPrimaryImage/$1');

$routes->post('service/book', 'Service_Controller::bookService');



// Service status management
$routes->match(['GET', 'POST'], 'service/deactivate/(:num)', 'Service_Controller::deactivate/$1');
$routes->match(['GET', 'POST'], 'service/reactivate/(:num)', 'Service_Controller::reactivate/$1');
$routes->match(['GET', 'POST'], 'service/delete/(:num)', 'Service_Controller::delete/$1');
$routes->post('service/toggle-status/(:num)', 'Service_Controller::toggleStatus/$1');


// Cart Routes
$routes->match(['GET', 'POST'], '/cart/add/(:num)', 'CartController::add/$1'); // Handles both GET and POST
$routes->get('/cart', 'CartController::index');
$routes->post('/cart/update/(:num)', 'CartController::update/$1');
$routes->get('/cart/remove/(:num)', 'CartController::remove/$1');
$routes->post('/cart/submit', 'CartController::submit');
$routes->post('payment/createPaymentIntent', 'PaymentController::createPaymentIntent');
$routes->get('booking/success/(:num)', 'BookingController::paymentSuccess/$1');
$routes->post('cart/submitToVendors', 'CartController::submitToVendors');
$routes->post('cart/processPayment', 'CartController::processPayment');
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

