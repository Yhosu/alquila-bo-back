<?php

namespace App\Exceptions;

use Exception;

class HttpConnectionException extends Exception
{
    public function __construct($message = "Error de conexion ", $code = 200, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
