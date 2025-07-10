<?php

namespace App\Repositories;

use App\Exceptions\BadRequestException;
use App\Exceptions\CustomException;
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
use App\Services\EmailService;
use App\DTOs\CompanyMapDTO;
use App\DTOs\SubscriberDTO;

class HomeRepository implements HomeInterface
{
    protected $modelSubscriber  = \App\Models\Subscriber::class;
    protected $modelNotificationTemplate  = \App\Models\NotificationTemplate::class;
    protected $modelPararameter  = \App\Models\Parameter::class;
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function getHome() {
        try {
            $now = date('ymd') . '-gethome';
            $result = [];
            $information = \Cache::store('database')->remember($now, 43200, function() use( &$result ) {
                $result['banners']    = \App\Models\Banner::where('enabled', 1)->orderBy('order', 'ASC')->get();
                $result['top_products']    = \App\Models\Product::where('enabled', 1)->where('top', 1)->orderBy('order', 'ASC')->get();
                $result['products']        = \App\Models\Product::where('enabled', 1)->get();
                $result['characteristics'] = \App\Models\Characteristic::where('enabled', 1)->get();
                $result['reviews']         = \App\Models\Review::where('enabled', 1)->get();
                $result['information']     = \App\Models\Information::where('enabled',1)->get();
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

    public function registerSubscription(string $email, ?string $name = null) {
        try {
            $now = date('ymd');
            $parametersCache = \Cache::store('database')->remember($now. '-getParameters', 43200, function() use( &$parameters ) {
                $parameters = $this->modelPararameter::where('domain', 'SUSCRIPCION')
                                                     ->where('status', 'H')
                                                     ->where('enabled', 1)->get();
		        return $parameters;
            });

            $urlConfirmationSuscription = $parametersCache
                                        ->where('subdomain', 'URL_SUSCRIPCION')
                                        ->pluck('value')
                                        ->first();
            $daysExpirationToken =  $parametersCache
                                        ->where('subdomain', 'DIAS_DURACION_TOKEN')
                                        ->pluck('value')
                                        ->first() ?? 7;

            $existSubscriber = $this->modelSubscriber::where('email', $email)->where('enabled', 1)->where('confirmation_email_sent', 1)
                ->where(function ($query) use ($daysExpirationToken){
                    $query->where('subscription_status',SubscriptionStatus::CONFIRMADO->value)
                          ->orWhere(function ($q) use ($daysExpirationToken) {
                                $q->where('subscription_status',SubscriptionStatus::PENDIENTE->value)
                                  ->whereRaw('DATEDIFF(CURDATE(), DATE(date_of_creation)) <= ?', [$daysExpirationToken]);
                    });
            })->first();

            if( $existSubscriber ) {
                $subscription =  $existSubscriber;
            }else{

                $existSubscriber = $this->modelSubscriber::where('email', $email)
                                                        ->where('enabled', 1)
                                                        ->where('confirmation_email_sent', 1)
                                                        ->where('subscription_status', SubscriptionStatus::PENDIENTE->value)
                                                        ->where(function ($query) use ($daysExpirationToken){
                                                            $query->whereRaw('DATEDIFF(CURDATE(), DATE(date_of_creation)) > ?', [$daysExpirationToken]);
                                                         })->first();
                if($existSubscriber){
                    $existSubscriber->subscription_status = SubscriptionStatus::CANCELADO->value;
                    $existSubscriber->enabled = 0;
                    $existSubscriber->save();
                }

                $confirmationToken = \Str::upper(\Str::uuid());

                $notificationTemplateCache = \Cache::store('database')->remember($now . '-getNotificationTemplate', 43200, function() use( &$notificationTemplate ) {
                    $notificationTemplate = $this->modelNotificationTemplate::where('cod_notification', 'WELCOME_EMAIL')->where('enabled', 1)->first();
			        return $notificationTemplate;
                });
                $notificationTemplateCache->template = str_replace(array('{{name}}','{{year}}','{{confirmation_link}}'),
                                                                   array($name ?? 'querido usuario',date('Y'),$urlConfirmationSuscription . sprintf("?token=%s", $confirmationToken)),$notificationTemplateCache->template);

                $resultSendEmail = $this->emailService->sendEmail(
                    $email,
                    $notificationTemplateCache->subject,
                    $notificationTemplateCache->template
                );

                $subscription = $this->modelSubscriber::create([
                    'email'                     => $email,
                    'name'                      => $name,
                    'subscription_status'       => SubscriptionStatus::PENDIENTE->value,
                    'confirmation_token'        => $confirmationToken,
                    'cancelation_token'         => \Str::upper(\Str::uuid()),
                    'confirmation_email_sent'   => $resultSendEmail
                ]);

            }
            return SubscriberDTO::fromModel($subscription);
        } catch( Throwable $th) {
            throw $th;
        }
    }

    public function confirmSubscription(string $tokenConfirmSubscription){
        try {
            $now = date('ymd');
            $daysExpirationTokenCache = \Cache::store('database')->remember($now. '-getParameterDay', 43200, function() use( &$daysExpirationToken ) {
                $daysExpirationToken = $this->modelPararameter::where('domain', 'SUSCRIPCION')
                                                     ->where('subdomain', 'DIAS_DURACION_TOKEN')
                                                     ->where('status', 'H')
                                                     ->where('enabled', 1)->get();
		        return $daysExpirationToken;
            });

            $subscriber = $this->modelSubscriber::where('confirmation_token', $tokenConfirmSubscription)
                                                         ->where('subscription_status', SubscriptionStatus::PENDIENTE->value)
                                                         ->where('enabled', 1)
                                                         ->where('confirmation_email_sent', 1)
                                                         ->where(function ($query) use ($daysExpirationTokenCache){
                                                            $query->whereRaw('DATEDIFF(CURDATE(), DATE(date_of_creation)) <= ?', [$daysExpirationTokenCache]);
                                                         })->first();
            if( $subscriber ) {
                $subscriber->subscription_status = SubscriptionStatus::CONFIRMADO->value;
                $subscriber->confirmation_date = now();
                $subscriber->save();

            }else{
                throw new BadRequestException("Hubo un error al confirmar la suscripción", ['Token invalido o expirado.']);
            }
            return $subscriber->only(['email', 'subscription_status']);

        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function cancelSubscription(string $tokenCancelSubscription){
        try {

            $subscriber = $this->modelSubscriber::where('cancelation_token', $tokenCancelSubscription)
                                                         ->where('subscription_status', SubscriptionStatus::CONFIRMADO->value)
                                                         ->where('enabled', 1)
                                                         ->where('confirmation_email_sent', 1)
                                                         ->first();
            if( $subscriber ) {
                $subscriber->subscription_status = SubscriptionStatus::CANCELADO->value;
                $subscriber->cancelation_date = now();
                $subscriber->save();

            }else{
                throw new BadRequestException("Hubo un error al cancelar la suscripción", ['Token invalido o expirado.']);
            }
            return $subscriber->only(['email', 'subscription_status']);

        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function getInformation() {
        try {
            $now = date('ymd') . '-information';
            $result = [];
            $information = \Cache::store('database')->remember($now, 43200, function() use( &$result ) {
                $info = \App\Models\Information::where('enabled', 1)->get();
			    return $info;
            });
                /** Enviar */
            // \Func::sendEmail( $email, $title, $content );
            return $information;
        } catch( Throwable $th) {
            throw $th;
        }
    }

    public function getProduct( string $id ) {
        $product = \App\Models\Product::find( $id );
        if( $product == null ) throw new BadRequestException("Hubo un error al buscar su producto", ['No se encuentra el producto asociado al id ingresado.']);
        return $product;
    }

    public function registerForm( $userId, $productId, $initDate, $finishDate, $filters = '' ) {
        $product = \App\Models\Product::find( $productId );
        if( !$product ) throw new BadRequestException("Hubo un error al buscar su producto", ['No se encuentra el producto asociado al id ingresado.']);
        $nwForm = new \App\Models\ReservationForm;
        $nwForm->user_id     = $userId;
        $nwForm->product_id  = $productId;
        $nwForm->init_date   = $initDate;
        $nwForm->finish_date = $finishDate;
        $nwForm->filters     = $filters;
        $nwForm->save();
            /** TODO: Enviar correo electrónico */
        return $nwForm;
    }

    public function getCompaniesMap(){
        try {
            $now = date('ymd') . '-getCompaniesMap';
            return \Cache::store('database')->remember($now, 43200, function () {
                $companies = \App\Models\Company::where('enabled', 1)->get();
                return CompanyMapDTO::fromCollection($companies);
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
