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

    Route::post('/node-list/{node}/{paginate?}', [AppController::class, 'getNode']);
    Route::get('/home-info',                     [AppController::class, 'getHomeInfo']);
    Route::get('/faqs',                          [AppController::class, 'getFaqs']);
    //Route::get('/information',                   [AppController::class, 'getInformation']);
    Route::get('/about-us',                      [AppController::class, 'getAboutus']);
        /** TODO: Tiene que la información del producto por UUID  */
    Route::get('/product/{uuid}',                [AppController::class,  'getProduct']);
        /** TODO: Tiene que traer todas las latitutdes y longitudes de todas las compañías */
    Route::get('/get-companies-map',             [AppController::class,  'getCompaniesMap']);
    Route::post('/register-subscription',        [AppController::class,  'registerSubscription']);
    Route::post('/confirmation-subscription',        [AppController::class,  'confirmSubscription']);
    Route::post('/cancelation-subscription',        [AppController::class,  'cancelSubscription']);

});

    /* TODO LO QUE ESTA DEBAJO DE ACÁ ES CON TOKEN */
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
        /* Recibir el formulario del cliente y enviar un email y whatsapp (front) */
    Route::get('/logout',                           [AuthController::class, 'getLogout']);
    Route::post('/send-form',                       [AppController::class,  'getSendForm']);
    Route::post('/register-comment',                [AppController::class,  'registerComment']);
    Route::get('/get-comments-by-product/{uuid}',    [AppController::class,  'getCommentsByProduct']);
});
