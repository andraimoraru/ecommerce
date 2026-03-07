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
        /** @return array<int, array<string,mixed>> */

    public function allActiveForStorefront(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                c.id,
                c.name,
                c.slug,
                c.parent_id,
                COUNT(pc.product_id) AS product_count
            FROM categories c
            LEFT JOIN product_categories pc ON pc.category_id = c.id
            WHERE c.is_active = 1
            GROUP BY c.id, c.name, c.slug, c.parent_id
            ORDER BY c.name ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findActiveBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, name, slug, parent_id, is_active, created_at
            FROM categories
            WHERE slug = :slug
            AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute(['slug' => $slug]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, name, slug, parent_id, is_active, created_at
            FROM categories
            WHERE slug = :slug
            LIMIT 1
        ");
        $stmt->execute(['slug' => $slug]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}