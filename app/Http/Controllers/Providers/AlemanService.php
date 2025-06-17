<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;

class AlemanService extends Controller
{

    protected $url;

    public function __construct()
    {
        $this->url_test = 'https://34.122.188.216/jsonrpc';
        $this->url      = 'https://34.122.188.216/jsonrpc';
        $this->user     = 92;
        $this->password = '1nt3gr4c10n2022';
        $this->database = 'Prueba-web_service_test';
        $this->jsonrpc  = [
            'jsonrpc' => '2.0',
            'method'  => 'object',
            'params'  => [
                'service' => 'object',
                'method'  => 'execute_kw',
                'args'    => [
                    $this->database,
                    $this->user,
                    $this->password,
                ]
            ]
        ];
    }

    public function processPost($action, $parameters, $headers = []) {
        return \External::post($this->url, $action, $parameters, $headers);
    }

    public function processGet($action, $parameters, $headers = []) {
        return \External::get($this->url, $action, $parameters, $headers);
    }


    public function getParameters() {
        return [
            [
                'id' => "family",
                'label' => "ID de familia",
                'type' => "text",
                'required' => true,
                'initial_value' => NULL,
            ]
        ];
    }

    public function rules(){
        return [
            'family' => 'required'
        ];
    }

    public function verifyDebts(
        $apiKey,
        $account,
        $provider,
        $provider_fields,
        $url
    ) {
        $this->jsonrpc['params']['args'][] = 'op.request.charge';
        $this->jsonrpc['params']['args'][] = 'service_get_charges';
        $this->jsonrpc['params']['args'][] = [$provider_fields['family']];
        $array    = $this->processPost('', $this->jsonrpc);
        $invoices = $array['result']['cargos'] ?? [];
        return empty( $invoices ) ? false : true;
    }

    public function getDebts(
        $apiKey,
        $account,
        $provider,
        $provider_fields,
        $url
    ) {
        $this->jsonrpc['params']['args'][] = 'op.request.charge';
        $this->jsonrpc['params']['args'][] = 'service_get_charges';
        $this->jsonrpc['params']['args'][] = [$provider_fields['family']];
        $array = $this->processPost('', $this->jsonrpc) ?? null;
        $error = $array['error'] ?? null;
        if( !$array ) {
            return ['status' => false, 'message' => 'Servicio fuera de linea temporalmente.', 'items' => []];
        } elseif( $error ) {
            return ['status' => false, 'message' => 'Hubo un error al procesar su solicitud, verifique que el ID de familia ingresado es el correcto', 'items' => []];
        }
        $invoices = $array['result']['cargos'];
        $accountId = $account->id ?? null;
        $arrayDebts = [];
        foreach ($invoices as $data) {
            $item = \App\Models\ProviderItem::where('api_key_id', $apiKey->id)
                ->where('account_id', $accountId)
                ->where('environment', $apiKey->environment)
                ->where('code', $data['id'])
                ->first();
            $itemStatus = $item->status ?? null;
            if( in_array( $itemStatus, ['paid', 'sent'] ) ) continue;
            $data['request_id'] = $array['result']['request_id'];
            if( !$item ) {
                $item               = new \App\Models\ProviderItem;
                $item->account_id   = $account->id ?? null;
                $item->api_key_id   = $apiKey->id;
                $item->provider_id  = $provider->id;
                $item->environment  = $apiKey->environment;
                $item->status       = 'holding';
                $item->name         = $data['Descripcion'] . ' - ' . $data['NombreEstudiante_id'];
                $item->date         = date('Y-m-d');
                $item->metadata     = json_encode($data);
                $item->code         = $data['id'];
                $item->save();
            }
            $arrayDebts['DEUDAS'][] = [
                'id'             => $item->id,
                'amount'         => currencyFormat( $item->amount ),
                'originalAmount' => $item->amount,
                'partnerName'    => $data['name'],
                'partnerCode'    => $data['socio_code'],
                'name'           => $item->name,
                'date'           => getLiteralFromYearAndMonth( date('d/m/Y') ),
                'expDate'        => null,
                'currency'       => 'BOB',
                'debtref'        => $provider_fields['family'],
                'emits'          => 'Recibo'
            ];
        }
        return $arrayDebts;
    }

    public function getFormatItems($transaction_code, $payment_provider, $params, &$detail) {
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
        $detail = [];
        $items = $this->getFormatItems($transaction_code, $payment_provider, $params, $detail);
        if ($tenantUrl) {
            $validate_name = $profile->name ?? $profile->first_name ?? 'ALEMAN';
            $paramsTransaction = [
                "user_id"        => $profile->id,
                "identifier"     => $transaction_code,
                "email"          => $profile->email,
                "cellphone"      => $profile->cellphone,
                "first_name"     => $profile->name ?? $profile->first_name ?? 'ALEMAN',
                "last_name"      => $profile->last_name ?? 'ALEMAN',
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
        foreach ($providerItems as $payment_provider_item) {
            $json = json_decode($payment_provider_item->metadata, true);
            $this->jsonrpc['params']['args'][] = 'op.request.charge';
            $this->jsonrpc['params']['args'][] = 'service_pay_charges';
            $this->jsonrpc['params']['args'][] = [
                $json['request_id'],
                'bs',
                [
                    [
                        'cargo_id' => $json['cargo_id'],
                        'montoBs'  => $json['MontoBs'],
                        'MontoUs'  => $json['MontoUs'],
                    ]
                ],
                intval($payment_provider_item->debt_code),
                123,
                'razon',
                123,
                false,
                false,
                false,
                false,     // TODO: En el succesfull del custom func, necesitamos guardar el cuf y el url del siat
                false      // TODO: En el succesfull del custom func, necesitamos guardar el cuf y el url del siat
            ];
            $array  = $this->processPost('', $this->jsonrpc);
            $error  = $array['result']['error'] ?? null;
            if( $error ) {
                continue;
            }
            $idsSend[] = $payment_provider_item->id;
        }
        return [ 'status' => true, 'message' => 'Pagos registrados exitósamente.', 'idsSend' => $idsSend];
    }

    public function validateDebts($arrayDebts, $apiKey) {
        $elements = \App\Models\ProviderItem::whereIn('id', $arrayDebts)->orderBy('order', 'ASC')->get();
        $arrayOrder = $elements->pluck('order')->toArray();
        if (empty($arrayOrder)) return ['status' => false, 'message' => 'Debe seleccionar al menos 1 deuda.'];
        if (isConsecutive($arrayOrder) !== false) return ['status' => false, 'message' => 'Debe seleccionar deudas consecutivas.'];
        $firstElement = $elements->first();
        $accountId    = $firstElement->account->id;
        $providerId   = $firstElement->provider->id;
        $verifyPendigs = \App\Models\ProviderItem::where('order', '<', $firstElement->order)
            ->where('account_id', $accountId)
            ->where('provider_id', $providerId)
            ->where('api_key_id', $apiKey->id)
            ->where('status', 'holding')
            ->count();
        if ($verifyPendigs > 0) {
            return ['status' => false, 'message' => 'Debe seleccionar/pagar las deudas anteriores antes de poder seleccionar estas deudas.'];
        }
        return ['status' => true, 'message' => 'Datos validados con éxito'];
    }

    public function enableMultipleSteps() {
        return false;
    }

    public function finishedForm( $request ) {
        return true;
    }
}
