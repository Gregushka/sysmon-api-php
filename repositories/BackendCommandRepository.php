<?php

class BackendCommandRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    public function findAll(): array
    {
        return array_map(
            fn($r) => new BackendCommand($r),
            $this->db->query('SELECT * FROM backend_commands ORDER BY id')->fetchAll()
        );
    }

    public function findById(int $id): ?BackendCommand
    {
        $stmt = $this->db->prepare('SELECT * FROM backend_commands WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? new BackendCommand($row) : null;
    }

    public function findByCommand(string $command): ?BackendCommand
    {
        $stmt = $this->db->prepare('SELECT * FROM backend_commands WHERE command = :cmd');
        $stmt->execute([':cmd' => $command]);
        $row = $stmt->fetch();
        return $row ? new BackendCommand($row) : null;
    }
}
