<?php

class AuthMiddleware
{
    /**
     * Validate the bearer token and check route permission.
     *
     * @param string $method   HTTP method of the current request
     * @param string $path     Normalised API path (e.g. /users/5)
     * @param string $apiPath  Pattern path used for permission lookup
     *                         (e.g. /api/v1/users/:id)
     *
     * @return array  Authenticated user row with roles attached
     */
    public static function check(string $method, string $path, string $apiPath = ''): array
    {
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

        // Attach roles to the session array
        $roles = AuthHelper::getUserRoles((int)$session['user_id']);
        $session['roles'] = $roles;

        // Permission check (skip if no apiPath provided)
        if ($apiPath !== '') {
            $fullApiPath = '/api/v1' . $apiPath;
            if (!AuthHelper::isPermitted($roles, $method, $fullApiPath)) {
                Logger::logError($method, $path, 'Permission denied', 403, $session);
                ResponseHelper::forbidden('You do not have permission to perform this action');
            }
        }

        return $session;
    }
}
