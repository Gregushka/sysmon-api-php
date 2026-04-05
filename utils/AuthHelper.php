<?php

class AuthHelper
{
    /**
     * Generate a cryptographically secure token string.
     */
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Hash a password for storage.
     * Client sends sha256(plain); we bcrypt that hash for storage.
     */
    public static function hashPassword(string $sha256Hash): string
    {
        return password_hash($sha256Hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify a password attempt.
     *
     * @param string $sha256Input  sha256 of the plain password (from client)
     * @param string $storedHash   bcrypt hash stored in DB
     */
    public static function verifyPassword(string $sha256Input, string $storedHash): bool
    {
        return password_verify($sha256Input, $storedHash);
    }

    /**
     * Create a new session for a user. Returns the plain token.
     */
    public static function createSession(int $userId): string
    {
        $token     = self::generateToken();
        $expiresAt = date('Y-m-d H:i:s', time() + TOKEN_TTL);
        $ip        = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $ua        = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $db   = Database::get();
        $stmt = $db->prepare(
            'INSERT INTO sessions (user_id, token, expires_at, ip, user_agent)
             VALUES (:uid, :token, :exp, :ip, :ua)'
        );
        $stmt->execute([
            ':uid'   => $userId,
            ':token' => $token,
            ':exp'   => $expiresAt,
            ':ip'    => $ip,
            ':ua'    => $ua,
        ]);

        return $token;
    }

    /**
     * Validate a token. Returns the session row + user row merged, or null.
     */
    public static function validateToken(string $token): ?array
    {
        $db   = Database::get();
        $stmt = $db->prepare(
            'SELECT s.id AS session_id, s.user_id, s.expires_at,
                    u.login, u.fname, u.lname, u.pname, u.position
             FROM   sessions s
             JOIN   users    u ON u.id = s.user_id
             WHERE  s.token = :token
               AND  s.expires_at > CURRENT_TIMESTAMP'
        );
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Delete a specific session (logout).
     */
    public static function destroySession(string $token): void
    {
        $db = Database::get();
        $db->prepare('DELETE FROM sessions WHERE token = :token')
           ->execute([':token' => $token]);
    }

    /**
     * Delete all sessions for a user (force-logout everywhere).
     */
    public static function destroyAllSessions(int $userId): void
    {
        $db = Database::get();
        $db->prepare('DELETE FROM sessions WHERE user_id = :uid')
           ->execute([':uid' => $userId]);
    }

    /**
     * Get all role names for a user.
     *
     * @return array<int, array{id:int, name:string}>
     */
    public static function getUserRoles(int $userId): array
    {
        $db   = Database::get();
        $stmt = $db->prepare(
            'SELECT r.id, r.name, r.description, r.permissions
             FROM   roles r
             JOIN   user_roles_map m ON m.role_id = r.id
             WHERE  m.user_id = :uid
             ORDER BY r.id DESC'
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Check whether a user (identified by their role IDs) is permitted to call
     * a given method+path combination.
     *
     * @param array  $roles   Array of role rows from getUserRoles()
     * @param string $method  HTTP method
     * @param string $command Normalised path pattern (e.g. /v1/users)
     */
    public static function isPermitted(array $roles, string $method, string $command): bool
    {
        if (empty($roles)) {
            return false;
        }

        $roleIds = array_column($roles, 'id');
        $in      = implode(',', array_fill(0, count($roleIds), '?'));

        $db   = Database::get();
        $stmt = $db->prepare(
            "SELECT COUNT(*) AS cnt
             FROM   role_api_command rac
             JOIN   api_commands     ac  ON ac.id = rac.api_command_id
             WHERE  rac.role_id IN ($in)
               AND  ac.command = ?
               AND  ac.method  = ?"
        );
        $stmt->execute([...$roleIds, $command, strtoupper($method)]);
        $row = $stmt->fetch();

        return (int)($row['cnt'] ?? 0) > 0;
    }

    /**
     * Extract the Bearer / X-Auth-Token from the current request headers.
     */
    public static function getTokenFromRequest(): ?string
    {
        // Prefer Authorization: Bearer <token>
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (str_starts_with($auth, 'Bearer ')) {
            return trim(substr($auth, 7));
        }

        // Fall back to X-Auth-Token header
        return $_SERVER['HTTP_X_AUTH_TOKEN'] ?? null;
    }
}
