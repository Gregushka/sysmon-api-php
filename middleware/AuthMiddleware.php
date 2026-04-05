<?php

class AuthMiddleware
{
    /**
     * Validate the bearer token and check route permission.
     *
     * @param string $method   HTTP method
     * @param string $path     Actual request path (e.g. /users/5)
     * @param string $apiPath  Bare pattern path for permission lookup (e.g. /users/:id)
     *                         Empty string = no permission check (public routes).
     * @param string $version  API version detected from URI (e.g. 'v1')
     *
     * @return array  Authenticated session row with 'roles' attached
     */
    public static function check(
        string $method,
        string $path,
        string $apiPath  = '',
        string $version  = ''
    ): array {
        $token = AuthHelper::getTokenFromRequest();

        if ($token === null || $token === '') {
            Logger::logError($method, $path, 'Missing auth token', 401);
            ResponseHelper::unauthorized('Authentication token required');
        }

        $session = AuthHelper::validateToken($token);
        if ($session === null) {
            Logger::logError($method, $path, 'Invalid or expired token', 401);
            ResponseHelper::unauthorized('Invalid or expired token');
        }

        // Attach roles to the session array for downstream use
        $roles = AuthHelper::getUserRoles((int)$session['user_id']);
        $session['roles'] = $roles;

        // Permission check — skip for public routes (empty apiPath)
        if ($apiPath !== '') {
            // Build the versioned lookup key that matches api_commands.command:
            //   version='v1', apiPath='/users/:id'  →  '/v1/users/:id'
            $fullApiPath = "/{$version}{$apiPath}";

            if (!AuthHelper::isPermitted($roles, $method, $fullApiPath)) {
                Logger::logError($method, $path, 'Permission denied', 403, $session);
                ResponseHelper::forbidden('You do not have permission to perform this action');
            }
        }

        return $session;
    }
}
