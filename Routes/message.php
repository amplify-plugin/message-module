<?php

/*
|--------------------------------------------------------------------------
| Message related routes
|--------------------------------------------------------------------------
*/

use Amplify\Frontend\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

Route::controller(MessageController::class)->middleware('web')->group(function () {
    Route::group([
        'prefix' => config('backpack.base.route_prefix', 'backpack'),
        'middleware' => array_merge(
            config('backpack.base.web_middleware', ['web']),
            (array) config('backpack.base.middleware_key', 'admin'),
            ['admin_password_reset_required']
        ),
    ], function () {});

    Route::get('/messages/recipients/{type}', \Amplify\System\Message\Http\Controllers\MessageRecipientController::class)
        ->name('messages.recipients');
});
