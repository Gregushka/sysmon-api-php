<?php

class IndicatorRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    /**
     * Get all indicators, optionally filtered by screen_id and/or ind_id.
     * Returns flat rows with aggregate name and screen name attached.
     */
    public function findByContext(?int $screenId = null, ?string $indId = null): array
    {
        $sql = 'SELECT i.*, it.front_name AS type, u.symbol AS unit,
                       a.name AS aggregate_name, s.name AS screen_name
                FROM   indicators i
                JOIN   aggregate_indicator ai ON ai.indicator_id = i.id
                JOIN   aggregates          a  ON a.id  = ai.aggregate_id
                JOIN   screen_aggregate    sa ON sa.aggregate_id = a.id
                JOIN   screens             s  ON s.id  = sa.screen_id
                JOIN   indicator_types     it ON it.id = i.type_id
                LEFT   JOIN units          u  ON u.id  = i.unit_id
                WHERE  1=1';

        $params = [];
        if ($screenId !== null) {
            $sql        .= ' AND s.id = :sid';
            $params[':sid'] = $screenId;
        }
        if ($indId !== null) {
            $sql        .= ' AND i.ind_id = :ind_id';
            $params[':ind_id'] = $indId;
        }
        $sql .= ' ORDER BY s.id, a.id, i.id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT i.*, it.front_name AS type, u.symbol AS unit
             FROM   indicators i
             JOIN   indicator_types it ON it.id = i.type_id
             LEFT   JOIN units u ON u.id = i.unit_id
             WHERE  i.id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function updatePosition(int $id, int $top, int $left): bool
    {
        $stmt = $this->db->prepare('UPDATE indicators SET top = :top, left = :left WHERE id = :id');
        $stmt->execute([':top' => $top, ':left' => $left, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Find indicator id by ind_id within a specific screen context.
     * Needed because ind_id is not globally unique.
     */
    public function findIndicatorInScreen(string $indId, int $screenId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT i.id, i.ind_id, i.top, i.left,
                    a.name AS aggregate_name, s.name AS screen_name
             FROM   indicators i
             JOIN   aggregate_indicator ai ON ai.indicator_id = i.id
             JOIN   aggregates          a  ON a.id  = ai.aggregate_id
             JOIN   screen_aggregate    sa ON sa.aggregate_id = a.id
             JOIN   screens             s  ON s.id  = sa.screen_id
             WHERE  i.ind_id = :ind_id AND s.id = :sid
             LIMIT  1'
        );
        $stmt->execute([':ind_id' => $indId, ':sid' => $screenId]);
        return $stmt->fetch() ?: null;
    }
}
