<?php

class Install
{
    /**
     * Run installation only if the flag file does not exist.
     * Safe to call on every request — cost is a single file_exists() check.
     */
    public static function runIfNeeded(): void
    {
        if (file_exists(DB_INSTALL_FLAG)) {
            return;
        }
        self::run();
    }

    /**
     * Full installation: create DB, run schema, seed data, create default admin.
     */
    public static function run(): void
    {
        // Ensure DB directory exists and is writable
        $dbDir = dirname(DB_PATH);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0750, true);
        }

        // Run schema
        $pdo = Database::get();
        self::execSqlFile($pdo, DB_INIT_SQL);

        // Run seed data
        self::execSqlFile($pdo, DB_SEED_SQL);

        // Create default admin user (password: admin)
        // Client will send sha256('admin'); we store bcrypt of that
        self::createDefaultAdmin($pdo);

        // Grant admin access to all screens
        self::grantAdminScreenAccess($pdo);

        // Mark as installed
        file_put_contents(DB_INSTALL_FLAG, date('Y-m-d H:i:s'));
    }

    private static function execSqlFile(PDO $pdo, string $path): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException("SQL file not found: {$path}");
        }

        $sql = file_get_contents($path);

        // Strip single-line comments before splitting on ';'
        // so that comment lines preceding a statement don't swallow it
        $sql = preg_replace('/--[^\r\n]*/', '', $sql);

        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($s) => $s !== ''
        );

        // Run DDL without an explicit transaction wrapper —
        // SQLite auto-commits DDL and PRAGMAs cannot run inside transactions.
        foreach ($statements as $stmt) {
            $pdo->exec($stmt);
        }
    }

    private static function createDefaultAdmin(PDO $pdo): void
    {
        // Check if any user already exists
        $count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($count > 0) {
            return;
        }

        // sha256('admin') — the value the client would send
        $sha256 = hash('sha256', 'admin');
        // bcrypt that for storage
        $bcrypt = password_hash($sha256, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = $pdo->prepare(
            'INSERT INTO users (login, password, fname, lname, position)
             VALUES (:login, :password, :fname, :lname, :position)'
        );
        $stmt->execute([
            ':login'    => 'admin',
            ':password' => $bcrypt,
            ':fname'    => 'Admin',
            ':lname'    => 'User',
            ':position' => 'System Administrator',
        ]);

        $userId = (int)$pdo->lastInsertId();

        // Assign almighty role
        $roleStmt = $pdo->prepare('SELECT id FROM roles WHERE name = :name');
        $roleStmt->execute([':name' => 'almighty']);
        $role = $roleStmt->fetch();

        if ($role) {
            $pdo->prepare('INSERT OR IGNORE INTO user_roles_map (user_id, role_id) VALUES (:uid, :rid)')
                ->execute([':uid' => $userId, ':rid' => $role['id']]);
        }

        // Assign crema_full group
        $grpStmt = $pdo->prepare('SELECT id FROM groups WHERE name = :name');
        $grpStmt->execute([':name' => 'crema_full']);
        $group = $grpStmt->fetch();

        if ($group) {
            $pdo->prepare('INSERT OR IGNORE INTO user_groups_map (user_id, group_id) VALUES (:uid, :gid)')
                ->execute([':uid' => $userId, ':gid' => $group['id']]);
        }
    }

    private static function grantAdminScreenAccess(PDO $pdo): void
    {
        // Give the admin user access to all screens
        $adminId  = (int)$pdo->query("SELECT id FROM users WHERE login = 'admin' LIMIT 1")->fetchColumn();
        $screenIds = $pdo->query('SELECT id FROM screens')->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $pdo->prepare(
            'INSERT OR IGNORE INTO user_screen (user_id, screen_id, permissions)
             VALUES (:uid, :sid, :perm)'
        );
        foreach ($screenIds as $screenId) {
            $stmt->execute([
                ':uid'  => $adminId,
                ':sid'  => $screenId,
                ':perm' => '{"read":true,"control":true}',
            ]);
        }
    }
}
