<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use App\Services\PaymentTenantService;

use AdminItem;
use GuzzleHttp\Client;
use App\Helpers\Func;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class PaymentTenantController extends Controller {

    protected $url;

    public function __construct() {
    }

    public function processPost( $url, $action, $parameters, $headers = [] ) {
        return \External::post($url, $action, $parameters, $headers);
    }

    public function processGet( $action, $parameters, $headers = [] ) {
        return \External::get($url, $action, $parameters, $headers);
    }

    public function generatePayment(
        $url,
        $paramsTransaction,
        $bearerToken
    ) {
        return $this->processPost($url . '/payments', 'add-payment-with-transaction', $paramsTransaction, ['Authorization' => 'Bearer ' . $bearerToken ] ); 
    }

    public function payWithCard(
        $url,
        $params
    ) {
        return $this->processPost($url . '/payments', 'pay-transaction', $params ); 
    }
}