<?php

/**
 * DataController
 *
 * Returns mocked live indicator values.
 * Replace getMockValues() with a real data-source adapter when sensors are integrated.
 */
class DataController
{
    public static function readData(array $params, ?array $user): never
    {
        $data = [
            'hdr' => AppHeader::get('force_update'),
            'indicators' => self::getMockValues(),
        ];

        ResponseHelper::send(['data' => $data]);
    }

    /**
     * Return an associative array of ind_id → current value.
     * Used both by this endpoint and by AuthController / ScreenController
     * to populate the `value` field on indicator objects.
     */
    public static function getMockValues(): array
    {
        $burner = ['off', 'on', 'alarm', 'nodata', 'fullpower', 'minpower'];

        $raw = [
            // ── Diagram 1 (Pumps) ──────────────────────────────────────────
            ['name' => 'te_1-1',    'value' => rand(-40, 120)],
            ['name' => 'pe_1-1',    'value' => rand(-40, 120)],
            ['name' => 'te_1-2',    'value' => rand(-40, 120)],
            ['name' => 'pe_1-2',    'value' => rand(-40, 120)],
            ['name' => 'te_1-3',    'value' => rand(-40, 120)],
            ['name' => 'pe_1-3',    'value' => rand(-40, 120)],
            ['name' => 'te_1-4',    'value' => rand(-40, 120)],
            ['name' => 'pe_1-4',    'value' => rand(-40, 120)],
            ['name' => 'te_1-5',    'value' => rand(-40, 120)],
            ['name' => 'pe_1-5',    'value' => rand(-40, 120)],
            ['name' => 'pe_1-6',    'value' => rand(-40, 120)],
            ['name' => 'te_1-6',    'value' => rand(-40, 120)],
            ['name' => 'te_1-7',    'value' => rand(-40,  50)],
            ['name' => 'pump1',     'value' => rand(-40, 120)],
            ['name' => 'pds2',      'value' => rand(-40, 120)],
            ['name' => 'pds4',      'value' => rand(-40, 120)],			
            ['name' => 'pds3',      'value' => rand(-40, 120)],
            ['name' => 'di-valve',  'value' => rand(0,   100)],
            ['name' => 'di-flow',   'value' => rand(-40, 120)],
            ['name' => 'pds1',      'value' => rand(-40, 120)],
            ['name' => 'pump1-d2',  'value' => rand(-40, 120)],
            ['name' => 'di-vol',    'value' => rand(-40, 120)],
            ['name' => 'di-pressure','value' => rand(-40, 120)],
            ['name' => 'pumpGVS','value' => rand(-40, 120)],			
            ['name' => 'pump_k3_1','value' => rand(-40, 120)],						
			['name' => 'pump_k3_2','value' => rand(-40, 120)],						
			['name' => 'pump_k2','value' => rand(-40, 120)],						
            // ── Diagram 2 (Burners) ────────────────────────────────────────
            ['name' => 'burner_one',   'value' => random_int(0, 100)],
            ['name' => 'burner_two',   'value' => random_int(0, 100)],
            ['name' => 'burner_three', 'value' => random_int(0, 100)],
            ['name' => 'burner_flap',  'value' => random_int(0, 1)],
            ['name' => 'pds2',         'value' => rand(-40, 120)],
            ['name' => 'te_2-1',       'value' => rand(20, 1120)],
            ['name' => 'te_2-2',       'value' => rand(20, 1120)],
            ['name' => 'te_2-3',       'value' => rand(20, 1120)],
            ['name' => 'te_2-4',       'value' => rand(20, 1120)],
            ['name' => 'te_2-5',       'value' => rand(20, 1120)],
            ['name' => 'te_2-6',       'value' => rand(20, 1120)],
            ['name' => 'cooling',      'value' => random_int(0, 1)],
            ['name' => 'cooling2',     'value' => random_int(0, 1)],
            ['name' => 'chimney',      'value' => random_int(0, 1)],
            ['name' => 'cyclon',       'value' => random_int(0, 1)],
        ];

        // Add timestamps and reindex by name
        $indexed = [];
        $ts      = time();
        foreach ($raw as $item) {
            $item['ts']           = $ts;
            $indexed[$item['name']] = $item['value'];
        }
        return $indexed;
    }
}
