<?php

class Logger
{
    // Numeric log levels (matches LOG_LEVEL constants in config/app.php)
    const DEBUG = 0;
    const INFO  = 1;
    const WRITE = 2;
    const ERROR = 3;
    const AUDIT = 4;

    // Endpoints whose GET requests are suppressed at INFO level (indicator polling)
    private static array $highFrequencyCommands = ['/data', '/indicators'];

    /**
     * Determine effective log level for a given request.
     * Returns the level that this request should be logged at,
     * or -1 if the call should be skipped at the configured LOG_LEVEL.
     */
    private static function effectiveLevel(string $method, string $command): int
    {
        $method = strtoupper($method);

        // High-frequency indicator reads → DEBUG level only
        foreach (self::$highFrequencyCommands as $hf) {
            if (str_starts_with($command, $hf) && $method === 'GET') {
                return self::DEBUG;
            }
        }

        // Auth and user/role/group mutations → AUDIT
        if ($command === '/auth') {
            return self::AUDIT;
        }
        if (in_array($method, ['POST', 'PUT', 'DELETE'], true) &&
            preg_match('#^/(users|roles|groups)#', $command)) {
            return self::AUDIT;
        }

        // State-changing requests → WRITE
        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            return self::WRITE;
        }

        // Everything else → INFO
        return self::INFO;
    }

    /**
     * Log an incoming request.
     * Silently skips if the effective level is below LOG_LEVEL.
     *
     * @param string     $method      HTTP method
     * @param string     $command     Path (e.g. /users/5)
     * @param array      $params      Route params + query string
     * @param array|null $user        Authenticated user row (or null)
     * @param mixed      $response    Response payload (for logging)
     * @param int        $httpStatus  HTTP status code
     * @param string     $dataObject  Optional: affected object type
     * @param string     $attribute   Optional: affected attribute
     * @param mixed      $dataBefore  Optional: state before mutation
     * @param mixed      $dataAfter   Optional: state after mutation
     */
    public static function logRequest(
        string  $method,
        string  $command,
        array   $params      = [],
        ?array  $user        = null,
        mixed   $response    = null,
        int     $httpStatus  = 200,
        string  $dataObject  = '',
        string  $attribute   = '',
        mixed   $dataBefore  = null,
        mixed   $dataAfter   = null
    ): void {
        $level = self::effectiveLevel($method, $command);

        // Skip if below configured threshold
        if ($level < LOG_LEVEL) {
            return;
        }

        $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '';

        try {
            $db = Database::get();
            $stmt = $db->prepare(
                'INSERT INTO logs
                    (error_level, caller_ip, command, method, command_parameters,
                     response, data_object, object_attribute, data_was, data_is,
                     http_status_code)
                 VALUES
                    (:level, :ip, :cmd, :method, :params,
                     :response, :obj, :attr, :was, :is,
                     :status)'
            );
            $stmt->execute([
                ':level'    => $level,
                ':ip'       => $ip,
                ':cmd'      => $command,
                ':method'   => strtoupper($method),
                ':params'   => json_encode($params,   JSON_UNESCAPED_UNICODE),
                ':response' => $response !== null ? json_encode($response, JSON_UNESCAPED_UNICODE) : null,
                ':obj'      => $dataObject ?: null,
                ':attr'     => $attribute  ?: null,
                ':was'      => $dataBefore !== null ? json_encode($dataBefore) : null,
                ':is'       => $dataAfter  !== null ? json_encode($dataAfter)  : null,
                ':status'   => $httpStatus,
            ]);
        } catch (Throwable) {
            // Logger must never crash the application
        }
    }

    /**
     * Log an error response (always logged unless ERROR > LOG_LEVEL).
     */
    public static function logError(
        string $method,
        string $command,
        string $message,
        int    $httpStatus,
        ?array $user = null
    ): void {
        if (self::ERROR < LOG_LEVEL) {
            return;
        }

        $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '';

        try {
            $db   = Database::get();
            $stmt = $db->prepare(
                'INSERT INTO logs
                    (error_level, caller_ip, command, method, response, http_status_code)
                 VALUES
                    (:level, :ip, :cmd, :method, :response, :status)'
            );
            $stmt->execute([
                ':level'    => self::ERROR,
                ':ip'       => $ip,
                ':cmd'      => $command,
                ':method'   => strtoupper($method),
                ':response' => json_encode(['message' => $message]),
                ':status'   => $httpStatus,
            ]);
        } catch (Throwable) {
            // Silent
        }
    }
}
