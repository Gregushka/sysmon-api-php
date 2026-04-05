<?php

/**
 * Router
 *
 * Pattern syntax: literal segments and {name} placeholders.
 * Example: /users/{user_id}/roles
 *
 * Version-specific route tables live in routes/v1.php, routes/v2.php, …
 * index.php loads the correct file after extracting the version from the URI.
 *
 * The $apiPath argument to register() is the bare path pattern (no version prefix),
 * e.g. '/users/:id'. AuthMiddleware prepends the active version at runtime so
 * the lookup against api_commands resolves correctly: '/v1/users/:id'.
 */
class Router
{
    private static array  $routes  = [];
    private static string $version = '';

    public static function register(
        string $method,
        string $pattern,
        array  $handler,
        bool   $auth    = true,
        string $apiPath = ''
    ): void {
        self::$routes[] = compact('method', 'pattern', 'handler', 'auth', 'apiPath');
    }

    /**
     * @param string $version  The detected version string, e.g. 'v1'
     */
    public static function dispatch(string $method, string $path, string $version): never
    {
        self::$version = $version;
        $method        = strtoupper($method);

        foreach (self::$routes as $route) {
            if (strtoupper($route['method']) !== $method) {
                continue;
            }

            $params = self::match($route['pattern'], $path);
            if ($params === null) {
                continue;
            }

            // Authentication & permission check
            $user = null;
            if ($route['auth']) {
                $user = AuthMiddleware::check($method, $path, $route['apiPath'], $version);
            }

            // Lightweight log entry; controllers may add richer entries themselves
            Logger::logRequest($method, $path, $params, $user);

            call_user_func($route['handler'], $params, $user);
            exit; // handler should exit via ResponseHelper, but guard anyway
        }

        Logger::logError($method, $path, 'Route not found', 404);
        ResponseHelper::notFound('Route not found');
    }

    private static function match(string $pattern, string $path): ?array
    {
        $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#u';

        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }
        return $params;
    }
}
