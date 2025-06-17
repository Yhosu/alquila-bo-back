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
});
Route::middleware(['auth:sanctum', EnsureAdminIsValid::class])->prefix('admin')->group(function () {
});
