<?php

class LogRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    /**
     * @param int      $limit       Max rows to return
     * @param int      $offset      Pagination offset
     * @param int|null $level       Filter by minimum error_level
     * @param string|null $from     ISO datetime filter (>=)
     * @param string|null $to       ISO datetime filter (<=)
     */
    public function findAll(
        int     $limit  = 200,
        int     $offset = 0,
        ?int    $level  = null,
        ?string $from   = null,
        ?string $to     = null
    ): array {
        $sql    = 'SELECT * FROM logs WHERE 1=1';
        $params = [];

        if ($level !== null) {
            $sql .= ' AND error_level >= :level';
            $params[':level'] = $level;
        }
        if ($from !== null) {
            $sql .= ' AND timestamp >= :from';
            $params[':from'] = $from;
        }
        if ($to !== null) {
            $sql .= ' AND timestamp <= :to';
            $params[':to'] = $to;
        }

        $sql .= ' ORDER BY id DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn($r) => new Log($r), $stmt->fetchAll());
    }

    public function count(?int $level = null): int
    {
        $sql    = 'SELECT COUNT(*) FROM logs WHERE 1=1';
        $params = [];
        if ($level !== null) {
            $sql .= ' AND error_level >= :level';
            $params[':level'] = $level;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function clear(?int $level = null): int
    {
        if ($level !== null) {
            $stmt = $this->db->prepare('DELETE FROM logs WHERE error_level = :level');
            $stmt->execute([':level' => $level]);
        } else {
            $stmt = $this->db->query('DELETE FROM logs');
        }
        return $stmt->rowCount();
    }
}
