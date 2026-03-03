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

    public function skuExists(string $sku): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM products WHERE sku = :sku LIMIT 1");
        $stmt->execute(['sku' => $sku]);
        return (bool)$stmt->fetchColumn();
    }

    public function slugExists(string $slug): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM products WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Creates:
     * - products row
     * - product_categories row (single category)
     * - inventory row
     * - product_images rows (0..n)
     */
    public function createFull(array $data): int
    {
        $this->pdo->beginTransaction();

        try {
            // 1) Product
            $stmt = $this->pdo->prepare("
                INSERT INTO products (sku, name, slug, description, price_minor, currency, is_active)
                VALUES (:sku, :name, :slug, :description, :price_minor, :currency, :is_active)
            ");
            $stmt->execute([
                'sku' => $data['sku'],
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] !== '' ? $data['description'] : null,
                'price_minor' => (int)$data['price_minor'],
                'currency' => $data['currency'] ?? 'GBP',
                'is_active' => (int)$data['is_active'],
            ]);

            $productId = (int)$this->pdo->lastInsertId();

            // 2) Category link (optional, but you want it in form)
            if (!empty($data['category_id'])) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO product_categories (product_id, category_id)
                    VALUES (:product_id, :category_id)
                ");
                $stmt->execute([
                    'product_id' => $productId,
                    'category_id' => (int)$data['category_id'],
                ]);
            }

            // 3) Inventory (your schema uses stock_on_hand/stock_reserved)
            $stmt = $this->pdo->prepare("
                INSERT INTO inventory (product_id, stock_on_hand, stock_reserved)
                VALUES (:product_id, :on_hand, :reserved)
            ");
            $stmt->execute([
                'product_id' => $productId,
                'on_hand' => (int)($data['stock_on_hand'] ?? 0),
                'reserved' => 0,
            ]);

            // 4) Images (URLs)
            $images = $data['images'] ?? [];
            if (is_array($images) && count($images) > 0) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO product_images (product_id, url, alt_text, sort_order)
                    VALUES (:product_id, :url, :alt_text, :sort_order)
                ");

                $sort = 0;
                foreach ($images as $img) {
                    $url = trim((string)($img['url'] ?? ''));
                    if ($url === '') continue;

                    $stmt->execute([
                        'product_id' => $productId,
                        'url' => $url,
                        'alt_text' => ($img['alt_text'] ?? '') !== '' ? trim((string)$img['alt_text']) : null,
                        'sort_order' => isset($img['sort_order']) ? (int)$img['sort_order'] : $sort,
                    ]);

                    $sort++;
                }
            }

            $this->pdo->commit();
            return $productId;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    /** @return array<int, array<string,mixed>> */
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
                p.is_active,
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

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}