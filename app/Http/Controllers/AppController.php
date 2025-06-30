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
    public function getInformation() {
        try {
            $result = $this->homeInterfaceRepository->getInformation();
            return ApiResponseService::success('Agregar información obtenidos con éxito.', $result);
        } catch( Throwable $e ) {
            return $this->execLog($e);
        }
    }
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
}
