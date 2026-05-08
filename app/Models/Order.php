<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

final class Order
{
    private PDO $pdo;

    // Bootstrap a PDO-backed order model.
    public function __construct()
    {
        $db = new Database();
        $pdo = $db->getPdo();

        if (!$pdo instanceof PDO) {
            throw new \RuntimeException('DB connection not available: ' . ($db->getError() ?? 'unknown'));
        }

        $this->pdo = $pdo;
    }

    // Generate the next year-prefixed order number.
    public function nextOrderNumber(): string
    {
        $prefix = date('Y') . '-';

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) + 1
            FROM orders
            WHERE order_number LIKE :prefix
        ");
        $stmt->execute(['prefix' => $prefix . '%']);

        $sequence = (int)$stmt->fetchColumn();

        return $prefix . str_pad((string)$sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * @param array<string,mixed> $orderData
     * @param array<int,array<string,mixed>> $items
     * @param array<string,string|null> $shipping
     * @param array<string,string|null> $billing
     */
    // Create the order, addresses, and line items inside one transaction.
    public function createFull(array $orderData, array $items, array $shipping, array $billing): int
    {
        $this->pdo->beginTransaction();

        try {
            // 1) create order
            $stmt = $this->pdo->prepare("
                INSERT INTO orders (
                    order_number,
                    user_id,
                    status,
                    currency,
                    subtotal_minor,
                    shipping_minor,
                    tax_minor,
                    discount_minor,
                    total_minor,
                    placed_at,
                    shipping_address_id,
                    billing_address_id,
                    customer_email,
                    customer_first_name,
                    customer_last_name,
                    customer_phone,
                    created_at,
                    updated_at
                ) VALUES (
                    :order_number,
                    :user_id,
                    :status,
                    :currency,
                    :subtotal_minor,
                    :shipping_minor,
                    :tax_minor,
                    :discount_minor,
                    :total_minor,
                    NOW(),
                    NULL,
                    NULL,
                    :customer_email,
                    :customer_first_name,
                    :customer_last_name,
                    :customer_phone,
                    NOW(),
                    NOW()
                )
            ");

            $stmt->execute([
                'order_number' => $orderData['order_number'],
                'user_id' => $orderData['user_id'],
                'status' => $orderData['status'],
                'currency' => $orderData['currency'],
                'subtotal_minor' => $orderData['subtotal_minor'],
                'shipping_minor' => $orderData['shipping_minor'],
                'tax_minor' => $orderData['tax_minor'],
                'discount_minor' => $orderData['discount_minor'],
                'total_minor' => $orderData['total_minor'],
                'customer_email' => $orderData['customer_email'],
                'customer_first_name' => $orderData['customer_first_name'],
                'customer_last_name' => $orderData['customer_last_name'],
                'customer_phone' => $orderData['customer_phone'],
            ]);

            $orderId = (int)$this->pdo->lastInsertId();

            // 2) shipping address
            $shippingAddressId = $this->insertOrderAddress($orderId, 'SHIPPING', $shipping);

            // 3) billing address
            $billingAddressId = $this->insertOrderAddress($orderId, 'BILLING', $billing);

            // 4) update order with address ids
            $stmt = $this->pdo->prepare("
                UPDATE orders
                SET shipping_address_id = :shipping_address_id,
                    billing_address_id = :billing_address_id,
                    updated_at = NOW()
                WHERE id = :id
            ");

            $stmt->execute([
                'id' => $orderId,
                'shipping_address_id' => $shippingAddressId,
                'billing_address_id' => $billingAddressId,
            ]);

            // 5) order items
            $stmt = $this->pdo->prepare("
                INSERT INTO order_items (
                    order_id,
                    product_id,
                    sku,
                    product_name,
                    unit_price_minor,
                    quantity,
                    line_total_minor,
                    created_at
                ) VALUES (
                    :order_id,
                    :product_id,
                    :sku,
                    :product_name,
                    :unit_price_minor,
                    :quantity,
                    :line_total_minor,
                    NOW()
                )
            ");

            foreach ($items as $item) {
                $stmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'sku' => $item['sku'],
                    'product_name' => $item['product_name'],
                    'unit_price_minor' => $item['unit_price_minor'],
                    'quantity' => $item['quantity'],
                    'line_total_minor' => $item['line_total_minor'],
                ]);
            }

            $this->pdo->commit();
            return $orderId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @param array<string,string|null> $address
     */
    // Store a snapshot of the shipping or billing address for an order.
    private function insertOrderAddress(int $orderId, string $type, array $address): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO order_addresses (
                order_id,
                type,
                first_name,
                last_name,
                phone,
                line1,
                line2,
                city,
                region,
                postcode,
                country_name,
                created_at
            ) VALUES (
                :order_id,
                :type,
                :first_name,
                :last_name,
                :phone,
                :line1,
                :line2,
                :city,
                :region,
                :postcode,
                :country_name,
                NOW()
            )
        ");

        $stmt->execute([
            'order_id' => $orderId,
            'type' => $type,
            'first_name' => $address['first_name'],
            'last_name' => $address['last_name'],
            'phone' => $address['phone'],
            'line1' => $address['line1'],
            'line2' => $address['line2'],
            'city' => $address['city'],
            'region' => $address['region'],
            'postcode' => $address['postcode'],
            'country_name' => $address['country_name'],
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    // Fetch the summary fields shown on the order success page.
    public function findSummaryById(int $orderId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                order_number,
                status,
                currency,
                subtotal_minor,
                shipping_minor,
                tax_minor,
                discount_minor,
                total_minor,
                customer_email,
                customer_first_name,
                customer_last_name,
                customer_phone,
                placed_at
            FROM orders
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute(['id' => $orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /** @return array<int, array<string,mixed>> */
    // Fetch every line item for a given order.
    public function findItemsByOrderId(int $orderId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                product_id,
                sku,
                product_name,
                unit_price_minor,
                quantity,
                line_total_minor
            FROM order_items
            WHERE order_id = :order_id
            ORDER BY id ASC
        ");

        $stmt->execute(['order_id' => $orderId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
