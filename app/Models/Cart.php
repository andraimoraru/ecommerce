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

        foreach ($ids as $i => $id) {
            $stmt->bindValue($i + 1, $id, PDO::PARAM_INT);
        }

        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $items = [];
        $totalMinor = 0;

        foreach ($products as $p) {
            $productId = (int)$p['id'];
            $qty = (int)($cart[$productId] ?? 0);

            if ($qty <= 0) {
                continue;
            }

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
                'line_total_minor' => $lineTotal,
            ];

            $totalMinor += $lineTotal;
        }

        return [
            'items' => $items,
            'total_minor' => $totalMinor,
        ];
    }

    // Remove all items from the session cart.
    public function clear(): void
    {
        unset($_SESSION['cart']);
    }
}
