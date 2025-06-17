<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Closure;
use App\Http\Requests\Account\CreateAccountRequest;
use App\Http\Requests\Account\CreateProfileRequest;
use App\Http\Requests\Payment\PayWithCardRequest;
use App\Services\ApiResponseService;

class AdminProviderController extends Controller {
    public function __construct(){
    }

    public function getVariables( $provider ) {
        return [
            'title'    => $provider->name,
            'subtitle' => $provider->name,
            'summary'  => $provider->description,
        ];
    }    

    public function getProviderFields( $providerId ) {
        return app('App\Http\Controllers\ProviderController')->getProviderFields( $providerId );
    }

    public function getProviderDebts( $providerId, Request $request ) {
        $apiKey    = $request->apiKey;
        $urlEnv    = 'url_' . $apiKey->environment;
        $provider  = \App\Models\Provider::find( $providerId );
        $params    = $request->all();
        $strParams = \Func::getConcatParamsDebt( $params );
        $account   = null;
        $key       = $providerId . '-' . $strParams;
        $result    = \Cache::store('database')->remember($key, 60*60, function() use( $apiKey, $account, $provider, $params, $urlEnv ) {
            return app( sprintf('App\Http\Controllers\Providers\%s', $provider->class) )->getDebts( 
                $apiKey,
                $account,
                $provider, 
                $params,
                $provider->$urlEnv 
            );
        });
        $variables = $this->getVariables( $provider );
        return ApiResponseService::success('Deudas obtenidas exitósamente.', $result, $variables);
    }
}
