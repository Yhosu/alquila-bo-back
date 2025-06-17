<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;

class ViasBoliviaService extends Controller
{

    protected $url;

    public function __construct() {}


    public function processPost($url, $action, $parameters, $headers = [])
    {
        return \External::post($url, $action, $parameters, $headers);
    }

    public function processGet($action, $parameters, $headers = [])
    {
        return \External::get($url, $action, $parameters, $headers);
    }

    public function rules()
    {
        return [
            'total_price' => 'required'
        ];
    }

    public function getParameters()
    {
        return [
            [
                'id' => "total_price",
                'label' => "Monto total",
                'type' => "text",
                'required' => true,
                'initial_value' => NULL,
            ]
        ];
    }

     public function validateDebts($arrayDebts, $apiKey)
    {
        return ['status' => true, 'message' => 'Datos validados con éxito'];
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
        $item                   = new \App\Models\ProviderItem;
        $item->account_id       = $account->id ?? null;
        $item->api_key_id       = $apiKey->id;
        $item->provider_id      = $provider->id;
        $item->environment      = $apiKey->environment;
        $item->status           = 'holding';
        $item->amount           = floatval($provider_fields['total_price']);
        $item->name             = sprintf('%s', 'Recarga usuario');
        $item->date             = date('Y-m-d');
        $item->code             = time().'';
        $item->metadata         = null;
        $item->order            = 0;
        $item->save();

        $arrayDebts['deudas'][] = [
            'id'             => $item->id,
            'amount'         => currencyFormat($item->amount),
            'originalAmount' => $item->amount,
            'name'           => $item->name,
            'date'           => date('Y-m-d'),
            'expDate'        => null,
            'currency'       => 'BOB',
            'debtref'        => null
        ];
        return $arrayDebts;
    }

    public function getFormatItems($transaction_code, $payment_provider, $params, &$detail)
    {
        $items = \App\Models\ProviderItem::where('provider_id', $payment_provider->id)
            ->where('transaction_code', $transaction_code)
            ->where('status', 'holding')
            ->get();
        $elems = [];
        foreach ($items as $item) {
            $json = json_decode($item->metadata, true);
            $elems[] = ['concept' => $item->name, 'quantity' => 1, 'unit_price' => $item->amount, 'invoice' => 0];
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
        $items = $this->getFormatItems($transaction_code, $payment_provider, $params, $detail);
        $strDetail = implode(' | ', $detail);
        $appkeyLibelulaDefault = config('services.libelula.appkey');
        if ($tenantUrl) {
            $validate_name=$partnerName ?? ($profile->name ?? ($profile->first_name  . ' ' . $profile->last_name) ?? 'VIAS') . ' - ' . $partnerCode;
            $paramsTransaction = [
                "user_id"        => $profile->id,
                "identifier"     => $transaction_code,
                "email"          => $profile->email,
                "cellphone"      => $profile->cellphone,
                "first_name"     => $validate_name,
                "last_name"      => 'BOLIVIA',
                "ci_number"      => $from_chatbot ? $partnerNit : ($profile->ci_number ?? $profile->country_identity),
                "nit_number"     => $from_chatbot ? $partnerNit : ($profile->nit ?? $profile->invoice_number),
                "nit_name"       => $profile->name ?? $profile->invoice_name,
                "detail"         => sprintf('Pago realizado por : ' . $validate_name),
                "document_type"  => null,
                "callback_url"   => url('api/v1/bridge-pago-confirmado/' . $transaction_code),
                "redirect_front" => "",
                "redirect_app"   => true,
                "payment_method" => "payment-link",
                "items"          => $items,
                /* "metadata_lines" => [
                    ["key" => "SOCIO", "value" => $partnerCode],
                ] */
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
    ) {}

    public function enableMultipleSteps() {
        return false;
    }

    public function finishedForm( $request ) {
        return true;
    }
}
