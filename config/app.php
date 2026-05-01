<?php
// sysmon-api-php application configuration

define('APP_VERSION',            'v1');
define('APP_SUPPORTED_VERSIONS', ['v1']);
define('APP_ENV',                getenv('APP_ENV') ?: 'development');
define('APP_DEBUG',              APP_ENV === 'development');

// Token TTL in seconds (24 hours)
define('TOKEN_TTL', 86400);

// ─── Logging levels ──────────────────────────────────────────────────────────
define('LOG_LEVEL', (int)(getenv('LOG_LEVEL') ?: 1));
define('LOG_SKIP_COMMANDS', []);

// ─── Database (MySQL) ─────────────────────────────────────────────────────────
define('DB_HOST',     getenv('DB_HOST')     ?: 'u457836.mysql.masterhost.ru');
define('DB_NAME',     getenv('DB_NAME')     ?: 'u457836_sysmonapi');
define('DB_USER',     getenv('DB_USER')     ?: 'u457836_sysmnapi');
define('DB_PASS',     getenv('DB_PASS')     ?: 'iNI6ORAT_4eo_t');
define('DB_CHARSET',  'utf8mb4');

define('DB_INIT_SQL',     __DIR__ . '/../db/init.sql');
define('DB_SEED_SQL',     __DIR__ . '/../db/seed.sql');
define('DB_INSTALL_FLAG', __DIR__ . '/../db/.installed');
