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

$routes->get('/profile', 'Profile::index');
$routes->get('/profile/edit', 'Profile::edit'); 
$routes->post('/profile/edit', 'Profile::edit');

// Service Routes
$routes->get('/service', 'Service_Controller::index'); 
$routes->match(['get', 'post'],'/service/create', 'Service_Controller::create');
$routes->get('/service/view/(:num)', 'Service_Controller::view/$1'); 
$routes->match(['get', 'post'], '/service/edit/(:num)', 'Service_Controller::update/$1'); 
$routes->get('/service/search', 'Service_Controller::search');
// Service Routes
$routes->delete('/service/edit/(:num)', 'Service_Controller::delete/$1');



// Cart Routes
$routes->match(['get', 'post'], '/cart/add/(:num)', 'CartController::add/$1'); // Handles both GET and POST
$routes->get('/cart', 'CartController::index');
$routes->post('/cart/update/(:num)', 'CartController::update/$1');
$routes->get('/cart/remove/(:num)', 'CartController::remove/$1');
$routes->post('/cart/submit', 'CartController::submit');


// Events
$routes->match(['get', 'post'],'/event/create', 'EventController::create');

// Service Routes (Use the Service controller for service-related actions)
$routes->post('/profile/update-booking-status/(:num)', 'Profile::updateBookingStatus/$1');


