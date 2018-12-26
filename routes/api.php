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
        'api.throttle',
        \Barryvdh\Cors\HandleCors::class,
    ],
    'limit' => 60,
    'expires' => 1,
],function($api) {
    $api->post('appointments', 'AppointmentsController@store');
    $api->get('teachers', 'TeachersController@index');
});

