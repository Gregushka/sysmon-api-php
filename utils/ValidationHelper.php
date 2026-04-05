<?php

class ValidationHelper
{
    /**
     * Assert that required keys are present and non-empty in the given array.
     * Calls ResponseHelper::error() and exits on failure.
     *
     * @param array    $data      Input array (e.g. parsed JSON body or $_GET)
     * @param string[] $required  List of required field names
     */
    public static function requireFields(array $data, array $required): void
    {
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                ResponseHelper::error(-1, "Missing required field: {$field}", 422);
            }
        }
    }

    /**
     * Parse the request body as JSON and return the decoded array.
     * Exits with 400 if the body is not valid JSON.
     */
    public static function parseJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === '' || $raw === false) {
            return [];
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            ResponseHelper::error(-1, 'Request body must be valid JSON', 400);
        }
        return $data;
    }

    /**
     * Sanitise a string to prevent basic injection.
     */
    public static function sanitizeString(string $value, int $maxLength = 255): string
    {
        return substr(trim(strip_tags($value)), 0, $maxLength);
    }

    /**
     * Validate that a value is a positive integer (or a string that is one).
     */
    public static function isPositiveInt(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
    }

    /**
     * Assert that $value is a positive integer; exit with 422 otherwise.
     */
    public static function requirePositiveInt(mixed $value, string $fieldName = 'id'): int
    {
        if (!self::isPositiveInt($value)) {
            ResponseHelper::error(-1, "Invalid value for {$fieldName}: must be a positive integer", 422);
        }
        return (int)$value;
    }
}
