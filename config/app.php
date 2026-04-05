<?php
// sysmon-api-php application configuration

define('APP_VERSION',            'v1');
define('APP_SUPPORTED_VERSIONS', ['v1']);   // add 'v2' here when ready
define('APP_ENV',                getenv('APP_ENV') ?: 'development');  // development | production
define('APP_DEBUG',              APP_ENV === 'development');

// Token TTL in seconds (24 hours)
define('TOKEN_TTL', 86400);

// ─── Logging levels ──────────────────────────────────────────────────────────
// 0 = DEBUG  : everything, including every indicator poll
// 1 = INFO   : all requests except indicator data updates (GET /data, GET /indicators)
// 2 = WRITE  : only state-changing requests (POST / PUT / DELETE)
// 3 = ERROR  : only 4xx / 5xx responses
// 4 = AUDIT  : auth events and user/role/group changes only
define('LOG_LEVEL', (int)(getenv('LOG_LEVEL') ?: 1));

// Requests at or above this level will NOT be logged even at DEBUG
// (used to suppress extremely noisy endpoints on demand)
define('LOG_SKIP_COMMANDS', []);

// ─── Database ─────────────────────────────────────────────────────────────────
define('DB_PATH', __DIR__ . '/../db/sysmon.db');
define('DB_INIT_SQL',  __DIR__ . '/../db/init.sql');
define('DB_SEED_SQL',  __DIR__ . '/../db/seed.sql');
define('DB_INSTALL_FLAG', __DIR__ . '/../db/.installed');
