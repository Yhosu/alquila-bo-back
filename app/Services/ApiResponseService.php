<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseService
{
    public static function success($message = 'Success', $data = [], $variables = [], $others = [], $code = Response::HTTP_OK, $enableMultipleSteps = false): JsonResponse
    {
        return response()->json(
            [
                'status'         => true,
                'message'        => $message,
                'multiple_steps' => $enableMultipleSteps,
                'variables'      => $variables,
                'result'         => $data,
                'errors'         => [],
            ],
            $code
        );
    }

    public static function create($message = 'Success', $data = [], $others = [], $code = Response::HTTP_CREATED): JsonResponse
    {
        return response()->json(
            [
                'status' => true,
                'message' => $message,
                'errors' => [],
                'data' => $data,
                'others' => $others,
            ],
            $code
        );
    }

    public static function error($message = 'Error', $errors = [], $data = [],  $others = [], $code = 500): JsonResponse
    {
        return response()->json(
            [
                'status' => false,
                'message' => $message,
                'errors' => $errors,
                'data' => $data,
                'others' => $others,
            ],
            $code
        );
    }


    public static function unauthorized($message = 'Unauthorized'): JsonResponse
    {
        return response()->json(
            [
                'status' => false,
                'message' => $message,
                'errors' => [],
                'data' => [],
            ],
            response::HTTP_UNAUTHORIZED
        );
    }

    public static function not_found($message = 'Not Found', $errors = []): JsonResponse
    {
        return response()->json(
            [
                'status' => false,
                'message' => $message,
                'errors' => $errors,
                'data' => [],
                'others' => [],
            ],
            Response::HTTP_NOT_FOUND
        );
    }


    public static function throttled(int $max_attempts = 60, int $retry_after = 60): JsonResponse
    {
        return response()->json(
            [
                'status' => false,
                'message' => 'Too many attemps, please slow down the request.',
                'retry_after' => $retry_after,
                'max_attempts' => $max_attempts,
            ],
            response::HTTP_TOO_MANY_REQUESTS
        );
    }

    public static function warning($message = 'Warning', $errors = [], $data = [], $others = [], $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json(
            [
                'status' => false,
                'message' => $message,
                'errors' => $errors,
                'data' => $data,
                'others' => $others,
            ],
            $code
        );
    }

    public static function forbidden($message = 'Warning', $errors = [], $data = [], $others = [], $code = Response::HTTP_FORBIDDEN): JsonResponse
    {
        return response()->json(
            [
                'status' => false,
                'message' => $message,
                'errors' => $errors,
                'data' => $data,
                'others' => $others,
            ],
            $code
        );
    }

    public static function badRequest($message = 'Warning', $errors = [], $data = [], $others = [], $code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json(
            [
                'status' => false,
                'message' => $message,
                'errors' => $errors,
                'data' => $data,
                'others' => $others,
            ],
            $code
        );
    }
}
