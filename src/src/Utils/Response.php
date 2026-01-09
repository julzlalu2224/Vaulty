<?php

namespace Vaulty\Utils;

class Response
{
    public static function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
        
        echo json_encode($data);
        exit;
    }

    public static function success(string $message, array $data = [], int $statusCode = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    public static function error(string $message, int $statusCode = 400): void
    {
        self::json([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }

    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, 403);
    }

    public static function notFound(string $message = 'Not Found'): void
    {
        self::error($message, 404);
    }

    public static function validationError(array $errors): void
    {
        self::json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ], 422);
    }
}