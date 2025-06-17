<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

use AdminItem;
use GuzzleHttp\Client;
use App\Helpers\Func;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Helpers\External;

class SasService extends Controller {

    protected $url;

    public function __construct() {
        $this->groups = [
            'Cursos'                => 1,
            'Uniformes'             => 2,
            'Mesualidades'          => 3,
        ];

        $this->groups_translate = [
            'Cursos' => 'Talleres',
            'Uniformes' => 'Góndolas',
            'Mesualidades' => 'Mensualidades y otros',
        ];
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
                'id'            => "SASUser",
                'label'         => "Introduzca su usuario",
                'type'          => "text",
                'required'      => true,
                'initial_value' => null,
            ],
            [
                'id'            => "SASPassword",
                'label'         => "Introduzca su contraseña",
                'type'          => "text",
                'required'      => true,
                'initial_value' => null,
            ],
            [
                'id'     => "group",
                'label'  => "Grupo",
                'type'   => "select",
                'values' => [
                    ['value'=> 'Cursos',       'label' => 'Talleres'],
                    ['value'=> 'Uniformes',    'label' => 'Góndolas'],
                    ['value'=> 'Mesualidades', 'label' => 'Mensualidades y otros'],
                ],
                'required' => true,
                'initial_value' => 'Cursos',
            ]
        ];
    }    

    public function getDebts(
        $apiKey,
        $account,
        $provider,
        $provider_fields,
        $url
    ) {
        $params = [ 'SASUser' => $provider_fields['SASUser'], 'SASPassword' => $provider_fields['SASPassword'] ];
        $array  = $this->processPost( $url, 'GetDebts', $params );
        if( $array['IsError'] ) {
            return [];
        }
        $groups_translate = array_flip($this->groups_translate);
        $debts = getElemsOfArrayOfArray( $array['Data'], $groups_translate[$provider_fields['group']] );
        if( empty( $debts['debts'] ) ) return [];
        $debts_array = likeProductsArray( $debts['debts'], 'nombreCliente', $provider_fields['names'] );
        if( empty( $debts_array ) ) return [];
        $validate_childrens = collect( $debts_array )->groupBy('nombreCliente');
        if( count( $validate_childrens ) != 1 ) {
            return [
                'status'  => 'nok', 
                'message' => 'Por favor ingrese mas información en el campo nombre  (Primer Nombre o Segundo Nombre, Ej. Abel', 
                'items'   => [] 
            ];
        }
        usort($debts_array, function($a, $b) {
            return strtotime($a['fecha']) - strtotime($b['fecha']);
        });
        $arrayDebts = [];
        $accountId = $account->id ?? null;
        foreach( $debts_array as $key => $debt ) {
            $item = \App\Models\ProviderItem::where('api_key_id', $apiKey->id)
                ->where('account_id', $accountId)
                ->where('environment', $apiKey->environment)
                ->where('code', $debt['codigoInterno'])
                ->first();
            $expDate = date('Y-m-t', strtotime($debt['period']. '/01'));
            $itemStatus = $item->status ?? null;
            if( in_array( $itemStatus, ['paid', 'sent'] ) ) continue;
            $debt['codigoCliente'] = $debts['client_code'];
            $debt['debtref']       = $provider_fields['SASUser'];
            $debt['SASUser']       = $provider_fields['SASUser'];
            $debt['SASPassword']   = $provider_fields['SASPassword'];
            $debt['group']         = $provider_fields['group'];
            $debt['names']         = $provider_fields['names'];
            $item                   = new \App\Models\ProviderItem;
            $item->account_id       = $account->id ?? null;
            $item->api_key_id       = $apiKey->id;
            $item->provider_id      = $provider->id;
            $item->environment      = $apiKey->environment;
            $item->status           = 'holding';
            $item->amount           = floatval($debt['importeAdeudado']);
            $item->name             = $debt['nombreCliente']  . ' | ' . $debt['razonSocial'] . ' | ' . $debt['periodo'] . ' | ' . $debt['codigoInterno'];
            $item->date             = $expDate;
            $item->code             = $debt['codigoInterno'];
            $item->metadata         = json_encode($debt);
            $item->order            = $key + 1;
            $item->save();
            $arrayDebts[$groups_translate[$provider_fields['group']]][] = [
                'id'             => $item->id,
                'amount'         => currencyFormat( $item->amount ),
                'originalAmount' => $item->amount,
                'partnerName'    => $debt['nombreCliente'],
                'partnerCode'    => $debt['razonSocial'],
                'name'           => $item->name,
                'date'           => getLiteralFromYearAndMonth( $debt['period'] ),
                'expDate'        => $expDate,
                'currency'       => 'BOB',
                'debtref'        => $provider_fields['SASUser'],
                'emits'          => 'Recibo'
            ];
        }
        return $arrayDebts;
    }

    public function getChildrens( $SASUser, $SASPassword, $url ) {
        $params = [ 'SASUser' => $SASUser, 'SASPassword' => $SASPassword ];
        $array  = $this->processPost( $url, 'GetDebts', $params );
        if( $array['IsError'] ) {
            return [
                'status'    => false, 
                'message'   => 'No se han encontrado pagos pendientes con los datos ingresados.', 
                'items'     => [] 
            ];
        }
        return [ 'status' => true, 'data' => $array['Data'] ];
    }

    public function getParametersStep2( $request, $provider, $apiKey ) {
        $env = $apiKey->environment;
        $fieldUrl = 'url_' . $env;
        $childrens = $this-> getChildrens($request->SASUser, $request->SASPassword, $provider->$fieldUrl);
        $array_childrens = [];
        foreach( $childrens as $children_item ) {
            $array_childrens[] = [ 'value' => $children_item['nombreCliente'], 'label' => $children_item['nombreCliente'] ];
        }
        return [
            [
                'id'            => "SASUser",
                'label'         => "Introduzca su usuario",
                'type'          => "text",
                'disabled'      => true,
                'required'      => true,
                'initial_value' => $request->SASUser,
            ],
            [
                'id'            => "SASPassword",
                'label'         => "Introduzca su contraseña",
                'disabled'      => true,
                'type'          => "text",
                'required'      => true,
                'initial_value' => $request->SASPassword,
            ],
            [
                'id'       => "group",
                'label'    => "Grupo",
                'disabled' => true,
                'type'     => "text",
                'required'      => true,
                'initial_value' => $this->groups_translate[$request->group],
            ],
            [
                'id'            => "names",
                'label'         => "Seleccione al estudiante",
                'type'          => "select",
                'required'      => true,
                'values'        => $array_childrens,
                'initial_value' => NULL,
            ]
        ];
    }

    public function enableMultipleSteps() {
        return true;
    }

    public function finishedForm( $request ) {
        $items = [];
        if( $request->SASUser ) $items[] = $request->SASUser;
        if( $request->SASPassword ) $items[] = $request->SASPassword;
        if( $request->group ) $items[] = $request->group;
        if( $request->names ) $items[] = $request->names;
        if( count( $items ) == 4 ) return true;
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
        $params = [
            'debtRef'       => '',
            'razonSocial'   => $invoice_params['invoice_name'] ?? '',
            'DocType'       => $invoice_params['invoice_doc_type'] ?? 'CI',
            'ci_nit'        => $invoice_params['invoice_doc_number'] ?? '',
            'email'         => $invoice_params['invoice_email'] ?? '',
            'codigoCliente' => '',
            'SasDebtType'   => '',
            'SasDebtList'   => []
        ];
        $sasDebts = [];
        $debtref  = '';
        $debttype = '';
        $code     = '';
        $amount   = 0;
        foreach( $providerItems as $providerItem ) {
            $debtItem = (object)json_decode( $providerItem->metadata, true );
            $debtref   = $debtItem->debtref;
            $code      = $debtItem->codigoCliente;
            $debttype  = $debtItem->group;
            $sasDebts[] = [
                'descripcion'     => $debtItem->descripcion,
                'codigoDocumento' => $debtItem->codigoDocumento,
                'facturable'      => $debtItem->facturable,
                'importeAdeudado' => $debtItem->importeAdeudado,
                'fecha'           => $debtItem->fecha,
                'codigoInterno'   => $debtItem->codigoInterno,
                'nombreCliente'   => $debtItem->nombreCliente,
                'moneda'          => $debtItem->moneda,
                'mensualidad'     => $debtItem->mensualidad,
                'razonSocial'     => $debtItem->razonSocial,
                'nit'             => $debtItem->nit,
                'periodo'         => $debtItem->periodo,
                'codigoProducto'  => $debtItem->codigoProducto,
                'numeroDocumento' => $debtItem->numeroDocumento,
                'codigo_documento_sector' => $debtItem->codigo_documento_sector,
            ];
            $amount += $debtItem->importeAdeudado;
            $params['SASUser']     = $debtItem->SASUser;
            $params['SASPassword'] = $debtItem->SASPassword;
        }
        $groupsTranslate         = array_flip($this->groups_translate);
        $realGroup               = $groupsTranslate[$debttype];
        $params['codigoCliente'] = $code;
        $params['debtRef']       = $debtref;
        $params['email']         = $invoice_params['invoice_email'] ?? '';
        $params['SasDebtType']   = $this->groups[$realGroup];
        $params['SasDebtList']   = $sasDebts;
        $result = $this->processPost( $url, 'PayDebt', $params );
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
    
}