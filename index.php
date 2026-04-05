<?php
declare(strict_types=1);

define('BASE_PATH', __DIR__);

// ─── Bootstrap ───────────────────────────────────────────────────────────────

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';

// Utils (no inter-dependencies, load first)
require_once BASE_PATH . '/utils/ResponseHelper.php';
require_once BASE_PATH . '/utils/ValidationHelper.php';
require_once BASE_PATH . '/utils/AuthHelper.php';
require_once BASE_PATH . '/utils/AppHeader.php';
require_once BASE_PATH . '/utils/Logger.php';

// Middleware
require_once BASE_PATH . '/middleware/CorsMiddleware.php';
require_once BASE_PATH . '/middleware/AuthMiddleware.php';

// Models
foreach (glob(BASE_PATH . '/models/*.php') as $f) { require_once $f; }

// Repositories
foreach (glob(BASE_PATH . '/repositories/*.php') as $f) { require_once $f; }

// Controllers
foreach (glob(BASE_PATH . '/controllers/*.php') as $f) { require_once $f; }

// Router (registers all routes)
require_once BASE_PATH . '/routes/routes.php';

// ─── Installation check ──────────────────────────────────────────────────────

require_once BASE_PATH . '/setup/install.php';
Install::runIfNeeded();

// ─── Handle request ──────────────────────────────────────────────────────────

CorsMiddleware::handle();

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Strip version prefix — all valid routes live under /v1/ (or /v2/ etc.)
$prefix = '/' . APP_VERSION;   // '/v1'
if (!str_starts_with($uri, $prefix)) {
    ResponseHelper::error(-1, 'Invalid API path. Expected prefix: ' . $prefix, 404);
}

$path = rtrim(substr($uri, strlen($prefix)), '/') ?: '/';

Router::dispatch($method, $path);
