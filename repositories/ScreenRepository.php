<?php

class ScreenRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    /**
     * Return all screens with aggregates+indicators embedded.
     * If $userId is provided, filter to screens the user has access to.
     */
    public function findAll(?int $userId = null): array
    {
        if ($userId !== null) {
            $stmt = $this->db->prepare(
                'SELECT s.*, st.name AS type_name
                 FROM   screens s
                 JOIN   screen_types st ON st.id = s.type_id
                 JOIN   user_screen  us ON us.screen_id = s.id AND us.user_id = :uid
                 ORDER  BY s.id'
            );
            $stmt->execute([':uid' => $userId]);
        } else {
            $stmt = $this->db->query(
                'SELECT s.*, st.name AS type_name
                 FROM   screens s
                 JOIN   screen_types st ON st.id = s.type_id
                 ORDER  BY s.id'
            );
        }

        $rows    = $stmt->fetchAll();
        $screens = [];
        foreach ($rows as $row) {
            $screen             = new Screen($row);
            $screen->aggregates = $this->buildAggregatesForScreen($screen->id);
            $screens[]          = $screen;
        }
        return $screens;
    }

    public function findById(int $id, ?int $userId = null): ?Screen
    {
        if ($userId !== null) {
            $stmt = $this->db->prepare(
                'SELECT s.*, st.name AS type_name
                 FROM   screens s
                 JOIN   screen_types st ON st.id = s.type_id
                 JOIN   user_screen  us ON us.screen_id = s.id AND us.user_id = :uid
                 WHERE  s.id = :id'
            );
            $stmt->execute([':uid' => $userId, ':id' => $id]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT s.*, st.name AS type_name
                 FROM   screens s
                 JOIN   screen_types st ON st.id = s.type_id
                 WHERE  s.id = :id'
            );
            $stmt->execute([':id' => $id]);
        }

        $row = $stmt->fetch();
        if (!$row) return null;

        $screen             = new Screen($row);
        $screen->aggregates = $this->buildAggregatesForScreen($id);
        return $screen;
    }

    /**
     * Build the nested aggregates → indicators structure for a screen.
     * Returned as associative array keyed by aggregate name.
     * Indicator 'unit' is resolved to the unit symbol.
     */
    public function buildAggregatesForScreen(int $screenId, array $mockValues = []): array
    {
        // Get aggregates for this screen
        $aggStmt = $this->db->prepare(
            'SELECT a.id, a.name, a.description, a.settings
             FROM   aggregates a
             JOIN   screen_aggregate sa ON sa.aggregate_id = a.id
             WHERE  sa.screen_id = :sid
             ORDER  BY a.id'
        );
        $aggStmt->execute([':sid' => $screenId]);
        $aggRows = $aggStmt->fetchAll();

        $result = [];
        foreach ($aggRows as $aggRow) {
            $indicators = $this->getIndicatorsForAggregate((int)$aggRow['id'], $mockValues);
            $result[$aggRow['name']] = ['indicators' => $indicators];
        }
        return $result;
    }

    private function getIndicatorsForAggregate(int $aggregateId, array $mockValues = []): array
    {
        $stmt = $this->db->prepare(
            'SELECT i.*, it.front_name AS type, u.symbol AS unit
             FROM   indicators i
             JOIN   aggregate_indicator ai ON ai.indicator_id = i.id
             JOIN   indicator_types     it ON it.id = i.type_id
             LEFT   JOIN units           u  ON u.id  = i.unit_id
             WHERE  ai.aggregate_id = :aid
             ORDER  BY i.id'
        );
        $stmt->execute([':aid' => $aggregateId]);
        $rows = $stmt->fetchAll();

        $indicators = [];
        foreach ($rows as $row) {
            $ind        = new Indicator($row);
            $ind->value = $mockValues[$ind->indId] ?? null;
            $indicators[] = $ind->toArray();
        }
        return $indicators;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO screens (type_id, name, description, tab_header, background, settings)
             VALUES (:type_id, :name, :description, :tab_header, :background, :settings)'
        );
        $stmt->execute([
            ':type_id'     => $data['type_id'],
            ':name'        => $data['name'],
            ':description' => $data['description'] ?? '',
            ':tab_header'  => $data['tab_header']  ?? null,
            ':background'  => $data['background']  ?? null,
            ':settings'    => isset($data['settings']) ? json_encode($data['settings']) : '{}',
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        foreach (['name', 'description', 'tab_header', 'background'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "{$col} = :{$col}";
                $params[":{$col}"] = $data[$col];
            }
        }
        if (isset($data['type_id'])) {
            $fields[]           = 'type_id = :type_id';
            $params[':type_id'] = $data['type_id'];
        }
        if (isset($data['settings'])) {
            $fields[]           = 'settings = :settings';
            $params[':settings'] = json_encode($data['settings']);
        }
        if (empty($fields)) return false;

        $this->db->prepare('UPDATE screens SET ' . implode(', ', $fields) . ' WHERE id = :id')
                 ->execute($params);
        return true;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM screens WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
