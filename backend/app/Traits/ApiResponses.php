<?php

namespace App\Traits;

trait ApiResponses
{
    protected function ok(string $message, mixed $data = [])
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'status' => 200
        ], 200);
    }

    protected function success(string $message, array $data = [], $statusCode = 200)
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'status' => $statusCode
        ], $statusCode);
    }

    protected function error(array $errors, int $statusCode, array $data = [])
    {
        if (is_string($errors)) {
            return response()->json([
                'data' => $data,
                'message' => $errors,
                'status' => $statusCode
            ], $statusCode);
        }

        return [
            'errors' => $errors
        ];
    }

    protected function withCookies(string $message, array $cookies, array $data = [], $statusCode = 200)
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'status' => $statusCode
        ], $statusCode)->withCookies($cookies);
    }
}
