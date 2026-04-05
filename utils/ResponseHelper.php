<?php

class ResponseHelper
{
    /**
     * Send a successful JSON response.
     *
     * @param mixed $data    Payload to encode
     * @param int   $status  HTTP status code
     */
    public static function send(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send an error JSON response.
     *
     * @param int    $code     Application-level error code (negative by convention)
     * @param string $message  Human-readable message
     * @param int    $status   HTTP status code
     */
    public static function error(int $code, string $message, int $status = 400): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            ['code' => $code, 'message' => $message],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }

    /**
     * Convenience: 401 Unauthorized
     */
    public static function unauthorized(string $message = 'Unauthorized'): never
    {
        self::error(-2, $message, 401);
    }

    /**
     * Convenience: 403 Forbidden
     */
    public static function forbidden(string $message = 'Forbidden'): never
    {
        self::error(-3, $message, 403);
    }

    /**
     * Convenience: 404 Not Found
     */
    public static function notFound(string $message = 'Not found'): never
    {
        self::error(-4, $message, 404);
    }
}
