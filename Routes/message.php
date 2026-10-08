<?php

/*
|--------------------------------------------------------------------------
| Message related routes
|--------------------------------------------------------------------------
*/

use Amplify\Frontend\Http\Middlewares\CustomerDefaultValues;
use Amplify\System\Message\Http\Controllers\MessageController;
use Amplify\System\Message\Http\Controllers\MessageRecipientController;
use Illuminate\Support\Facades\Route;

Route::get('/messages/recipients/{type}', MessageRecipientController::class)
    ->middleware('web')
    ->name('messages.recipients');

Route::name('frontend.')
    ->middleware(['web', 'frontend', 'customers', CustomerDefaultValues::class])
    ->group(function () {
        Route::get('messages/recent', [MessageController::class, 'recent'])
            ->middleware('throttle:60,1')
            ->name('messages.recent');
        Route::get('messages/{message}/live', [MessageController::class, 'live'])
            ->middleware('throttle:60,1')
            ->name('messages.live')
            ->where(['message' => '[\d]+']);
        Route::resource('messages', MessageController::class)
            ->names('messages')
            ->where(['message' => '[\d]+']);
    });
