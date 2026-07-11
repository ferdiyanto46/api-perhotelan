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

// === Rute Autentikasi (Publik) ===
$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('/register', 'AuthController@register'); // Registrasi customer
    $router->post('/login', 'AuthController@login');

    // Hanya Super Admin yang bisa mendaftarkan Admin baru
    $router->post('/register-admin', [
        'middleware' => ['auth', 'role:super-admin'],
        'uses'       => 'AuthController@registerAdmin',
    ]);
});

// === Rute yang Memerlukan Autentikasi ===
$router->group(['middleware' => 'auth'], function () use ($router) {

    // --- Rute khusus Admin & Super Admin ---
    $router->group(['middleware' => 'role:admin,super-admin'], function () use ($router) {
        $router->put('/hotels/{id}', 'HotelController@update');
        $router->post('/hotels/{id}', 'HotelController@update'); // Alternatif untuk upload gambar (POST + _method spoofing)
        $router->delete('/hotels/{id}', 'HotelController@destroy');

        $router->post('/room-types', 'RoomTypeController@store');
        $router->put('/room-types/{id}', 'RoomTypeController@update');
        $router->delete('/room-types/{id}', 'RoomTypeController@destroy');

        $router->post('/rooms', 'RoomController@store');
        $router->put('/rooms/{id}', 'RoomController@update');
        $router->post('/rooms/{id}', 'RoomController@update'); // Alternatif untuk upload gambar (POST + _method spoofing)
        $router->delete('/rooms/{id}', 'RoomController@destroy');
    });

    // --- Rute khusus Super Admin saja ---
    $router->group(['middleware' => 'role:super-admin'], function () use ($router) {
        $router->post('/hotels', 'HotelController@store');
        $router->get('/hotels/overview', 'HotelController@overview'); // Overview hotel untuk dashboard

        // Account Management
        $router->get('/accounts', 'AccountController@index');
        $router->get('/accounts/{id}', 'AccountController@show');
        $router->put('/accounts/{id}', 'AccountController@update');
        $router->delete('/accounts/{id}', 'AccountController@destroy');
    });

    // --- Rute untuk semua pengguna terautentikasi ---
    $router->post('/bookings/checkout', 'BookingController@checkout');
    $router->post('/bookings/{id}/pay', 'BookingController@retryPayment'); // Bayar ulang booking pending
    $router->get('/bookings', 'BookingController@index');
    $router->get('/bookings/{id}', 'BookingController@show');
});

// === Rute Publik (Tidak perlu login) ===
$router->get('/hotels', 'HotelController@index');            // Daftar + filter: ?search=...&city=...
$router->get('/hotels/{id}', 'HotelController@showById');    // Detail hotel
$router->get('/room-types', 'RoomTypeController@index');
$router->get('/room-types/{id}', 'RoomTypeController@show');
$router->get('/rooms', 'RoomController@index');              // Filter: ?status=available&room_type_id=1
$router->get('/rooms/{id}', 'RoomController@show');

// Webhook Midtrans (tidak perlu auth)
$router->post('/midtrans/notification', 'BookingController@handleNotification');