<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api',
    'middleware' => [
        'serializer:array',
        \Barryvdh\Cors\HandleCors::class,
    ],
], function ($api) {
    $api->group([
        'middleware' => 'api.throttle',
        'limit' => 500,
        'expires' => 1,
    ], function ($api) {
        $api->get('images', 'ImagesController@index');
    });

    $api->group([
        'middleware' => 'api.throttle',
        'limit' => 60,
        'expires' => 1,
    ], function ($api) {
        $api->post('appointments', 'AppointmentsController@store');
    });
});

