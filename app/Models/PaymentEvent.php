<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class PaymentEvent
{
    private PDO $pdo;

    // Bootstrap a PDO-backed payment event audit log.
    public function __construct()
    {
        $db = new Database();
        $pdo = $db->getPdo();

        if (!$pdo instanceof PDO) {
            throw new \RuntimeException('DB connection not available: ' . ($db->getError() ?? 'unknown'));
        }

        $this->pdo = $pdo;
    }

    // Check Stripe event ids before processing so webhook retries stay idempotent.
    public function exists(string $provider, string $providerEventId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM payment_events
            WHERE provider = :provider
              AND provider_event_id = :provider_event_id
            LIMIT 1
        ");

        $stmt->execute([
            'provider' => $provider,
            'provider_event_id' => $providerEventId,
        ]);

        return (bool)$stmt->fetchColumn();
    }

    // Save the raw webhook payload as an audit trail before mutating payment/order state.
    public function create(string $provider, string $providerEventId, string $eventType, string $payloadJson): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO payment_events (
                provider,
                provider_event_id,
                event_type,
                payload_json,
                received_at
            ) VALUES (
                :provider,
                :provider_event_id,
                :event_type,
                :payload_json,
                NOW()
            )
        ");

        $stmt->execute([
            'provider' => $provider,
            'provider_event_id' => $providerEventId,
            'event_type' => $eventType,
            'payload_json' => $payloadJson,
        ]);
    }
}
