<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class ProductImage
{
    private PDO $pdo;

    // Bootstrap a PDO-backed product image model.
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
    // Return all images for a product in gallery order.
    public function forProduct(int $productId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, product_id, url, alt_text, sort_order, created_at
            FROM product_images
            WHERE product_id = :product_id
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute(['product_id' => $productId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create a new image row for the given product.
    public function create(int $productId, string $url, ?string $altText, int $sortOrder): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO product_images (product_id, url, alt_text, sort_order)
            VALUES (:product_id, :url, :alt_text, :sort_order)
        ");

        $stmt->execute([
            'product_id' => $productId,
            'url' => $url,
            'alt_text' => $altText !== '' ? $altText : null,
            'sort_order' => $sortOrder,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    // Fetch one image that belongs to the given product.
    public function findById(int $id, int $productId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, product_id, url, alt_text, sort_order, created_at
            FROM product_images
            WHERE id = :id AND product_id = :product_id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id,
            'product_id' => $productId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Update the editable metadata for one image.
    public function updateMeta(int $id, int $productId, ?string $altText, int $sortOrder): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE product_images
            SET alt_text = :alt_text,
                sort_order = :sort_order
            WHERE id = :id AND product_id = :product_id
        ");

        $stmt->execute([
            'id' => $id,
            'product_id' => $productId,
            'alt_text' => $altText !== '' ? $altText : null,
            'sort_order' => $sortOrder,
        ]);
    }

    // Delete one image row for the given product.
    public function delete(int $id, int $productId): void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM product_images
            WHERE id = :id AND product_id = :product_id
        ");

        $stmt->execute([
            'id' => $id,
            'product_id' => $productId,
        ]);
    }
}
