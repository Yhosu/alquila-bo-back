<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Closure;
use App\Http\Requests\Account\CreateAccountRequest;
use App\Http\Requests\Account\CreateProfileRequest;
use App\Http\Requests\Payment\PayWithCardRequest;
use App\Services\ApiResponseService;
use Barryvdh\DomPDF\Facade\Pdf;

class ProviderController extends Controller
{
    public function __construct() {}

    public function getVariables($provider)
    {
        return [
            'title'    => $provider->name,
            'subtitle' => $provider->name,
            'summary'  => $provider->description,
        ];
    }

    public function getAuthorizedProviders()
    {
        $apiKey = request()->apiKey;
        return ApiResponseService::success('Perfil obtenido con éxito', $apiKey->providers);
    }

    public function createProfile(CreateProfileRequest $request)
    {
        $apiKey = auth()->user();
        $request->merge(['api_key_id' => $apiKey->id]);
        $profile = \App\Models\Profile::where('user_id', $request->user_id)
            ->where('customer_id', $request->customer_id)
            ->where('tenant_id', $request->tenant_id)
            ->where('email', $request->email)
            ->where('api_key_id', $apiKey->id)
            ->first();
        if (!$profile) $profile = \App\Models\Profile::create($request->all());
        return ApiResponseService::success('Perfil obtenido con éxito', $profile);
    }

    public function showProfile($profileId)
    {
        $apiKey  = auth()->user();
        $profile = \App\Models\Profile::where('id', $profileId)
            ->where('api_key_id', $apiKey->id)
            ->first();
        return ApiResponseService::success('Perfil obtenido con éxito', $profile);
    }

    public function createAccountProviderIdByProfile($providerId, $profileId, Request $request)
    {
        $apiKey   = auth()->user();
        $provider = \App\Models\Provider::where('id', $providerId)->firstOrFail();
        $profile  = \Func::getProfile($request->tenantUrl, $profileId);
        $rules     = app(sprintf('App\Http\Controllers\Providers\%s', $provider->class))->rules();
        $validator = \Validator::make($request->all(), $rules);
        $params    = [];
        if ($validator->fails()) return ApiResponseService::error('Error en el envío de parámetros', $validator->errors()->all(), [], [], 401);
        $strQueryArray = [];
        foreach ($rules as $key => $value) $strQueryArray[] = 'JSON_EXTRACT(metadata, \'$.' . $key . '\') = "' . $request->$key . '"';
        $account = \App\Models\Account::where(function ($q) use ($profile) {
            $q->where('profile_id', $profile->id)->orWhere('external_profile_id', $profile->id);
        })
            ->where('provider_id', $providerId)
            ->where(function ($q) use ($strQueryArray) {
                $q->whereRaw(implode(' and ', $strQueryArray));
            })
            ->where('active', 1)
            ->first();
        if (!$account) {
            $enableMultipleSteps = app(sprintf('App\Http\Controllers\Providers\%s', $provider->class))->enableMultipleSteps();
            if( $enableMultipleSteps ) {
                $finishedForm = app(sprintf('App\Http\Controllers\Providers\%s', $provider->class))->finishedForm( $request ); 
                if( !$finishedForm ) {
                    $stepParams = app(sprintf(  'App\Http\Controllers\Providers\%s', $provider->class))->getParametersStep2( $request, $provider, $apiKey);
                    return ApiResponseService::success('Campos obtenidos exitosamente.', $stepParams, $this->getVariables($provider), [], Response::HTTP_OK, true);
                }
            }
            \Func::createParamsToInsertAccount($provider, $profile, $request->all(), $params);
            $params['external_profile_id'] = !$request->tenantUrl ? null : $profile->id;
            $params['tenant_url'] = $request->tenantUrl;
            $params['tenant_id'] = $request->tenantId;
            if ($request->tenantUrl) unset($params['profile_id']);
            $account = \App\Models\Account::create($params);
            if (!$this->verifyExistAccountDebts($provider, $account->id)) {
                $account->delete();
                return ApiResponseService::error(message: 'Hubo un error al realizar su solicitud.', errors: ['No se encontro ninguna deuda asociada con los datos enviados.'], code: 401);
            }
        };
        return ApiResponseService::success('Cuenta creada con éxito', $account);
    }

    public function getProviderFields($providerId)
    {
        $provider  = \App\Models\Provider::where('id', $providerId)->firstOrFail();
        $params    = app(sprintf('App\Http\Controllers\Providers\%s', $provider->class))->getParameters();
        $variables = $this->getVariables($provider);
        return ApiResponseService::success('Campos obtenidos exitosamente.', $params, $variables);
    }

    public function getProfileAccounts($profileId)
    {
        $showProfile = \Func::getProfile(request()->tenantUrl, $profileId);
        $manyAccounts = \App\Models\Account::where('active', 1)->where(function ($q) use ($showProfile) {
            $q->where('profile_id', $showProfile->id)->orWhere('external_profile_id', $showProfile->id);
        })->get();
        $showProfile->accounts = $manyAccounts;
        return ApiResponseService::success('Perfil obtenido exitósamente.', $showProfile);
    }

    public function verifyExistAccountDebts($provider, $accountId)
    {
        $apiKey    = auth()->user();
        $account   = \App\Models\Account::where('id', $accountId)->where('active', 1)->first();
        if (!$account) return ApiResponseService::error(message: 'Hubo un error al realizar su solicitud.', errors: ['No se encontro ninguna cuenta asociada con los datos enviados.'], code: 401);
        $params = json_decode($account->metadata, true);
        $urlEnv = 'url_' . $apiKey->environment;
        return app(sprintf('App\Http\Controllers\Providers\%s', $provider->class))->verifyDebts(
            $apiKey,
            $account,
            $provider,
            $params,
            $provider->$urlEnv
        );
    }

    public function getAccountDebts($providerId, $accountId = null)
    {
        $apiKey    = auth()->user();
        $provider  = \App\Models\Provider::findOrFail($providerId);
        $account   = \App\Models\Account::where('id', $accountId)->where('active', 1)->first();
        if (!$account) return ApiResponseService::error(message: 'Hubo un error al realizar su solicitud.', errors: ['No se encontro ninguna cuenta asociada con los datos enviados.'], code: 401);
        $params    = json_decode($account->metadata, true);
        $urlEnv    = 'url_' . $apiKey->environment;
        $variables = $this->getVariables($provider);
        $result    = \Cache::store('database')->remember($account->internal_code, 60 * 60, function () use ($apiKey, $account, $provider, $params, $urlEnv) {
            return app(sprintf('App\Http\Controllers\Providers\%s', $provider->class))->getDebts(
                $apiKey,
                $account,
                $provider,
                $params,
                $provider->$urlEnv
            );
        });
        return ApiResponseService::success('Deudas obtenidas exitósamente.', $result, $variables);
    }

    public function createTransaction($providerId, $profileId, Request $request)
    {
        $apiKey   = auth()->user();
        $provider = \App\Models\Provider::findOrFail($providerId);
        $vlDebts  = app(sprintf('App\Http\Controllers\Providers\%s', $provider->class))->validateDebts($request->array_debts, $apiKey);
        if (!$vlDebts['status']) return $vlDebts;
        $profile       = \Func::getProfile($request->tenantUrl, $profileId);
        $generateCode  = \Func::generateCode('\App\Models\ProviderItem', 'transaction_code', 22);
        $providerItems = \App\Models\ProviderItem::whereIn('id', $request->array_debts)->where('status', 'holding')->get();
        $updateItems   = \App\Models\ProviderItem::whereIn('id', $request->array_debts)->update(['transaction_code' => $generateCode]);
        $paymentCodes  = $providerItems->pluck('code')->toArray();
        $amount        = $providerItems->sum('amount');
        $invoiceParams['invoice_name']       = $profile->name ?? ($profile->first_name ?? $profile->last_name);
        $invoiceParams['invoice_doc_type']   = 'CI';
        $invoiceParams['invoice_doc_number'] = $profile->ci_number ?? $profile->country_identity;
        $invoiceParams['invoice_email']      = $profile->email;
        $urlEnv = 'url_' . $apiKey->environment;
        $result = app(sprintf('App\Http\Controllers\Providers\%s', $provider->class))->generateTransactionLink(
            $providerItems,
            $request->all(), //[]
            $provider,
            $amount,
            $generateCode,
            $invoiceParams,
            $provider->$urlEnv,
            $profile,
            $profile->tenant_url ?? $request->tenantUrl,
            $request->callbackUrl,
            $request->from_chatbot ?? false
        );
        $status = $result['status'] ?? false;
        if (!$status) return ApiResponseService::error(message: 'Hubo un error al generar su transacción', errors: $result['errors'] ?? [], code: 401);
        $result['transaction_code'] = $generateCode;
        unset($result['status']);
        return ApiResponseService::success('Transacción generada con éxito.', $result);
    }

    public function getCategories()
    {
        $providerIds = request()->providerIds;
        $categories = \App\Models\Category::withWhereHas('providers', function ($q) use ($providerIds) {
            $q->whereIn('provider_id', $providerIds);
        })->get();
        return ApiResponseService::success('Transacción generada con éxito.', $categories);
    }

    public function getAllProviders()
    {
        return ApiResponseService::success('Transacción generada con éxito.', request()->apiKey->providers);
    }

    public function deleteAccount($accountId)
    {
        $apiKey = request()->apiKey;
        $account = \App\Models\Account::where('id', $accountId)
            // ->whereHas( 'profile', function($q) use( $apiKey ) { $q->where( 'api_key_id', $apiKey->id ); })
            ->where('active', 1)
            ->first();
        if (!$account) return ApiResponseService::error(message: 'Hubo un error al realizar su solicitud.', errors: ['No se encontro ninguna cuenta asociada con los datos enviados.'], code: 401);
        $account->active = 0;
        $account->save();
        return ApiResponseService::success('Cuenta eliminada con éxito.', []);
    }

    public function updatePaymentConfirmed($code)
    {
        \Log::info('Callback recibido... :' . $code);
        $request = request();
        $transaction = $request->data['transaction'];
        $invoices    = $request->data['invoices'] ?? [];
        \App\Models\ProviderItem::where('external_transaction_code', $transaction['external_transaction_code'])
            ->where('transaction_code', $code)
            ->update(['status' => 'paid']);
        $providerItems = \App\Models\ProviderItem::where('external_transaction_code', $transaction['external_transaction_code'])
            ->where('transaction_code', $code)
            ->where('status', 'paid')
            ->orderBy('code', 'ASC')
            ->get();
        $firstProviderItem = $providerItems->first();
        $account = $firstProviderItem->account ?? null;
        if ($account) \Cache::store('database')->forget($account->internal_code);
        $provider = \App\Models\Provider::whereHas('provider_items', function ($q) use ($transaction) {
            $q->where('external_transaction_code', $transaction['external_transaction_code'])->where('status', 'paid');
        })->first();
        $callbackUrl = $firstProviderItem->callback_url ?? null;
        /* Url del recibo */
        $extraData = ['receiptUrl' => $invoices[0]['url_sin_sfe'] ?? url(sprintf('receipt/%s/%s', $provider->code, $transaction['external_transaction_code']))];
        if ($callbackUrl) \Func::registerCallback($callbackUrl, (object)$transaction, $invoices, 'POST', $extraData);
        $apiKey = $request->apiKey;
        $urlEnv = 'url_' . $apiKey->environment;
        $result = app(sprintf('App\Http\Controllers\Providers\%s', $provider->class))->updatePayments(
            $providerItems,
            $providerItems->sum('amount'),
            $transaction['external_transaction_code'],
            $transaction['invoice_nit'],
            $transaction['invoice_name'],
            $provider->$urlEnv
        );
        \App\Models\ProviderItem::whereIn('id', $result['idsSend'])
            ->update(['status' => 'sent']);
        return ApiResponseService::success('Callback actualizado exitósamente.', $result);
    }

    public function payWithCard(PayWithCardRequest $request)
    {
        $request  = request();
        $profile  = \Func::getProfile($request->tenantUrl, $request->profileId);
        $apiKey   = $request->apiKey;
        $urlEnv   = 'url_' . $apiKey->environment;
        $provider = \App\Models\Provider::whereHas('provider_items', function ($q) use ($request) {
            $q->where('external_transaction_code', $request->externalTransactionCode);
        })->first();
        $result = app(sprintf('App\Http\Controllers\Providers\%s', $provider->class))->payWithCard(
            $request->transactionId,
            $request->cardCvv,
            $request->cardId,
            $request->tenantUrl
        );
        return $result;
    }

    public function showReceiptByExternalTransactionCode($code, $externalTransactionCode)
    {
        $providerItems = \App\Models\ProviderItem::where('external_transaction_code', $externalTransactionCode)->get();
        if (count($providerItems) == 0) return '"Recibo no encontrado."';
        $firstProviderItem = $providerItems->first();
        $provider  = $firstProviderItem->provider;
        $account   = $firstProviderItem->account;
        $metadata  = json_decode($account->metadata, true);
        $tenantUrl = $metadata['tenantUrl'];
        if ($code != $provider->code) return '.';
        $profile   = \Func::getProfile($tenantUrl, $account->external_profile_id);
        $pdf       = PDF::loadHTML(view('exports.voucher', compact('providerItems', 'provider', 'account', 'profile', 'firstProviderItem'))->render());
        $pdf       = $pdf->setPaper('Letter');
        return $pdf->download(sprintf('RECIBO-%s.pdf', $externalTransactionCode));
    }

    public function testSuccess(Request $request, $code)
    {
        $provider = \App\Models\Provider::where('code', $request->tenant)->first();

        $providerItems =  \App\Models\ProviderItem::where('transaction_code', $code)->orderBy('code', 'ASC')->get(); //->where('status', 'paid');
        //$provider->provider_items;

        $result = app(sprintf('App\Http\Controllers\Providers\%s', $provider->class))->updatePayments(
            $providerItems,
            $providerItems->sum('amount'),
            $request->external_transaction_code,
            $request->invoice_nit,
            $request->invoice_name,
            $provider->url_testing
        );

        return $result;
    }
}
