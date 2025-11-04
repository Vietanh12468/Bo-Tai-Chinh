<?php

namespace App;

use Illuminate\Http\JsonResponse;

trait ApiResponser
{
    public function sendResponse($data, $message = '', $code = 200): JsonResponse
    {
        return response()->json([
            'error_code' => 0,
            'notification' => $message,
            'data' => $data
        ], $code);
    }

    public function sendError($message, $errors = [], $code = 200): JsonResponse
    {
        return response()->json([
            'error_code' => 1,
            'notification' => $message,
            'errors' => $errors
        ], $code);
    }

    public function responseSuccess($message, $data = [], $statusCode = 0, $httpCode = 200): JsonResponse
    {
        return response()->json([
            'error_code' => $statusCode,
            'notification' => $message,
            'data' => $data
        ], $httpCode);
    }

    public function responseError($message, $data = [], $statusCode = 1, $httpCode = 200): JsonResponse
    {
        return response()->json([
            'error_code' => $statusCode,
            'notification' => $message,
            'data' => $data
        ], $httpCode);
    }

    public function listresponseError($message, $data = [], $statusCode = 1, $httpCode = 200): JsonResponse
    {
        return response()->json([
            'error_code' => $statusCode,
            'notification' => $message,
            'data' => $data
        ], $httpCode);
    }

    public function customValidationErrorResponse(array $errors, string $notification = 'Lỗi kiểu dữ liệu.', int $errorCode = 2, int $statusCode = 422): JsonResponse
    {
        return response()->json([
            'error_code' => $errorCode,
            'notification' => $notification,
            'field' => $errors,
        ], $statusCode);
    }
}
