<?php
declare(strict_types=1);

define('BASE_PATH', __DIR__);

// ─── Shared bootstrap (version-agnostic) ─────────────────────────────────────

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';

require_once BASE_PATH . '/utils/ResponseHelper.php';
require_once BASE_PATH . '/utils/ValidationHelper.php';
require_once BASE_PATH . '/utils/AuthHelper.php';
require_once BASE_PATH . '/utils/AppHeader.php';
require_once BASE_PATH . '/utils/Logger.php';

require_once BASE_PATH . '/middleware/CorsMiddleware.php';
require_once BASE_PATH . '/middleware/AuthMiddleware.php';

// Router class (no routes registered yet — that happens per-version below)
require_once BASE_PATH . '/routes/routes.php';

// Shared DB layers — models and repositories never change between API versions.
// If a breaking change requires a new model shape, extend the class in a
// version-specific file rather than forking the whole layer.
foreach (glob(BASE_PATH . '/models/*.php')       as $f) { require_once $f; }
foreach (glob(BASE_PATH . '/repositories/*.php') as $f) { require_once $f; }

// ─── Installation check ──────────────────────────────────────────────────────

require_once BASE_PATH . '/setup/install.php';
Install::runIfNeeded();

// ─── CORS (handles OPTIONS preflight and sets headers) ───────────────────────

CorsMiddleware::handle();

// ─── Detect API version ──────────────────────────────────────────────────────

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Strip the subdirectory base path so the app works at any mount point.
// e.g. deployed at /scada/sysmon-api/ → strip that prefix before matching.
// dirname(SCRIPT_NAME) gives '/scada/sysmon-api'; rtrim removes trailing slash.
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($basePath !== '' && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}
if ($uri === '' || $uri === false) {
    $uri = '/';
}

// URI is now relative to the app root, e.g. /v1/auth
if (!preg_match('#^/(v\d+)(/.*)?$#', $uri, $m)) {
    ResponseHelper::error(-1, 'Missing or invalid API version in URL. Example: /v1/auth', 404);
}

$version = $m[1];                             // 'v1', 'v2', …
$path    = rtrim($m[2] ?? '', '/') ?: '/';    // '/auth', '/users/5', …

// Guard against unsupported or unimplemented versions
if (!in_array($version, APP_SUPPORTED_VERSIONS, true)) {
    ResponseHelper::error(
        -1,
        "API version '{$version}' is not supported. Supported: " . implode(', ', APP_SUPPORTED_VERSIONS),
        404
    );
}

$controllerDir = BASE_PATH . "/controllers/{$version}";
$routeFile     = BASE_PATH . "/routes/{$version}.php";

if (!is_dir($controllerDir) || !file_exists($routeFile)) {
    ResponseHelper::error(-1, "API version '{$version}' is declared but not yet implemented", 501);
}

// ─── Load version-specific controllers and routes ────────────────────────────
// Controllers from different versions use the same class names.
// Only one version is ever loaded per request, so there is no collision.

foreach (glob("{$controllerDir}/*.php") as $f) { require_once $f; }
require_once $routeFile;

// ─── Dispatch ────────────────────────────────────────────────────────────────

Router::dispatch($method, $path, $version);
