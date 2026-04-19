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

$router->group(['prefix' => 'hotels'], function () use ($router) {
    $router->get('/', 'HotelController@index');
    $router->get('/search', 'HotelController@search');
    $router->get('/{id}', 'HotelController@show');
    $router->post('/', 'HotelController@store');
    $router->put('/{id}', 'HotelController@update');
    $router->delete('/{id}', 'HotelController@destroy');

    $router->group(['prefix' => 'room-types'], function () use ($router){
        $router->get('/', 'RoomTypeController@index');
        $router->get('/{id}', 'RoomTypeController@show');
        $router->post('/', 'RoomTypeController@store');
        $router->put('/{id}', 'RoomTypeController@update');
        $router->delete('/{id}', 'RoomTypeController@destroy');
    });

    $router->group(['prefix' => 'rooms'], function () use ($router){
        $router->get('/', 'RoomController@index');
        $router->get('/{id}', 'RoomController@show');
        $router->post('/', 'RoomController@store');
        $router->put('/{id}', 'RoomController@update');
        $router->delete('/{id}', 'RoomController@destroy');
    });
    
    $router->group(['prefix' => 'bookings'], function () use ($router){
        $router->post('/checkout', 'BookingController@checkout');
        $router->get('/', 'BookingController@index');
        $router->get('/{id}', 'BookingController@show');
    });

    // Rute yang memerlukan autentikasi
    // $router->group(['middleware' => 'auth'], function () use ($router){
    //     $router->post('/hotels', 'HotelController@store');
    // });
}); 



// $router->post('/hotels', 'HotelController@store');

// $router->get('/room-types', 'RoomTypeController@index');
// $router->get('/room-types/{id}', 'RoomTypeController@show');
// $router->post('/room-types', 'RoomTypeController@store');
// $router->put('/room-types/{id}', 'RoomTypeController@update');
// $router->delete('/room-types/{id}', 'RoomTypeController@destroy');

// $router->get('/rooms', 'RoomController@index');
// $router->get('/rooms/{id}', 'RoomController@show');
// $router->post('/rooms', 'RoomController@store');
// $router->put('/rooms/{id}', 'RoomController@update');
// $router->delete('/rooms/{id}', 'RoomController@destroy');

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