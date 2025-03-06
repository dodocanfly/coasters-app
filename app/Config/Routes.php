<?php

use App\Controllers\Api\CoasterController;
use App\Controllers\Home;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', [Home::class, 'index']);

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function(RouteCollection $routes) {
    $routes->post('coasters', [CoasterController::class, 'create']);
    $routes->put('coasters/(:segment)', [[CoasterController::class, 'update'], '$1']);

    $routes->post('coasters/(:segment)/wagons', [[CoasterController::class, 'addWagon'], '$1']);
    $routes->delete('coasters/(:segment)/wagons/(:segment)', [[CoasterController::class, 'removeWagon'], '$1/$2']);
});
