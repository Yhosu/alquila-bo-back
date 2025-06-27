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

class AppController extends Controller {

    use RegisterLogs;
    public function __construct(
        private readonly NodeInterface $nodeInterfaceRepository,
        private readonly HomeInterface $homeInterfaceRepository,
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
}
