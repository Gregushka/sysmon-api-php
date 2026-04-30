-- sysmon-api-php: MySQL database schema
-- Run once via setup/install.php

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─── Lookup tables ────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS screen_types (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    settings    JSON,
    PRIMARY KEY (id),
    UNIQUE KEY uq_screen_types_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS indicator_types (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    front_name  VARCHAR(100) NOT NULL,
    description TEXT,
    settings    JSON,
    PRIMARY KEY (id),
    UNIQUE KEY uq_indicator_types_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS units (
    id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name    VARCHAR(100) NOT NULL,
    symbol  VARCHAR(20)  NOT NULL,
    units   VARCHAR(50),
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS control_types (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    front_name  VARCHAR(100),
    description TEXT,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Core entities ────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS screens (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    type_id     INT UNSIGNED NOT NULL,
    name        VARCHAR(255) NOT NULL,
    description VARCHAR(500) NOT NULL DEFAULT '',
    tab_header  VARCHAR(100),
    background  VARCHAR(255),
    settings    JSON         NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_screens_type FOREIGN KEY (type_id) REFERENCES screen_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS aggregates (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    settings    JSON,
    PRIMARY KEY (id),
    UNIQUE KEY uq_aggregates_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS indicators (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ind_id      VARCHAR(100) NOT NULL,
    data_id     VARCHAR(100),
    type_id     INT UNSIGNED NOT NULL,
    label       VARCHAR(100),
    unit_id     INT UNSIGNED,
    top         INT,
    `left`      INT,
    label_font  JSON,
    unit_font   JSON,
    value_font  JSON,
    radius      INT,
    size        INT,
    box         JSON,
    settings    JSON,
    direction   INT,
    PRIMARY KEY (id),
    CONSTRAINT fk_indicators_type FOREIGN KEY (type_id) REFERENCES indicator_types(id),
    CONSTRAINT fk_indicators_unit FOREIGN KEY (unit_id) REFERENCES units(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS controls (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    front_name  VARCHAR(100),
    type_id     INT UNSIGNED,
    description TEXT,
    PRIMARY KEY (id),
    CONSTRAINT fk_controls_type FOREIGN KEY (type_id) REFERENCES control_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS backend_commands (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    command     VARCHAR(100) NOT NULL,
    response    TEXT,
    description TEXT,
    PRIMARY KEY (id),
    UNIQUE KEY uq_backend_commands_command (command)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Roles & groups ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS roles (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    permissions JSON,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `groups` (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    permissions JSON,
    PRIMARY KEY (id),
    UNIQUE KEY uq_groups_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Users ────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    login       VARCHAR(100) NOT NULL,
    password    VARCHAR(255) NOT NULL,
    fname       VARCHAR(50),
    lname       VARCHAR(50),
    pname       VARCHAR(50),
    position    VARCHAR(255),
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_login (login)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sessions (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED NOT NULL,
    token       VARCHAR(255) NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at  DATETIME     NOT NULL,
    ip          VARCHAR(45),
    user_agent  TEXT,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sessions_token (token),
    KEY idx_sessions_expires_at (expires_at),
    CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── M:N junction tables ──────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS user_roles_map (
    id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_roles (user_id, role_id),
    CONSTRAINT fk_urm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_urm_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_groups_map (
    id       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id  INT UNSIGNED NOT NULL,
    group_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_groups (user_id, group_id),
    CONSTRAINT fk_ugm_user  FOREIGN KEY (user_id)  REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_ugm_group FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_screen (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED NOT NULL,
    screen_id   INT UNSIGNED NOT NULL,
    permissions JSON NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_screen (user_id, screen_id),
    CONSTRAINT fk_us_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    CONSTRAINT fk_us_screen FOREIGN KEY (screen_id) REFERENCES screens(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS screen_aggregate (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    screen_id    INT UNSIGNED NOT NULL,
    aggregate_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_screen_aggregate (screen_id, aggregate_id),
    CONSTRAINT fk_sa_screen    FOREIGN KEY (screen_id)    REFERENCES screens(id)    ON DELETE CASCADE,
    CONSTRAINT fk_sa_aggregate FOREIGN KEY (aggregate_id) REFERENCES aggregates(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS aggregate_indicator (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    aggregate_id INT UNSIGNED NOT NULL,
    indicator_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_aggregate_indicator (aggregate_id, indicator_id),
    CONSTRAINT fk_ai_aggregate FOREIGN KEY (aggregate_id) REFERENCES aggregates(id)  ON DELETE CASCADE,
    CONSTRAINT fk_ai_indicator FOREIGN KEY (indicator_id) REFERENCES indicators(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS group_screen (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    group_id  INT UNSIGNED NOT NULL,
    screen_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_group_screen (group_id, screen_id),
    CONSTRAINT fk_gs_group  FOREIGN KEY (group_id)  REFERENCES `groups`(id) ON DELETE CASCADE,
    CONSTRAINT fk_gs_screen FOREIGN KEY (screen_id) REFERENCES screens(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS group_aggregate (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    group_id     INT UNSIGNED NOT NULL,
    aggregate_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_group_aggregate (group_id, aggregate_id),
    CONSTRAINT fk_ga_group     FOREIGN KEY (group_id)     REFERENCES `groups`(id)    ON DELETE CASCADE,
    CONSTRAINT fk_ga_aggregate FOREIGN KEY (aggregate_id) REFERENCES aggregates(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── API command permission system ────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS api_commands (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    command     VARCHAR(255) NOT NULL,
    method      VARCHAR(10)  NOT NULL,
    description TEXT,
    parameters  JSON,
    PRIMARY KEY (id),
    UNIQUE KEY uq_api_commands (command, method)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS role_api_command (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    role_id        INT UNSIGNED NOT NULL,
    api_command_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_role_api_command (role_id, api_command_id),
    CONSTRAINT fk_rac_role    FOREIGN KEY (role_id)        REFERENCES roles(id)        ON DELETE CASCADE,
    CONSTRAINT fk_rac_command FOREIGN KEY (api_command_id) REFERENCES api_commands(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Logs ─────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS logs (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    timestamp          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    error_level        INT          NOT NULL DEFAULT 1,
    caller_ip          VARCHAR(45),
    command            VARCHAR(255),
    method             VARCHAR(10),
    command_parameters JSON,
    response           JSON,
    data_object        VARCHAR(50),
    object_attribute   VARCHAR(50),
    data_was           JSON,
    data_is            JSON,
    http_status_code   INT,
    PRIMARY KEY (id),
    KEY idx_logs_timestamp   (timestamp),
    KEY idx_logs_error_level (error_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Settings ─────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS user_app_settings (
    id       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id  INT UNSIGNED NOT NULL,
    settings JSON NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_app_settings (user_id),
    CONSTRAINT fk_uas_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS app_settings (
    id                  TINYINT UNSIGNED NOT NULL DEFAULT 1,
    display_screen      INT          NOT NULL DEFAULT 0,
    status              INT          NOT NULL DEFAULT 0,
    status_text         VARCHAR(255) NOT NULL DEFAULT 'OK',
    system_status       INT          NOT NULL DEFAULT 0,
    system_status_text  VARCHAR(255) NOT NULL DEFAULT 'System OK',
    header              VARCHAR(255) NOT NULL DEFAULT 'SCADA System',
    settings            JSON         NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Setting definitions & values ─────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS setting_definitions (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT
                  COMMENT 'PK. Referenced by setting_values to bind a stored value to its definition.',

    `key`         VARCHAR(100) NOT NULL
                  COMMENT 'Unique machine-readable identifier used by the API and frontend to look up a setting, e.g. "msg.font_size". Never shown to the user.',

    label         VARCHAR(100) NOT NULL
                  COMMENT 'Setting name, normally above field.',

    description   TEXT NOT NULL
                  COMMENT 'Human-readable explanation rendered directly on the settings screen, normally below field. Adding a new row here is sufficient to surface a new setting in the UI without any frontend code change.',

    `group`       VARCHAR(50) NOT NULL
                  COMMENT 'Logical grouping used to cluster related settings under a shared header on the settings screen, e.g. "messaging", "system", "appearance".',

    data_type     ENUM('string','integer','float','boolean','color','enum') NOT NULL
                  COMMENT 'Tells the frontend which control to render (text input, number slider, color picker, toggle, dropdown). The value in setting_values is always TEXT and cast to this type at read time.',

    options       JSON NULL
                  COMMENT 'JSON array of objects with "label" and "value" keys, populated only when data_type = "enum" or "color", e.g. [{"label":"Light","value":"#ffffff"}]. NULL for all other types.',

    default_value TEXT NULL
                  COMMENT 'Fallback value returned when no row exists in setting_values for the requested (setting_id, user_id) pair. Acts as tier-3 in the resolution chain: user value → global value → this.',

    min_value     DECIMAL(20,6) NULL
                  COMMENT 'Lower bound for numeric types (integer, float). Enforced at the API layer before writing to setting_values. NULL means unbounded.',

    max_value     DECIMAL(20,6) NULL
                  COMMENT 'Upper bound for numeric types (integer, float). Enforced at the API layer before writing to setting_values. NULL means unbounded.',

    regex         TEXT NULL
                  COMMENT 'Validation pattern applied to string-type settings before persisting. NULL means no pattern restriction.',

    scope         ENUM('global','user') NOT NULL
                  COMMENT '"global" means one shared value (user_id = NULL in setting_values) read by everyone. "user" means each user can carry their own row.',

    is_overridable TINYINT(1) NOT NULL DEFAULT 0
                  COMMENT 'Only meaningful when scope = "global". When 1, a user may insert their own row in setting_values to override the global value.',

    editable_by_roles JSON NULL
                  COMMENT 'JSON array of role IDs permitted to write this setting. NULL means no role restriction on writes.',

    visible_to_roles  JSON NULL
                  COMMENT 'JSON array of role IDs permitted to read this setting. NULL means the setting is visible to all authenticated users.',

    PRIMARY KEY (id),
    UNIQUE KEY uq_setting_key (`key`),

    CONSTRAINT chk_min_max
        CHECK (min_value IS NULL OR max_value IS NULL OR min_value <= max_value),

    CONSTRAINT chk_options_requires_enum
        CHECK (options IS NULL OR data_type IN ('enum', 'color')),

    CONSTRAINT chk_overridable_requires_global
        CHECK (is_overridable = 0 OR scope = 'global')

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='One row per setting that can ever exist in the application. Defines the contract (type, limits, scope, access rules).';

CREATE TABLE IF NOT EXISTS setting_values (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT
                COMMENT 'PK.',

    setting_id  INT UNSIGNED NOT NULL
                COMMENT 'FK to setting_definitions.id.',

    user_id     INT UNSIGNED NULL DEFAULT NULL
                COMMENT 'FK to users.id. NULL represents a global (non-user-specific) value. NOTE: MySQL treats each NULL as distinct in a UNIQUE index, so uniqueness of the single global row per setting must be guarded by the application on INSERT/UPDATE.',

    value       TEXT NOT NULL
                COMMENT 'The actual setting value, always stored as TEXT and cast to setting_definitions.data_type at read time.',

    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                COMMENT 'Automatically maintained audit timestamp of the last write.',

    updated_by  INT UNSIGNED NOT NULL
                COMMENT 'FK to users.id. Records which user performed the last write.',

    PRIMARY KEY (id),

    UNIQUE KEY uq_setting_user (setting_id, user_id),

    CONSTRAINT fk_sv_setting  FOREIGN KEY (setting_id)  REFERENCES setting_definitions (id) ON DELETE RESTRICT  ON UPDATE CASCADE,
    CONSTRAINT fk_sv_user     FOREIGN KEY (user_id)     REFERENCES users (id)               ON DELETE CASCADE   ON UPDATE CASCADE,
    CONSTRAINT fk_sv_updater  FOREIGN KEY (updated_by)  REFERENCES users (id)               ON DELETE RESTRICT  ON UPDATE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Stores the live value for each setting. Resolution order at read time: user row → global row (user_id IS NULL) → setting_definitions.default_value.';

SET FOREIGN_KEY_CHECKS = 1;
