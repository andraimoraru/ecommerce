<?php
namespace App\Core;

use \PDO;
use \PDOException;

/**
 * PDO Database Class (PSR-4)
 * Reads connection values from environment variables by default.
 */
class Database
{
    private string $host;
    private string $user;
    private string $pass;
    private string $dbname;

    private ?PDO $dbh = null;
    private $stmt;
    private ?string $error = null;

    /**
     * Constructor.
     * Accepts optional config array for easier testing. If omitted,
     * values are read from environment variables (DB_HOST, DB_USER, DB_PASS, DB_NAME).
     *
     * @param array|null $config
     */
    public function __construct(?array $config = null)
    {
        $this->host = $config['host'] ?? (string) getenv('DB_HOST') ?: '127.0.0.1';
        $this->user = $config['user'] ?? (string) getenv('DB_USER') ?: 'root';
        // allow empty password explicitly
        $this->pass = $config['pass'] ?? getenv('DB_PASS') ?? '';
        $this->dbname = $config['name'] ?? (string) getenv('DB_NAME') ?: '';

        // Only attempt to connect if a dbname is provided
        if ($this->dbname !== '') {
            $this->connect();
        }
    }

    /**
     * Establish PDO connection.
     */
    private function connect(): void
    {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $this->host, $this->dbname);

        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        ];

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // Keep the error for callers; avoid echoing from library code.
        }
    }

    /**
     * Get last connection error message (if any).
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Prepare statement with query
     */
    public function query(string $sql): void
    {
        if ($this->dbh === null) {
            throw new \RuntimeException('Database connection not established.');
        }

        $this->stmt = $this->dbh->prepare($sql);
    }

    /**
     * Bind values
     */
    public function bind(string $param, $value, ?int $type = null): void
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * Execute the prepared statement
     */
    public function execute(): bool
    {
        return $this->stmt->execute();
    }

    /**
     * Get results set as array of objects
     * @return array<object>
     */
    public function resultSet(): array
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get single record as object
     */
    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get row count
     */
    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * Return underlying PDO instance (for advanced usage/testing)
     */
    public function getPdo(): ?PDO
    {
        return $this->dbh;
    }
}
