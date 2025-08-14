<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProviderController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/receipt/{code}/{externalTransactionCode}', [ProviderController::class, 'showReceiptByExternalTransactionCode']);
Route::get('/test-queries', function () {
    vardump('asdasd');
    die();
    $product = \App\Models\Product::with('product_characteristics');
    vardump( $product->product_characteristics );

});
