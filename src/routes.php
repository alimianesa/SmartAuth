<?php

use Illuminate\Support\Facades\Route;

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
Route::namespace('Alimianesa\SmartAuth\Http\Controllers')->prefix('api')->group(function () {
    Route::prefix('registration/office')->group(function () {
        Route::post('card' , 'ValidateUserController@cardPhoneValidation');
    });

    Route::prefix('/user')->group(function () {
        Route::post('/registration', 'ValidateUserController@phoneCard');
    });

    Route::prefix('users')->middleware('auth:api')->group(function () {
        Route::prefix('account')->group(function () {
            Route::post('/password', 'ValidateUserController@setPassword');
            Route::post('/images' , 'AliveFileController@store');
            Route::get('/speech/text','ValidateVideoController@getText');
            Route::post('/video' , 'ValidateVideoController@videoValidation');
        });
    });

});
