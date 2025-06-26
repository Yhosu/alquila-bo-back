<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends Exception
{
    public function __construct($message = "Item no encontrado", $code = Response::HTTP_NOT_FOUND, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

