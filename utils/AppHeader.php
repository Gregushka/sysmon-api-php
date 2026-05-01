<?php

/**
 * AppHeader
 *
 * Returns the standard `hdr` block included in most API responses.
 * Reads from the app_settings table (single row, id=1).
 * The user can extend/adjust this class as needed.
 */
class AppHeader
{
    /**
     * Return the hdr array for auth / screen / indicator responses.
     *
     * @param string $command  Backend command hint sent to the front end
     *                         (e.g. 'force_update').  Defaults to 'force_update'.
     */
    public static function get(string $command = 'force_update'): array
    {
        try {
            $row = Database::get()
                ->query('SELECT * FROM app_settings WHERE id = 1')
                ->fetch();
        } catch (Throwable) {
            $row = null;
        }

        return [
            'display_screen'     => (int)($row['display_screen']     ?? 0),
            'command'            => $command,
            'status'             => (int)($row['status']             ?? 0),
            'status_text'        => $row['status_text']              ?? 'OK',
            'system_status'      => (int)($row['system_status']      ?? 0),
            'system_status_text' => $row['system_status_text']       ?? 'System OK',
            'header'             => $row['header']                   ?? 'SCADA System',
        ];
    }
}
