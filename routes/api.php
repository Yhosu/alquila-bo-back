<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureKeysIsValid;
use App\Http\Middleware\EnsureAdminIsValid;
use App\Http\Middleware\EnsureProviderIsValid;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\AdminProviderController;
use App\Http\Controllers\AuthController;

Route::middleware(EnsureKeysIsValid::class)->prefix('auth')->group(function () {
    Route::get('/get-access-token',    [AuthController::class, 'getAccessToken']);
});

Route::middleware(['auth:sanctum', EnsureProviderIsValid::class])->prefix('v1')->group(function () {
    Route::get('/authorized-providers',                                     [ProviderController::class, 'getAuthorizedProviders']);
    /* Profiles */
    Route::post('/create-profile',                                          [ProviderController::class, 'createProfile']);
    Route::get('/show-profile/{profileId}',                                 [ProviderController::class, 'showProfile']);
    Route::get('/get-profile-accounts/{profileId}',                         [ProviderController::class, 'getProfileAccounts']);
    /* Accounts */
    Route::post('/create-account/{providerId}/{profileId}',                 [ProviderController::class, 'createAccountProviderIdByProfile']);
    /* Providers */
    Route::get('/provider/get-categories',                                  [ProviderController::class, 'getCategories']);
    Route::get('/provider/all',                                             [ProviderController::class, 'getAllProviders']);
    Route::get('/provider/{providerId}/get-provider-fields',                [ProviderController::class, 'getProviderFields']);
    Route::get('/provider/{providerId}/create-profile-account/{profileId}', [ProviderController::class, 'createProfileAccount']);
    Route::post('/provider/{providerId}/get-account-debts/{accountId?}',    [ProviderController::class, 'getAccountDebts']);
    Route::post('/provider/{providerId}/create-transaction/{profileId?}',   [ProviderController::class, 'createTransaction']);
    Route::post('/provider/delete-account/{accountId}',                     [ProviderController::class, 'deleteAccount']);
    Route::post('/bridge-pago-confirmado/{code}',                           [ProviderController::class, 'updatePaymentConfirmed']);
    Route::post('/pay-with-card',                                           [ProviderController::class, 'payWithCard']);
    Route::post('/test-success/{code}',                                     [ProviderController::class, 'testSuccess']);
});

Route::middleware(['auth:sanctum', EnsureAdminIsValid::class])->prefix('admin')->group(function () {
    Route::get('/{providerId}/get-provider-fields', [AdminProviderController::class, 'getProviderFields']);
    Route::post('/{providerId}/get-debts',          [AdminProviderController::class, 'getProviderDebts']);
});

// Route::post('/v1/bridge-pago-confirmado',                                  [ProviderController::class, 'updatePaymentConfirmed']);
