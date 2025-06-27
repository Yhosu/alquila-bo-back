<?php

namespace App\Repositories\Interfaces;

interface LoginInterface {
    public function loginUser( string $email, string $password );
    public function recoverPassword( string $email, string $password, string $passworConfirmation );
}
