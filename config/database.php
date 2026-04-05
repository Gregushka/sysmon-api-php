<?php

class Database
{
    private static ?PDO $instance = null;

    public static function get(): PDO
    {
        if (self::$instance === null) {
            try {
                $pdo = new PDO('sqlite:' . DB_PATH);
                $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $pdo->exec('PRAGMA foreign_keys = ON');
                $pdo->exec('PRAGMA journal_mode = WAL');
                self::$instance = $pdo;
            } catch (PDOException $e) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['code' => -1, 'message' => 'Database unavailable']);
                exit;
            }
        }
        return self::$instance;
    }

    // Reset the singleton (used in install only)
    public static function reset(): void
    {
        self::$instance = null;
    }
}
