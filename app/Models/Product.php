<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Product
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
    
    public function allAdminWithMeta(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                p.id,
                p.sku,
                p.name,
                p.slug,
                p.price_minor,
                p.currency,
                p.status,
                p.created_at,
                c.name AS category_name,
                i.stock_on_hand,
                i.stock_reserved,
                (
                    SELECT pi.url
                    FROM product_images pi
                    WHERE pi.product_id = p.id
                    ORDER BY pi.sort_order ASC, pi.id ASC
                    LIMIT 1
                ) AS primary_image
            FROM products p
            LEFT JOIN product_categories pc ON pc.product_id = p.id
            LEFT JOIN categories c ON c.id = pc.category_id
            LEFT JOIN inventory i ON i.product_id = p.id
            ORDER BY p.created_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allActiveForStorefront(int $limit = 12): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                p.id,
                p.name,
                p.slug,
                p.description,
                p.price_minor,
                p.currency,
                p.status,
                (
                    SELECT pi.url
                    FROM product_images pi
                    WHERE pi.product_id = p.id
                    ORDER BY pi.sort_order ASC, pi.id ASC
                    LIMIT 1
                ) AS primary_image
            FROM products p
            WHERE p.status = 'ACTIVE'
            ORDER BY p.created_at DESC
            LIMIT :lim
        ");

        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findActiveBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                p.*,
                (
                    SELECT pi.url
                    FROM product_images pi
                    WHERE pi.product_id = p.id
                    ORDER BY pi.sort_order ASC, pi.id ASC
                    LIMIT 1
                ) AS primary_image
            FROM products p
            WHERE p.slug = :slug
            AND p.status = 'ACTIVE'
            LIMIT 1
        ");

        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                p.*,
                pc.category_id,
                i.stock_on_hand,
                i.stock_reserved
            FROM products p
            LEFT JOIN product_categories pc ON pc.product_id = p.id
            LEFT JOIN inventory i ON i.product_id = p.id
            WHERE p.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function skuExists(string $sku, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->pdo->prepare("
                SELECT 1
                FROM products
                WHERE sku = :sku AND id != :id
                LIMIT 1
            ");
            $stmt->execute([
                'sku' => $sku,
                'id' => $excludeId,
            ]);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT 1
                FROM products
                WHERE sku = :sku
                LIMIT 1
            ");
            $stmt->execute(['sku' => $sku]);
        }

        return (bool)$stmt->fetchColumn();
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->pdo->prepare("
                SELECT 1
                FROM products
                WHERE slug = :slug AND id != :id
                LIMIT 1
            ");
            $stmt->execute([
                'slug' => $slug,
                'id' => $excludeId,
            ]);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT 1
                FROM products
                WHERE slug = :slug
                LIMIT 1
            ");
            $stmt->execute(['slug' => $slug]);
        }

        return (bool)$stmt->fetchColumn();
    }

    public function createFull(array $data): int
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO products (
                    sku, name, slug, description, description_html,
                    meta_title, meta_description, price_minor, currency, status
                )
                VALUES (
                    :sku, :name, :slug, :description, :description_html,
                    :meta_title, :meta_description, :price_minor, :currency, :status
                )
            ");

            $stmt->execute([
                'sku' => $data['sku'],
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] !== '' ? $data['description'] : null,
                'description_html' => $data['description_html'] !== '' ? $data['description_html'] : null,
                'meta_title' => $data['meta_title'] !== '' ? $data['meta_title'] : null,
                'meta_description' => $data['meta_description'] !== '' ? $data['meta_description'] : null,
                'price_minor' => (int)$data['price_minor'],
                'currency' => $data['currency'],
                'status' => $data['status'],
            ]);

            $productId = (int)$this->pdo->lastInsertId();

            $this->syncCategory($productId, (int)($data['category_id'] ?? 0));
            $this->syncInventory($productId, (int)($data['stock_on_hand'] ?? 0));

            $this->pdo->commit();
            return $productId;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateFull(int $id, array $data): void
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare("
                UPDATE products
                SET
                    sku = :sku,
                    name = :name,
                    slug = :slug,
                    description = :description,
                    description_html = :description_html,
                    meta_title = :meta_title,
                    meta_description = :meta_description,
                    price_minor = :price_minor,
                    currency = :currency,
                    status = :status
                WHERE id = :id
            ");

            $stmt->execute([
                'id' => $id,
                'sku' => $data['sku'],
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] !== '' ? $data['description'] : null,
                'description_html' => $data['description_html'] !== '' ? $data['description_html'] : null,
                'meta_title' => $data['meta_title'] !== '' ? $data['meta_title'] : null,
                'meta_description' => $data['meta_description'] !== '' ? $data['meta_description'] : null,
                'price_minor' => (int)$data['price_minor'],
                'currency' => $data['currency'],
                'status' => $data['status'],
            ]);

            $this->syncCategory($id, (int)($data['category_id'] ?? 0));
            $this->syncInventory($id, (int)($data['stock_on_hand'] ?? 0));

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function setStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE products
            SET status = :status
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);
    }

    private function syncCategory(int $productId, int $categoryId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM product_categories WHERE product_id = :product_id");
        $stmt->execute(['product_id' => $productId]);

        if ($categoryId > 0) {
            $stmt = $this->pdo->prepare("
                INSERT INTO product_categories (product_id, category_id)
                VALUES (:product_id, :category_id)
            ");
            $stmt->execute([
                'product_id' => $productId,
                'category_id' => $categoryId,
            ]);
        }
    }

    private function syncInventory(int $productId, int $stockOnHand): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO inventory (product_id, stock_on_hand, stock_reserved)
            VALUES (:product_id, :stock_on_hand, 0)
            ON DUPLICATE KEY UPDATE stock_on_hand = VALUES(stock_on_hand)
        ");

        $stmt->execute([
            'product_id' => $productId,
            'stock_on_hand' => $stockOnHand,
        ]);
    }

    public function allActiveByCategoryId(int $categoryId, int $limit = 48): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                p.id,
                p.name,
                p.slug,
                p.description,
                p.price_minor,
                p.currency,
                p.status,
                (
                    SELECT pi.url
                    FROM product_images pi
                    WHERE pi.product_id = p.id
                    ORDER BY pi.sort_order ASC, pi.id ASC
                    LIMIT 1
                ) AS primary_image
            FROM products p
            INNER JOIN product_categories pc ON pc.product_id = p.id
            WHERE pc.category_id = :category_id
            AND p.status = 'ACTIVE'
            ORDER BY p.created_at DESC
            LIMIT :lim
        ");

        $stmt->bindValue(':category_id', $categoryId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function allImagesForProduct(int $productId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, url, alt_text, sort_order
            FROM product_images
            WHERE product_id = :product_id
            ORDER BY sort_order ASC, id ASC
        ");

        $stmt->execute(['product_id' => $productId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}