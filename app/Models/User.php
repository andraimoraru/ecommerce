<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    private PDO $pdo;

    // Bootstrap a PDO-backed user model.
    public function __construct()
    {
        $db = new Database();
        $pdo = $db->getPdo();
        if (!$pdo instanceof PDO) {
            throw new \RuntimeException('DB connection not available.');
        }
        $this->pdo = $pdo;
    }

    // Fetch one user record by email for login and registration checks.
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

    // Check whether a user already exists for the given email address.
    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return (bool)$stmt->fetchColumn();
    }

    // Create a new active customer account.
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

    /** @return array<int,array<string,mixed>> */
    // Return customer accounts with order counts for the admin list.
    public function allCustomersAdmin(int $limit = 25, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                u.id,
                u.email,
                u.first_name,
                u.last_name,
                u.phone,
                u.role,
                u.is_active,
                u.created_at,
                u.updated_at,
                COUNT(o.id) AS order_count
            FROM users u
            LEFT JOIN orders o ON o.user_id = u.id
            WHERE u.role = 'CUSTOMER'
            GROUP BY
                u.id,
                u.email,
                u.first_name,
                u.last_name,
                u.phone,
                u.role,
                u.is_active,
                u.created_at,
                u.updated_at
            ORDER BY u.created_at DESC, u.id DESC
            LIMIT :lim OFFSET :off
        ");

        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Count customer accounts for admin pagination.
    public function countCustomersAdmin(): int
    {
        $stmt = $this->pdo->query("
            SELECT COUNT(*)
            FROM users
            WHERE role = 'CUSTOMER'
        ");

        return (int)$stmt->fetchColumn();
    }

    // Fetch one customer account for admin management.
    public function findCustomerById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                email,
                first_name,
                last_name,
                phone,
                role,
                is_active,
                created_at,
                updated_at
            FROM users
            WHERE id = :id
              AND role = 'CUSTOMER'
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /** @return array<int,array<string,mixed>> */
    // Fetch a customer's orders for the admin detail screen.
    public function ordersForCustomer(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                order_number,
                status,
                currency,
                total_minor,
                placed_at,
                created_at
            FROM orders
            WHERE user_id = :user_id
            ORDER BY COALESCE(placed_at, created_at) DESC, id DESC
        ");
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check whether a customer already has at least one order.
    public function customerHasOrders(int $userId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM orders
            WHERE user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);

        return (bool)$stmt->fetchColumn();
    }

    // Persist editable customer fields from the admin area.
    public function updateCustomer(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE users
            SET
                email = :email,
                first_name = :first_name,
                last_name = :last_name,
                phone = :phone,
                is_active = :is_active,
                updated_at = NOW()
            WHERE id = :id
              AND role = 'CUSTOMER'
        ");

        $stmt->execute([
            'id' => $id,
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] !== '' ? $data['phone'] : null,
            'is_active' => (int)$data['is_active'],
        ]);
    }

    // Check whether an email belongs to another user.
    public function emailExistsForAnotherUser(string $email, int $excludeId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM users
            WHERE email = :email
              AND id != :id
            LIMIT 1
        ");
        $stmt->execute([
            'email' => $email,
            'id' => $excludeId,
        ]);

        return (bool)$stmt->fetchColumn();
    }

    // Remove a customer account that has no order history.
    public function deleteCustomer(int $id): void
    {
        $deleteLinks = $this->pdo->prepare("DELETE FROM user_addresses WHERE user_id = :user_id");
        $deleteLinks->execute(['user_id' => $id]);

        $stmt = $this->pdo->prepare("
            DELETE FROM users
            WHERE id = :id
              AND role = 'CUSTOMER'
        ");
        $stmt->execute(['id' => $id]);
    }
}
