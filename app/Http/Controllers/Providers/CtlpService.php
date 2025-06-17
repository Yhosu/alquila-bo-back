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

class CtlpService extends Controller {

    protected $url;

    public function __construct() {
        $this->db_ctlp   = config('services.ctlp.database');
        $this->user_ctlp = config('services.ctlp.user');
        $this->id_ctlp   = config('services.ctlp.id');
        $this->front_url = config('services.ctlp.front_url');
        $this->groupsTranslate = [
            ['value'=> 'cuotas',     'label' => 'Cuotas Ordinarias y Alícuotas'],
            ['value'=> 'escuelas',   'label' => 'Escuelas'],
            ['value'=> 'otros',      'label' => 'Planes de pago, cuotas de admisión y otros'],
        ];
        $this->arrayEmits = [
            'C' => 'Recibo',
            'F' => 'Factura'
        ];
        $this->groups = [
            'escuelas' => [
                "C-TENAECO",
                "C-BASQT5",
                "C-BASQT1",
                "C-BASQT2",
                "C-BASQT3",
                "C-BASQT4",
                "C-BASINV ",
                "C-COLONIA",
                "C-COLONIAS1",
                "C-COLONIAS2",
                "C-COLONIAS3",
                "C-COLONIAS4",
                "C-COLNIAE",
                "C-COLNIAES1",
                "C-COLNIAES2",
                "C-COLNIAES3",
                "C-COLNIAES4",
                "C-FUTAED",
                "C-FUTAES",
                "C-FUTCU1",
                "C-FUTCU2",
                "C-FUTCU3",
                "C-FUTCU4",
                "C-FUTDAM",
                "C-FUTLOG",
                "C-FUTSEL1",
                "C-EVDEPFUT",
                "C-EVENTDEP",
                "C-NATCU3",
                "C-NATCU2",
                "C-NATCU1",
                "C-NATCU32",
                "C-NATCU22",
                "C-NATCU12",
                "C-NATCU33",
                "C-NATCU23",
                "C-NATCU13",
                "C-NATCU34",
                "C-NATCU24",
                "C-NATCU14",
                "C-NATAES",
                "C-NATESD",
                "C-NATCLI",
                "C-NATMAS",
                "C-TENKIN",
                "C-TENKIN 2",
                "C-TECOM1",
                "C-TECOM12",
                "C-TECOM2",
                "C-TECOM23",
                "C-TECOM22",
                "C-TECOM3",
                "C-TECOM32",
                "C-TECOM4",
                "C-TECOM42",
                "C-TENFO12",
                "C-TENFO13",
                "C-TENFO1",
                "C-TENFO2",
                "C-TENFO23",
                "C-TENFO22",
                "C-TENFO32",
                "C-TENFO33",
                "C-TENFO3",
                "C-TENFO4",
                "C-TENFO43",
                "C-TENFO42",
                "C-TENNA13",
                "C-TENNA12",
                "C-TENNA14",
                "C-TENNA22",
                "C-TENNA24",
                "C-TENNA23",
                "C-TENNA32",
                "C-TENNA33",
                "C-TENNA34",
                "C-TENNA43",
                "C-TENNA44",
                "C-TENNA42",
                "C-TENCO13",
                "C-TENCO1",
                "C-TENCO12",
                "C-TENCO22",
                "C-TENCO23",
                "C-TENCO2",
                "C-TENCO3",
                "C-TENCO33",
                "C-TENCO32",
                "C-TENCO42",
                "C-TENCO4",
                "C-TENCO43",
                "C-TENRO13",
                "C-TENRO1",
                "C-TENRO12",
                "C-TENRO23",
                "C-TENRO2",
                "C-TENRO22",
                "C-TENRO32",
                "C-TENRO33",
                "C-TENRO3",
                "C-TENRO42",
                "C-TENRO4",
                "C-TENRO43",
                "C-TENVE14",
                "C-TENVE13",
                "C-TENVE12",
                "C-TENVE22",
                "C-TENVE24",
                "C-TENVE23",
                "C-TENVE32",
                "C-TENVE34",
                "C-TENVE33",
                "C-TENVE44",
                "C-TENVE42",
                "C-TENVE43",
                "C-RAQCU1",
                "C-RAQCU2",
                "C-TENIN13",
                "C-TENIN1",
                "C-TENIN12",
                "C-EVDEPTAA",
                "C-EVDEPTAR",
                "C-TENTA2",
                "C-TENTA3",
                "C-TENTA13",
                "C-TENTA1",
                "C-TENTA12",
                "C-TENTA7",
                "C-TENTA72",
                "C-TENTA6",
                "C-TENTA52",
                "C-TENTA5",
                "C-TENTA53"
            ],
            'cuotas' => [
                "C-CERJUN",
                "C-CUOTAA",
                "C-CUOTAP",
                "C-CUOTHA",
                "C-CUOTHP",
                "C-CUOMAA",
                "C-CUOMAP",
                "C-CUOTAN1",
                "C-CUOTAN2",
                "C-CUOTEE",
                "C-CUOTPH1",
                "C-CUOTPH2",
                "C-CUOADM1",
                "C-CUOADM2",
                "C-CUOADM",
                "C-CUODIP",
                "C-CARNOT",
                "C-GASEMI",
                "TODOS-0001",
                "C-RETETM",
                "C-RECBTP",
                "C-RECBTM",
                "C-RESZTP",
                "C-RECTSC",
                "C-REQUTM",
                "C-RECTTA",
                "C-RECBCP",
                "C-RECBCM",
                "C-REVITM",
                "C-REVITP",
                "C-REHRTM",
                "C-CORPOR1",
                "C-CORPOR2",
                "C-CORPOR3",
                "C-CORMES"
            ],
            'otros'      =>  [
                "C-AUSCTLP",
                "C-CASVEN",
                "C-CARNET",
                "C-CARNET 2",
                "C-DEPORT",
                "C-EXTRAO",
                "C-MANADM",
                "C-MANCP",
                "C-MANTRA",
                "C-ALQPOL3",
                "C-CASALQ",
                "C-ALQCC2",
                "C-ALQCC1",
                "C-ALQCC3",
                "C-ALQCC4",
                "C-ALQSNK",
                "C-ALQSNK2",
                "C-USCTLP",
                "C-ALQHJO",
                "C-EVENTO",
                "C-EVENTO",
                "C-ALQHJM",
                "C-ALQHEI",
                "C-HJINVS",
                "C-ALQHJP",
                "C-VISHJMA",
                "C-VISHJME",
                "C-TOALLA",
                "C-REST2",
                "C-REST1",
                "C-REST4",
                "C-REST3",
                "C-REACAM",
                "C-VARIOS",
                "C-VARIOSHJ",
                "C-VENACT",
                "C-VISITA04",
                "C-VISIPUB",
                "C-VISITA05",
                "C-VISITA03",
                "C-VISITA01",
                "C-VISITA02"
            ]
        ];
    }

    public function processPost( $url, $action, $parameters, $headers = [] ) {
        return \External::post($url, $action, $parameters, $headers);
    }

    public function processGet( $url, $action, $parameters, $headers = [] ) {
        return \External::get($url, $action, $parameters, $headers);
    }

    public function rules() {
        return [
            'debtref' => 'required'
        ];
    }

    public function getParameters() {
        return [
            [
                'id' => "debtref",
                'label' => "Número de Carnet",
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
            /* Obtenemos el Código de socio en base al carnet */
        $partner = \App\Models\CtlpPartner::where( 'ci_number', $provider_fields['debtref'] )
            ->where( 'active', 1 )
            ->first();
        if( !$partner ) return false;
        if( $account ) {
            $nwMetadata = json_decode( $account->metadata, true );
            $nwMetadata['infoName'] = $partner->name;
            $account->metadata = json_encode( $nwMetadata );
            $account->save();
        }
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
                    'service_consult_due',
                    [ $partner->code ]
                ]
            ],
        ];
        $debts = $this->processPost( $url, '', $params )['result']['dues'] ?? [];
        return !empty($debts) ? true : false;
    }

    public function getDebts(
        $apiKey,
        $account,
        $provider,
        $provider_fields,
        $url
    ) {
        $partner = \App\Models\CtlpPartner::where( 'ci_number', $provider_fields['debtref'] )
            ->where( 'active', 1 )
            ->first();
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
                    'service_consult_due',
                    [ $partner->code ]
                ]
            ],
        ];
        $array = $this->processPost( $url, '', $params );
        $errorMessage = $array['result']['error'];
        if( $errorMessage ) return [];
        $dues     = $array['result']['dues'] ?? [];
        $cuotas   = \Func::getElemsOfArrayCtlp( $dues, 'cod_concept', $this->groups['cuotas'] );
        $escuelas = \Func::getElemsOfArrayCtlp( $dues, 'cod_concept', $this->groups['escuelas'] );
        $otros    = \Func::getElemsOfArrayCtlp( $dues, 'cod_concept', $this->groups['otros'] );
        $arrayDebts = [];
        $accountId = $account->id ?? null;
        foreach( $this->groupsTranslate as $group ) {
            $debts = ${$group['value']};
            if( empty( $debts ) ) {
                $arrayDebts[$group['value']] = [];
                continue;
            }
            $key = 1;
            foreach( $debts as $debt ) {
                $item = \App\Models\ProviderItem::where('api_key_id', $apiKey->id)
                    ->where('account_id', $accountId)
                    ->where('environment', $apiKey->environment)
                    ->where('date', $debt['date_due'])
                    ->where('code', $debt['odoo_id'])
                    ->first();
                $expDate = $debt['date_due'] != 'False' ? $debt['date_due'] : date('Y-m-t', strtotime($debt['period']. '/01'));
                $itemStatus = $item->status ?? null;
                if( in_array( $itemStatus, ['paid', 'sent'] ) ) continue;
                if( !$item ) {
                    $debt['debtref']        = $provider_fields['debtref'];
                    $debt['group']          = $debt['cod_concept'];
                    $debt['emits']          = $debt['emits'];
                    $debt['partnerCode']    = $debt['socio_code'];
                    $debt['nit']            = $debt['nit'];
                    $item                   = new \App\Models\ProviderItem;
                    $item->account_id       = $account->id ?? null;
                    $item->api_key_id       = $apiKey->id;
                    $item->provider_id      = $provider->id;
                    $item->environment      = $apiKey->environment;
                    $item->status           = 'holding';
                    $item->amount           = floatval($debt['amount_bs']);
                    $item->name             = sprintf( '%s %s %s', $debt['period'], $debt['concept'], $debt['cod_concept'] );
                    $item->date             = $expDate;
                    $item->code             = $debt['odoo_id'];
                    $item->metadata         = json_encode($debt);
                    $item->order            = $key;
                    $item->save();
                    $key++;
                }
                $arrayDebts[$group['value']][] = [
                    'id'             => $item->id,
                    'amount'         => currencyFormat( $item->amount ),
                    'originalAmount' => $item->amount,
                    'partnerName'    => $debt['name'],
                    'partnerCode'    => $debt['socio_code'],
                    'name'           => $item->name,
                    'date'           => getLiteralFromYearAndMonth( $debt['period'] ),
                    'expDate'        => $expDate,
                    'currency'       => 'BOB',
                    'debtref'        => $provider_fields['debtref'],
                    'emits'          => $this->arrayEmits[$debt['emits']] ?? 'Recibo'
                ];
            }
        }
        return $arrayDebts;
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
            $partnerCode = !$partnerCode ? $json['socio_code'] : $partnerCode;
            $partnerName = !$partnerName ? $json['name'] : $partnerName;
            $partnerNit  = !$partnerNit ? $json['nit'] : $partnerNit;
            $elems[] = [
                'concept'         => $item->name,
                'quantity'        => 1,
                'unit_price'      => $item->amount,
                'product_code'    => $json['group'] ?? null,
                'invoice'         => 0,
                'ignorar_factura' => 'true'
                // 'invoice'         => $emits == 'C' ? 0 : 1,
                // 'ignorar_factura' => $emits == 'C' ? 'true' : 'false'
            ];
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
                "last_name"      => 'CTLP',
                "ci_number"      => $from_chatbot ? $partnerNit : ( $profile->ci_number ?? $profile->country_identity ),
                "nit_number"     => $from_chatbot ? $partnerNit : ( $profile->nit ?? $profile->invoice_number ),
                "nit_name"       => $profile->name ?? $profile->invoice_name,
                "detail"         => sprintf('Pagos Varios CTLP: ' . $transaction_code),
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
