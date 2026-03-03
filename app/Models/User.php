<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    private PDO $pdo;

    public function __construct()
    {
        $db = new Database();
        $pdo = $db->getPdo();
        if (!$pdo instanceof PDO) {
            throw new \RuntimeException('DB connection not available.');
        }
        $this->pdo = $pdo;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, email, password_hash, first_name, last_name, phone, role, is_active
            FROM users
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return (bool)$stmt->fetchColumn();
    }

    public function createCustomer(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (email, password_hash, first_name, last_name, phone, role, is_active)
            VALUES (:email, :password_hash, :first_name, :last_name, :phone, 'CUSTOMER', 1)
        ");

        $stmt->execute([
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?: null,
        ]);

        return (int)$this->pdo->lastInsertId();
    }
}