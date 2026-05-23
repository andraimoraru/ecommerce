<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Category
{
    private PDO $pdo;

    // Bootstrap a PDO-backed category model.
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
    // Return every category for the admin area.
    public function all(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, name, slug, parent_id, is_active, created_at, updated_at
            FROM categories
            ORDER BY name ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<int, array<string,mixed>> */
    // Return only active categories for selection inputs.
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
        // Use a lightweight existence check rather than loading the full row.
        $stmt = $this->pdo->prepare("SELECT 1 FROM categories WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        return (bool)$stmt->fetchColumn();
    }

    // Check whether a slug exists on another category.
    public function slugExistsForAnotherCategory(string $slug, int $excludeId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM categories
            WHERE slug = :slug
              AND id != :id
            LIMIT 1
        ");
        $stmt->execute([
            'slug' => $slug,
            'id' => $excludeId,
        ]);

        return (bool)$stmt->fetchColumn();
    }

    // Insert a new category row.
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

    // Fetch one category by id for admin editing.
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, name, slug, parent_id, is_active, created_at, updated_at
            FROM categories
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Persist editable category fields.
    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE categories
            SET
                name = :name,
                slug = :slug,
                parent_id = :parent_id,
                is_active = :is_active,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'parent_id' => $data['parent_id'] ?: null,
            'is_active' => (int)$data['is_active'],
        ]);
    }

    // Check whether this category has child categories.
    public function hasChildren(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM categories
            WHERE parent_id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);

        return (bool)$stmt->fetchColumn();
    }

    // Check whether this category has assigned products.
    public function hasProducts(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM product_categories
            WHERE category_id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);

        return (bool)$stmt->fetchColumn();
    }

    // Delete one category and re-parent any direct children to its parent.
    public function delete(int $id): void
    {
        $category = $this->findById($id);

        if (!$category) {
            throw new \RuntimeException('Category not found.');
        }

        $newParentId = $category['parent_id'] ? (int)$category['parent_id'] : null;

        if ($newParentId === $id) {
            $newParentId = null;
        }

        $this->pdo->beginTransaction();

        try {
            $reparentChildren = $this->pdo->prepare("
                UPDATE categories
                SET parent_id = :new_parent_id
                WHERE parent_id = :id
            ");
            $reparentChildren->bindValue(':id', $id, PDO::PARAM_INT);

            if ($newParentId === null) {
                $reparentChildren->bindValue(':new_parent_id', null, PDO::PARAM_NULL);
            } else {
                $reparentChildren->bindValue(':new_parent_id', $newParentId, PDO::PARAM_INT);
            }

            $reparentChildren->execute();

            $detachSelf = $this->pdo->prepare("
                UPDATE categories
                SET parent_id = NULL
                WHERE id = :id
            ");
            $detachSelf->execute(['id' => $id]);

            $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = :id");
            $stmt->execute(['id' => $id]);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    /** @return array<int, array<string,mixed>> */
    // Return active categories together with storefront product counts.
    public function allActiveForStorefront(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                c.id,
                c.name,
                c.slug,
                c.parent_id,
                COUNT(p.id) AS product_count,
                (
                    SELECT pi.url
                    FROM product_categories pc2
                    INNER JOIN products p2
                        ON p2.id = pc2.product_id
                       AND p2.status = 'ACTIVE'
                    LEFT JOIN inventory i2
                        ON i2.product_id = p2.id
                    INNER JOIN product_images pi
                        ON pi.product_id = p2.id
                    WHERE pc2.category_id = c.id
                      AND COALESCE(i2.stock_on_hand, 0) > COALESCE(i2.stock_reserved, 0)
                      AND pi.id = (
                          SELECT pi2.id
                          FROM product_images pi2
                          WHERE pi2.product_id = p2.id
                          ORDER BY pi2.sort_order ASC, pi2.id ASC
                          LIMIT 1
                      )
                    ORDER BY RAND()
                    LIMIT 1
                ) AS featured_image
            FROM categories c
            LEFT JOIN product_categories pc ON pc.category_id = c.id
            LEFT JOIN products p ON p.id = pc.product_id AND p.status = 'ACTIVE'
            LEFT JOIN inventory i ON i.product_id = p.id
            WHERE c.is_active = 1
              AND (
                  p.id IS NULL
                  OR COALESCE(i.stock_on_hand, 0) > COALESCE(i.stock_reserved, 0)
              )
            GROUP BY c.id, c.name, c.slug, c.parent_id
            ORDER BY c.name ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch one active category by slug for the storefront.
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

    // Fetch one category by slug regardless of status.
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
