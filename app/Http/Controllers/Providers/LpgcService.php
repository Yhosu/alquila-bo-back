<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

use AdminItem;
use GuzzleHttp\Client;
use App\Helpers\Func;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class LpgcService extends Controller {

    protected $url;

    public function __construct() {
        $this->url      = 'https://lpgc.pagos.bo/api';
    }

    public function processPost( $action, $parameters, $headers = [] ) {
        return \External::post($this->url, $action, $parameters, $headers);
    }

    public function processGet( $action, $parameters, $headers = [] ) {
        return \External::get($this->url, $action, $parameters, $headers);
    }

    public function getParameters() {
        return [
            [
                'id'     => "type",
                'label'  => "Tipo",
                'type'   => "select",
                'values' => [
                    ['value'=> 'ci_number', 'label' => 'Carnet de identidad'],
                ],
                'required' => true,
                'initial_value' => 'ci_number',
            ],
            [
                'id' => "search",
                'label' => "Valor de búsqueda",
                'type' => "text",
                'required' => true,
                'initial_value' => NULL,
            ],
            [
                'id' => "birth_date",
                'label' => "Fecha de nacimiento",
                'type' => "date",
                'initial_value' => NULL,
            ],
        ];
    }

    public function verifyDebts(
        $apiKey,
        $account,
        $provider,
        $provider_fields,
        $url
    ) {
        return true;
    }    

    public function enableMultipleSteps() {
        return false;
    }

    public function finishedForm( $request ) {
        return true;
    }

    public function generateTransactionLink(
        $providerItems,
        $params,
        $payment_provider,
        $amount,
        $transaction_code,
        $invoice_params,
        $url,
        $profile,
        $tenantUrl,
        $callbackUrl = null,
        $from_chatbot = false
    ) {
        $params['payments_array'] = $providerItems->pluck('code')->toArray();
        $params['redirect_url']   = "https://web.cuentas.bo/home-ctlp";
        $result = $this->processPost( 'generar-transaccion', $params );
        return [
            'payment_url'               => $result['url'],
            'qr_url'                    => $result['url'],
            'amount'                    => $amount,
            'currency'                  => 'BOB',
            'codigo_recaudacion'        => $result['codigo_recaudacion'],
            'app_key'                   => null,
            'payment_code'              => null,
            'transaction_id'            => $result['id'],
            'external_transaction_code' => $result['id'],
            'status'                    => true,
        ];
    }

    public function getDebts(
        $apiKey,
        $account,
        $provider,
        $provider_fields,
        $url
    ) {
        $accountId = $account->id ?? null;
        $params = [ 
            'field_id' => $provider_fields['type'], 
            $provider_fields['type'] => $provider_fields['search']
        ];
        $params['birth_date'] = $provider_fields['birth_date'];
        $array  = $this->processPost( 'get-payments', $params );
        if( empty( $array['payments'] ) ) {
            return [];
        }
        $debts = $array['payments'];
        $arrayDebts = [];
        foreach( $debts as $debt ) {
            $item = \App\Models\ProviderItem::where('api_key_id', $apiKey->id)
                ->where('account_id', $accountId)
                ->where('environment', $apiKey->environment)
                ->where('code', $debt['codigoInterno'])
                ->first();
            $itemStatus = $item->status ?? null;            
            if( in_array( $itemStatus, ['paid', 'sent'] ) ) continue;
            $item                   = new \App\Models\ProviderItem;
            $item->account_id       = $account->id ?? null;
            $item->api_key_id       = $apiKey->id;
            $item->provider_id      = $provider->id;
            $item->environment      = $apiKey->environment;
            $item->status           = 'holding';
            $item->amount           = floatval($debt['amount']);
            $item->name             = $debt['name'];
            $item->date             = date('Y-m-d');
            $item->metadata         = json_encode($debt);
            $item->code             = $debt['id'];
            $item->save();
            $arrayDebts['DEUDAS'][] = [
                'id'             => $item->id,
                'amount'         => currencyFormat( $item->amount ),
                'originalAmount' => $item->amount,
                'partnerName'    => '',
                'partnerCode'    => '',
                'name'           => $item->name,
                'date'           => getLiteralFromYearAndMonth( date('d/m/Y') ),
                'expDate'        => null,
                'currency'       => 'BOB',
                'debtref'        => $provider_fields['id'],
                'emits'          => 'Recibo'    
            ];
        }
        return $arrayDebts;
    }

    public function updatePayments(
        $providerItems,
        $amount,
        $externalTransactionCode,
        $nitNumber,
        $nitName,
        $url
    ) {
        return [ 'status' => true, 'message' => 'Pagos registrados exitósamente.', 'idsSend' => []];
    }
}