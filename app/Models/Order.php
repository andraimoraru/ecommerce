<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException;
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
            SELECT order_number
            FROM orders
            WHERE order_number LIKE :prefix
            ORDER BY order_number DESC
            LIMIT 1
        ");
        $stmt->execute(['prefix' => $prefix . '%']);

        $latestOrderNumber = (string)($stmt->fetchColumn() ?: '');
        $sequence = 1;

        if ($latestOrderNumber !== '' && str_starts_with($latestOrderNumber, $prefix)) {
            $sequence = ((int)substr($latestOrderNumber, strlen($prefix))) + 1;
        }

        return $prefix . str_pad((string)$sequence, 6, '0', STR_PAD_LEFT);
    }

    /** @return array<int, array<string,mixed>> */
    // Return the latest orders for the admin listing.
    public function allAdmin(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                o.id,
                o.order_number,
                o.status,
                o.currency,
                o.total_minor,
                o.customer_email,
                o.customer_first_name,
                o.customer_last_name,
                o.placed_at,
                o.created_at,
                COUNT(oi.id) AS item_count
            FROM orders o
            LEFT JOIN order_items oi ON oi.order_id = o.id
            GROUP BY
                o.id,
                o.order_number,
                o.status,
                o.currency,
                o.total_minor,
                o.customer_email,
                o.customer_first_name,
                o.customer_last_name,
                o.placed_at,
                o.created_at
            ORDER BY COALESCE(o.placed_at, o.created_at) DESC, o.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        for ($attempt = 0; $attempt < 3; $attempt++) {
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
            } catch (PDOException $exception) {
                $this->pdo->rollBack();

                if ($this->isDuplicateOrderNumberError($exception) && $attempt < 2) {
                    $orderData['order_number'] = $this->nextOrderNumber();
                    continue;
                }

                throw $exception;
            } catch (Throwable $e) {
                $this->pdo->rollBack();
                throw $e;
            }
        }

        throw new \RuntimeException('Unable to create a unique order number.');
    }

    // Detect duplicate-key errors caused by colliding order numbers.
    private function isDuplicateOrderNumberError(PDOException $exception): bool
    {
        $message = $exception->getMessage();

        return $exception->getCode() === '23000'
            && str_contains($message, 'uq_orders_order_number');
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

    // Fetch one order summary for the admin detail page.
    public function findAdminById(int $orderId): ?array
    {
        return $this->findSummaryById($orderId);
    }

    /** @return array<int, array<string,mixed>> */
    // Fetch every line item for a given order.
    public function findItemsByOrderId(int $orderId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                oi.id,
                oi.product_id,
                oi.sku,
                oi.product_name,
                oi.unit_price_minor,
                oi.quantity,
                oi.line_total_minor,
                (
                    SELECT pi.url
                    FROM product_images pi
                    WHERE pi.product_id = oi.product_id
                    ORDER BY pi.sort_order ASC, pi.id ASC
                    LIMIT 1
                ) AS primary_image
            FROM order_items oi
            WHERE oi.order_id = :order_id
            ORDER BY oi.id ASC
        ");

        $stmt->execute(['order_id' => $orderId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<int, array<string,mixed>> */
    // Fetch the shipping and billing address snapshots stored against an order.
    public function findAddressesByOrderId(int $orderId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                type,
                first_name,
                last_name,
                phone,
                line1,
                line2,
                city,
                region,
                postcode,
                country_name
            FROM order_addresses
            WHERE order_id = :order_id
            ORDER BY id ASC
        ");

        $stmt->execute(['order_id' => $orderId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<string,mixed> $data
     */
    // Update one stored address snapshot for an order.
    public function updateAddress(int $addressId, int $orderId, array $data): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE order_addresses
            SET
                first_name = :first_name,
                last_name = :last_name,
                phone = :phone,
                line1 = :line1,
                line2 = :line2,
                city = :city,
                region = :region,
                postcode = :postcode,
                country_name = :country_name
            WHERE id = :id
              AND order_id = :order_id
        ");

        $stmt->execute([
            'id' => $addressId,
            'order_id' => $orderId,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] !== '' ? $data['phone'] : null,
            'line1' => $data['line1'],
            'line2' => $data['line2'] !== '' ? $data['line2'] : null,
            'city' => $data['city'],
            'region' => $data['region'] !== '' ? $data['region'] : null,
            'postcode' => $data['postcode'],
            'country_name' => $data['country_name'],
        ]);
    }

    /**
     * @param array<int,array<string,mixed>> $items
     */
    // Replace all line items for an order with a recalculated set.
    public function replaceItems(int $orderId, array $items): void
    {
        $delete = $this->pdo->prepare("DELETE FROM order_items WHERE order_id = :order_id");
        $delete->execute(['order_id' => $orderId]);

        $insert = $this->pdo->prepare("
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
            $insert->execute([
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'sku' => $item['sku'],
                'product_name' => $item['product_name'],
                'unit_price_minor' => $item['unit_price_minor'],
                'quantity' => $item['quantity'],
                'line_total_minor' => $item['line_total_minor'],
            ]);
        }
    }

    /**
     * @param array<string,mixed> $totals
     */
    // Update the stored monetary totals after item recalculation.
    public function updateTotals(int $orderId, array $totals): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE orders
            SET
                subtotal_minor = :subtotal_minor,
                discount_minor = :discount_minor,
                total_minor = :total_minor,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $orderId,
            'subtotal_minor' => (int)$totals['subtotal_minor'],
            'discount_minor' => (int)$totals['discount_minor'],
            'total_minor' => (int)$totals['total_minor'],
        ]);
    }

    /**
     * @param array<string,mixed> $totals
     * @param array<string,mixed> $shipping
     * @param array<int,array<string,mixed>> $items
     */
    // Persist shipping, line items, and recalculated totals in one transaction.
    public function updateEditableParts(int $orderId, array $totals, array $shipping, array $items): void
    {
        $this->pdo->beginTransaction();

        try {
            $this->replaceItems($orderId, $items);
            $this->updateTotals($orderId, $totals);
            $this->updateAddress((int)$shipping['id'], $orderId, $shipping);

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Update the current status of an order from the admin area.
    public function updateStatus(int $orderId, string $status): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE orders
            SET status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $orderId,
            'status' => $status,
        ]);
    }
}
