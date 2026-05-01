<?php

class UserAppSettingsRepository
{
    // Defaults for all user-specific settings
    private static array $defaults = [
        'message_text_size'  => 13,    // px (10–100)
        'message_text_color' => '#7ec8e3',
        'message_window_lines' => 20,  // number of visible lines
    ];

    /**
     * Load settings for a user. Returns defaults merged with stored values.
     */
    public static function getForUser(int $userId): array
    {
        $db   = Database::get();
        $stmt = $db->prepare('SELECT settings FROM user_app_settings WHERE user_id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if (!$row) {
            return self::$defaults;
        }

        $stored = json_decode($row['settings'], true) ?: [];
        return array_merge(self::$defaults, $stored);
    }

    /**
     * Upsert settings for a user. Only known keys are persisted.
     */
    public static function saveForUser(int $userId, array $settings): array
    {
        // Filter to allowed user-settable keys
        $allowed = array_keys(self::$defaults);
        $filtered = array_intersect_key($settings, array_flip($allowed));

        // Merge with current stored to preserve any keys not sent
        $current = self::getForUser($userId);
        $merged  = array_merge($current, $filtered);

        $db   = Database::get();
        $stmt = $db->prepare(
            'INSERT INTO user_app_settings (user_id, settings) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE settings = VALUES(settings)'
        );
        $stmt->execute([$userId, json_encode($merged)]);

        return $merged;
    }

    public static function getDefaults(): array
    {
        return self::$defaults;
    }
}
