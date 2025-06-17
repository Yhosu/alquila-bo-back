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

class OrganizationTenantController extends Controller {

    protected $url;

    public function __construct() {
    }

    public function processPost( $url, $action, $parameters, $headers = [] ) {
        return \External::post($url, $action, $parameters, $headers);
    }

    public function processGet( $url, $action, $parameters, $headers = [] ) {
        return \External::get($url, $action, $parameters, $headers);
    }

    public function getProfile(
        $url,
        $id
    ) {
        $data = $this->processGet($url . '/organizations/profile/' . $id, '', [] )['data'] ?? null;
        return $data ? (object)$data : []; 
    }
}