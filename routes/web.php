<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/hotels', 'HotelController@index');
$router->post('/hotels', 'HotelController@store');
// $router->get('/hotels/{id}', 'HotelController@show');
// $router->get('/room-types', 'HotelController@roomTypes');
// $router->get('/room-types/{id}', 'HotelController@roomTypeDetail');

// $router->post('/bookings', 'BookingController@checkout');
// $router->get('/bookings', 'BookingController@index');
// $router->get('/bookings/{id}', 'BookingController@show');
// $router->post('/midtrans/notification', 'BookingController@handleNotification');

// $router->get('/link-storage', function () {
//     $target = storage_path('app/public');
//     $shortcut = $_SERVER['DOCUMENT_ROOT'].'/storage';
//     symlink($target, $shortcut);
//     return "Symlink created!";
// });