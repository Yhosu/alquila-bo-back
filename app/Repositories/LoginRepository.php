<?php

namespace App\Repositories;

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
        return null;
    }
}
