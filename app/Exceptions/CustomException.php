<?php

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    private $errors;

    public function __construct($message = "Error de Validacion", array $errors = [], $code = 401, Exception $previous = null) {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public function getErrors() {
        return $this->errors;
    }
}
