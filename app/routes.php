<?php

require_once APP_PATH . '/controllers/AuthController.php';
require_once APP_PATH . '/controllers/DashboardController.php';
require_once APP_PATH . '/controllers/AdminController.php';
require_once APP_PATH . '/controllers/ClientController.php';

$router = $GLOBALS['app']->router ?? $this->router;

$router->get('/', function() {
    if (Session::has('user_id')) {
        redirect('/dashboard');
    } else {
        redirect('/login');
    }
});

$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

$router->get('/dashboard', 'DashboardController@index');

$router->get('/admin/users', 'AdminController@users');
$router->get('/admin/services', 'AdminController@services');
$router->get('/admin/orders', 'AdminController@orders');
$router->get('/admin/invoices', 'AdminController@invoices');
$router->get('/admin/payments', 'AdminController@payments');
$router->get('/admin/tickets', 'AdminController@tickets');
$router->get('/admin/settings', 'AdminController@settings');
$router->get('/admin/activity-logs', 'AdminController@activityLogs');

$router->get('/client/services', 'ClientController@services');
$router->get('/client/orders', 'ClientController@myOrders');
$router->get('/client/invoices', 'ClientController@invoices');
$router->get('/client/invoice/{id}', 'ClientController@viewInvoice');
$router->get('/client/tickets', 'ClientController@tickets');
$router->get('/client/ticket/{id}', 'ClientController@viewTicket');
$router->get('/client/account', 'ClientController@account');
