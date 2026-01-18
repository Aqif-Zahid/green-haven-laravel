<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(
        string $message,
        mixed $data = null,
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'status' => 1,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public static function error(
        string $message,
        int $statusCode = 400,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'status' => 0,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
