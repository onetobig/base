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


/**
 * 必须登录的路由
 */
Route::middleware([
    'backend-request:-1',
    'throttle:180,1',
])->group(function () {
    Route::post('users/captcha', 'UsersController@captcha')->name('backend.users.captcha');
    Route::post('users/login', 'UsersController@login')->name('backend.users.login');
});

/**
 * 必须登录的路由
 */
Route::middleware([
    'backend-request:1',
    'throttle:180,1',
])->group(function () {
    // 用户管理
    Route::post('users/index', 'UsersController@index')->name('backend.user.index');
    Route::post('users/get-filter-params', 'UsersController@getFilterParams')->name('backend.user.get-filter-params');

    // 推广中心
    Route::post('invitation-center/gift-card-records', 'InvitationCenterController@giftCardRecords')->name('backend.invitation_center.gift-card-records');
    Route::post('invitation-center/add-gift-card', 'InvitationCenterController@addGiftCard')->name('backend.invitation_center.add-gift-card');
    Route::post('invitation-center/update-type', 'InvitationCenterController@updateType')->name('backend.invitation_center.update-type');
    Route::post('invitation-center/index', 'InvitationCenterController@index')->name('backend.invitation_center.index');
    Route::post('invitation-center/member-index', 'InvitationCenterController@memberIndex')->name('backend.invitation_center.member-index');
    // 图书推荐
    Route::post('book-recommends/index', 'BookRecommendsController@index')->name('backend.book-recommends.index');
    Route::post('book-recommends/destroy', 'BookRecommendsController@destroy')->name('backend.book-recommends.destroy');
    Route::post('book-recommends/enable', 'BookRecommendsController@enable')->name('backend.book-recommends.enable');
    Route::post('book-recommends/disable', 'BookRecommendsController@disable')->name('backend.book-recommends.disable');
    Route::post('book-recommends/store', 'BookRecommendsController@store')->name('backend.book-recommends.store');
    Route::post('book-recommends/update', 'BookRecommendsController@update')->name('backend.book-recommends.update');
    Route::post('book-recommends/edit', 'BookRecommendsController@edit')->name('backend.book-recommends.edit');


    // 图书管理
    /*
    Route::post('books/index', 'BooksController@index')->name('backend.books.index');
    Route::post('books/destroy', 'BooksController@destroy')->name('backend.books.destroy');
    Route::post('books/on-sale', 'BooksController@onSale')->name('backend.books.on-sale');
    Route::post('books/off-sale', 'BooksController@offSale')->name('backend.books.off-sale');
    Route::post('books/store', 'BooksController@store')->name('backend.books.store');
    Route::post('books/update', 'BooksController@update')->name('backend.books.update');
    Route::post('books/edit', 'BooksController@edit')->name('backend.books.edit');
    */

    Route::post('categories/attach-book', 'CategoriesController@attachBook')->name('backend.categories.attach-book');
    Route::post('categories/detach-book', 'CategoriesController@detachBook')->name('backend.categories.detach-book');
    Route::post('categories/index', 'CategoriesController@index')->name('backend.categories.index');
    Route::post('categories/books/index', 'CategoriesController@booksIndex')->name('backend.categories.books.index');
    Route::post('categories/destroy', 'CategoriesController@destroy')->name('backend.categories.destroy');
    Route::post('categories/enable', 'CategoriesController@enable')->name('backend.categories.enable');
    Route::post('categories/disable', 'CategoriesController@disable')->name('backend.categories.disable');
    Route::post('categories/store', 'CategoriesController@store')->name('backend.categories.store');
    Route::post('categories/update', 'CategoriesController@update')->name('backend.categories.update');
    Route::post('categories/edit', 'CategoriesController@edit')->name('backend.categories.edit');

    // 专区
    Route::post('zones/index', 'ZonesController@index')->name('backend.zones.index');
    Route::post('zones/destroy', 'ZonesController@destroy')->name('backend.zones.destroy');
    Route::post('zones/enable', 'ZonesController@enable')->name('backend.zones.enable');
    Route::post('zones/disable', 'ZonesController@disable')->name('backend.zones.disable');
    Route::post('zones/store', 'ZonesController@store')->name('backend.zones.store');
    Route::post('zones/update', 'ZonesController@update')->name('backend.zones.update');
    Route::post('zones/edit', 'ZonesController@edit')->name('backend.zones.edit');
    Route::post('zones/relate-books', 'ZonesController@relateBooks')->name('backend.zones.relate-books');

    // 活动
    Route::post('activities/index', 'ActivitiesController@index')->name('backend.activities.index');
    Route::post('activities/destroy', 'ActivitiesController@destroy')->name('backend.activities.destroy');
    Route::post('activities/enable', 'ActivitiesController@enable')->name('backend.activities.enable');
    Route::post('activities/disable', 'ActivitiesController@disable')->name('backend.activities.disable');
    Route::post('activities/store', 'ActivitiesController@store')->name('backend.activities.store');
    Route::post('activities/update', 'ActivitiesController@update')->name('backend.activities.update');
    Route::post('activities/edit', 'ActivitiesController@edit')->name('backend.activities.edit');
    Route::post('activities/relate-books', 'ActivitiesController@relateBooks')->name('backend.activities.relate-books');

    // 自定义专区
    Route::post('custom-zones/index', 'CustomZonesController@index')->name('backend.custom-zones.index');
    Route::post('custom-zones/destroy', 'CustomZonesController@destroy')->name('backend.custom-zones.destroy');
    Route::post('custom-zones/enable', 'CustomZonesController@enable')->name('backend.custom-zones.enable');
    Route::post('custom-zones/disable', 'CustomZonesController@disable')->name('backend.custom-zones.disable');
    Route::post('custom-zones/store', 'CustomZonesController@store')->name('backend.custom-zones.store');
    Route::post('custom-zones/update', 'CustomZonesController@update')->name('backend.custom-zones.update');
    Route::post('custom-zones/edit', 'CustomZonesController@edit')->name('backend.custom-zones.edit');

    /*
    Route::post('tags/index', 'TagsController@index')->name('backend.tags.index');
    Route::post('tags/destroy', 'TagsController@destroy')->name('backend.tags.destroy');
    Route::post('tags/enable', 'TagsController@enable')->name('backend.tags.enable');
    Route::post('tags/disable', 'TagsController@disable')->name('backend.tags.disable');
    Route::post('tags/store', 'TagsController@store')->name('backend.tags.store');
    Route::post('tags/update', 'TagsController@update')->name('backend.tags.update');
    Route::post('tags/edit', 'TagsController@edit')->name('backend.tags.edit');
    */

    // 上传参数
    Route::post('uploads/oss', 'OssUploadController@__invoke')->name('backend.uploads.oss');

    // 动态
    Route::post('clock-in-dynamics/update-praise-count', 'ClockInDynamicsController@updatePraiseCount')->name('backend.clock-in-dynamics.update-praise-count');
    Route::post('clock-in-dynamics/update-status', 'ClockInDynamicsController@updateStatus')->name('backend.clock-in-dynamics.update-status');
    Route::post('clock-in-dynamics/toggle-switch', 'ClockInDynamicsController@toggleSwitch')->name('backend.clock-in-dynamics.toggle-switch');
    Route::post('clock-in-dynamics/index', 'ClockInDynamicsController@index')->name('backend.clock-in-dynamics.index');
    Route::post('clock-in-dynamics/destroy', 'ClockInDynamicsController@destroy')->name('backend.clock-in-dynamics.destroy');
    Route::post('clock-in-dynamics/get-filter-params', 'ClockInDynamicsController@getFilterParams')->name('backend.clock-in-dynamics.get-filter-params');

    // 企业定制
    Route::post('company-custom-cards/index', 'CompanyCustomCardsController@index')->name('backend.company-custom-cards.index');
    Route::post('company-custom-cards/switch-status', 'CompanyCustomCardsController@switchStatus')->name('api.company-custom-cards.switch-status');
//    backend_routes('companies', \App\Http\Controllers\Backend\CompaniesController::class);

    // 提现
    Route::post('cash-outs/index', 'CashOutsController@index')->name('backend.cash-outs.index');
    Route::post('cash-outs/reject', 'CashOutsController@reject')->name('backend.cash-outs.reject');
    Route::post('cash-outs/pass', 'CashOutsController@pass')->name('backend.cash-outs.pass');
    Route::post('cash-outs/update-remark', 'CashOutsController@updateRemark')->name('backend.cash-outs.update-remark');
    Route::post('cash-outs/pay-to-balance', 'CashOutsController@payToBalance')->name('backend.cash-outs.pay-to-balance');

    // 设置
    Route::post('settings/base', 'SettingsController@base' )->name('backend.settings.beep-music-update');
});
