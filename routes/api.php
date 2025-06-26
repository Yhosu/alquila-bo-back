<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureKeysIsValid;
use App\Http\Middleware\EnsureAdminIsValid;
use App\Http\Middleware\EnsureProviderIsValid;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppController;

Route::prefix('v1')->group(function () {
    Route::post('/login',                        [AuthController::class, 'getLogin']);
    Route::post('/register',                     [AuthController::class, 'getRegister']);
    Route::post('/node-list/{node}/{paginate?}', [AppController::class,  'getNode']);
    Route::get('/home-info',                     [AppController::class,  'getHomeInfo']);
    Route::post('/faqs',                         [AppController::class,  'getFaqs']);
});

Route::middleware(['auth:sanctum', EnsureProviderIsValid::class])->prefix('v1')->group(function () {
    Route::post('/send-form',                    [AppController::class,  'getSendForm']);
});

Route::middleware(['auth:sanctum', EnsureAdminIsValid::class])->prefix('admin')->group(function () {

});
