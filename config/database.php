<?php
/**
 * Ibrahim Azzouz Cars — Database Connection (PDO Singleton)
 */

declare(strict_types=1);

require_once __DIR__ . '/app.php';

final class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;

    private string $host;
    private string $dbname;
    private string $username;
    private string $password;
    private int    $port;

    private function __construct()
    {
        $this->host     = getenv('DB_HOST')     ?: 'localhost';
        $this->dbname   = getenv('DB_NAME')     ?: 'ibrahim_azzouz_cars';
        $this->username = getenv('DB_USER')     ?: 'root';
        $this->password = getenv('DB_PASS')     ?: '';
        $this->port     = (int) (getenv('DB_PORT') ?: 3307);

        $this->connect();
    }

    /** Prevent cloning of singleton */
    private function __clone() {}

    /** Prevent unserialization of singleton */
    public function __wakeup()
    {
        throw new \RuntimeException('Cannot unserialize singleton.');
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect(): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $this->host,
            $this->port,
            $this->dbname
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            PDO::MYSQL_ATTR_FOUND_ROWS   => true,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            if (APP_ENV === 'local') {
                die('Database connection failed: ' . $e->getMessage());
            }
            error_log('Database connection failed: ' . $e->getMessage());
            die('A database error occurred. Please try again later.');
        }
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Execute a raw query and return the PDOStatement.
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (APP_ENV === 'local') {
                throw $e;
            }
            error_log('Query failed: ' . $e->getMessage());
            throw new \RuntimeException('A database error occurred.');
        }
    }

    /**
     * Insert a row and return the last insert ID.
     */
    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_map(fn($c) => "`$c`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO `$table` ($columns) VALUES ($placeholders)";
        $this->query($sql, array_values($data));

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Update rows and return the number of affected rows.
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setClauses = [];
        $params = [];
        foreach ($data as $column => $value) {
            $key = 'set_' . preg_replace('/[^A-Za-z0-9_]/', '_', $column);
            $setClauses[] = "`$column` = :$key";
            $params[$key] = $value;
        }

        $sql = "UPDATE `$table` SET " . implode(', ', $setClauses) . " WHERE $where";
        $params = array_merge($params, $whereParams);

        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Delete rows and return the number of affected rows.
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM `$table` WHERE $where";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Begin a transaction.
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }
}
