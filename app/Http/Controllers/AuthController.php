<?php

namespace App\Http\Controllers;


use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Closure;
use App\Services\ApiResponseService;
use App\Repositories\Interfaces\LoginInterface;
use App\Repositories\Interfaces\RegisterInterface;
use Throwable;
use App\Traits\RegisterLogs;

class AuthController extends Controller {
    use RegisterLogs;
    public function __construct( 
        private readonly LoginInterface $loginInterfaceRepository,
        private readonly RegisterInterface $registerInterfaceRepository,
    ){
    }

    public function getLogin( LoginRequest $request ) {
        try {
            $result = $this->loginInterfaceRepository->loginUser(
            $request->validated('email'), 
            $request->validated('password')
            );
            return ApiResponseService::success('Usuario logueado con éxito', $result);
        } catch( Throwable $e ) {
            return ApiResponseService::error('Hubo un error al intentar iniciar sesión', ['Ingrese los datos correctos para poder iniciar sesión']);
        }
    }

    public function getRegister( RegisterRequest $request ) {
        try {
            $result = $this->registerInterfaceRepository->registerUser(
                $request->name,
                    $request->email,
                $request->password,
                $request->cellphone,
                $request->lat,
                $request->lng
            );
            return ApiResponseService::create('Usuario creado con éxito', $result);
        } catch (Throwable $e) {
            return $this->execLog($e);
        }        
    }
    public function getLogout( Request $request ) {
        try {
            $request->user()->currentAccessToken()->delete();
            return ApiResponseService::create('Haz cerrado sesión con éxito');
        } catch (Throwable $e) {
            return $this->execLog($e);
        }        
    }
}
