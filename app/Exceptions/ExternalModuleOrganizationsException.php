<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Exception;

class ExternalModuleOrganizationsException extends Exception
{
    private $errors;

    public function __construct($message = "Hubo un error", array $errors = [], $code = Response::HTTP_FORBIDDEN, ?Exception $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
