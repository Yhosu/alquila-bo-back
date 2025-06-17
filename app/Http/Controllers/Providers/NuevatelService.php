<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use AdminItem;
use GuzzleHttp\Client;

class NuevatelService extends Controller
{
    public function __construct() {
        $this->test  = false;
        $this->url   = 'https://vapiservicestwo.nuevatel.com';
        $this->alias = '713134291';
        $this->headers = [
            'Authorization' => 'Basic ' . base64_encode( 'libelula:zW#rtW82529bR8')
        ];
        $this->front_url = config('services.ctlp.front_url');
    }

    public function processPost($action, $parameters, $url = null) {
        return \External::post( $url ?? $this->url, $action, $parameters, $this->headers);
    }

    public function processGet($action, $parameters, $url = null) {
        return \External::get( $url ?? $this->url, $action, $parameters, $this->headers);
    }

    public function rules() {
        return [
            'debtref' => 'required'
        ];
    }

    public function getParameters() {
        return [
            [
                'id' => "cellphone",
                'label' => "Teléfono",
                'type' => "text",
                'required' => true,
                'initial_value' => NULL,
            ],
            [
                'id' => "amount",
                'label' => "Monto",
                'type' => "text",
                'required' => true,
                'initial_value' => NULL
            ]
        ];
    }    

        /* Validar suscriptor */
    public function validateSuscriptor( $cellphone ) {    
        $params = [
            'productName'  => 'compraCredito',
            'msisdn'       => '+591' . $cellphone,
            'providerCode' => 'LIBELULA',
            'appCode' => [
                'name' => 'APP/LIBELULA-BRIDGE',
                'ip' => ''
            ],
            'requestId' => time() . '-' . $cellphone
        ];            
        return $this->processPost('country/BO/site/crm-vivaservices.com/prepaid/validateSubscriber', $params);
    }

        /* Verificar si el cliente final puede acceder al servicio */
    public function verifySuscriptor( $cellphone, $amount, $request_id, $id, $token ) {
        $params = [
            "productName"    => "compraCredito",
            "alias"          => $this->alias,
            "token"          => $token,
            "paymentMode"    => "EXTERNAL_PAYMENT",
            "payType"        => "PPRE",
            "msisdn"         => $cellphone,
            "amount"         => $amount,
            "documentNumber" => "4402263",
            "businessName"   => "limbert llave",
            "email"          => "limbert.llave@nuevatel.com",
            "clientId"       => "",
            "cardNumber"     => "",
            "additionalData" => [
                "searchSource" => "LIBELULA",
                "paymentConformation" => [],
                "generateInvoice" => false, 
            ],
            "providerCode" => "LIBELULA",
            "appCode" => [
                "name" => "APP/LIBELULA-BRIDGE",
                "ip"   => ""
            ],
            "requestId"  => $request_id,
            "validating" => false
        ];
        return $this->processPost('country/BO/site/crm-vivaservices.com/rrss/savePay', $params);
    }

        /* Verificar comproiso de pago */
    public function checkValidity( $data ) {
        $params = [
            "transactionCode" => $data['transactionCode'],
            "amount"          => $data['amount'],
            "productName"     => "compraCredito",
            "providerCode"    => "LIBELULA",
            "appCode" => [
                "name" => "APP/LIBELULA-BRIDGE",
                "ip"   => ""
            ],
            "requestId" => $data['requestId'],
        ];            
        return $this->processPost('country/BO/site/crm-vivaservices.com/payment/verifyPay', $params);
    }

    public function cancelService( $params ) {
        return $this->processPost('country/BO/site/crm-vivaservices.com/payment/confirmPay', $params);
    }

        /* Confirmar el uso del servicio */
    public function confirmService( $data, $id, $payment_method = 'ATC_QR' ) {
        $params = [
            'transactionCode' => $data['transactionCode'],
            'payStatus'       => 'SUCCESS',
            'reason'          => '',
            'reasonDetail'    => '',
            'cardNumber'      => '1234000000006789',
            'externalPaymentDetail' => null,
            'externalPaymentDetailList' => [
                [
                    'id'          => $id,
                    'amount'      => number_format(floatval( $data['amount'] ), 2),
                    'currency'    => 'BOB',
                    'paymentMode' => $payment_method,
                ]
            ],
            'productName'  => 'compraCredito',
            'providerCode' => 'LIBELULA',
            'appCode' => [
                'name' => 'APP/LIBELULA-BRIDGE',
                'ip'   => '',
            ],
            'requestId' => $data['requestId']
        ];
        return $this->processPost('country/BO/site/crm-vivaservices.com/payment/confirmPay', $params);
    }

    public function getDebts(
        $apiKey,
        $account,
        $provider,
        $provider_fields,
        $url
    ) {
            /* Validamos el número de celular */
        $id = sprintf( '%s-%s', time(), $provider_fields['cellphone'] );
        $validate_suscriptor = $this->validateSuscriptor( $provider_fields['cellphone'] );
        $response_type_validate_suscriptor = $validate_suscriptor['status'] ?? null;
        if( $response_type_validate_suscriptor == 'ERROR' ) return [ 'status'  => false, 'message' => $validate_suscriptor['errorDescription'], 'items' => [] ];
        $verify_suscriptor = $this->verifySuscriptor( 
            $provider_fields['cellphone'], 
            $provider_fields['amount'], 
            $validate_suscriptor['requestId'], 
            $id, 
            $validate_suscriptor['token'] 
        );
        $response_type_verify_suscriptor = $verify_suscriptor['status'] ?? null;
        if( $response_type_verify_suscriptor == 'ERROR' ) return [ 'status'  => false, 'message' => $verify_suscriptor['errorDescription'], 'items' => [] ];
        $verify_suscriptor['amount'] = $provider_fields['amount'];
        $verify_suscriptor['cellphone'] = $provider_fields['cellphone'];
        $item               = new \App\Models\ProviderItem;
        $item->account_id   = $account->id ?? null;
        $item->api_key_id   = $apiKey->id;
        $item->provider_id  = $provider->id;
        $item->environment  = $apiKey->environment;
        $item->amount       = floatval( $provider_fields['amount'] );
        $item->can_pay      = 1;
        $item->name         = sprintf('Recarga crédito %s por %s', $provider_fields['cellphone'], $provider_fields['amount']);
        $item->date         = date('Y-m-d');
        $item->metadata     = json_encode( $verify_suscriptor );
        $item->code         = $id;
        $item->save();
        $arrayDebts['RECARGA'][] = [
            'id'             => $item->id,
            'amount'         => currencyFormat( $item->amount ),
            'originalAmount' => $item->amount,
            'partnerName'    => '',
            'partnerCode'    => '',
            'name'           => $item->name,
            'date'           => getLiteralFromYearAndMonth( $item->date ),
            'expDate'        => $item->date,
            'currency'       => 'BOB',
            'debtref'        => $provider_fields['cellphone'],
            'emits'          => 'Recibo'
        ];
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
        $detail = [];
        $partnerCode = null;
        $partnerName = null;
        $partnerNit  = null;
        $items = $this->getFormatItems( $transaction_code, $payment_provider, $params, $detail, $partnerCode, $partnerName, $partnerNit );
        $strDetail = implode( ' | ', $detail );
        $appkeyLibelulaDefault = config('services.libelula.appkey');
        if( $tenantUrl ) {
            $paramsTransaction = [
                "user_id"        => $profile->id,
                "identifier"     => $transaction_code,
                "email"          => $profile->email,
                "cellphone"      => $profile->cellphone,
                "first_name"     => $partnerName ?? ( $profile->name ?? ( $profile->first_name  . ' ' . $profile->last_name ) ?? 'LIBELULA' ) . ' - ' . $partnerCode,
                "last_name"      => 'NUEVATEL',
                "ci_number"      => $from_chatbot ? $partnerNit : ( $profile->ci_number ?? $profile->country_identity ),
                "nit_number"     => $from_chatbot ? $partnerNit : ( $profile->nit ?? $profile->invoice_number ),
                "nit_name"       => $profile->name ?? $profile->invoice_name,
                "detail"         => sprintf('Pagos Varios NUEVATEL: ' . $transaction_code),
                "document_type"  => null,
                "callback_url"   => url('api/v1/bridge-pago-confirmado/' . $transaction_code),
                "redirect_front" => $this->front_url,
                "redirect_app"   => true,
                "payment_method" => "payment-link",
                "items"          => $items,
                "metadata_lines" => [
                    ["key" => "SOCIO", "value" => $partnerCode ],
                ]
            ];
            $result = app('App\Http\Controllers\PaymentTenantController')->generatePayment(
                $tenantUrl,
                $paramsTransaction,
                request()->bearerToken()
            );
            $statusResult = $result['status'] ?? false;
            if( !$statusResult ) {
                return [
                    'status'  => false,
                    'message' => 'Hubo un error al procesar su solicitud.',
                    'errors'  => ['La transacción no ha podido ser generada, intente nuevamente más tarde.']
                ];
            }
            \App\Models\ProviderItem::where('provider_id', $payment_provider->id)
                ->where('transaction_code', $transaction_code)
                ->update(['external_transaction_code'=>$result['transaction']['external_transaction_code'], 'callback_url' => $callbackUrl]);
            return [
                'payment_url'               => $result['url'],
                'qr_url'                    => $result['qr_simple_url'],
                'amount'                    => $amount,
                'currency'                  => 'BOB',
                'codigo_recaudacion'        => $result['transaction']['collection_code'],
                'app_key'                   => null,
                'payment_code'              => null,
                'transaction_id'            => $result['transaction']['identifier'] ?? null,
                'external_transaction_code' => $result['transaction']['external_transaction_code'],
                'status'                    => true,
            ];
        } else {
            // TODO: Generar la transacción por defecto
        }
    }

    public function getFormatItems( $transaction_code, $payment_provider, $params, &$detail, &$partnerCode, &$partnerName, &$partnerNit ) {
        $items = \App\Models\ProviderItem::where('provider_id', $payment_provider->id)
            ->where('transaction_code', $transaction_code)
            ->where('status', 'holding')
            ->get();
        $elems = [];
        foreach( $items as $item ) {
            $json = json_decode( $item->metadata, true );
            $emits = $json['emits'] ?? 'C';
            $elems[] = [
                'concept'         => $item->name,
                'quantity'        => 1,
                'unit_price'      => $item->amount,
                'product_code'    => $json['group'] ?? null,
                'invoice'         => 0,
                'ignorar_factura' => 'true'
            ];
            $detail[] = $item->name;
        }
        return $elems;            
    }

    public function validateDebts( $arrayDebts, $apiKey ) {
        return [ 'status' => true, 'message' => 'Datos validados con éxito' ];
    }

    public function payWithCard(
        $transactionId,
        $cardCvv,
        $cardId,
        $tenantUrl
    ) {
        $params = [
            'card_id'        => $cardId,
            'card_cvv'       => $cardCvv,
            'transaction_id' => $transactionId,
            'payment_method' => 'card',
            'cash_name'      => 'Eduardo Mejía',
            'cash_user'      => '1',
            'metadata_lines' => []
        ];
        $result = app('App\Http\Controllers\PaymentTenantController')->payWithCard(
            $tenantUrl,
            $params
        );
        return $result;
    }    

    public function updatePayments(
        $providerItems,
        $amount,
        $externalTransactionCode,
        $nitNumber,
        $nitName,
        $url
    ) {
        $idsSend = [];
        foreach( $providerItems as $key => $providerItem ) {
            $json = json_decode( json: $providerItem->metadata );
            $checkValidity = $this->checkValidity( $json );
            $responseTypeCheckValidity = $checkValidity['status'] ?? null;
            if( $responseTypeCheckValidity == 'ERROR' ) {
                return ['status'=> false , 'message'=> 'Hubo un error al procesar su solicitud, sitio en mantenimiento.', 'data'=>[]];
            }
            $confirmService = $this->confirmService( $json, $providerItem->code, 'ATC_QR' );
            $responseConfirmService = $confirmService['status'] ?? null;
            if( $responseConfirmService == 'ERROR' ) {
                return ['status'=> false , 'message'=> 'Hubo un error al procesar su solicitud, sitio en mantenimiento.', 'data'=>[]];
            }
            $idsSend[] = $providerItem->id;
        }
        return [ 'status' => true, 'message' => 'Pagos registrados exitósamente.', 'idsSend' => $idsSend];
    }

    public function enableMultipleSteps() {
        return false;
    }

    public function finishedForm( $request ) {
        return true;
    }
}
