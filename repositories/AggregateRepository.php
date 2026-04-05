<?php

class AggregateRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    public function findAll(): array
    {
        return array_map(
            fn($r) => new Aggregate($r),
            $this->db->query('SELECT * FROM aggregates ORDER BY id')->fetchAll()
        );
    }

    public function findById(int $id): ?Aggregate
    {
        $stmt = $this->db->prepare('SELECT * FROM aggregates WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? new Aggregate($row) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO aggregates (name, description, settings) VALUES (:name, :description, :settings)'
        );
        $stmt->execute([
            ':name'        => $data['name'],
            ':description' => $data['description'] ?? null,
            ':settings'    => isset($data['settings']) ? json_encode($data['settings']) : null,
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
        if (isset($data['settings'])) {
            $fields[]           = 'settings = :settings';
            $params[':settings'] = json_encode($data['settings']);
        }
        if (empty($fields)) return false;

        $this->db->prepare('UPDATE aggregates SET ' . implode(', ', $fields) . ' WHERE id = :id')
                 ->execute($params);
        return true;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM aggregates WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
