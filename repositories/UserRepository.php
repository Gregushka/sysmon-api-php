<?php

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    public function findAll(): array
    {
        $rows  = $this->db->query('SELECT id, login, fname, lname, pname, position FROM users ORDER BY id')->fetchAll();
        $users = [];
        foreach ($rows as $row) {
            $user         = new User($row);
            $user->roles  = $this->getRoleIds($user->id);
            $user->groups = $this->getGroupIds($user->id);
            $users[]      = $user;
        }
        return $users;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare('SELECT id, login, fname, lname, pname, position FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $user         = new User($row);
        $user->roles  = $this->getRoleIds($id);
        $user->groups = $this->getGroupIds($id);
        return $user;
    }

    public function findByLogin(string $login): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE login = :login');
        $stmt->execute([':login' => $login]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (login, password, fname, lname, pname, position)
             VALUES (:login, :password, :fname, :lname, :pname, :position)'
        );
        $stmt->execute([
            ':login'    => $data['login'],
            ':password' => $data['password'],
            ':fname'    => $data['fname']    ?? null,
            ':lname'    => $data['lname']    ?? null,
            ':pname'    => $data['pname']    ?? null,
            ':position' => $data['position'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        foreach (['fname', 'lname', 'pname', 'position'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "{$col} = :{$col}";
                $params[":{$col}"] = $data[$col];
            }
        }
        if (isset($data['password'])) {
            $fields[]            = 'password = :password';
            $params[':password'] = $data['password'];
        }
        if (empty($fields)) {
            return false;
        }

        $sql  = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function setRoles(int $userId, array $roleIds): void
    {
        $del = $this->db->prepare('DELETE FROM user_roles_map WHERE user_id = :uid');
        $del->execute([':uid' => $userId]);

        $ins = $this->db->prepare('INSERT IGNORE INTO user_roles_map (user_id, role_id) VALUES (:uid, :rid)');
        foreach ($roleIds as $rid) {
            $ins->execute([':uid' => $userId, ':rid' => (int)$rid]);
        }
    }

    public function setGroups(int $userId, array $groupIds): void
    {
        $del = $this->db->prepare('DELETE FROM user_groups_map WHERE user_id = :uid');
        $del->execute([':uid' => $userId]);

        $ins = $this->db->prepare('INSERT IGNORE INTO user_groups_map (user_id, group_id) VALUES (:uid, :gid)');
        foreach ($groupIds as $gid) {
            $ins->execute([':uid' => $userId, ':gid' => (int)$gid]);
        }
    }

    private function getRoleIds(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT role_id FROM user_roles_map WHERE user_id = :uid ORDER BY role_id');
        $stmt->execute([':uid' => $userId]);
        return array_column($stmt->fetchAll(), 'role_id');
    }

    private function getGroupIds(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT group_id FROM user_groups_map WHERE user_id = :uid ORDER BY group_id');
        $stmt->execute([':uid' => $userId]);
        return array_column($stmt->fetchAll(), 'group_id');
    }
}
