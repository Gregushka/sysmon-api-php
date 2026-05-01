<?php

class AuthController
{
    public static function login(array $params, ?array $user): never
    {
        $login = trim($_GET['login'] ?? $_GET['l'] ?? '');
        $pwd   = trim($_GET['password'] ?? $_GET['p'] ?? '');

        if ($login === '' || $pwd === '') {
            Logger::logError('GET', '/auth', 'Missing credentials', 400);
            ResponseHelper::error(-1, 'Authentication failed', 400);
        }

        // Load user record
        $repo    = new UserRepository();
        $userRow = $repo->findByLogin($login);

        if ($userRow === null || !AuthHelper::verifyPassword($pwd, $userRow['password'])) {
            Logger::logError('GET', '/auth', 'Invalid credentials for: ' . $login, 401);
            ResponseHelper::error(-1, 'Authentication failed', 401);
        }

        // Create session token
        $token = AuthHelper::createSession((int)$userRow['id']);
        header('X-Auth-Token: ' . $token);

        // Load roles for the authenticated user
        $roles    = AuthHelper::getUserRoles((int)$userRow['id']);
        $topRole  = !empty($roles) ? $roles[0] : null;   // highest role (ORDER BY id DESC)

        // Build full user object (with role/group IDs)
        $userObj        = $repo->findById((int)$userRow['id']);
        $userArr        = $userObj->toArray();
        $userArr['last_token'] = null;  // don't expose token in body

        // Build screens with indicators and mock values
        $mockValues    = DataController::getMockValues();
        $screenRepo    = new ScreenRepository();
        $screens       = $screenRepo->findAll((int)$userRow['id']);
        $screensOut    = [];
        foreach ($screens as $screen) {
            $screen->aggregates = $screenRepo->buildAggregatesForScreen($screen->id, $mockValues);
            $screensOut[]       = $screen->toArray();
        }

        $response = [
            'auth' => [
                'code'    => 0,
                'message' => 'Success',
                'data'    => [
                    'user' => $userArr,
                    'role' => $topRole ? (new Role($topRole))->toArray() : null,
                ],
            ],
            'data' => [
                'hdr'     => AppHeader::get('force_update'),
                'screens' => $screensOut,
            ],
        ];

        Logger::logRequest('GET', '/auth', ['login' => $login], $userRow, $response, 200);
        ResponseHelper::send($response);
    }
}
