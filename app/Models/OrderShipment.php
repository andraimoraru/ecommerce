<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class OrderShipment
{
    private PDO $pdo;

    // Bootstrap the shipment store and ensure its table exists.
    public function __construct()
    {
        $db = new Database();
        $pdo = $db->getPdo();

        if (!$pdo instanceof PDO) {
            throw new \RuntimeException('DB connection not available: ' . ($db->getError() ?? 'unknown'));
        }

        $this->pdo = $pdo;
        $this->ensureTable();
    }

    public function findByOrderId(int $orderId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                order_id,
                provider,
                status,
                service_code,
                shipping_cost_minor,
                currency,
                royal_mail_shipment_id,
                tracking_number,
                label_url,
                created_by_user_id,
                created_at,
                updated_at
            FROM order_shipments
            WHERE order_id = :order_id
            LIMIT 1
        ");

        $stmt->execute(['order_id' => $orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * @param array<string,mixed> $data
     */
    // Create or update the stored shipment details for an order.
    public function upsertForOrder(int $orderId, array $data): void
    {
        $existing = $this->findByOrderId($orderId);

        if ($existing) {
            $stmt = $this->pdo->prepare("
                UPDATE order_shipments
                SET
                    provider = :provider,
                    status = :status,
                    service_code = :service_code,
                    shipping_cost_minor = :shipping_cost_minor,
                    currency = :currency,
                    royal_mail_shipment_id = :royal_mail_shipment_id,
                    tracking_number = :tracking_number,
                    label_url = :label_url,
                    created_by_user_id = :created_by_user_id,
                    updated_at = NOW()
                WHERE order_id = :order_id
            ");
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO order_shipments (
                    order_id,
                    provider,
                    status,
                    service_code,
                    shipping_cost_minor,
                    currency,
                    royal_mail_shipment_id,
                    tracking_number,
                    label_url,
                    created_by_user_id,
                    created_at,
                    updated_at
                ) VALUES (
                    :order_id,
                    :provider,
                    :status,
                    :service_code,
                    :shipping_cost_minor,
                    :currency,
                    :royal_mail_shipment_id,
                    :tracking_number,
                    :label_url,
                    :created_by_user_id,
                    NOW(),
                    NOW()
                )
            ");
        }

        $stmt->execute([
            'order_id' => $orderId,
            'provider' => (string)($data['provider'] ?? 'ROYAL_MAIL'),
            'status' => (string)($data['status'] ?? 'LABEL_CREATED'),
            'service_code' => (string)($data['service_code'] ?? ''),
            'shipping_cost_minor' => (int)($data['shipping_cost_minor'] ?? 0),
            'currency' => (string)($data['currency'] ?? 'GBP'),
            'royal_mail_shipment_id' => (string)($data['royal_mail_shipment_id'] ?? ''),
            'tracking_number' => ($data['tracking_number'] ?? null) !== '' ? $data['tracking_number'] : null,
            'label_url' => ($data['label_url'] ?? null) !== '' ? $data['label_url'] : null,
            'created_by_user_id' => !empty($data['created_by_user_id']) ? (int)$data['created_by_user_id'] : null,
        ]);
    }

    // Create the shipment table on demand to match the current app schema.
    private function ensureTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS order_shipments (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                order_id INT UNSIGNED NOT NULL,
                provider ENUM('ROYAL_MAIL') NOT NULL,
                status ENUM('NOT_CREATED','LABEL_CREATED','SHIPPED','CANCELLED') NOT NULL DEFAULT 'NOT_CREATED',
                service_code VARCHAR(80) NOT NULL,
                shipping_cost_minor INT(10) UNSIGNED NOT NULL DEFAULT 0,
                currency CHAR(3) NOT NULL DEFAULT 'GBP',
                royal_mail_shipment_id VARCHAR(255) NULL,
                tracking_number VARCHAR(255) NULL,
                label_url VARCHAR(600) NULL,
                created_by_user_id BIGINT(20) UNSIGNED NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uq_order_shipments_order_id (order_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}
