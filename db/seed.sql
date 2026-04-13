-- sysmon-api-php: initial seed data
-- All inserts use INSERT OR IGNORE so this file is safe to re-run.

PRAGMA foreign_keys = ON;

-- ─── Screen types ────────────────────────────────────────────────────────────

INSERT OR IGNORE INTO screen_types (id, name, description) VALUES
(1, 'indicators',  'Standard display screen: indicators reflect system status'),
(2, 'users',       'List users and enables to create, delete, update users, groups and roles'),
(3, 'positioning', 'Drag and drop positioning of indicators and controls on the selected background, adjust coords manually'),
(4, 'sensors',     'Sensor management'),
(5, 'settings',    'General application settings, normally site specific'),
(6, 'aggregates',  'Aggregates management: list and setup');

-- ─── Indicator types ─────────────────────────────────────────────────────────

INSERT OR IGNORE INTO indicator_types (id, name, front_name, description) VALUES
(1, 'dig_ind',     'DigitalIndicator',    NULL),
(2, 'dig_round',   'DigitalRound',        NULL),
(3, 'on_off',      'OnOffIndicator',      NULL),
(4, 'pump_ind',    'PumpIndicator',       NULL),
(5, 'burner_gas',  'GasBurnerIndicator',  NULL),
(6, 'flap_binary', 'GasFlapIndicator',    NULL),
(7, 'tmprtr_ind',  'TempIndicator',       NULL);

-- ─── Units ───────────────────────────────────────────────────────────────────

INSERT OR IGNORE INTO units (id, name, symbol, units) VALUES
(1, 'Pressure',              'b',    'bar'),
(2, 'Temperature',           '°C',   'celsius'),
(3, 'Voltage',               'V',    'volt'),
(4, 'Rotation',              'rpm',  'rpm'),
(5, 'Speed',                 'kmh',  'kmh'),
(6, 'Percentage',            '%',    'percent'),
(7, 'Litres',                'L',    'litres'),
(8, 'Cubic meters per hour', 'm³/h', 'm³/h');

-- ─── Control types ───────────────────────────────────────────────────────────

INSERT OR IGNORE INTO control_types (id, name, front_name, description) VALUES
(1, 'Switch NC', 'Switch_NC', 'Normally closed switch'),
(2, 'Switch NO', 'Switch_NO', 'Normally opened switch');

-- ─── Backend commands ────────────────────────────────────────────────────────

INSERT OR IGNORE INTO backend_commands (command, response, description) VALUES
('force_update',         NULL, 'Use all indicator static data to update them at front and switch to specified screen if set'),
('front_version',        NULL, 'Response with front version'),
('available_indicators', NULL, 'List all indicator types defined at front'),
('available_screens',    NULL, 'List all screen types defined at front');

-- ─── Roles ───────────────────────────────────────────────────────────────────

INSERT OR IGNORE INTO roles (id, name, description, permissions) VALUES
(1, 'operator', 'Can view sensor data and basic operations',    '{"read":true,"write":false,"admin":false}'),
(2, 'manager',  'Can manage shifts and view all data',          '{"read":true,"write":true,"admin":false}'),
(3, 'admin',    'Can manage users and indicators',              '{"read":true,"write":true,"admin":true}'),
(4, 'almighty', 'Has full system access with no restrictions',  '{"read":true,"write":true,"admin":true,"superadmin":true}');

-- ─── Groups ──────────────────────────────────────────────────────────────────

INSERT OR IGNORE INTO groups (id, name, description) VALUES
(1, 'crema',      NULL),
(2, 'crema_full', NULL);

-- ─── Screens ─────────────────────────────────────────────────────────────────

INSERT OR IGNORE INTO screens (id, type_id, name, description, tab_header, background, settings) VALUES
(1, 1, 'Pumps',       'Pumping Pumps Screen',                       'Pumps',       'diagram1.png', '{}'),
(2, 1, 'Burners',     'Burning Burners Screen',                     'Burners',     'diagram2.png', '{}'),
(3, 2, 'Users',       'User Management Screen',                     'Users',       'default',      '{}'),
(4, 5, 'Settings',    'Application Settings',                       'Settings',    'default',      '{}'),
(5, 3, 'Positioning', 'Indicator Positioning',                      'Positioning', 'default',      '{}'),
(6, 4, 'Sensors',     'Sensor Settings',                            'Sensors',     NULL,           '{}'),
(7, 6, 'Aggregates',  'Aggregates Management',                      'Aggregates',  'default',      '{}');

-- ─── Aggregates ──────────────────────────────────────────────────────────────

INSERT OR IGNORE INTO aggregates (id, name, description) VALUES
(1, 'aggr_1', 'Pumps aggregate'),
(2, 'aggr_2', 'Burners aggregate');

-- ─── Indicators ──────────────────────────────────────────────────────────────
-- type_id resolved via front_name; unit_id is the units.id from the design doc

INSERT OR IGNORE INTO indicators (id, ind_id, data_id, type_id, label, unit_id, top,  left,  radius, size, box, settings) VALUES
(1,  'te_1-1',     'te_1-1',  1, 'TE1',       2,  830,  1960, 45,   NULL, NULL, '{"fontSize":30,"defaultBg":"#0a1a2a"}'),
(2,  'pe_1-1',     'pe_1-1',  2, 'PE1',       1,  790,  1580, 45,   NULL, NULL, '{"fontSize":30,"defaultBg":"#0a1a2a"}'),
(3,  'te_1-2',     NULL,      1, 'TE2',       2, 1270,  1425, NULL, NULL, NULL, '{"fontSize":26}'),
(4,  'pe_1-2',     'pe_1-2',  2, 'PE2',       1, 1300,  1250, 45,   NULL, NULL, '{"fontSize":30,"defaultBg":"#0a1a2a"}'),
(5,  'te_1-3',     NULL,      1, 'TE3',       2,  130,  1860, NULL, NULL, NULL, '{"fontSize":26}'),
(6,  'pe_1-3',     'pe_1-3',  2, 'PE3',       1,  190,  1680, 45,   NULL, NULL, '{"fontSize":30,"defaultBg":"#0a1a2a"}'),
(7,  'te_1-4',     NULL,      1, 'TE4',       2,  445,  1870, NULL, NULL, NULL, '{"fontSize":26}'),
(8,  'pe_1-4',     'pe_1-4',  2, 'PE4',       1,  640,  1810, 45,   NULL, NULL, '{"fontSize":30,"defaultBg":"#0a1a2a"}'),
(9,  'te_1-5',     NULL,      1, 'TE5',       2,  380,   540, NULL, NULL, NULL, '{"fontSize":26}'),
(10, 'pe_1-5',     'pe_1-5',  2, 'PE5',       1,  400,   440, 45,   NULL, NULL, '{"fontSize":30,"defaultBg":"#0a1a2a"}'),
(11, 'pe_1-6',     'pe_1-6',  2, 'PE6',       1, 1200,   410, 45,   NULL, NULL, '{"fontSize":30,"defaultBg":"#0a1a2a"}'),
(12, 'te_1-6',     NULL,      1, 'TE6',       2, 1180,   510, NULL, NULL, NULL, '{"fontSize":26}'),
(13, 'te_1-7',     NULL,      1, 'TE7',       2,  450,    90, NULL, NULL, NULL, '{"fontSize":26}'),
(14, 'di-pressure',NULL,      1, 'Pressure',  1, 1270,  1290, NULL, NULL, NULL, '{"fontSize":26,"color":"#00ffff"}'),
(15, 'di-valve',   NULL,      1, 'Valve',     6,  800,  1180, NULL, NULL, NULL, '{"fontSize":26,"color":"#00ffff"}'),
(16, 'pump1',      NULL,      4, 'PumpOne',   NULL, 435,  895, NULL, 60,  NULL, '{"bg_id":"diagram1"}'),
(17, 'pds3',       NULL,      4, 'PDS3',      NULL, 385,  803, NULL, 60,  NULL, '{"bg_id":"diagram1","fontSize":26}'),
(18, 'pds1',       NULL,      3, 'PDS 1',     NULL, 500,  900, 36,   NULL, NULL, '{"bg_id":"diagram1","color":"#00e676"}'),
(19, 'cooling',    NULL,      3, 'Cooling',   NULL,1495,   548, 36,  NULL, NULL, '{"bg_id":"diagram2","color":"#00e676"}'),
(20, 'chimney',    NULL,      3, 'Chimney',   NULL,1210,  1160, 36,  NULL, NULL, '{"bg_id":"diagram2","color":"#00e676"}'),
(21, 'cyclon',     NULL,      3, 'Cyclon',    NULL,1240,  1035, 36,  NULL, NULL, '{"bg_id":"diagram2","color":"#00e676"}'),
(22, 'te_2-1',     NULL,      1, 'TE1',       2, 1215,   240, NULL, NULL, NULL, '{"bg_id":"diagram2","fontSize":26}'),
(23, 'te_2-2',     NULL,      1, 'TE2',       2, 1645,   335, NULL, NULL, NULL, '{"bg_id":"diagram2","fontSize":26}'),
(24, 'te_2-3',     NULL,      1, 'TE3',       2,  100,  1660, NULL, NULL, NULL, '{"bg_id":"diagram2","fontSize":26}'),
(25, 'te_2-4',     NULL,      1, 'TE4',       2,  860,   560, NULL, NULL, NULL, '{"bg_id":"diagram2","fontSize":26}'),
(26, 'te_2-5',     NULL,      1, 'TE5',       2,  860,  1035, NULL, NULL, NULL, '{"bg_id":"diagram2","fontSize":26}'),
(27, 'te_2-6',     NULL,      1, 'TE6',       2,  550,  1150, NULL, NULL, NULL, '{"bg_id":"diagram2","fontSize":26}'),
(28, 'burner_one', NULL,      5, 'Burner1',   NULL,1325,  260, NULL, NULL, NULL, '{"bg_id":"diagram2","fontsize":26,"burnerType":"small","scale":0.9}'),
(29, 'burner_two', NULL,      5, 'Burner2',   NULL,1550,  260, NULL, NULL, NULL, '{"bg_id":"diagram2","fontsize":26,"burnerType":"small","scale":0.9}'),
(30, 'burner_three',NULL,     5, 'Burner3',   NULL, 430, 1190, NULL, NULL, NULL, '{"bg_id":"diagram2","fontsize":26,"burnerType":"small","scale":0.9}'),
(31, 'burner_flap',NULL,      6, 'Заслонка',  NULL, 900,  650, NULL, NULL, NULL, '{"bg_id":"diagram2","fontsize":26,"labelChimney":"Выхлоп","labelBypass":"Мимо","size":100}'),
(32, 'di-flow',    NULL,      1, 'Flow Rate', 8,   300,   600, NULL, NULL, NULL, '{"bg_id":"diagram2","fontSize":26}'),
(33, 'pump1-d2',   NULL,      4, 'PumpOne',   NULL, 600,  400, NULL, 90,  NULL, '{"bg_id":"diagram2"}'),
(34, 'di-vol',     NULL,      1, 'Volume',    7,   400,   200, NULL, NULL, NULL, '{"bg_id":"diagram2","fontSize":26}'),
(35, 'pds2',       NULL,      3, 'PDS 2',     NULL, 600,  800, 36,   NULL, NULL, '{"bg_id":"diagram2","color":"#00e676"}');

-- ─── Screen ↔ Aggregate ──────────────────────────────────────────────────────

INSERT OR IGNORE INTO screen_aggregate (screen_id, aggregate_id) VALUES
(1, 1),  -- Pumps → aggr_1
(2, 2);  -- Burners → aggr_2

-- ─── Aggregate ↔ Indicator ───────────────────────────────────────────────────
-- aggr_1 (Pumps): indicators 1-18
-- aggr_2 (Burners): indicators 19-35

INSERT OR IGNORE INTO aggregate_indicator (aggregate_id, indicator_id) VALUES
(1, 1),(1, 2),(1, 3),(1, 4),(1, 5),(1, 6),(1, 7),(1, 8),(1, 9),(1,10),
(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,18),
(2,19),(2,20),(2,21),(2,22),(2,23),(2,24),(2,25),(2,26),(2,27),
(2,28),(2,29),(2,30),(2,31),(2,32),(2,33),(2,34),(2,35);

-- ─── Group → Screen / Aggregate ──────────────────────────────────────────────

INSERT OR IGNORE INTO group_aggregate (group_id, aggregate_id) VALUES
(1, 1);  -- crema → aggr_1

INSERT OR IGNORE INTO group_screen (group_id, screen_id) VALUES
(2, 1),  -- crema_full → Pumps
(2, 2);  -- crema_full → Burners

-- ─── API commands ────────────────────────────────────────────────────────────

INSERT OR IGNORE INTO api_commands (id, command, method, description) VALUES
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
INSERT OR IGNORE INTO role_api_command (role_id, api_command_id) VALUES
(1,1),(1,3),(1,9),(1,10),(1,14),(1,15),(1,16),(1,28),(1,29),(1,30),(1,31),(1,32);

-- manager (2): operator + full user list, roles list, groups list, logs read + settings
INSERT OR IGNORE INTO role_api_command (role_id, api_command_id) VALUES
(2,1),(2,2),(2,3),(2,9),(2,10),(2,14),(2,15),(2,16),(2,18),(2,22),(2,26),(2,28),(2,29),(2,30),(2,31),(2,32);

-- admin (3): manager + full user/screen/indicator/role/group management (no log clear, no delete roles/groups) + settings
INSERT OR IGNORE INTO role_api_command (role_id, api_command_id) VALUES
(3,1),(3,2),(3,3),(3,4),(3,5),(3,6),(3,7),(3,8),
(3,9),(3,10),(3,11),(3,12),(3,13),(3,14),(3,15),(3,16),(3,17),
(3,18),(3,19),(3,20),(3,22),(3,23),(3,24),(3,26),(3,28),(3,29),(3,30),(3,31),(3,32);

-- almighty (4): everything
INSERT OR IGNORE INTO role_api_command (role_id, api_command_id) VALUES
(4,1),(4,2),(4,3),(4,4),(4,5),(4,6),(4,7),(4,8),
(4,9),(4,10),(4,11),(4,12),(4,13),(4,14),(4,15),(4,16),(4,17),
(4,18),(4,19),(4,20),(4,21),(4,22),(4,23),(4,24),(4,25),
(4,26),(4,27),(4,28),(4,29),(4,30),(4,31),(4,32);

-- ─── App settings (single row) ───────────────────────────────────────────────

INSERT OR IGNORE INTO app_settings (id, display_screen, status, status_text, system_status, system_status_text, header) VALUES
(1, 0, 0, 'OK', 0, 'System OK', 'SCADA System');
