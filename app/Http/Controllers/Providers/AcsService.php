<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

use AdminItem;
use GuzzleHttp\Client;
use App\Helpers\Func;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class AcsService extends Controller {

    protected $url;

    public function __construct() {
    }

    public function processPost( $url, $action, $parameters, $headers = [] ) {
        return \External::post($url, $action, $parameters, $headers);
    }

    public function processGet( $url, $action, $parameters, $headers = [] ) {
        return \External::get($url, $action, $parameters, $headers);
    }

    public function getParameters() {
        return [
            [
                'id' => "debtref",
                'label' => "Introduzca su Email",
                'type' => "text",
                'required' => true,
                'initial_value' => null,
            ]
        ];
    }

    public function getParametersStep2( $request, $provider, $apiKey ) {
        $env = $apiKey->environment;
        $fieldUrl = 'url_' . $env;
        $childrens = $this-> getChildrens($request->debtref, $provider->$fieldUrl);
        $array_childrens = [];
        foreach( $childrens as $children_item ) {
            $array_childrens[] = [ 'value' => $children_item['StudentID'], 'label' => $children_item['StudentName'] ];
        }
        return [
            [
                'id' => "debtref",
                'label' => "Introduzca su Email",
                'type' => "text",
                'initial_value' => $request->debtref,
                'disabled' => true,
            ],
            [
                'id'     => "StudentID",
                'label'  => "Seleccione hijo",
                'type'   => "select",
                'values' => $array_childrens,
                'initial_value' => null,
            ],
        ];
    }

    public function getChildrens($debtref, $url) {
        $params = [ 'debtref' => $debtref ];
        $array  = $this->processPost( $url, 'GetDebts', $params );
        if( $array['IsError'] ) {
            return [
                'status'    => false, 
                'show_form' => false,
                'message'   => 'No se han encontrado pagos pendientes con los datos ingresados.', 
                'items'     => [] 
            ];
        }
        return [ 'status' => true, 'data' => $array['Data'] ];
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
        return true;
    }

    public function finishedForm( $request ) {
        $items = [];
        if( $request->debtref ) $items[] = $request->debtref;
        if( $request->StudentID ) $items[] = $request->StudentID;
        if( count( $items ) == 2 ) return true;
        return false;
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
        $paymentCodes          = \App\Models\ProviderItem::where('transaction_code', $transaction_code)->pluck('code')->toArray();
        $firstTx               = \App\Models\ProviderItem::where('transaction_code', $transaction_code)->first();
        $params                = [ 'AcsDebtIds' => $paymentCodes ];
        $jsonData              = json_decode( $firstTx->metadata, true);
        $params['debtRef']     = $jsonData['debtref'];
        $params['razonSocial'] = $invoice_params['invoice_name'];
        $params['DocType']     = $invoice_params['invoice_doc_type'] ?? 'CI';
        $params['ci_nit']      = $invoice_params['invoice_doc_number'];
        $params['email']       = $jsonData['debtref'] ?? $invoice_params['invoice_email'];
        $result = $this->processPost( $payment_provider->url, 'PayDebt', $params );
        if( $result['IsError'] ) {
            return [
                'status'  => false, 
                'message' => $result['Message'] ?? 'Hubo un error al procesar su solicitud.', 
                'errors'  => ['La transacción no ha podido ser generada, intente nuevamente más tarde.'] 
            ];
        }
        return [
            'payment_url'               => $result['Data']['url'],
            'qr_url'                    => $result['Data']['url'],
            'amount'                    => $amount,
            'currency'                  => 'BOB',
            'codigo_recaudacion'        => $result['Data']['codigo_recaudacion'],
            'app_key'                   => null,
            'payment_code'              => null,
            'transaction_id'            => $result['Data']['TransactionId'],
            'external_transaction_code' => $result['Data']['TransactionId'],
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
        $params = [ 'debtref' => $provider_fields['debtref'] ];
        $array  = $this->processPost( $url, 'GetDebts', $params );
        if( $array['IsError'] ) {
            return [];
        }
        $debts = array_values( array_filter( $array['Data'], function( $item ) use( $provider_fields ) {
            return $item['StudentID'] == $provider_fields['StudentID'];
        }));
        if( empty( $debts ) || empty( $debts[0]['debtList'] ) )  {
            return [];
        }
        $debts = $debts[0]['debtList'];
        usort($debts, function($a, $b) {
            return strtotime($a['DueDate']) - strtotime($b['DueDate']);
        });
        $arrayDebts = [];
        foreach( $debts as $debt ) {
            $item = \App\Models\ProviderItem::where('api_key_id', $apiKey->id)
                ->where('account_id', $accountId)
                ->where('environment', $apiKey->environment)
                ->where('code', $debt['Id'])
                ->first();
            $itemStatus = $item->status ?? null;
            if( in_array( $itemStatus, ['paid', 'sent'] ) ) continue;
            $debt['debtref']        = $provider_fields['debtref'];
            $item                   = new \App\Models\ProviderItem;
            $item->account_id       = $account->id ?? null;
            $item->api_key_id       = $apiKey->id;
            $item->provider_id      = $provider->id;
            $item->environment      = $apiKey->environment;
            $item->status           = 'holding';
            $item->amount           = floatval($debt['TotalBs']);
            $item->name             = sprintf('%s %s - %s', $debt['StudentName'], $debt['StudentLastName'], $debt['Description']);
            $item->date             = date('Y-m-d');
            $item->metadata         = json_encode($debt);
            $item->code             = $debt['Id'];                     // ID en el servicio
            $item->save();
            $arrayDebts['DEUDAS'][] = [
                'id'             => $item->id,
                'amount'         => currencyFormat( $item->amount ),
                'originalAmount' => $item->amount,
                'partnerName'    => $debt['StudentName'] . ' ' . $debt['StudentLastName'],
                'partnerCode'    => $debt['Description'],
                'name'           => $item->name,
                'date'           => getLiteralFromYearAndMonth( date('d/m/Y') ),
                'expDate'        => $item->date,
                'currency'       => 'BOB',
                'debtref'        => $provider_fields['debtref'],
                'emits'          => 'Recibo'
            ];
        }
        return $arrayDebts;
    }
}