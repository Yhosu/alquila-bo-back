<?php

namespace App\Traits;

use App\Exceptions\BadRequestException;
use App\Exceptions\CustomException;
use App\Exceptions\ExternalModuleException;
use App\Exceptions\ExternalModuleOrganizationsException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Helpers\Func;
use App\Models\HandleError;
use App\Services\ApiResponseService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;


trait RegisterLogs
{
    public function execLog(Throwable $exception)
    {
        if ($exception instanceof CustomException) {
            $message = substr($exception->getMessage(), 0, 1500);
            $errors = count($exception->getErrors()) > 0 ? $exception->getErrors() : [$message];
            return $this->handleValidationsException($message, $errors);
        } elseif ($exception instanceof ValidationException) {
            $message = substr($exception->getMessage(), 0, 1500);
            $formatted_errors = [];
            $errors = $exception->errors();
            foreach ($errors as $error) {
                $formatted_errors[] = $error[0];
            }
            return $this->handleFormValidationException($message, $formatted_errors);
        } elseif ($exception instanceof UnauthorizedException) {
            $message = substr($exception->getMessage(), 0, 1500);
            return $this->handleValidationsException($message, [$message]);
        } elseif ($exception instanceof NotFoundException) {
            $message = substr($exception->getMessage(), 0, 1500);
            return $this->handleNotFoundException($message);
        } elseif ($exception instanceof ForbiddenException) {
            $message = substr($exception->getMessage(), 0, 1500);
            return $this->handleNotForbiddenException($message);
        } elseif ($exception instanceof BadRequestException) {
            $message = substr($exception->getMessage(), 0, 1500);
            $errors = count($exception->getErrors()) > 0 ? $exception->getErrors() : [$message];
            return $this->handleBadRequestException($message, $errors);
        } elseif ($exception instanceof ExternalModuleOrganizationsException) {
            $message = substr($exception->getMessage(), 0, 1500);
            $errors = count($exception->getErrors()) > 0 ? $exception->getErrors() : [$message];
            return $this->handleGeneralException($exception, $message);
        } else {
            $message = $exception->getMessage();
            return $this->handleGeneralException($exception, $message);
        }
    }

    private function handleValidationsException($message, $errors)
    {
        $response = ApiResponseService::error($message, $errors);
        return $response;
    }

    private function handleFormValidationException($message = 'Hubo un problema.', $errors)
    {
        return ApiResponseService::badRequest($message, $errors);
    }

    private function handleUnauthorizedException($message)
    {
        return ApiResponseService::unauthorized($message);
    }

    private function handleNotFoundException($message)
    {
        return ApiResponseService::not_found($message, [$message]);
    }

    private function handleNotForbiddenException($message)
    {
        return ApiResponseService::forbidden($message, [$message]);
    }

    private function handleBadRequestException($message, $errors)
    {
        return ApiResponseService::badRequest($message, $errors);
    }


    private function handleGeneralException(Throwable $exception, $message)
    {
        $url = request()->url();
        if (config('app.env') == 'local') {
            return $exception;
        } elseif( config('app.env') == 'production' ) {
            Mail::html(
                "<h1>Error tickets.prod.todotix.com</h1>
                <h4>Hubo un error en el server</h4>
                <p>Mesaje de Error: {$exception->getMessage()}</p>
                <p>Codigo: {$exception->getCode()}</p>
                <p>Archivo: {$exception->getFile()}</p>
                <p>Linea: {$exception->getLine()}</p>
                <p>API: $url </p>",
                function ($message) {
                    $message->to(['josuandroidg7@gmail.com'])
                        ->subject('Log de Error');
                }
            );            
        }
        return ApiResponseService::error(message: 'Hubo un error en el servidor', errors: ["Estamos apenados por este suceso, lo corregiremos lo antes posible"], code: 500);
    }
}
