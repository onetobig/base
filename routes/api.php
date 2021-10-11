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

    // 用户标签
    Route::post('user-categories/create', 'UserCategoriesController@create')->name('api.user-categories.create');
    Route::post('user-categories/store', 'UserCategoriesController@store')->name('api.user-categories.store');

    // 企业定制
    Route::post('company-custom-cards/store', 'CompanyCustomCardsController@store')->name('api.company-custom-cards.store');
    Route::post('company-custom-cards/index', 'CompanyCustomCardsController@index')->name('api.company-custom-cards.index');
    Route::post('company-custom-cards/cancel', 'CompanyCustomCardsController@cancel')->name('api.company-custom-cards.cancel');
    Route::post('company-custom-cards/create', 'CompanyCustomCardsController@create')->name('api.company-custom-cards.create');

    // 想看的书
    Route::post('feed-back-books/store', 'FeedBackBooksController@store')->name('api.feed-back-books.store');

    // 乐豆中心
    Route::post('bean-logs/head', 'BeanLogsController@head')->name('api.bean-logs.head');
    Route::post('bean-logs/index', 'BeanLogsController@index')->name('api.bean-logs.index');
    Route::post('bean-logs/products', 'BeanLogsController@products')->name('api.bean-logs.products');
    Route::post('bean-logs/buy-product', 'BeanLogsController@buyProduct')->name('api.bean-logs.buy-product');
    Route::post('bean-logs/show-product', 'BeanLogsController@showProduct')->name('api.bean-logs.show-product');

    // 提现
    Route::post('cash-outs/store', 'CashOutsController@store')->name('api.cash-outs.store');

    // 奖学金
    Route::post('users/scholarship', 'UsersController@scholarship')->name('api.users.scholarship');
    // 上传参数
    Route::post('uploads/oss', 'OssUploadController@__invoke')->name('api.uploads.oss');

    // 优惠券
    Route::post('user-coupons/index', 'UserCouponsController@index')->name('api.user-coupons.index');
    Route::post('coupons/index', 'CouponsController@index')->name('api.coupons.index');
    Route::post('coupons/receive', 'CouponsController@receive')->name('api.coupons.receive');

    // 书袋-购物车
    Route::post('cars/store', 'CarsController@store')->name('api.cars.store');
    Route::post('cars/index', 'CarsController@index')->name('api.cars.index');
    Route::post('cars/destroy', 'CarsController@destroy')->name('api.cars.destroy');
    Route::post('cars/update', 'CarsController@update')->name('api.cars.update');

    // 订单
    Route::post('orders/compute-price', 'OrdersController@computePrice')->name('api.orders.compute-price');

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
    Route::any('users/login-test', 'Userscontroller@loginTest')->name('api.login.test');
    // 图片原路径
    Route::post('images/get-ori-file-url', 'ImagesController@getOriFileUrl')->name('api.images.get-ori-file-url');

});
