<?php

class GroupRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    public function findAll(): array
    {
        $rows   = $this->db->query('SELECT * FROM groups ORDER BY id')->fetchAll();
        $groups = [];
        foreach ($rows as $row) {
            $g             = new Group($row);
            $g->aggregates = $this->getAggregateIds($g->id);
            $g->screens    = $this->getScreenIds($g->id);
            $groups[]      = $g;
        }
        return $groups;
    }

    public function findById(int $id): ?Group
    {
        $stmt = $this->db->prepare('SELECT * FROM groups WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $g             = new Group($row);
        $g->aggregates = $this->getAggregateIds($id);
        $g->screens    = $this->getScreenIds($id);
        return $g;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO groups (name, description, permissions)
             VALUES (:name, :description, :permissions)'
        );
        $stmt->execute([
            ':name'        => $data['name'],
            ':description' => $data['description'] ?? null,
            ':permissions' => isset($data['permissions']) ? json_encode($data['permissions']) : null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        foreach (['name', 'description'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "{$col} = :{$col}";
                $params[":{$col}"] = $data[$col];
            }
        }
        if (isset($data['permissions'])) {
            $fields[]               = 'permissions = :permissions';
            $params[':permissions'] = json_encode($data['permissions']);
        }
        if (empty($fields)) return false;

        $this->db->prepare('UPDATE groups SET ' . implode(', ', $fields) . ' WHERE id = :id')
                 ->execute($params);
        return true;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM groups WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    private function getAggregateIds(int $groupId): array
    {
        $stmt = $this->db->prepare('SELECT aggregate_id FROM group_aggregate WHERE group_id = :gid ORDER BY aggregate_id');
        $stmt->execute([':gid' => $groupId]);
        return array_column($stmt->fetchAll(), 'aggregate_id');
    }

    private function getScreenIds(int $groupId): array
    {
        $stmt = $this->db->prepare('SELECT screen_id FROM group_screen WHERE group_id = :gid ORDER BY screen_id');
        $stmt->execute([':gid' => $groupId]);
        return array_column($stmt->fetchAll(), 'screen_id');
    }
}
