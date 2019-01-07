<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');
    $router->post('upload/image', 'UploadsController@store');
    $router->get('users', 'UsersController@index');
    $router->get('users/create', 'UsersController@create');
    $router->get('users', 'UsersController@index');

    $router->get('images', 'ImagesController@index');
    $router->get('images/create', 'ImagesController@create');
    $router->post('images', 'ImagesController@store');
    $router->get('images/{id}/edit', 'ImagesController@edit');
    $router->put('images/{id}', 'ImagesController@update');
    $router->delete('images/{id}', 'ImagesController@destroy');

    $router->get('appointments', 'AppointmentsController@index')->name("apppointments.index");

    $router->get('teachers', 'TeachersController@index');
    $router->get('teachers/create', 'TeachersController@create');
    $router->post('teachers', 'TeachersController@store');
    $router->get('teachers/{id}/edit', 'TeachersController@edit');
    $router->put('teachers/{id}', 'TeachersController@update');
    $router->delete('teachers/{id}', 'TeachersController@destroy');
});
