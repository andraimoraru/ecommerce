<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class OrderPayment
{
    private PDO $pdo;

    // Bootstrap a PDO-backed order payment model.
    public function __construct()
    {
        $db = new Database();
        $pdo = $db->getPdo();

        if (!$pdo instanceof PDO) {
            throw new \RuntimeException('DB connection not available: ' . ($db->getError() ?? 'unknown'));
        }

        $this->pdo = $pdo;
    }

    // Create or update the Stripe payment row linked to an order.
    public function upsertStripePayment(
        int $orderId,
        string $status,
        int $amountMinor,
        string $currency,
        string $checkoutSessionId,
        ?string $paymentIntentId
    ): void {
        $existing = $this->findStripePayment($orderId);

        if ($existing) {
            $stmt = $this->pdo->prepare("
                UPDATE order_payments
                SET
                    status = :status,
                    amount_minor = :amount_minor,
                    currency = :currency,
                    stripe_checkout_session_id = :stripe_checkout_session_id,
                    stripe_payment_intent_id = :stripe_payment_intent_id,
                    updated_at = NOW()
                WHERE id = :id
            ");

            $stmt->execute([
                'id' => (int)$existing['id'],
                'status' => $status,
                'amount_minor' => $amountMinor,
                'currency' => strtoupper($currency),
                'stripe_checkout_session_id' => $checkoutSessionId,
                'stripe_payment_intent_id' => $paymentIntentId,
            ]);

            return;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO order_payments (
                order_id,
                provider,
                status,
                amount_minor,
                currency,
                stripe_checkout_session_id,
                stripe_payment_intent_id,
                created_at,
                updated_at
            ) VALUES (
                :order_id,
                'STRIPE',
                :status,
                :amount_minor,
                :currency,
                :stripe_checkout_session_id,
                :stripe_payment_intent_id,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            'order_id' => $orderId,
            'status' => $status,
            'amount_minor' => $amountMinor,
            'currency' => strtoupper($currency),
            'stripe_checkout_session_id' => $checkoutSessionId,
            'stripe_payment_intent_id' => $paymentIntentId,
        ]);
    }

    // Find the current Stripe payment row for an order.
    private function findStripePayment(int $orderId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id
            FROM order_payments
            WHERE order_id = :order_id
              AND provider = 'STRIPE'
            ORDER BY id DESC
            LIMIT 1
        ");

        $stmt->execute(['order_id' => $orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}
