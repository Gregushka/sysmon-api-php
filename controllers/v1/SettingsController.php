<?php

class SettingsController
{
    /**
     * GET /settings
     * Returns the authenticated user's app settings merged with defaults.
     */
    public static function getSettings(array $params, array $user): void
    {
        $settings = UserAppSettingsRepository::getForUser((int)$user['user_id']);
        ResponseHelper::success($settings);
    }

    /**
     * POST /settings
     * Body: { message_text_size?, message_text_color?, message_window_lines? }
     * Saves and returns merged settings.
     */
    public static function saveSettings(array $params, array $user): void
    {
        $body = ValidationHelper::parseJsonBody();

        if (empty($body) || !is_array($body)) {
            ResponseHelper::error(1, 'Request body must be a JSON object', 400);
        }

        // Validate individual fields if provided
        if (isset($body['message_text_size'])) {
            $sz = (int)$body['message_text_size'];
            if ($sz < 10 || $sz > 100) {
                ResponseHelper::error(1, 'message_text_size must be between 10 and 100', 400);
            }
            $body['message_text_size'] = $sz;
        }

        if (isset($body['message_text_color'])) {
            $allowed = ['#ffffff','#e0e0e0','#7ec8e3','#39ff14','#ffaa00','#ff4444','#00e676','#ff80ab','#82b1ff','#ffd740'];
            if (!in_array($body['message_text_color'], $allowed, true)) {
                ResponseHelper::error(1, 'message_text_color must be one of the allowed web colors', 400);
            }
        }

        if (isset($body['message_window_lines'])) {
            $lines = (int)$body['message_window_lines'];
            if ($lines < 1 || $lines > 200) {
                ResponseHelper::error(1, 'message_window_lines must be between 1 and 200', 400);
            }
            $body['message_window_lines'] = $lines;
        }

        $saved = UserAppSettingsRepository::saveForUser((int)$user['user_id'], $body);
        ResponseHelper::success($saved);
    }
}
