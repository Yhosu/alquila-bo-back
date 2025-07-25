<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Closure;
use App\Services\ApiResponseService;
use Throwable;
use App\Traits\RegisterLogs;
use App\Repositories\Interfaces\NodeInterface;
use App\Repositories\Interfaces\HomeInterface;
use App\Repositories\Interfaces\AboutusInterface;
use App\Http\Requests\SubscriptionRequest;
use App\Http\Requests\ConfirmSubscriptionRequest;
use App\Http\Requests\CancelSubscriptionRequest;
use App\Http\Requests\ReservationFormRequest;
use App\Http\Requests\CommentRequest;
class AppController extends Controller {

    use RegisterLogs;
    public function __construct(
        private readonly NodeInterface $nodeInterfaceRepository,
        private readonly HomeInterface $homeInterfaceRepository,
        private readonly AboutusInterface $aboutusInterfaceRepository,
    ){
    }

    public function getNode( $node, $paginate = 0 ) {
        $request = request();
        try {
            $result = $this->nodeInterfaceRepository->getListOfNode(
                $node,
                $paginate,
                $request->all()
            );
            return ApiResponseService::success('Items del Nodo obtenido con éxito', $result);
        } catch( Throwable $e ) {
            return $this->execLog($e);
        }
    }

    public function getHomeInfo() {
        try {
            $result = $this->homeInterfaceRepository->getHome();
            return ApiResponseService::success('Información obenida con éxito.', $result);
        } catch( Throwable $e ) {
            return $this->execLog($e);
        }
    }

    public function getFaqs() {
        try {
            $result = $this->homeInterfaceRepository->getFaqs();
            return ApiResponseService::success('Faqs obtenidos con éxito.', $result);
        } catch( Throwable $e ) {
            return $this->execLog($e);
        }
    }
    // public function getInformation() {
    //     try {
    //         $result = $this->homeInterfaceRepository->getInformation();
    //         return ApiResponseService::success('Agregar información obtenidos con éxito.', $result);
    //     } catch( Throwable $e ) {
    //         return $this->execLog($e);
    //     }
    // }
    public function getAboutus() {
        try {
            $result = $this->aboutusInterfaceRepository->getAboutus();
            return ApiResponseService::success('Acerca de nosotros obtenidos con éxito.', $result);
        } catch( Throwable $e ) {
            return $this->execLog($e);
        }
    }

    public function getProduct( $id ) {
        try {
            $product = $this->homeInterfaceRepository->getProduct(
                $id
            );
            return ApiResponseService::success('Producto obtenido con éxito.', $product);
        } catch( Throwable $e ) {
            return $this->execLog($e);
        }
    }

    public function registerSubscription( SubscriptionRequest $request ) {
        try {
            $result = $this->homeInterfaceRepository->registerSubscription(
                $request->email,
                $request->name
            );
            return ApiResponseService::success('Registro de suscripción correcta.', $result);
        } catch( Throwable $e ) {
            return $this->execLog($e);
        }
    }

    public function getSendForm( ReservationFormRequest $request ) {
        try {
            $user = auth()->user();
            $result = $this->homeInterfaceRepository->registerForm(
                $user->id,
                $request->product_id,
                $request->init_date,
                $request->finish_date,
                $request->filters
            );
            return ApiResponseService::success('Registro de formulario exitoso.', $result);
        } catch( Throwable $e ) {
            return $this->execLog($e);
        }
    }


    public function confirmSubscription( ConfirmSubscriptionRequest $request ) {
        try {
            $result = $this->homeInterfaceRepository->confirmSubscription(
                $request->tokenConfirmSubscription
            );
            return ApiResponseService::success('Confirmación de suscripción correcta.', $result);
        } catch( Throwable $e ) {
            return $this->execLog($e);
        }
    }

    public function cancelSubscription( CancelSubscriptionRequest $request ) {
        try {
            $result = $this->homeInterfaceRepository->cancelSubscription(
                $request->tokenCancelSubscription
            );
            return ApiResponseService::success('Cancelacion de suscripción correcta.', $result);
        } catch( Throwable $e ) {
            return $this->execLog($e);
        }
    }
    public function getCompaniesMap() {
        try {
            $result = $this->homeInterfaceRepository->getCompaniesMap();
            return ApiResponseService::success('Información de las empresas para el mapa obtenidas con éxito.', $result);
        } catch( Throwable $e ) {
            return $this->execLog($e);
        }
    }


    public function registerComment( CommentRequest $request ) {
        try {
            $user = auth()->user();
            $result = $this->homeInterfaceRepository->registerComment(
                $user->id,
                $request->product_id,
                $request->text
            );
            return ApiResponseService::success('Registro de comentario exitoso.', $result);
        } catch( Throwable $e ) {
            return $this->execLog($e);
        }
    }


}
