<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;

class AxsService extends Controller
{

    protected $url, $url_test, $password, $user;

    public function __construct()
    {
        $this->url_test = 'https://zeus.axsbolivia.com:8111/movil-rest/restful/sco';
        $this->url = 'https://zeus.axsbolivia.com:8111/movil-rest/restful/sco';
        // https://zeus.axsbolivia.com:8111/movil-rest-dev/restful/sco/buscarServiciosPP?codCliente=200091&origenOperacion=11
        $this->user = 'todotix';
        $this->password = 'T0d0tix2022';
    }


    public function processPost($action, $parameters, $url = null)
    {
        if ($url != null) {
            $this->url = $url;
        } elseif (config('customer.enable_test')) {
            $this->url = $this->url_test;
        }

        $authorization = "Basic " . base64_encode($this->user . ':' . $this->password);
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $authorization,
        ];
        return \External::guzzlePost($this->url, $action, $parameters, $headers);
    }

    public function processGet($action, $parameters, $url = null)
    {
        if ($url != null) {
            $this->url = $url;
        } elseif (config('customer.enable_test')) {
            $this->url = $this->url_test;
        }
        $authorization = "Basic " . base64_encode($this->user . ':' . $this->password);
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $authorization,
        ];
        return \External::guzzleGet($this->url, $action, $parameters, $headers);
    }

    public function rules()
    {
        return [
            'codCliente' => 'required'
        ];
    }

    public function getParameters()
    {
        return [
            [
                'id'    => 'codCliente',
                'label' => 'Introduzca su código de Cliente',
                'type'  => 'text',
                'required' => true,
                'initial_value' => NULL,
            ]
        ];

        /*        return [
            'status'  => 'ok',
            'message' => "Consulta exitosa",
            'data'    => [
                'fields' => [
                    [
                        'label' => 'Código del cliente',
                        'title' => 'Código del cliente',
                        'subfields' => [
                            [
                                'id'    => 'codCliente',
                                'label' => 'Introduzca su código de Cliente',
                                'type'  => 'text',
                                'initial_value' => NULL,
                            ]
                        ]
                    ]
                ]
            ]
        ]; */
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
        $result['code'] = $provider->code;
        $array = $this->processGet('buscarServiciosPP', $provider_fields);
        if (empty($array)) {
            return [];
        }
        $quantity_services = $array['entity']['result']['cantidadServicios'];
        if ($quantity_services == 0) {
            return [];
        }
        $invoices = $array['entity']['result']['datosFacturas'];
        foreach ($invoices as $data) {
            $provider_fields['idServicio'] = $data['idServicio'];
            $array2 = $this->processGet('buscarFacturas', $provider_fields);
            $detalle_facturas = $array2['entity']['result']['detalleFacturas'];
            foreach ($detalle_facturas as $subitem) {
                $subitem['codCliente']      = $provider_fields['codCliente'];
                $subitem['idServicio']      = $array2['entity']['result']['idServicio'];
                $subitem['nomCuenta']       = $array['entity']['result']['nomCuenta'];
                $subitem['dirCuenta']       = $array['entity']['result']['dirCuenta'];
                $subitem['origenOperacion'] = $array['entity']['result']['origenOperacion'];
                $subitem['nitCuenta']       = $array['entity']['result']['nitCuenta'];
                $item            = new \App\Models\ProviderItem();
                $item->parent_id = $provider->id;
                $item->amount    = floatval($subitem['montoFactura']);
                $item->debt_code = $subitem["idRow"];
                $item->can_pay   = 1;
                $item->name      = $data['descPlan'] . ' ' . $subitem['periodoFactura'];
                $item->date      = date('Y-m-d');
                $item->metadata = json_encode($subitem);
                $item->save();
                $items[] = [
                    'id' => $item->id,
                    'amount' => $item->amount,
                    'name' => $item->name,
                    'date' => $item->date,
                    'can_pay' => $item->can_pay,
                    'invoice' => "AXS",
                    'currency' => 'Bs'
                ];
            }
        }

        return $items;
    }

    public function getFormatItems($transaction_code, $payment_provider, $params, &$detail)
    {
        $items = \App\Models\ProviderItem::where('provider_id', $payment_provider->id)
            ->where('transaction_code', $transaction_code)
            ->where('status', 'holding')
            ->get();
        $elems = [];
        foreach ($items as $item) {
            $elems[] = ['concept' => $item->name, 'quantity' => 1, 'unit_price' => $item->amount, 'invoice' => 0, "metadata" => $item->metadata];
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
        //ESTO DEBE UTILIZAR EL GENERAR QR NUEVO
        $detail = [];
        $items = $this->getFormatItems($transaction_code, $payment_provider, $params, $detail);

        if ($tenantUrl) {
            $validate_name = $profile->name ?? $profile->first_name ?? 'AXS';
            $paramsTransaction = [
                "user_id"        => $profile->id,
                "identifier"     => $transaction_code,
                "email"          => $profile->email,
                "cellphone"      => $profile->cellphone,
                "first_name"     => $profile->name ?? $profile->first_name ?? 'AXS',
                "last_name"      => $profile->last_name ?? 'AXS',
                "ci_number"      => $profile->ci_number ?? $profile->country_identity,
                "nit_number"     => $profile->nit ?? $profile->invoice_number,
                "nit_name"       => $profile->name ?? $profile->invoice_name,
                "detail"         => sprintf('Pago realizado por : ' . $validate_name),
                "document_type"  => null,
                "callback_url"   => url('api/v1/bridge-pago-confirmado/' . $transaction_code),
                "redirect_front" => "/",
                "redirect_app"   => true,
                "payment_method" => "payment-link",
                "items"          => $items,

            ];
            $result = app('App\Http\Controllers\PaymentTenantController')->generatePayment(
                $tenantUrl,
                $paramsTransaction,
                request()->bearerToken()
            );
            $statusResult = $result['status'] ?? false;
            if (!$statusResult) {
                return [
                    'status'  => false,
                    'message' => 'Hubo un error al procesar su solicitud.',
                    'errors'  => ['La transacción no ha podido ser generada, intente nuevamente más tarde.']
                ];
            }
            \App\Models\ProviderItem::where('provider_id', $payment_provider->id)
                ->where('transaction_code', $transaction_code)
                ->update(['external_transaction_code' => $result['transaction']['external_transaction_code'], 'callback_url' => $callbackUrl]);
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
        foreach ($providerItems as $item) {
            $json = json_decode($item->metadata, true);
            $params = [
                'tipoTransaccion'   => 0,
                'cantidadFacturas'  => 1,
                'idRows'            => [
                    ['idRow' => $json['idRow']]
                ],
                'origenTransaccion' => 'TDTX',
                'codigoDepto'       => 1,
                'codigoCiudad'      => 11,
                'codigoEntidad'     => 111,
                'codigoAgencia'     => 1,
                'codigoUsuario'     => 0,
                'fechaProceso'      => date('Ymd'),
                'horaProceso'       => date('His'),
                'nombreFactura'     => $json['nombreFactura'],
                'nitFactura'        => $json['nitFactura'],
                'idTransaccionPP'   => $item->id,
                'origenOperacion'   => 11,
                'formaPago'         => 1,
                'tipoMoneda'        => 1,
            ];
            $result    = $this->processPost('procesarCobranzasXFacturasPP', $params);
            $statusTxt = $result['entity']['result']['respuesta'] ?? '';
            if ($statusTxt == 'Se procesaron todos los pagos') {
                //se proceso bien
                $idsSend[] = $item->id;
            }
            //return ['status' => false, 'message' => 'Hubo un error al procesar su solicitud', 'data' => $result['entity'] ?? []];
        }

        return ['status' => true, 'message' => 'Pagos registrados exitósamente.', 'idsSend' => $idsSend];
    }

    public function enableMultipleSteps() {
        return false;
    }

    public function finishedForm( $request ) {
        return true;
    }
}
