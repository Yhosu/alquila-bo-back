<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProviderController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/receipt/{code}/{externalTransactionCode}', [ProviderController::class, 'showReceiptByExternalTransactionCode']);
Route::get('/test-queries', function () {
    $arrayOrder = \App\Models\ProviderItem::whereIn( 'id', ['a4dc4568-1d5d-4347-ba71-f3cd1b14ebbb', '4c73912f-fe21-429c-b647-293a3858d7e9', 'c1a5e216-557e-4920-bf17-4b9e9655ef74'] )->orderBy('order', 'ASC')->pluck('order')->toArray();
    $txt = '';
    if( isConsecutive( $arrayOrder ) !== false ) {
        $txt = 'hola';
    }
    vardump($txt);
    // vardump( $arrayOrder );
    die();
    vardump(database_path('sqls/ctlp_partners.sql'));
    die();
    $account = \App\Models\Account::first();
    $arrayItems = [];
    $metadata = json_decode( $account->metadata, true );
    unset($metadata['tenantId'], $metadata['tenantUrl']);
    foreach( $metadata as $key => $elem ) {
        $arrayItems[] = [ 'label' => \Lang::get('ctlp.' . $key ) , 'value' => $elem];
    }
    vardump($arrayItems);
});
