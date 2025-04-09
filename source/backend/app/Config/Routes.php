<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->post('api/test', 'Api::index');

$routes->group('api', function($routes) {
    // $routes->post('api/login', ['controller' => 'Api']);
    $routes->post('login', 'Api::login');
    $routes->post('refresh', 'Api::refresh');

    // Protected routes
    $routes->group('', ['filter' => 'jwtAuth'], function($routes) {
        $routes->get('profile', 'Api::profile');
        // Add other protected routes here
    });
});

