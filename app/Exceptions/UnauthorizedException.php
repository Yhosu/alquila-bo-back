<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class UnauthorizedException extends Exception
{
    public function __construct($message = "No tiene permisos", $code = Response::HTTP_UNAUTHORIZED, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
