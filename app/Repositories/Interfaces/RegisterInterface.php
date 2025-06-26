<?php

namespace App\Repositories\Interfaces;

interface RegisterInterface {
    public function registerUser( string $name, string $email, string $password, string $cellphone, string $lat = null, string $lng = null );
}
