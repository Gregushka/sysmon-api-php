-- sysmon-api-php: database schema
-- Run once via setup/install.php
-- PRAGMAs are set by Database::get() on every connection; omitted here.

-- Lookup tables

CREATE TABLE IF NOT EXISTS screen_types (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT    NOT NULL UNIQUE,
    description TEXT,
    settings    JSON
);

CREATE TABLE IF NOT EXISTS indicator_types (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT    NOT NULL UNIQUE,
    front_name  TEXT    NOT NULL,
    description TEXT,
    settings    JSON
);

CREATE TABLE IF NOT EXISTS units (
    id      INTEGER PRIMARY KEY AUTOINCREMENT,
    name    TEXT    NOT NULL,
    symbol  TEXT    NOT NULL,
    units   TEXT
);

CREATE TABLE IF NOT EXISTS control_types (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT    NOT NULL,
    front_name  TEXT,
    description TEXT
);

-- ─── Core entities ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS screens (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    type_id     INTEGER NOT NULL REFERENCES screen_types(id),
    name        TEXT    NOT NULL,
    description TEXT    NOT NULL DEFAULT '',
    tab_header  TEXT,
    background  VARCHAR(255),
    settings    JSON    NOT NULL DEFAULT '{}'
);

CREATE TABLE IF NOT EXISTS aggregates (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT    NOT NULL UNIQUE,
    description TEXT,
    settings    JSON
);

CREATE TABLE IF NOT EXISTS indicators (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    ind_id      TEXT    NOT NULL,
    data_id     TEXT,
    type_id     INTEGER NOT NULL REFERENCES indicator_types(id),
    label       TEXT,
    unit_id     INTEGER REFERENCES units(id),
    top         INTEGER,
    left        INTEGER,
    label_font  JSON,
    unit_font   JSON,
    value_font  JSON,
    radius      INTEGER,
    size        INTEGER,
    box         JSON,
    settings    JSON
);

CREATE TABLE IF NOT EXISTS controls (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT    NOT NULL,
    front_name  TEXT,
    type_id     INTEGER REFERENCES control_types(id),
    description TEXT
);

CREATE TABLE IF NOT EXISTS backend_commands (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    command     TEXT    NOT NULL UNIQUE,
    response    TEXT,
    description TEXT
);

-- ─── Roles & groups ──────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS roles (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT    NOT NULL UNIQUE,
    description TEXT,
    permissions JSON
);

CREATE TABLE IF NOT EXISTS groups (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT    NOT NULL UNIQUE,
    description TEXT,
    permissions JSON
);

-- ─── Users ───────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS users (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    login       TEXT        NOT NULL UNIQUE,
    password    TEXT        NOT NULL,  -- bcrypt(sha256(plain))
    fname       VARCHAR(50),
    lname       VARCHAR(50),
    pname       VARCHAR(50),
    position    VARCHAR(255)
);

-- Multi-session token store
CREATE TABLE IF NOT EXISTS sessions (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token       TEXT    NOT NULL UNIQUE,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at  DATETIME NOT NULL,
    ip          VARCHAR(45),
    user_agent  TEXT
);

-- ─── M:N junction tables ─────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS user_roles_map (
    id      INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL REFERENCES users(id)  ON DELETE CASCADE,
    role_id INTEGER NOT NULL REFERENCES roles(id)  ON DELETE CASCADE,
    UNIQUE(user_id, role_id)
);

CREATE TABLE IF NOT EXISTS user_groups_map (
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id  INTEGER NOT NULL REFERENCES users(id)  ON DELETE CASCADE,
    group_id INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    UNIQUE(user_id, group_id)
);

CREATE TABLE IF NOT EXISTS user_screen (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id     INTEGER NOT NULL REFERENCES users(id)   ON DELETE CASCADE,
    screen_id   INTEGER NOT NULL REFERENCES screens(id) ON DELETE CASCADE,
    permissions JSON    NOT NULL DEFAULT '{"read":true,"control":false}',
    UNIQUE(user_id, screen_id)
);

CREATE TABLE IF NOT EXISTS screen_aggregate (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    screen_id    INTEGER NOT NULL REFERENCES screens(id)    ON DELETE CASCADE,
    aggregate_id INTEGER NOT NULL REFERENCES aggregates(id) ON DELETE CASCADE,
    UNIQUE(screen_id, aggregate_id)
);

-- aggregate_indicator links by indicators.id (PK), ind_id is the business identifier
CREATE TABLE IF NOT EXISTS aggregate_indicator (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    aggregate_id INTEGER NOT NULL REFERENCES aggregates(id)  ON DELETE CASCADE,
    indicator_id INTEGER NOT NULL REFERENCES indicators(id)  ON DELETE CASCADE,
    UNIQUE(aggregate_id, indicator_id)
);

CREATE TABLE IF NOT EXISTS group_screen (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    group_id  INTEGER NOT NULL REFERENCES groups(id)  ON DELETE CASCADE,
    screen_id INTEGER NOT NULL REFERENCES screens(id) ON DELETE CASCADE,
    UNIQUE(group_id, screen_id)
);

CREATE TABLE IF NOT EXISTS group_aggregate (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    group_id     INTEGER NOT NULL REFERENCES groups(id)     ON DELETE CASCADE,
    aggregate_id INTEGER NOT NULL REFERENCES aggregates(id) ON DELETE CASCADE,
    UNIQUE(group_id, aggregate_id)
);

-- ─── API command permission system ───────────────────────────────────────────

CREATE TABLE IF NOT EXISTS api_commands (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    command     VARCHAR(255) NOT NULL,
    method      VARCHAR(10)  NOT NULL,
    description TEXT,
    parameters  JSON,
    UNIQUE(command, method)
);

CREATE TABLE IF NOT EXISTS role_api_command (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    role_id        INTEGER NOT NULL REFERENCES roles(id)        ON DELETE CASCADE,
    api_command_id INTEGER NOT NULL REFERENCES api_commands(id) ON DELETE CASCADE,
    UNIQUE(role_id, api_command_id)
);

-- ─── Logs ────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS logs (
    id                 INTEGER PRIMARY KEY AUTOINCREMENT,
    timestamp          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    error_level        INTEGER      NOT NULL DEFAULT 1,
    caller_ip          VARCHAR(45),
    command            VARCHAR(255),
    method             VARCHAR(10),
    command_parameters JSON,
    response           JSON,
    data_object        VARCHAR(50),
    object_attribute   VARCHAR(50),
    data_was           JSON,
    data_is            JSON,
    http_status_code   INTEGER
);

-- ─── Settings ────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS user_app_settings (
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id  INTEGER NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    settings JSON    NOT NULL DEFAULT '{}'
);

-- Single-row application settings (enforced by CHECK)
CREATE TABLE IF NOT EXISTS app_settings (
    id                  INTEGER PRIMARY KEY DEFAULT 1 CHECK(id = 1),
    display_screen      INTEGER NOT NULL DEFAULT 0,
    status              INTEGER NOT NULL DEFAULT 0,
    status_text         TEXT    NOT NULL DEFAULT 'OK',
    system_status       INTEGER NOT NULL DEFAULT 0,
    system_status_text  TEXT    NOT NULL DEFAULT 'System OK',
    header              TEXT    NOT NULL DEFAULT 'SCADA System',
    settings            JSON    NOT NULL DEFAULT '{}'
);

-- ─── Indexes ─────────────────────────────────────────────────────────────────

CREATE INDEX IF NOT EXISTS idx_sessions_token      ON sessions(token);
CREATE INDEX IF NOT EXISTS idx_sessions_user_id    ON sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_sessions_expires_at ON sessions(expires_at);
CREATE INDEX IF NOT EXISTS idx_logs_timestamp      ON logs(timestamp);
CREATE INDEX IF NOT EXISTS idx_logs_error_level    ON logs(error_level);
CREATE INDEX IF NOT EXISTS idx_aggind_aggregate    ON aggregate_indicator(aggregate_id);
CREATE INDEX IF NOT EXISTS idx_scrnagg_screen      ON screen_aggregate(screen_id);
CREATE INDEX IF NOT EXISTS idx_user_screen_user    ON user_screen(user_id);
