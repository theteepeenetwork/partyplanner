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

$routes->get('/login', 'Login::index');
$routes->post('/login/attempt', 'Login::attempt');
$routes->get('/logout', 'Login::logout');

// Profile Routes
$routes->get('/profile', 'Profile::index');
$routes->get('/profile/edit', 'Profile::edit'); 
$routes->post('/profile/edit', 'Profile::edit');
$routes->post('/profile/update-booking-status/(:num)', 'Profile::updateBookingStatus/$1');

// Service Routes
$routes->post('service/test', 'Service_Controller::test');
$routes->get('service/view/(:num)', 'Service_Controller::view/$1');
$routes->get('/service', 'Service_Controller::index'); 
$routes->match(['GET', 'POST'], '/service/create', 'Service_Controller::create');
$routes->get('/service/view/(:num)', 'Service_Controller::view/$1'); 
$routes->match(['GET', 'POST'], '/service/edit/(:num)', 'Service_Controller::update/$1'); 
$routes->get('/service/search', 'Service_Controller::search');
$routes->post('/service/update/(:num)', 'Service_Controller::update/$1');
$routes->post('/service/delete-image/(:num)', 'Service_Controller::deleteImage/$1');
$routes->post('/service/set-primary-image/(:num)', 'Service_Controller::setPrimaryImage/$1');

$routes->post('service/book', 'Service_Controller::bookService');



// Use distinct URL patterns for deactivate, reactivate, and delete
$routes->match(['GET', 'POST'], 'service/deactivate/(:num)', 'Service_Controller::deactivate/$1');
$routes->match(['GET', 'POST'], 'service/reactivate/(:num)', 'Service_Controller::reactivate/$1');
$routes->match(['GET', 'POST'], 'service/delete/(:num)', 'Service_Controller::delete/$1');


// Cart Routes
$routes->match(['GET', 'POST'], '/cart/add/(:num)', 'CartController::add/$1'); // Handles both GET and POST
$routes->get('/cart', 'CartController::index');
$routes->post('/cart/update/(:num)', 'CartController::update/$1');
$routes->get('/cart/remove/(:num)', 'CartController::remove/$1');
$routes->post('/cart/submit', 'CartController::submit');

// Events
$routes->match(['GET', 'POST'], '/event/create', 'EventController::create');

// Chat Routes
$routes->get('/chat/start/(:num)/(:num)', 'ChatController::startChat/$1/$2');
$routes->get('/chat/view/(:num)', 'ChatController::viewChat/$1');
$routes->post('/chat/sendMessage', 'ChatController::sendMessage');
$routes->get('/chat/checkNewMessages', 'ChatController::checkNewMessages');



//booking routes
$routes->get('calendarView/(:num)/(:num)', 'BookingController::calendarView/$1/$2');
$routes->get('calendarView', 'BookingController::calendarView');
$routes->get('calendarData/(:num)/(:num)', 'BookingController::calendarData/$1/$2');

//Payment routes
$routes->get('/payment', 'PaymentController::index'); // Show the payment form
$routes->post('/payment/charge', 'PaymentController::charge'); // Handle the payment form submission


