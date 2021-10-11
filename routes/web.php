<?php

//Route::any('payment/fy-notify', 'Api\PaymentController@FYNotify')->name('payment.fy-notify');
//Route::any('express/notify/{no}', 'Api\OrdersController@expressNotify')->name('express.notify');
Route::any('oss/notify', 'Api\OssController@notify')->name('oss.notify');
