-- sysmon-api-php: initial seed data
-- All inserts use INSERT IGNORE so this file is safe to re-run.



-- ─── Screen types ────────────────────────────────────────────────────────────

INSERT IGNORE INTO screen_types (id, name, description) VALUES
(1, 'indicators',  'Standard display screen: indicators reflect system status'),
(2, 'users',       'List users and enables to create, delete, update users, groups and roles'),
(3, 'positioning', 'Drag and drop positioning of indicators and controls on the selected background, adjust coords manually'),
(4, 'sensors',     'Sensor management'),
(5, 'settings',    'General application settings, normally site specific'),
(6, 'aggregates',  'Aggregates management: list and setup');

-- ─── Indicator types ─────────────────────────────────────────────────────────

INSERT IGNORE INTO indicator_types (id, name, front_name, description) VALUES
(1, 'dig_ind',     'DigitalIndicator',    NULL),
(2, 'dig_round',   'DigitalRound',        NULL),
(3, 'on_off',      'OnOffIndicator',      NULL),
(4, 'pump_ind',    'PumpIndicator',       NULL),
(5, 'burner_gas',  'GasBurnerIndicator',  NULL),
(6, 'flap_binary', 'GasFlapIndicator',    NULL),
(7, 'tmprtr_ind',  'TempIndicator',       NULL);

-- ─── Units ───────────────────────────────────────────────────────────────────

INSERT IGNORE INTO units (id, name, symbol, units) VALUES
(1, 'Pressure',              'b',    'bar'),
(2, 'Temperature',           '°C',   'celsius'),
(3, 'Voltage',               'V',    'volt'),
(4, 'Rotation',              'rpm',  'rpm'),
(5, 'Speed',                 'kmh',  'kmh'),
(6, 'Percentage',            '%',    'percent'),
(7, 'Litres',                'L',    'litres'),
(8, 'Cubic meters per hour', 'm³/h', 'm³/h');

-- ─── Control types ───────────────────────────────────────────────────────────

INSERT IGNORE INTO control_types (id, name, front_name, description) VALUES
(1, 'Switch NC', 'Switch_NC', 'Normally closed switch'),
(2, 'Switch NO', 'Switch_NO', 'Normally opened switch');

-- ─── Backend commands ────────────────────────────────────────────────────────

INSERT IGNORE INTO backend_commands (command, response, description) VALUES
('force_update',         NULL, 'Use all indicator static data to update them at front and switch to specified screen if set'),
('front_version',        NULL, 'Response with front version'),
('available_indicators', NULL, 'List all indicator types defined at front'),
('available_screens',    NULL, 'List all screen types defined at front');

-- ─── Roles ───────────────────────────────────────────────────────────────────

INSERT IGNORE INTO roles (id, name, description, permissions) VALUES
(1, 'operator', 'Can view sensor data and basic operations',    '{"read":true,"write":false,"admin":false}'),
(2, 'manager',  'Can manage shifts and view all data',          '{"read":true,"write":true,"admin":false}'),
(3, 'admin',    'Can manage users and indicators',              '{"read":true,"write":true,"admin":true}'),
(4, 'almighty', 'Has full system access with no restrictions',  '{"read":true,"write":true,"admin":true,"superadmin":true}');

-- ─── Groups ──────────────────────────────────────────────────────────────────

INSERT IGNORE INTO `groups` (id, name, description) VALUES
(1, 'crema',      NULL),
(2, 'crema_full', NULL);

-- ─── Screens ─────────────────────────────────────────────────────────────────

INSERT IGNORE INTO screens (id, type_id, name, description, tab_header, background, settings) VALUES
(1, 1, 'Pumps',       'Pumping Pumps Screen',                       'Pumps',       'diagram1.png', '{}'),
(2, 1, 'Burners',     'Burning Burners Screen',                     'Burners',     'diagram2.png', '{}'),
(3, 2, 'Users',       'User Management Screen',                     'Users',       'default',      '{}'),
(4, 5, 'Settings',    'Application Settings',                       'Settings',    'default',      '{}'),
(5, 3, 'Positioning', 'Indicator Positioning',                      'Positioning', 'default',      '{}'),
(6, 4, 'Sensors',     'Sensor Settings',                            'Sensors',     NULL,           '{}'),
(7, 6, 'Aggregates',  'Aggregates Management',                      'Aggregates',  'default',      '{}');

-- ─── Aggregates ──────────────────────────────────────────────────────────────

INSERT IGNORE INTO aggregates (id, name, description) VALUES
(1, 'aggr_1', 'Pumps aggregate'),
(2, 'aggr_2', 'Burners aggregate');

-- ─── Indicators ──────────────────────────────────────────────────────────────

INSERT IGNORE INTO indicators (id, ind_id, data_id, type_id, label, unit_id, top, `left`, label_font, unit_font, value_font, radius, size, box, settings, direction) VALUES
(1,  'te_1-1',      'te_1-1',  1, 'TE1',       2,  614, 2292, NULL, NULL, NULL, 45,   NULL, NULL, '{"fontSize": 30, "defaultBg": "#0a1a2a"}', NULL),
(2,  'pe_1-1',      'pe_1-1',  2, 'PE1',       1,  625, 2140, NULL, NULL, NULL, 30,   NULL, NULL, '{"fontSize": 30, "defaultBg": "#0a1a2a"}', NULL),
(3,  'te_1-2',      NULL,      1, 'TE2',       2,  945, 1850, NULL, NULL, NULL, NULL, NULL, NULL, '{"fontSize": 26}', NULL),
(4,  'pe_1-2',      'pe_1-2',  2, 'PE2',       1,  960, 1700, NULL, NULL, NULL, 30,   NULL, NULL, '{"fontSize": 30, "defaultBg": "#0a1a2a"}', NULL),
(5,  'te_1-3',      NULL,      1, 'TE3',       2,  140, 2420, NULL, NULL, NULL, NULL, NULL, NULL, '{"fontSize": 26}', NULL),
(6,  'pe_1-3',      'pe_1-3',  2, 'PE3',       1,  160, 2280, NULL, NULL, NULL, 30,   NULL, NULL, '{"fontSize": 30, "defaultBg": "#0a1a2a"}', NULL),
(7,  'te_1-4',      NULL,      1, 'TE4',       2,  320, 2430, NULL, NULL, NULL, NULL, NULL, NULL, '{"fontSize": 26}', NULL),
(8,  'pe_1-4',      'pe_1-4',  2, 'PE4',       1,  390, 2310, NULL, NULL, NULL, 30,   NULL, NULL, '{"fontSize": 30, "defaultBg": "#0a1a2a"}', NULL),
(9,  'te_1-5',      NULL,      1, 'TE5',       2,  164,  696, NULL, NULL, NULL, NULL, NULL, NULL, '{"fontSize": 26}', NULL),
(10, 'pe_1-5',      'pe_1-5',  2, 'PE5',       1,  179,  555, NULL, NULL, NULL, 30,   NULL, NULL, '{"fontSize": 30, "defaultBg": "#0a1a2a"}', NULL),
(11, 'pe_1-6',      'pe_1-6',  2, 'PE6',       1,  960,  530, NULL, NULL, NULL, 30,   NULL, NULL, '{"fontSize": 30, "defaultBg": "#0a1a2a"}', NULL),
(12, 'te_1-6',      NULL,      1, 'TE6',       2,  945,  675, NULL, NULL, NULL, NULL, NULL, NULL, '{"fontSize": 26}', NULL),
(13, 'te_1-7',      NULL,      1, 'TE7',       2,  230,   90, NULL, NULL, NULL, NULL, NULL, NULL, '{"fontSize": 26}', NULL),
(14, 'di-pressure', NULL,      1, 'Pressure',  1, 1400, 2050, NULL, NULL, NULL, NULL, NULL, NULL, '{"color": "#00ffff", "fontSize": 26}', NULL),
(15, 'di-valve',    NULL,      1, 'Valve',     6, 1400, 2200, NULL, NULL, NULL, NULL, NULL, NULL, '{"color": "#00ffff", "fontSize": 26}', NULL),
(16, 'pump1',       NULL,      4, 'PumpOne',   NULL, 311, 1141, NULL, NULL, NULL, 30,  60,  NULL, '{"bg_id": "diagram1"}', NULL),
(17, 'pds3',        NULL,      3, 'PDS3',      NULL, 276, 1017, NULL, NULL, NULL, NULL, 60, NULL, '{"bg_id": "diagram1", "fontSize": 26}', NULL),
(18, 'pds1',        NULL,      3, 'PDS 1',     NULL, 537, 1817, NULL, NULL, NULL, 30,  NULL, NULL, '{"bg_id": "diagram1", "color": "#00e676"}', NULL),
(19, 'cooling',     NULL,      3, 'Cooling',   NULL, 1299, 675, NULL, NULL, NULL, 36,  NULL, NULL, '{"bg_id": "diagram2", "color": "#00e676"}', NULL),
(20, 'chimney',     NULL,      3, 'Chimney',   NULL, 1255, 1252, NULL, NULL, NULL, 36, NULL, NULL, '{"bg_id": "diagram2", "color": "#00e676"}', NULL),
(21, 'cyclon',      NULL,      3, 'Cyclon',    NULL, 1238, 1116, NULL, NULL, NULL, 36, NULL, NULL, '{"bg_id": "diagram2", "color": "#00e676"}', NULL),
(22, 'te_2-1',      NULL,      1, 'TE1',       2, 1147,  180, NULL, NULL, NULL, NULL, NULL, NULL, '{"bg_id": "diagram2", "fontSize": 26}', NULL),
(23, 'te_2-2',      NULL,      1, 'TE2',       2, 1295,  435, NULL, NULL, NULL, NULL, NULL, NULL, '{"bg_id": "diagram2", "fontSize": 26}', NULL),
(24, 'te_2-3',      NULL,      1, 'TE3',       2,  180, 1914, NULL, NULL, NULL, NULL, NULL, NULL, '{"bg_id": "diagram2", "fontSize": 26}', NULL),
(25, 'te_2-4',      NULL,      1, 'TE4',       2,  912,  560, NULL, NULL, NULL, NULL, NULL, NULL, '{"bg_id": "diagram2", "fontSize": 26}', NULL),
(26, 'te_2-5',      NULL,      1, 'TE5',       2,  901, 1058, NULL, NULL, NULL, NULL, NULL, NULL, '{"bg_id": "diagram2", "fontSize": 26}', NULL),
(27, 'te_2-6',      NULL,      1, 'TE6',       2,  750, 1515, NULL, NULL, NULL, NULL, NULL, NULL, '{"bg_id": "diagram2", "fontSize": 26}', NULL),
(28, 'burner_one',  NULL,      5, 'Burner1',   NULL, 1300, 280, NULL, NULL, NULL, NULL, NULL, NULL, '{"bg_id": "diagram2", "scale": 0.9, "fontsize": 26, "burnerType": "small"}', 90),
(29, 'burner_two',  NULL,      5, 'Burner2',   NULL, 1150, 570, NULL, NULL, NULL, NULL, NULL, NULL, '{"bg_id": "diagram2", "scale": 0.9, "fontsize": 26, "burnerType": "small"}', 270),
(30, 'burner_three',NULL,      5, 'Burner3',   NULL,  500, 1520, NULL, NULL, NULL, NULL, NULL, NULL, '{"bg_id": "diagram2", "scale": 0.9, "fontsize": 26, "burnerType": "small"}', 90),
(31, 'burner_flap', NULL,      6, 'Заслонка',  NULL,  930,  930, NULL, NULL, NULL, NULL, NULL, NULL, '{"size": 100, "bg_id": "diagram2", "fontsize": 26, "labelBypass": "Мимо", "labelChimney": "Выхлоп"}', 90),
(32, 'di-flow',     NULL,      1, 'Flow Rate', 8,    50,  250, NULL, NULL, NULL, NULL, NULL, NULL, '{"bg_id": "diagram2", "fontSize": 26}', NULL),
(33, 'pump1-d2',    NULL,      4, 'PumpOne',   NULL,  70,  400, NULL, NULL, NULL, NULL, 90,  NULL, '{"bg_id": "diagram2"}', NULL),
(34, 'di-vol',      NULL,      1, 'Volume',    7,    50,  100, NULL, NULL, NULL, NULL, NULL, NULL, '{"bg_id": "diagram2", "fontSize": 26}', NULL),
(35, 'pds2',        NULL,      3, 'PDS 2',     NULL, 1300, 550, NULL, NULL, NULL, 36,  NULL, NULL, '{"bg_id": "diagram2", "color": "#00e676"}', NULL),
(36, 'pumpGVS',     NULL,      4, 'PumpGVS',   NULL,  312, 2166, NULL, NULL, NULL, NULL, 60, NULL, '{"bg_id": "diagram1"}', 270),
(37, 'pds2',        NULL,      3, 'PDS2',      NULL,  455, 2167, NULL, NULL, NULL, 30,  NULL, NULL, '{"bg_id": "diagram1", "color": "#00e676"}', NULL),
(38, 'pds4',        NULL,      3, 'PDS4',      NULL,  795,  230, NULL, NULL, NULL, 30,  NULL, NULL, '{"bg_id": "diagram1", "color": "#00e676"}', NULL),
(39, 'pump_k3_1',   NULL,      4, 'Pump_K3_1', NULL,  660, 1792, NULL, NULL, NULL, NULL, 60, NULL, '{"bg_id": "diagram1"}', 90),
(40, 'pump_k3_2',   NULL,      4, 'Pump_K3_2', NULL,  768, 1792, NULL, NULL, NULL, NULL, 60, NULL, '{"bg_id": "diagram1"}', 90),
(41, 'pump_k2',     NULL,      4, 'Pump_K2',   NULL,  845,  280, NULL, NULL, NULL, NULL, 60, NULL, '{"bg_id": "diagram1"}', 0);

-- ─── Screen ↔ Aggregate ──────────────────────────────────────────────────────

INSERT IGNORE INTO screen_aggregate (screen_id, aggregate_id) VALUES
(1, 1),  -- Pumps → aggr_1
(2, 2);  -- Burners → aggr_2

-- ─── Aggregate ↔ Indicator ───────────────────────────────────────────────────
-- aggr_1 (Pumps):   indicators 1-18, 36-41
-- aggr_2 (Burners): indicators 19-35

INSERT IGNORE INTO aggregate_indicator (aggregate_id, indicator_id) VALUES
(1, 1),(1, 2),(1, 3),(1, 4),(1, 5),(1, 6),(1, 7),(1, 8),(1, 9),(1,10),
(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,18),
(1,36),(1,37),(1,38),(1,39),(1,40),(1,41),
(2,19),(2,20),(2,21),(2,22),(2,23),(2,24),(2,25),(2,26),(2,27),
(2,28),(2,29),(2,30),(2,31),(2,32),(2,33),(2,34),(2,35);

-- ─── Group → Screen / Aggregate ──────────────────────────────────────────────

INSERT IGNORE INTO group_aggregate (group_id, aggregate_id) VALUES
(1, 1);  -- crema → aggr_1

INSERT IGNORE INTO group_screen (group_id, screen_id) VALUES
(2, 1),  -- crema_full → Pumps
(2, 2);  -- crema_full → Burners

-- ─── API commands ────────────────────────────────────────────────────────────

INSERT IGNORE INTO api_commands (id, command, method, description) VALUES
( 1, '/v1/auth',                    'GET',    'Authenticate user'),
( 2, '/v1/users',                   'GET',    'List all users'),
( 3, '/v1/users/:id',               'GET',    'Get user by ID'),
( 4, '/v1/users',                   'POST',   'Create user'),
( 5, '/v1/users/:id',               'PUT',    'Update user'),
( 6, '/v1/users/:id',               'DELETE', 'Delete user'),
( 7, '/v1/users/:id/roles',         'POST',   'Assign roles to user'),
( 8, '/v1/users/:id/groups',        'POST',   'Assign groups to user'),
( 9, '/v1/screen',                  'GET',    'List all screens'),
(10, '/v1/screen/:id',              'GET',    'Get screen by ID'),
(11, '/v1/screen',                  'POST',   'Create screen'),
(12, '/v1/screen/:id',              'PUT',    'Update screen'),
(13, '/v1/screen/:id',              'DELETE', 'Delete screen'),
(14, '/v1/indicators',              'GET',    'Get all indicator values'),
(15, '/v1/indicators/:screen_id',   'GET',    'Get indicator values for screen'),
(16, '/v1/indicators/:screen_id/:ind_id', 'GET', 'Get specific indicator value'),
(17, '/v1/position/:ind_id',        'POST',   'Set indicator position'),
(18, '/v1/roles',                   'GET',    'List roles'),
(19, '/v1/roles',                   'POST',   'Create role'),
(20, '/v1/roles/:id',               'PUT',    'Update role'),
(21, '/v1/roles/:id',               'DELETE', 'Delete role'),
(22, '/v1/groups',                  'GET',    'List groups'),
(23, '/v1/groups',                  'POST',   'Create group'),
(24, '/v1/groups/:id',              'PUT',    'Update group'),
(25, '/v1/groups/:id',              'DELETE', 'Delete group'),
(26, '/v1/logs',                    'GET',    'Read logs'),
(27, '/v1/logs',                    'DELETE', 'Clear logs'),
(28, '/v1/data',                    'GET',    'Get live indicator data (mocked)'),
(29, '/v1/commands',                'GET',    'List backend commands'),
(30, '/v1/controls',                'GET',    'List controls'),
(31, '/v1/settings',                'GET',    'Get user app settings'),
(32, '/v1/settings',                'POST',   'Save user app settings');

-- ─── Role → API command permissions ─────────────────────────────────────────
-- operator (1): read-only access to own data, screens, indicators, live data + settings
INSERT IGNORE INTO role_api_command (role_id, api_command_id) VALUES
(1,1),(1,3),(1,9),(1,10),(1,14),(1,15),(1,16),(1,28),(1,29),(1,30),(1,31),(1,32);

-- manager (2): operator + full user list, roles list, groups list, logs read + settings
INSERT IGNORE INTO role_api_command (role_id, api_command_id) VALUES
(2,1),(2,2),(2,3),(2,9),(2,10),(2,14),(2,15),(2,16),(2,18),(2,22),(2,26),(2,28),(2,29),(2,30),(2,31),(2,32);

-- admin (3): manager + full user/screen/indicator/role/group management (no log clear, no delete roles/groups) + settings
INSERT IGNORE INTO role_api_command (role_id, api_command_id) VALUES
(3,1),(3,2),(3,3),(3,4),(3,5),(3,6),(3,7),(3,8),
(3,9),(3,10),(3,11),(3,12),(3,13),(3,14),(3,15),(3,16),(3,17),
(3,18),(3,19),(3,20),(3,22),(3,23),(3,24),(3,26),(3,28),(3,29),(3,30),(3,31),(3,32);

-- almighty (4): everything
INSERT IGNORE INTO role_api_command (role_id, api_command_id) VALUES
(4,1),(4,2),(4,3),(4,4),(4,5),(4,6),(4,7),(4,8),
(4,9),(4,10),(4,11),(4,12),(4,13),(4,14),(4,15),(4,16),(4,17),
(4,18),(4,19),(4,20),(4,21),(4,22),(4,23),(4,24),(4,25),
(4,26),(4,27),(4,28),(4,29),(4,30),(4,31),(4,32);

-- ─── App settings (single row) ───────────────────────────────────────────────

INSERT IGNORE INTO app_settings (id, display_screen, status, status_text, system_status, system_status_text, header) VALUES
(1, 0, 0, 'OK', 0, 'System OK', 'SCADA System');

-- ─── Setting definitions ──────────────────────────────────────────────────────

INSERT IGNORE INTO setting_definitions
    (`key`, label, description, `group`, data_type, options, default_value, min_value, max_value, regex, scope, is_overridable, editable_by_roles, visible_to_roles)
VALUES
(
    'msg_txt_size',
    'Размер текста (px)',
    'Размер шрифта сообщений (10–100 px)',
    'msg_window_settings',
    'integer',
    NULL,
    '20',
    10, 100,
    NULL,
    'global', 1, NULL, NULL
),
(
    'msg_txt_color',
    'Цвет текста',
    'Цвет основного текста сообщений',
    'msg_window_settings',
    'color',
    '[{"label":"White","value":"#ffffff"},{"label":"Light Grey","value":"#e0e0e0"},{"label":"Cyan","value":"#7ec8e3"},{"label":"Green","value":"#39ff14"},{"label":"Amber","value":"#ffaa00"},{"label":"Red","value":"#ff4444"},{"label":"Bright Green","value":"#00e676"},{"label":"Pink","value":"#ff80ab"},{"label":"Blue","value":"#82b1ff"},{"label":"Yellow","value":"#ffd740"}]',
    '#ffffff',
    NULL, NULL,
    NULL,
    'global', 1, NULL, NULL
),
(
    'msg_window_size',
    'Размер окна (строк)',
    'Количество видимых строк в буфере сообщений (1–200)',
    'msg_window_settings',
    'integer',
    NULL,
    '10',
    1, 200,
    NULL,
    'global', 1, NULL, NULL
),
(
    'polling_interval',
    'Интервал опроса (мс)',
    '0 = использовать случайные тестовые данные',
    'system_settings',
    'integer',
    NULL,
    '2000',
    100, 120000,
    NULL,
    'global', 1, '[3,4]', NULL
),
(
    'tab_text_size',
    'Размер текста вкладок (px)',
    '',
    'system_settings',
    'integer',
    NULL,
    '13',
    5, 30,
    NULL,
    'global', 1, NULL, NULL
);
