<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Product
{
    private PDO $pdo;

    // Bootstrap a PDO-backed product model.
    public function __construct()
    {
        $db = new Database();
        $pdo = $db->getPdo();

        if (!$pdo instanceof PDO) {
            throw new \RuntimeException('DB connection not available: ' . ($db->getError() ?? 'unknown'));
        }

        $this->pdo = $pdo;
    }

    // Return admin-facing products with category, inventory, and thumbnail data.
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

    // Return the latest active products for storefront listings.
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

    // Fetch one active product by slug for the storefront.
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

    // Fetch one product by id together with category and inventory data.
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

    // Return lightweight product data for admin order editing.
    public function allSellableForOrderEditor(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                p.id,
                p.sku,
                p.name,
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
            WHERE p.status IN ('ACTIVE', 'DRAFT')
            ORDER BY p.name ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check whether a SKU already exists, optionally excluding one product.
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

    // Check whether a slug already exists, optionally excluding one product.
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

    // Create a product and its related category/inventory records atomically.
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

    // Update a product and keep category/inventory data in sync.
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

    // Change only the lifecycle status for a product.
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

    // Replace the product-to-category link with the current selection.
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

    // Upsert the product's stock row so inventory stays editable in one place.
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

    // Return active products that belong to a specific category.
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

    // Return all gallery images for a product in display order.
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

    // Fetch the active products needed to hydrate the session cart.
    public function findManyForCart(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

        $stmt = $this->pdo->prepare("
            SELECT
                p.id,
                p.sku,
                p.name,
                p.slug,
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
            WHERE p.id IN ($placeholders)
            AND p.status = 'ACTIVE'
        ");

        foreach ($productIds as $i => $id) {
            $stmt->bindValue($i + 1, (int)$id, \PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
