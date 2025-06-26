<?php

namespace App\Repositories;

use App\Exceptions\BadRequestException;
use App\Exceptions\CustomException;
use App\Traits\CRUDOperations;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\Interfaces\RegisterInterface;
use App\Services\ApiResponseService;
use Throwable;

class RegisterRepository implements RegisterInterface
{
    // use CRUDOperations;
    protected $model = \App\Models\User::class;

    public function registerUser( string $name, string $email, string $password, string $cellphone, string $lat = null, string $lng = null,  ) {
        try {
            $existUser = $this->model::where('email', $email)->first();
            if( $existUser ) throw new CustomException("Hubo un error al validar su usuario.", ['El usuario ya fue registrado antes previamente, intente con otro correo']);
            $user = $this->model::create([
                'name'      => $name,
                'email'     => $email,
                'password'  => bcrypt( $password),
                'cellphone' => $cellphone,
                'lat'       => $lat,
                'lng'       => $lng,
            ]);
            return $user;
        } catch( Throwable $th) {
            throw $th;
        }
    }
}
