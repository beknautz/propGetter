<?php
/**
 * PropIntel CRM - Database Connection (PDO singleton)
 */

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Log and show friendly error; never expose credentials
                error_log('Database connection failed: ' . $e->getMessage());
                if (APP_DEBUG) {
                    throw new RuntimeException('Database connection failed: ' . $e->getMessage());
                }
                throw new RuntimeException('A database error occurred. Please try again later.');
            }
        }

        return self::$instance;
    }

    /** Convenience wrapper — runs a prepared statement and returns the statement */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $pdo  = self::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Fetch a single row */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    /** Fetch all rows */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /** Return last insert ID */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    /** Begin transaction */
    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }

    /** Commit transaction */
    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    /** Roll back transaction */
    public static function rollBack(): void
    {
        self::getInstance()->rollBack();
    }
}
