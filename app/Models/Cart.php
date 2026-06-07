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
        if (!empty($_SESSION['user_id']) && empty($_SESSION['cart'])) {
            $this->loadUserCartIntoSession((int)$_SESSION['user_id']);
        }

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

        $this->saveSessionForCurrentUser();

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

    // Merge a user's saved active cart into the current session after login.
    public function mergeUserCartIntoSession(int $userId): void
    {
        $savedCart = $this->savedCartItemsForUser($userId);

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        foreach ($savedCart as $productId => $quantity) {
            $_SESSION['cart'][$productId] = (int)($_SESSION['cart'][$productId] ?? 0) + $quantity;
        }

        if ($_SESSION['cart'] === []) {
            return;
        }

        $this->saveSessionForUser($userId);
        $this->getFull();
    }

    // Persist the current session cart for the logged-in customer.
    public function saveSessionForCurrentUser(): void
    {
        if (empty($_SESSION['user_id'])) {
            return;
        }

        $this->saveSessionForUser((int)$_SESSION['user_id']);
    }

    // Remove all items from the session cart and close the active saved cart.
    public function clear(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $cartId = $this->activeCartIdForUser((int)$_SESSION['user_id'], false);

            if ($cartId !== null) {
                $delete = $this->pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id");
                $delete->execute(['cart_id' => $cartId]);

                $update = $this->pdo->prepare("
                    UPDATE carts
                    SET status = 'CHECKED_OUT',
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $update->execute(['id' => $cartId]);
            }
        }

        unset($_SESSION['cart']);
    }

    // Load the saved cart only when the session does not already contain items.
    private function loadUserCartIntoSession(int $userId): void
    {
        $_SESSION['cart'] = $this->savedCartItemsForUser($userId);
    }

    // Replace the active saved cart with the current session quantities.
    private function saveSessionForUser(int $userId): void
    {
        $sessionCart = $_SESSION['cart'] ?? [];
        $cart = [];

        if (is_array($sessionCart)) {
            foreach ($sessionCart as $productId => $quantity) {
                $productId = (int)$productId;
                $quantity = (int)$quantity;

                if ($productId > 0 && $quantity > 0) {
                    $cart[$productId] = $quantity;
                }
            }
        }

        $cartId = $this->activeCartIdForUser($userId, true);

        if ($cartId === null) {
            return;
        }

        $this->pdo->beginTransaction();

        try {
            $delete = $this->pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id");
            $delete->execute(['cart_id' => $cartId]);

            if ($cart !== []) {
                $insert = $this->pdo->prepare("
                    INSERT INTO cart_items (
                        cart_id,
                        product_id,
                        quantity,
                        price_minor,
                        created_at,
                        updated_at
                    )
                    SELECT
                        :cart_id,
                        p.id,
                        :quantity,
                        p.price_minor,
                        NOW(),
                        NOW()
                    FROM products p
                    WHERE p.id = :product_id
                    LIMIT 1
                ");

                foreach ($cart as $productId => $quantity) {
                    $insert->execute([
                        'cart_id' => $cartId,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                    ]);
                }
            }

            $update = $this->pdo->prepare("
                UPDATE carts
                SET session_id = :session_id,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $update->execute([
                'id' => $cartId,
                'session_id' => session_id() ?: null,
            ]);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    /** @return array<int,int> */
    // Return product quantities from a customer's active saved cart.
    private function savedCartItemsForUser(int $userId): array
    {
        $cartId = $this->activeCartIdForUser($userId, false);

        if ($cartId === null) {
            return [];
        }

        $stmt = $this->pdo->prepare("
            SELECT product_id, quantity
            FROM cart_items
            WHERE cart_id = :cart_id
        ");
        $stmt->execute(['cart_id' => $cartId]);

        $items = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $productId = (int)$row['product_id'];
            $quantity = (int)$row['quantity'];

            if ($productId > 0 && $quantity > 0) {
                $items[$productId] = $quantity;
            }
        }

        return $items;
    }

    // Return the active cart id, creating one when requested.
    private function activeCartIdForUser(int $userId, bool $create): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT id
            FROM carts
            WHERE user_id = :user_id
              AND status = 'ACTIVE'
            ORDER BY updated_at DESC, id DESC
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        $cartId = $stmt->fetchColumn();

        if ($cartId !== false) {
            return (int)$cartId;
        }

        if (!$create) {
            return null;
        }

        $insert = $this->pdo->prepare("
            INSERT INTO carts (
                user_id,
                session_id,
                status,
                created_at,
                updated_at
            ) VALUES (
                :user_id,
                :session_id,
                'ACTIVE',
                NOW(),
                NOW()
            )
        ");
        $insert->execute([
            'user_id' => $userId,
            'session_id' => session_id() ?: null,
        ]);

        return (int)$this->pdo->lastInsertId();
    }
}
