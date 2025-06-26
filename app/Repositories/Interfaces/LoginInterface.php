<?php

namespace App\Repositories\Interfaces;

interface LoginInterface {
    public function loginUser( string $email, string $password );
}
