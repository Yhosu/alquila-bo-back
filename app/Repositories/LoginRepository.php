<?php

namespace App\Repositories;

use App\Exceptions\CustomException;
use App\Exceptions\NotFoundException;
use App\Exceptions\BadRequestException;
use App\Traits\CRUDOperations;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\Interfaces\LoginInterface;
use App\Services\ApiResponseService;

class LoginRepository implements LoginInterface
{
    // use CRUDOperations;
    protected $model = Login::class;

    public function loginUser( string $email, string $password ) {
        if(\Auth::attempt(['email' => $email, 'password' => $password])){ 
            $user = \Auth::user(); 
            $result = [
                'token' => $user->createToken('AlquilaBo')->plainTextToken,
                'user'  => $user
            ];
            return $result;
        } 
        throw new CustomException("Hubo un error al iniciar sesión", ['Ingrese los datos correctos para iniciar sesión']);
    }

    public function recoverPassword(string $email, string $password, string $passworConfirmation) {
        /** Terminar el proceso de recuperar contraseña */
    }
}
