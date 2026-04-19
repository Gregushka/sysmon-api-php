-- sysmon-api-php: MySQL database schema
-- Run once via setup/install.php

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─── Lookup tables ────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS screen_types (
    id          INT          NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    settings    JSON,
    PRIMARY KEY (id),
    UNIQUE KEY uq_screen_types_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS indicator_types (
    id          INT          NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    front_name  VARCHAR(100) NOT NULL,
    description TEXT,
    settings    JSON,
    PRIMARY KEY (id),
    UNIQUE KEY uq_indicator_types_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS units (
    id      INT          NOT NULL AUTO_INCREMENT,
    name    VARCHAR(100) NOT NULL,
    symbol  VARCHAR(20)  NOT NULL,
    units   VARCHAR(50),
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS control_types (
    id          INT          NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    front_name  VARCHAR(100),
    description TEXT,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Core entities ────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS screens (
    id          INT          NOT NULL AUTO_INCREMENT,
    type_id     INT          NOT NULL,
    name        VARCHAR(255) NOT NULL,
    description VARCHAR(500) NOT NULL DEFAULT '',
    tab_header  VARCHAR(100),
    background  VARCHAR(255),
    settings    JSON         NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_screens_type FOREIGN KEY (type_id) REFERENCES screen_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS aggregates (
    id          INT          NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    settings    JSON,
    PRIMARY KEY (id),
    UNIQUE KEY uq_aggregates_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS indicators (
    id          INT          NOT NULL AUTO_INCREMENT,
    ind_id      VARCHAR(100) NOT NULL,
    data_id     VARCHAR(100),
    type_id     INT          NOT NULL,
    label       VARCHAR(100),
    unit_id     INT,
    top         INT,
    `left`      INT,
    label_font  JSON,
    unit_font   JSON,
    value_font  JSON,
    radius      INT,
    size        INT,
    box         JSON,
    settings    JSON,
    PRIMARY KEY (id),
    CONSTRAINT fk_indicators_type FOREIGN KEY (type_id) REFERENCES indicator_types(id),
    CONSTRAINT fk_indicators_unit FOREIGN KEY (unit_id) REFERENCES units(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS controls (
    id          INT          NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    front_name  VARCHAR(100),
    type_id     INT,
    description TEXT,
    PRIMARY KEY (id),
    CONSTRAINT fk_controls_type FOREIGN KEY (type_id) REFERENCES control_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS backend_commands (
    id          INT          NOT NULL AUTO_INCREMENT,
    command     VARCHAR(100) NOT NULL,
    response    TEXT,
    description TEXT,
    PRIMARY KEY (id),
    UNIQUE KEY uq_backend_commands_command (command)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Roles & groups ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS roles (
    id          INT          NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    permissions JSON,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `groups` (
    id          INT          NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    permissions JSON,
    PRIMARY KEY (id),
    UNIQUE KEY uq_groups_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Users ────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS users (
    id          INT          NOT NULL AUTO_INCREMENT,
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
    id          INT          NOT NULL AUTO_INCREMENT,
    user_id     INT          NOT NULL,
    token       VARCHAR(255) NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at  DATETIME     NOT NULL,
    ip          VARCHAR(45),
    user_agent  TEXT,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sessions_token (token),
    CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── M:N junction tables ──────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS user_roles_map (
    id      INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_roles (user_id, role_id),
    CONSTRAINT fk_urm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_urm_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_groups_map (
    id       INT NOT NULL AUTO_INCREMENT,
    user_id  INT NOT NULL,
    group_id INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_groups (user_id, group_id),
    CONSTRAINT fk_ugm_user  FOREIGN KEY (user_id)  REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_ugm_group FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_screen (
    id          INT  NOT NULL AUTO_INCREMENT,
    user_id     INT  NOT NULL,
    screen_id   INT  NOT NULL,
    permissions JSON NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_screen (user_id, screen_id),
    CONSTRAINT fk_us_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    CONSTRAINT fk_us_screen FOREIGN KEY (screen_id) REFERENCES screens(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS screen_aggregate (
    id           INT NOT NULL AUTO_INCREMENT,
    screen_id    INT NOT NULL,
    aggregate_id INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_screen_aggregate (screen_id, aggregate_id),
    CONSTRAINT fk_sa_screen    FOREIGN KEY (screen_id)    REFERENCES screens(id)    ON DELETE CASCADE,
    CONSTRAINT fk_sa_aggregate FOREIGN KEY (aggregate_id) REFERENCES aggregates(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS aggregate_indicator (
    id           INT NOT NULL AUTO_INCREMENT,
    aggregate_id INT NOT NULL,
    indicator_id INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_aggregate_indicator (aggregate_id, indicator_id),
    CONSTRAINT fk_ai_aggregate FOREIGN KEY (aggregate_id) REFERENCES aggregates(id)  ON DELETE CASCADE,
    CONSTRAINT fk_ai_indicator FOREIGN KEY (indicator_id) REFERENCES indicators(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS group_screen (
    id        INT NOT NULL AUTO_INCREMENT,
    group_id  INT NOT NULL,
    screen_id INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_group_screen (group_id, screen_id),
    CONSTRAINT fk_gs_group  FOREIGN KEY (group_id)  REFERENCES `groups`(id) ON DELETE CASCADE,
    CONSTRAINT fk_gs_screen FOREIGN KEY (screen_id) REFERENCES screens(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS group_aggregate (
    id           INT NOT NULL AUTO_INCREMENT,
    group_id     INT NOT NULL,
    aggregate_id INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_group_aggregate (group_id, aggregate_id),
    CONSTRAINT fk_ga_group     FOREIGN KEY (group_id)     REFERENCES `groups`(id)    ON DELETE CASCADE,
    CONSTRAINT fk_ga_aggregate FOREIGN KEY (aggregate_id) REFERENCES aggregates(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── API command permission system ────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS api_commands (
    id          INT          NOT NULL AUTO_INCREMENT,
    command     VARCHAR(255) NOT NULL,
    method      VARCHAR(10)  NOT NULL,
    description TEXT,
    parameters  JSON,
    PRIMARY KEY (id),
    UNIQUE KEY uq_api_commands (command, method)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS role_api_command (
    id             INT NOT NULL AUTO_INCREMENT,
    role_id        INT NOT NULL,
    api_command_id INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_role_api_command (role_id, api_command_id),
    CONSTRAINT fk_rac_role    FOREIGN KEY (role_id)        REFERENCES roles(id)        ON DELETE CASCADE,
    CONSTRAINT fk_rac_command FOREIGN KEY (api_command_id) REFERENCES api_commands(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Logs ─────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS logs (
    id                 INT          NOT NULL AUTO_INCREMENT,
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
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Settings ─────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS user_app_settings (
    id       INT  NOT NULL AUTO_INCREMENT,
    user_id  INT  NOT NULL,
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

-- ─── Indexes ──────────────────────────────────────────────────────────────────

CREATE INDEX idx_sessions_user_id    ON sessions(user_id);
CREATE INDEX idx_sessions_expires_at ON sessions(expires_at);
CREATE INDEX idx_logs_timestamp      ON logs(timestamp);
CREATE INDEX idx_logs_error_level    ON logs(error_level);
CREATE INDEX idx_aggind_aggregate    ON aggregate_indicator(aggregate_id);
CREATE INDEX idx_scrnagg_screen      ON screen_aggregate(screen_id);
CREATE INDEX idx_user_screen_user    ON user_screen(user_id);

SET FOREIGN_KEY_CHECKS = 1;
