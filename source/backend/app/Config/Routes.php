<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->post('api/test', 'Api::index');

// $routes->post('api/login', ['controller' => 'Api']);
$routes->post('api/login', 'Api::login');
$routes->post('api/refresh', 'Api::refresh');


