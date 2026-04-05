<?php

class RoleRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    public function findAll(): array
    {
        return array_map(
            fn($r) => new Role($r),
            $this->db->query('SELECT * FROM roles ORDER BY id')->fetchAll()
        );
    }

    public function findById(int $id): ?Role
    {
        $stmt = $this->db->prepare('SELECT * FROM roles WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? new Role($row) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO roles (name, description, permissions)
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

        $this->db->prepare('UPDATE roles SET ' . implode(', ', $fields) . ' WHERE id = :id')
                 ->execute($params);
        return true;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM roles WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
