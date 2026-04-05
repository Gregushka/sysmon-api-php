<?php

class LogController
{
    public static function getLogs(array $params, ?array $user): never
    {
        $limit  = max(1, min(1000, (int)($_GET['limit']  ?? 200)));
        $offset = max(0,           (int)($_GET['offset'] ?? 0));
        $level  = isset($_GET['level']) ? (int)$_GET['level'] : null;
        $from   = $_GET['from'] ?? null;
        $to     = $_GET['to']   ?? null;

        $repo  = new LogRepository();
        $logs  = array_map(fn($l) => $l->toArray(), $repo->findAll($limit, $offset, $level, $from, $to));
        $total = $repo->count($level);

        ResponseHelper::send([
            'total'  => $total,
            'limit'  => $limit,
            'offset' => $offset,
            'logs'   => $logs,
        ]);
    }

    public static function clearLogs(array $params, ?array $user): never
    {
        $level   = isset($_GET['level']) ? (int)$_GET['level'] : null;
        $repo    = new LogRepository();
        $deleted = $repo->clear($level);

        Logger::logRequest('DELETE', '/logs', ['level' => $level], $user, null, 200, 'logs', 'clear');
        ResponseHelper::send([
            'status_code' => 0,
            'message'     => "Deleted {$deleted} log entries",
        ]);
    }
}
