<?php

class CorsMiddleware
{
    public static function handle(): void
    {
        // Allowed origins — tighten in production
        $allowed = APP_ENV === 'production'
            ? (getenv('ALLOWED_ORIGINS') ?: '*')
            : '*';

        header('Access-Control-Allow-Origin: '  . $allowed);
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Authorization');
        header('Access-Control-Expose-Headers: X-Auth-Token');

        // Handle preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
