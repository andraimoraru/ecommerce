<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Category
{
    private PDO $pdo;

    public function __construct()
    {
        $db = new Database();
        $pdo = $db->getPdo();

        if (!$pdo instanceof PDO) {
            throw new \RuntimeException('DB connection not available: ' . ($db->getError() ?? 'unknown'));
        }

        $this->pdo = $pdo;
    }

    /** @return array<int, array<string,mixed>> */

    public function all(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, name, slug, parent_id, is_active, created_at
            FROM categories
            ORDER BY name ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<int, array<string,mixed>> */
    
    public function allActive(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, name
            FROM categories
            WHERE is_active = 1
            ORDER BY name ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function slugExists(string $slug): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM categories WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        return (bool)$stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO categories (name, slug, parent_id, is_active)
            VALUES (:name, :slug, :parent_id, :is_active)
        ");

        $stmt->execute([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'parent_id' => $data['parent_id'] ?: null,
            'is_active' => (int)$data['is_active'],
        ]);

        return (int)$this->pdo->lastInsertId();
    }
}