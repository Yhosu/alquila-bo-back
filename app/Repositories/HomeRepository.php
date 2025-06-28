<?php

namespace App\Repositories;

use App\Exceptions\BadRequestException;
use App\Traits\CRUDOperations;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\Interfaces\HomeInterface;
use App\Services\ApiResponseService;
use Throwable;
use Illuminate\Http\Request;
use App\Helpers\Func;
use App\Enums\SubscriptionStatus;
use App\Exceptions\CustomException;

class HomeRepository implements HomeInterface
{
    protected $modelSubscriber  = \App\Models\Subscriber::class;

    public function getHome() {
        try {
            $now = date('ymd') . '-gethome';
            $result = [];
            $information = \Cache::store('database')->remember($now, 43200, function() use( &$result ) {
                $result['banners']         = \App\Models\Gallery::where('type', 'banner')->limit(4)->get();
                $result['top_products']    = \App\Models\Product::where('enabled', 1)->where('top', 1)->orderBy('order', 'ASC')->get();
                $result['categories']      = \App\Models\Category::where('enabled')->withWhereHas('products', function($q) { $q->with(['product_characteristics', 'product_filters'])->whereHas('company', function($qq){ $qq->where('enabled', 1); }); })->get();
                $result['characteristics'] = \App\Models\Characteristic::where('enabled', 1)->get();
                $result['reviews']         = \App\Models\Review::where('enabled', 1)->get();
                    /** TODO: FALTA AGREGAR INFORMACIÓN DE ALQUILA BO */
			    return $result;
            });
            return $information;
        } catch( Throwable $th) {
            throw $th;
        }
    }

    public function getFaqs() {
        try {
            $now = date('ymd') . '-faqs';
            $result = [];
            $information = \Cache::store('database')->remember($now, 43200, function() use( &$result ) {
                $faqs = \App\Models\Faq::where('enabled', 1)->get();
			    return $faqs;
            });
                /** Enviar */
            // \Func::sendEmail( $email, $title, $content );
            return $information;
        } catch( Throwable $th) {
            throw $th;
        }
    }

    public function registerSubscription(string $email) {
        try {
            /*$existSubscriber = $this->modelSubscriber::where('email', $email)->first();
            if( $existSubscriber ) throw new CustomException("Ocurrio un .", ['El usuario ya fue registrado antes previamente, intente con otro correo']);*/

            $subscription = $this->modelSubscriber::create([
                'email'     => $email,
                'subscription_status'  => SubscriptionStatus::PENDIENTE,
                'confirmation_token' => \Str::uuid(),
            ]);
            return $subscription;
        } catch( Throwable $th) {
            throw $th;
        }
    }
}
