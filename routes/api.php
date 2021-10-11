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


Route::any('/test', 'TestController@test')->name('api.test');
/**
 * 必须登录的路由
 */
Route::middleware([
    'api-request:1',
    'throttle:180,1',
])->group(function () {
    // 退出登录
    Route::post('users/logout', 'UsersController@logout')->name('api.users.logout');
    Route::post('users/me', 'UsersController@me')->name('api.users.me');

});

/**
 * 非必须登录的路由
 */
Route::middleware([
    'api-request:0',
    'throttle:180,1',
])->group(function () {
});

/**
 * 不用登录的路由
 */
Route::middleware([
    'api-request:-1',
    'throttle:180,1',
])->group(function () {
    // 用户登录
    Route::post('users/login-by-weixin', 'UsersController@loginByWeixin')->name('api.users.login-by-weixin');
});
