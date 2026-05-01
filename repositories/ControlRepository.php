<?php

class ControlRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    public function findAll(): array
    {
        $rows = $this->db->query(
            'SELECT c.*, ct.name AS type_name
             FROM   controls c
             LEFT   JOIN control_types ct ON ct.id = c.type_id
             ORDER  BY c.id'
        )->fetchAll();
        return array_map(fn($r) => new Control($r), $rows);
    }

    public function findById(int $id): ?Control
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, ct.name AS type_name
             FROM   controls c
             LEFT   JOIN control_types ct ON ct.id = c.type_id
             WHERE  c.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? new Control($row) : null;
    }
}
