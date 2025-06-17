<?php

namespace App\Helpers;

use Form;
use Barryvdh\Snappy\Facades\SnappyImage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use App\Events\PusherBroadcast;

class Func {

    public static function generateCode( $model, $field, $digits = 6, $type='number') {
        $code = \Func::generateRawCode($digits, $type);
        $check_unique = $model::where($field, $code)->first();
        if($check_unique){
            $code = \Func::generateCode($model, $field, $digits, $type);
        }
        return $code;
    }

    public static function generateRawCode($digits, $type) {
        $digits = $digits -1;
        $chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
        srand((double)microtime()*1000000);
        $i = 0;
        $pass = '';
        if( $type == 'number' ) {
          while ($i <= $digits) {
            $num = rand(0,9);
            $pass = $pass . $num;
            $i++;
          }
        } elseif( $type == 'alphanumeric' ) {
          while ($i <= $digits) {
            $num = rand(1,31);
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
          }
        }
        return $pass;
    }

    public static function createParamsToInsertAccount(
      $provider,
      $profile,
      $metadata,
      &$params
    ) {
      unset($metadata['providerIds']);
      unset($metadata['apiKey']);
      $params = [
        'profile_id'    => $profile->id,
        'provider_id'   => $provider->id,
        'internal_code' => \Func::generateCode( 'App\Models\Account', 'internal_code', 10, 'alphanumeric'),
        'metadata'      => json_encode( $metadata, true ),
        'name'          => ( $profile->name ?? ( $profile->first_name . ' ' . $profile->last_name ) ),
        'currency'      => 'BOB'
      ];
    }

    public static function getElemsOfArrayCtlp( $array, $key, $value ) {
      $debts = array_values( array_filter( $array, function ($elem) use ( $key, $value ) { return in_array( $elem[$key], $value ); }) );
      if( empty( $debts ) ) return [];
      usort($debts, function($a, $b) {
        return strtotime($a['date_due']) - strtotime($b['date_due']);
      });
      return $debts;
    }

    public static function getItems( $transaction_code, $payment_provider, $params, &$detail ) {
      $items = \App\Models\ProviderItem::where('provider_id', $payment_provider->id)
            ->where('transaction_code', $transaction_code)
            ->where('status', 'holding')
            ->get();
      $elems = [];
      foreach( $items as $item ) {
        $json = json_decode( $item->metadata, true );
        $elems[] = [ 'concept' => $item->name, 'quantity' => 1, 'unit_price' => $item->amount, 'invoice' => 0, 'product_code' => $json['group'] ?? null ];
        $detail[] = $item->name;
      }
      return $elems;
    }

    public static function getProfile( $tenantUrl, $profileId ) {
      $profile  = $tenantUrl
          ? app('App\Http\Controllers\OrganizationTenantController')->getProfile( $tenantUrl, $profileId )
          : \App\Models\Profile::where('id', $profileId)->first();
      return $profile;
    }

    public static function getMetadataAccount( $metadata, $code ) {
      $metadata = json_decode( $metadata, true );
      unset($metadata['tenantId'], $metadata['tenantUrl']);
      foreach( $metadata as $key => $elem ) {
        $arrayItems[] = [ 'label' => __("$code.$key" ) , 'value' => $elem];
      }
      return $arrayItems;
    }

    public static function getConcatParamsDebt( $params ) {
      unset($params['apiKey']);
      $str = [];
      foreach( $params as $key => $value ) $str[] = $value;
      return preg_replace('/\s+/', '', implode('-', $str));
    }

    public static function registerCallback( $url, $transaction, $invoices, $method = 'POST', $extraData = [] ) {
      $data['status']                 = true;
      $data['message']                = 'Enviando callback.';
      $data['data']['transaction_id'] = $transaction->id;
      $data['data']['transaction']    = $transaction;
      $data['data']['extra']          = $invoices[0] ?? $extraData;
      $nw_callback = \App\Models\Callback::where('transaction_id', $transaction->id)->first();
      if( $nw_callback && $nw_callback->status == 'success' ) return $nw_callback;
      if( !$nw_callback ) {
          $nw_callback = new \App\Models\Callback;
          $nw_callback->transaction_id = $transaction->id;
          $nw_callback->url            = $url;
          $nw_callback->method         = $method;
          $nw_callback->body           = json_encode( $data );
          $nw_callback->save();
      }
      try {
          $exec_callback = \Http::withHeaders([
              'Authorization' => 'Bearer ' . $transaction->bearer_token
          ])->$method( $url, $data );
          $response = '';
          if( $exec_callback->failed() ){
              \Log::info( 'error callback :' . $nw_callback->id );
              $response = '{ "status" : false, "message" : "ERROR 400/500"}';
          } elseif( $exec_callback->serverError() ) {
              $response = '{ "status" : false, "message" : "ERROR 500"}';
          } elseif( $exec_callback->clientError() ) {
              $response = '{ "status" : false, "message" : "ERROR CLIENTE"}';
          }
          $exist_response = $exec_callback->json() ?? null;
          $nw_callback->response = $exist_response ? json_encode( $exist_response ) : $response;
          $status = $exec_callback->json()['status'] ?? false;
          $nw_callback->status   = $status ? 'success' : 'error';
      } catch(Exception $e) {
          $nw_callback->status   = 'error';
          $nw_callback->response = $e->getMessage();
      } catch(\Illuminate\Http\Client\ConnectionException $e) {
          $nw_callback->status   = 'error';
          $nw_callback->response = $e->getMessage();
      }
      $nw_callback->save();
      return $nw_callback;
    }
}
