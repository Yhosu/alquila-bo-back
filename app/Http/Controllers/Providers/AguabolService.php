<?php

namespace App\Http\Controllers\Providers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use App\Services\PaymentTenantService;

use AdminItem;
use GuzzleHttp\Client;
use App\Helpers\Func;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class AguabolService extends Controller {

    protected $url;

    public function __construct() {
    }

    public function processPost( $url, $action, $parameters, $headers = [] ) {
        return \External::post($url, $action, $parameters, $headers);
    }

    public function processGet( $url, $action, $parameters, $headers = [] ) {
        return \External::get($url, $action, $parameters, $headers);
    }

    public function rules() {
        return [
            'type'   => 'required',
            'search' => 'required'
        ];
    }

    public function getParameters() {
        return [
            [
                'id'    => "type",
                'label' => "Tipo de búsqueda",
                'type'  => "select",
                'values' => [
                    ['value'=> 'Recorrido',         'label' => 'Recorrido'],
                    ['value'=> 'Código de cliente', 'label' => 'Código de cliente'],
                ],
                'required' => true,
                'initial_value' => 'recorrido',
            ],
            [
                'id' => "search",
                'label' => "Valor de búsqueda",
                'type' => "text",
                'required' => true,
                'initial_value' => NULL,
            ]
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

    public function getDebts(
        $apiKey,
        $account,
        $provider,
        $provider_fields,
        $url
    ) {
        $debts = [
            [
                'id'             => '6f32300a-d870-4f60-9410-970e72634e8e',
                'amount'         => currencyFormat( 30 ),
                'originalAmount' => 30,
                'type'           => 'recorrido',
                'name'           => 'Deuda mes ENERO',
                'date'           => '2025-01-01',
                'expDate'        => null,
                'currency'       => 'BOB',
                'search'         => '131415',
            ],
            [
                'id'             => '8b8eea4d-5f71-46b9-8eb1-8ede1874afd8',
                'amount'         => currencyFormat( 40 ),
                'originalAmount' => 40,
                'type'           => 'recorrido',
                'name'           => 'Deuda mes FEBRERO',
                'date'           => '2025-02-01',
                'expDate'        => null,
                'currency'       => 'BOB',
                'debtref'        => null,
                'search'         => '131415',
            ],
            [
                'id'             => '9b36bc2d-4b12-4337-9296-733f189bbf17',
                'amount'         => currencyFormat( 40 ),
                'originalAmount' => 40,
                'name'           => 'Deuda mes MARZO',
                'date'           => '2025-03-01',
                'expDate'        => null,
                'currency'       => 'BOB',
                'debtref'        => null,
                'search'         => '131415',
            ]
        ];
        $arrayDebts['deudas'] = [];
        $accountId = $account->id ?? null;
        $key = 1;
        foreach( $debts as $debt ) {
            $item = \App\Models\ProviderItem::where('api_key_id', $apiKey->id)
                ->where('account_id', $accountId)
                ->where('environment', $apiKey->environment)
                ->where('date', $debt['date'])
                ->where('code', $debt['id'])
                ->first();
            $expDate = null;
            $itemStatus = $item->status ?? null;
            if( in_array( $itemStatus, ['paid', 'sent'] ) ) continue;
            if( !$item ) {
                $debt['type']           = $provider_fields['type'];
                $debt['search']         = $provider_fields['search'];
                $item                   = new \App\Models\ProviderItem;
                $item->account_id       = $account->id ?? null;
                $item->api_key_id       = $apiKey->id;
                $item->provider_id      = $provider->id;
                $item->environment      = $apiKey->environment;
                $item->status           = 'holding';
                $item->amount           = floatval($debt['originalAmount']);
                $item->name             = sprintf( '%s', $debt['name'] );
                $item->date             = $debt['date'];
                $item->code             = $debt['id'];
                $item->metadata         = json_encode($debt);
                $item->order            = $key;
                $item->save();
                $key++;
            }
            $arrayDebts['deudas'][] = [
                'id'             => $item->id,
                'amount'         => currencyFormat( $item->amount ),
                'originalAmount' => $item->amount,
                'name'           => $item->name,
                'date'           => $debt['date'],
                'expDate'        => $expDate,
                'currency'       => 'BOB',
                'debtref'        => null
            ];
        }
        return $arrayDebts;
    }

    public function getFormatItems( $transaction_code, $payment_provider, $params, &$detail ) {
        $items = \App\Models\ProviderItem::where('provider_id', $payment_provider->id)
            ->where('transaction_code', $transaction_code)
            ->where('status', 'holding')
            ->get();
        $elems = [];
        foreach( $items as $item ) {
            $json = json_decode( $item->metadata, true );
            $elems[] = [ 'concept' => $item->name, 'quantity' => 1, 'unit_price' => $item->amount, 'invoice' => 0];
            $detail[] = $item->name;
        }
        return $elems;
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
        $items = $this->getFormatItems( $transaction_code, $payment_provider, $params, $detail );
        $strDetail = implode( ' | ', $detail );
        $appkeyLibelulaDefault = config('services.libelula.appkey');
        if( $tenantUrl ) {
            $paramsTransaction = [
                "user_id"        => $profile->id,
                "identifier"     => $transaction_code,
                "email"          => $profile->email,
                "cellphone"      => $profile->cellphone,
                "first_name"     => $profile->name ?? $profile->first_name ?? 'LIBELULA',
                "last_name"      => $profile->last_name ?? 'BRIDGE',
                "ci_number"      => $profile->ci_number ?? $profile->country_identity,
                "nit_number"     => $profile->nit ?? $profile->invoice_number,
                "nit_name"       => $profile->name ?? $profile->invoice_name,
                "detail"         => sprintf('Pagos Varios CTLP: ' . $transaction_code),
                "product_code"   => null,
                "document_type"  => null,
                "invoice"        => true,
                "callback_url"   => url('api/v1/bridge-pago-confirmado/' . $transaction_code),
                "redirect_front" => 'https://ctlp.test.front.solunes.com/my-accounts',
                "redirect_app"   => true,
                "payment_method" => "payment-link",
                "items"          => $items
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
                'transaction_id'            => $result['transaction_id'],
                'external_transaction_code' => $result['transaction']['external_transaction_code'],
                'status'                    => true,
            ];
        } else {
            // TODO: Generar la transacción por defecto
        }
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
            $params = [
                'jsonrpc' => '2.0',
                'method' => 'object',
                'params' => [
                    'service' => 'object',
                    'method' => 'execute_kw',
                    'args' => [
                        $this->db_ctlp,
                        $this->id_ctlp,
                        $this->user_ctlp,
                        'ws.transaction',
                        'service_pay_due',
                        [
                            [
                                'odoo_ids'       => [$providerItem->code],
                                'currency'       => 'bs',
                                'tdtx_id'        => $externalTransactionCode,
                                'tdtx_date'      => date('Y-m-d'),
                                'tdtx_canal'     => "Canal 8",
                                'tdtx_deuda'     => $providerItem->amount,
                                'tdtx_sucursal'  => "Sucursal 8",
                                'tdtx_recauda'   => "Recaudado",
                                'amount_pay'     => $providerItem->amount,
                                'nit'            => $nitNumber,
                                'razon'          => $nitName,
                                'invoice_number' => $key + 1,
                                'control_code'   => "CA-CA-CA-CA"
                            ]
                        ]
                    ]
                ],
            ];
            $result = $this->processPost( $url, '', $params );
            $errorMessage = $result['result']['error'] ?? false;
            if( !$errorMessage ) {
                $idsSend[] = $providerItem->id;
            }
        }
        return [ 'status' => true, 'message' => 'Pagos registrados exitósamente.', 'idsSend' => $idsSend];
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
        return $verifyPendigs > 0
            ? [ 'status' => false, 'message' => 'Debe seleccionar/pagar las deudas anteriores antes de poder seleccionar estas deudas.']
            : [ 'status' => true, 'message' => 'Datos validados con éxito' ];
    }

    public function enableMultipleSteps() {
        return false;
    }

    public function finishedForm( $request ) {
        return true;
    }
}
