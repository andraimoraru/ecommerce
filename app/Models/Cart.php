<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Cart
{
    private PDO $pdo;

    // Bootstrap a PDO-backed cart helper.
    public function __construct()
    {
        $db = new Database();
        $pdo = $db->getPdo();

        if (!$pdo instanceof PDO) {
            throw new \RuntimeException('DB connection not available: ' . ($db->getError() ?? 'unknown'));
        }

        $this->pdo = $pdo;
    }

    // Hydrate the session cart with current product data and totals.
    public function getFull(): array
    {
        $cart = $_SESSION['cart'] ?? [];

        if (!$cart) {
            return [
                'items' => [],
                'total_minor' => 0,
            ];
        }

        $ids = array_map('intval', array_keys($cart));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $this->pdo->prepare("
            SELECT
                p.id,
                p.name,
                p.slug,
                p.sku,
                p.price_minor,
                p.currency,
                COALESCE(i.stock_on_hand, 0) AS stock_on_hand,
                COALESCE(i.stock_reserved, 0) AS stock_reserved,
                (
                    SELECT pi.url
                    FROM product_images pi
                    WHERE pi.product_id = p.id
                    ORDER BY pi.sort_order ASC, pi.id ASC
                    LIMIT 1
                ) AS primary_image
            FROM products p
            LEFT JOIN inventory i ON i.product_id = p.id
            WHERE p.id IN ($placeholders)
              AND p.status = 'ACTIVE'
        ");

        foreach ($ids as $i => $id) {
            $stmt->bindValue($i + 1, $id, PDO::PARAM_INT);
        }

        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $items = [];
        $totalMinor = 0;

        foreach ($products as $p) {
            $productId = (int)$p['id'];
            $availableQty = max(0, (int)$p['stock_on_hand'] - (int)$p['stock_reserved']);
            $qty = min((int)($cart[$productId] ?? 0), $availableQty);

            if ($qty <= 0) {
                unset($_SESSION['cart'][$productId]);
                continue;
            }

            $_SESSION['cart'][$productId] = $qty;

            $lineTotal = ((int)$p['price_minor']) * $qty;

            $items[] = [
                'product_id' => $productId,
                'name' => (string)$p['name'],
                'slug' => (string)$p['slug'],
                'sku' => (string)($p['sku'] ?? ''),
                'qty' => $qty,
                'price_minor' => (int)$p['price_minor'],
                'currency' => (string)($p['currency'] ?? 'GBP'),
                'primary_image' => $p['primary_image'] ?? null,
                'available_qty' => $availableQty,
                'line_total_minor' => $lineTotal,
            ];

            $totalMinor += $lineTotal;
        }

        return [
            'items' => $items,
            'total_minor' => $totalMinor,
        ];
    }

    // Return the quantity currently available to sell for one product.
    public function findAvailableQuantity(int $productId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT GREATEST(COALESCE(i.stock_on_hand, 0) - COALESCE(i.stock_reserved, 0), 0)
            FROM products p
            LEFT JOIN inventory i ON i.product_id = p.id
            WHERE p.id = :product_id
              AND p.status = 'ACTIVE'
            LIMIT 1
        ");

        $stmt->execute(['product_id' => $productId]);
        $available = $stmt->fetchColumn();

        return $available === false ? 0 : (int)$available;
    }

    // Remove all items from the session cart.
    public function clear(): void
    {
        unset($_SESSION['cart']);
    }
}
