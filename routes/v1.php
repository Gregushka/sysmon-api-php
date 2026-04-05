<?php

/**
 * API v1 route definitions.
 *
 * apiPath (5th argument) is the bare path pattern used for permission lookup
 * in the api_commands table — WITHOUT the version prefix.
 * AuthMiddleware prepends the detected version at runtime, e.g.:
 *   '/users' → '/v1/users'  (looked up in api_commands)
 *
 * To add v2: copy this file to routes/v2.php, point handlers at
 * controllers/v2/ classes, and only override what changed.
 */

// Auth (no token required)
Router::register('GET',    '/auth',                        [AuthController::class,           'login'],        false, '');

// Live data (mocked sensor values)
Router::register('GET',    '/data',                        [DataController::class,           'readData'],     true,  '/data');
Router::register('GET',    '/data/{screen_id}',            [DataController::class,           'readData'],     true,  '/data');

// Users
Router::register('GET',    '/users',                       [UserController::class,           'getAll'],       true,  '/users');
Router::register('GET',    '/users/{user_id}',             [UserController::class,           'getOne'],       true,  '/users/:id');
Router::register('POST',   '/users',                       [UserController::class,           'create'],       true,  '/users');
Router::register('PUT',    '/users/{user_id}',             [UserController::class,           'update'],       true,  '/users/:id');
Router::register('DELETE', '/users/{user_id}',             [UserController::class,           'delete'],       true,  '/users/:id');
Router::register('POST',   '/users/{user_id}/roles',       [UserController::class,           'assignRoles'],  true,  '/users/:id/roles');
Router::register('POST',   '/users/{user_id}/groups',      [UserController::class,           'assignGroups'], true,  '/users/:id/groups');

// Screens
Router::register('GET',    '/screen',                      [ScreenController::class,         'getAll'],       true,  '/screen');
Router::register('GET',    '/screen/{screen_id}',          [ScreenController::class,         'getOne'],       true,  '/screen/:id');
Router::register('POST',   '/screen',                      [ScreenController::class,         'create'],       true,  '/screen');
Router::register('PUT',    '/screen/{screen_id}',          [ScreenController::class,         'update'],       true,  '/screen/:id');
Router::register('DELETE', '/screen/{screen_id}',          [ScreenController::class,         'delete'],       true,  '/screen/:id');

// Indicators (values) — most specific patterns first
Router::register('GET',    '/indicators/{screen_id}/{ind_id}', [IndicatorController::class,  'getValues'],    true,  '/indicators/:screen_id/:ind_id');
Router::register('GET',    '/indicators/{screen_id}',          [IndicatorController::class,  'getValues'],    true,  '/indicators/:screen_id');
Router::register('GET',    '/indicators',                      [IndicatorController::class,  'getValues'],    true,  '/indicators');
Router::register('POST',   '/position/{ind_id}',               [IndicatorController::class,  'setPosition'],  true,  '/position/:ind_id');

// Roles
Router::register('GET',    '/roles',                       [RoleController::class,           'getRoles'],     true,  '/roles');
Router::register('POST',   '/roles',                       [RoleController::class,           'createRole'],   true,  '/roles');
Router::register('PUT',    '/roles/{role_id}',             [RoleController::class,           'updateRole'],   true,  '/roles/:id');
Router::register('DELETE', '/roles/{role_id}',             [RoleController::class,           'deleteRole'],   true,  '/roles/:id');

// Groups
Router::register('GET',    '/groups',                      [RoleController::class,           'getGroups'],    true,  '/groups');
Router::register('POST',   '/groups',                      [RoleController::class,           'createGroup'],  true,  '/groups');
Router::register('PUT',    '/groups/{group_id}',           [RoleController::class,           'updateGroup'],  true,  '/groups/:id');
Router::register('DELETE', '/groups/{group_id}',           [RoleController::class,           'deleteGroup'],  true,  '/groups/:id');

// Logs
Router::register('GET',    '/logs',                        [LogController::class,            'getLogs'],      true,  '/logs');
Router::register('DELETE', '/logs',                        [LogController::class,            'clearLogs'],    true,  '/logs');

// Backend commands & controls
Router::register('GET',    '/commands',                    [BackendCommandController::class,  'getAll'],      true,  '/commands');
Router::register('GET',    '/controls',                    [ControlController::class,         'getAll'],      true,  '/controls');
