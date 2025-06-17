<?php

namespace App\Http\Controllers\Providers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

use AdminItem;
use GuzzleHttp\Client;
use App\Helpers\Func;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Artisaninweb\SoapWrapper\SoapWrapper;

class AlianzaVidaLpService extends Controller {

    protected $url;
    protected $soapWrapper;

    public function __construct(SoapWrapper $soapWrapper) {
        $this->soapWrapper = $soapWrapper;
        if (\Request::secure()){
          $protocol = 'http://';
        } else {
          $protocol = 'http://';
        }
        if(config('services.enable_test')){
            $this->url = $protocol.'qualitynet.alianza.com.bo/WS/DataSending.asmx';
            $this->user = config('services.test_alianza_user');
            $this->password = config('services.test_alianza_password');
        } else {
            $this->url = $protocol.'services.alianza.com.bo/WS/DataSending.asmx';
            $this->user = config('services.prod_alianza_user');
            $this->password = config('services.prod_alianza_password');
        }
        $this->userString = 'nUser='.$this->user.'&sPassword='.$this->password;
        $this->userParameters = ['nUser'=>$this->user, 'sPassword'=>$this->password];
        $this->userString2 = 'lstrUser='.$this->user.'&lstrPassword='.$this->password;
        $this->userParameters2 = ['lstrUser'=>$this->user, 'lstrPassword'=>$this->password];
    }

    public function processPost( $url, $action, $parameters, $headers = [] ) {
        return \External::post($url, $action, $parameters, $headers);
    }

    public function processGet( $action, $parameters, $headers = [] ) {
        return \External::get($url, $action, $parameters, $headers);
    }

    public function rules() {
        return [
            'rules' => 'required',
        ];
    }

    public function getParameters() {
        return [
            [
                'id' => "debtref",
                'label' => "Introduzca su CI",
                'type' => "text",
                'initial_value' => NULL,
            ],
            [
                'id'     => "group",
                'label'  => "Grupo",
                'type'   => "select",
                'values' => [
                    ['value'=> 'cuotas',     'label' => 'Cuotas Ordinarias y Alícuotas'],
                    ['value'=> 'escuelas',   'label' => 'Escuelas'],
                    ['value'=> 'otros',      'label' => 'Planes de pago, cuotas de admisión y otros'],
                ],
                'initial_value' => 'cuotas',
            ],
        ];
    }

    public function getDebts(
        $apiKey,
        $account,
        $provider,
        $provider_fields,
        $url
    ) {
        $params = [ 'debtref' => $provider_fields['debtref'] ];
        $array  = $this->processPost( $url, 'GetDebts', $params );
        if( $array['IsError'] ) {
            return [];
        }
        $dues = $array['Data']['Dues'] ?? [];
        $debts = \Func::getElemsOfArray( $dues, 'cod_concept', $this->groups[$provider_fields['group']] );
        if( empty( $debts ) ) {
            return [];
        }
        usort($debts, function($a, $b) { return strtotime($a['date_due']) - strtotime($b['date_due']); });
        $items = [];
        foreach( $debts as $debt ) {
            $debt['debtref'] = $provider_fields['debtref'];
            $debt['group']   = $provider_fields['group'];
            $item                   = new \App\Models\ProviderItem;
            $item->account_id       = $account->id;
            $item->api_key_id       = $apiKey->id;
            $item->provider_id      = $provider->id;
            $item->environment      = $apiKey->environment;
            $item->status           = 'holding';
            $item->amount           = floatval($debt['amount_bs']);
            $item->name             = sprintf( '%s - %s - %s', $debt['name'], $debt['cod_concept'], $debt['period'] );
            $item->date             = date('Y-m-d');
            $item->code             = $debt['odoo_id'];
            $item->metadata         = json_encode($debt);
            $item->save();
            $items[] = [
                'id'       => $item->id,
                'amount'   => $item->amount,
                'name'     => $item->name,
                'date'     => $item->date,
                'currency' => 'BOB'
            ];
        }
        return $items;
    }

    public function generateTransactionLink(
        $params,
        $payment_provider,
        $amount,
        $transaction_code,
        $invoice_params,
        $url
    ) {
        $ppitem = \App\Models\ProviderItem::whereIn('code', $params['CtlpDebtIds'])
            ->where('provider_id', $payment_provider->id)
            ->where('transaction_code', $transaction_code)
            ->first();
        $json = (object)json_decode( $ppitem->metadata, true );
        $params['debtRef']     = $json->debtref;
        $params['name']        = $json->name;
        $params['razonSocial'] = $invoice_params['invoice_name'];
        $params['DocType']     = $invoice_params['invoice_doc_type'];
        $params['ci_nit']      = $invoice_params['invoice_doc_number'];
        $params['email']       = $invoice_params['invoice_email'];
        $params['redirect_url']  = "https://web.cuentas.bo/home-ctlp";
        $result = $this->processPost( $url, 'PayDebt', $params );
        if( $result['IsError'] ) {
            return [
                'status'  => false,
                'message' => 'Hubo un error al procesar su solicitud.',
                'errors'  => ['La transacción no ha podido ser generada, intente nuevamente más tarde.']
            ];
        }
        $success_result = [
            'payment_url'        => $result['Data']['url'],
            'qr_url'             => $result['Data']['url'],
            'amount'             => $amount,
            'currency'           => 'BOB',
            'codigo_recaudacion' => $result['Data']['codigo_recaudacion'],
            'app_key'            => $result['Data']['TransactionId'],
            'payment_code'       => $result['Data']['TransactionId'],
            'status'             => true,
        ];
        return $success_result;
    }

    public function validateDebts( $arrayDebts, $apiKey ) {
        $elements = \App\Models\ProviderItem::whereIn( 'id', $arrayDebts )->orderBy('order', 'ASC')->get();
        $arrayOrder = $elements->pluck('order')->toArray();
        if( empty( $arrayOrder ) ) return [ 'status' => false, 'message' => 'Debe seleccionar al menos 1 deuda.'];
        if( isConsecutive( $arrayOrder ) !== false ) return [ 'status' => false, 'message' => 'Debe seleccionar deudas consecutivas.'];
        $firstElement = $elements->first();
        $accountId    = $firstElement->account->id;
        $providerId   = $firstElement->provider->id;
        $verifyPendigs = \App\Models\ProviderItem::where('order', '<', $firstElement->order )
            ->where( 'account_id', $accountId )
            ->where( 'provider_id', $providerId )
            ->where( 'api_key_id', $apiKey->id )
            ->where( 'status', 'holding' )
            ->count();
        if( $verifyPendigs > 0 ) {
            return [ 'status' => false, 'message' => 'Debe seleccionar/pagar las deudas anteriores antes de poder seleccionar estas deudas.'];
        }
        return [ 'status' => true, 'message' => 'Datos validados con éxito' ];
    }

    public function enableMultipleSteps() {
        return false;
    }

    public function finishedForm( $request ) {
        return true;
    }
}
