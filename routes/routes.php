<?php

/**
 * Router
 *
 * Pattern syntax: literal segments and {name} placeholders.
 * Example: /users/{user_id}/roles
 *
 * Routes are tested in registration order; first match wins.
 * The $auth flag controls whether AuthMiddleware::check() is invoked.
 * The $apiPath is the permission-lookup pattern (maps to api_commands table).
 */
class Router
{
    private static array $routes = [];

    public static function register(
        string $method,
        string $pattern,
        array  $handler,
        bool   $auth    = true,
        string $apiPath = ''
    ): void {
        self::$routes[] = compact('method', 'pattern', 'handler', 'auth', 'apiPath');
    }

    public static function dispatch(string $method, string $path): never
    {
        $method = strtoupper($method);

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
                $user = AuthMiddleware::check($method, $path, $route['apiPath']);
            }

            // Log the request (controllers may add richer log entries themselves)
            Logger::logRequest($method, $path, $params, $user);

            // Dispatch to controller
            call_user_func($route['handler'], $params, $user);
            exit; // handler should have exited via ResponseHelper, but be safe
        }

        Logger::logError($method, $path, 'Route not found', 404);
        ResponseHelper::notFound('Route not found');
    }

    private static function match(string $pattern, string $path): ?array
    {
        $regex  = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex  = '#^' . $regex . '$#u';

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

// ─── Route definitions ───────────────────────────────────────────────────────
// Format: method, path-pattern, [ControllerClass, method], requiresAuth, apiPathPattern

// Auth (no token required)
Router::register('GET',    '/auth',                        [AuthController::class,           'login'],        false, '');

// Live data (mocked sensor values)
Router::register('GET',    '/data',                        [DataController::class,           'readData'],     true,  '/api/v1/data');
Router::register('GET',    '/data/{screen_id}',            [DataController::class,           'readData'],     true,  '/api/v1/data');

// Users
Router::register('GET',    '/users',                       [UserController::class,           'getAll'],       true,  '/api/v1/users');
Router::register('GET',    '/users/{user_id}',             [UserController::class,           'getOne'],       true,  '/api/v1/users/:id');
Router::register('POST',   '/users',                       [UserController::class,           'create'],       true,  '/api/v1/users');
Router::register('PUT',    '/users/{user_id}',             [UserController::class,           'update'],       true,  '/api/v1/users/:id');
Router::register('DELETE', '/users/{user_id}',             [UserController::class,           'delete'],       true,  '/api/v1/users/:id');
Router::register('POST',   '/users/{user_id}/roles',       [UserController::class,           'assignRoles'],  true,  '/api/v1/users/:id/roles');
Router::register('POST',   '/users/{user_id}/groups',      [UserController::class,           'assignGroups'], true,  '/api/v1/users/:id/groups');

// Screens
Router::register('GET',    '/screen',                      [ScreenController::class,         'getAll'],       true,  '/api/v1/screen');
Router::register('GET',    '/screen/{screen_id}',          [ScreenController::class,         'getOne'],       true,  '/api/v1/screen/:id');
Router::register('POST',   '/screen',                      [ScreenController::class,         'create'],       true,  '/api/v1/screen');
Router::register('PUT',    '/screen/{screen_id}',          [ScreenController::class,         'update'],       true,  '/api/v1/screen/:id');
Router::register('DELETE', '/screen/{screen_id}',          [ScreenController::class,         'delete'],       true,  '/api/v1/screen/:id');

// Indicators (values) — order matters: most specific first
Router::register('GET',    '/indicators/{screen_id}/{ind_id}', [IndicatorController::class,  'getValues'],    true,  '/api/v1/indicators/:screen_id/:ind_id');
Router::register('GET',    '/indicators/{screen_id}',          [IndicatorController::class,  'getValues'],    true,  '/api/v1/indicators/:screen_id');
Router::register('GET',    '/indicators',                      [IndicatorController::class,  'getValues'],    true,  '/api/v1/indicators');
Router::register('POST',   '/position/{ind_id}',               [IndicatorController::class,  'setPosition'],  true,  '/api/v1/position/:ind_id');

// Roles
Router::register('GET',    '/roles',                       [RoleController::class,           'getRoles'],     true,  '/api/v1/roles');
Router::register('POST',   '/roles',                       [RoleController::class,           'createRole'],   true,  '/api/v1/roles');
Router::register('PUT',    '/roles/{role_id}',             [RoleController::class,           'updateRole'],   true,  '/api/v1/roles/:id');
Router::register('DELETE', '/roles/{role_id}',             [RoleController::class,           'deleteRole'],   true,  '/api/v1/roles/:id');

// Groups
Router::register('GET',    '/groups',                      [RoleController::class,           'getGroups'],    true,  '/api/v1/groups');
Router::register('POST',   '/groups',                      [RoleController::class,           'createGroup'],  true,  '/api/v1/groups');
Router::register('PUT',    '/groups/{group_id}',           [RoleController::class,           'updateGroup'],  true,  '/api/v1/groups/:id');
Router::register('DELETE', '/groups/{group_id}',           [RoleController::class,           'deleteGroup'],  true,  '/api/v1/groups/:id');

// Logs
Router::register('GET',    '/logs',                        [LogController::class,            'getLogs'],      true,  '/api/v1/logs');
Router::register('DELETE', '/logs',                        [LogController::class,            'clearLogs'],    true,  '/api/v1/logs');

// Backend commands & controls (read-only for now)
Router::register('GET',    '/commands',                    [BackendCommandController::class,  'getAll'],      true,  '/api/v1/commands');
Router::register('GET',    '/controls',                    [ControlController::class,         'getAll'],      true,  '/api/v1/controls');
