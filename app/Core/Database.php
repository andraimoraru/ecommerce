<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private string $host;
    private string $user;
    private string $pass;
    private string $dbname;
    private int $port;
    private ?string $socket;
    private bool $throwExceptions;

    private ?PDO $dbh = null;
    private ?\PDOStatement $stmt = null;
    private ?string $error = null;

    /**
     * @param array{host?:string,user?:string,pass?:string,name?:string,port?:int|string,socket?:string,throwExceptions?:bool}|null $config
     */
    public function __construct(?array $config = null)
    {
        $this->host   = $config['host'] ?? DB_HOST;
        $this->user   = $config['user'] ?? DB_USER;
        $this->pass   = $config['pass'] ?? DB_PASS;
        $this->dbname = $config['name'] ?? DB_NAME;

        $portEnv = $config['port'] ?? ($_ENV['DB_PORT'] ?? null);
        $this->port = $portEnv !== null ? (int)$portEnv : 3306;

        $socketEnv = $config['socket'] ?? ($_ENV['DB_SOCKET'] ?? null);
        $this->socket = ($socketEnv !== null && $socketEnv !== '') ? (string)$socketEnv : null;

        $this->throwExceptions = $config['throwExceptions']
            ?? (
                (getenv('DB_THROW_EXCEPTIONS') !== false)
                && filter_var(getenv('DB_THROW_EXCEPTIONS'), FILTER_VALIDATE_BOOLEAN)
            )
            ?? false;

        if ($this->dbname === '') {
            $this->error = 'DB_NAME is empty (check .env loading and DB_NAME value).';
            return;
        }

        $this->connect();
    }

    private function connect(): void
    {
        if ($this->socket !== null) {
            $dsn = sprintf('mysql:unix_socket=%s;dbname=%s;charset=utf8mb4', $this->socket, $this->dbname);
        } else {
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $this->host, $this->port, $this->dbname);
        }

        $options = [
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
            $this->error = null;
        } catch (PDOException $e) {
            $this->dbh = null;
            $this->error = $e->getMessage();
            error_log('Database connection error: ' . $e->getMessage());

            if ($this->throwExceptions) {
                // rethrow so caller / runtime shows the exception stack
                throw $e;
            }
        }
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function query(string $sql): void
    {
        if ($this->dbh === null) {
            throw new \RuntimeException('Database connection not established: ' . ($this->error ?? 'unknown error'));
        }
        $this->stmt = $this->dbh->prepare($sql);
    }

    public function bind(string $param, mixed $value, ?int $type = null): void
    {
        if ($this->stmt === null) {
            throw new \RuntimeException('No prepared statement. Call query() first.');
        }

        if ($type === null) {
            $type = match (true) {
                is_int($value)  => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default         => PDO::PARAM_STR,
            };
        }

        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute(): bool
    {
        if ($this->stmt === null) {
            throw new \RuntimeException('No prepared statement. Call query() first.');
        }
        return $this->stmt->execute();
    }

    /** @return array<object> */
    public function resultSet(): array
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function single(): object|false
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    public function rowCount(): int
    {
        if ($this->stmt === null) {
            throw new \RuntimeException('No prepared statement. Call query() first.');
        }
        return $this->stmt->rowCount();
    }

    public function getPdo(): ?PDO
    {
        return $this->dbh;
    }
}