<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Address
{
    private PDO $pdo;

    // Bootstrap a PDO-backed address model.
    public function __construct()
    {
        $db = new Database();
        $pdo = $db->getPdo();

        if (!$pdo instanceof PDO) {
            throw new \RuntimeException(
                'DB connection not available: ' . ($db->getError() ?? 'unknown')
            );
        }

        $this->pdo = $pdo;
    }

    // Create a reusable address record from checkout or account data.
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO addresses (
                first_name,
                last_name,
                email,
                phone,
                line1,
                line2,
                city,
                region,
                postcode,
                country_name,
                is_snapshot,
                created_at,
                updated_at
            ) VALUES (
                :first_name,
                :last_name,
                :email,
                :phone,
                :line1,
                :line2,
                :city,
                :region,
                :postcode,
                :country_name,
                0,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            'first_name'   => $data['first_name'],
            'last_name'    => $data['last_name'],
            'email'        => ($data['email'] ?? '') !== '' ? $data['email'] : null,
            'phone'        => ($data['phone'] ?? '') !== '' ? $data['phone'] : null,
            'line1'        => $data['line1'],
            'line2'        => ($data['line2'] ?? '') !== '' ? $data['line2'] : null,
            'city'         => $data['city'],
            'region'       => ($data['region'] ?? '') !== '' ? $data['region'] : null,
            'postcode'     => $data['postcode'],
            'country_name' => $data['country_name'],
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    // Attach an address to a user and optionally mark it as a default.
    public function linkToUser(
        int $userId,
        int $addressId,
        ?string $label = null,
        bool $isDefaultShipping = false,
        bool $isDefaultBilling = false
    ): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO user_addresses (
                user_id,
                address_id,
                label,
                is_default_shipping,
                is_default_billing,
                created_at
            ) VALUES (
                :user_id,
                :address_id,
                :label,
                :is_default_shipping,
                :is_default_billing,
                NOW()
            )
        ");

        $stmt->execute([
            'user_id'             => $userId,
            'address_id'          => $addressId,
            'label'               => $label,
            'is_default_shipping' => $isDefaultShipping ? 1 : 0,
            'is_default_billing'  => $isDefaultBilling ? 1 : 0,
        ]);
    }

    // Fetch the latest default shipping address for a user.
    public function getDefaultShippingForUser(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                a.id,
                a.first_name,
                a.last_name,
                a.email,
                a.phone,
                a.line1,
                a.line2,
                a.city,
                a.region,
                a.postcode,
                a.country_name
            FROM user_addresses ua
            INNER JOIN addresses a ON a.id = ua.address_id
            WHERE ua.user_id = :user_id
              AND ua.is_default_shipping = 1
            ORDER BY ua.created_at DESC
            LIMIT 1
        ");

        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    // Fetch the latest default billing address for a user.
    public function getDefaultBillingForUser(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                a.id,
                a.first_name,
                a.last_name,
                a.email,
                a.phone,
                a.line1,
                a.line2,
                a.city,
                a.region,
                a.postcode,
                a.country_name
            FROM user_addresses ua
            INNER JOIN addresses a ON a.id = ua.address_id
            WHERE ua.user_id = :user_id
              AND ua.is_default_billing = 1
            ORDER BY ua.created_at DESC
            LIMIT 1
        ");

        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

        /** @return array<int, array<string,mixed>> */
    public function allForUser(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                ua.label,
                ua.is_default_shipping,
                ua.is_default_billing,
                ua.created_at AS linked_at,
                a.id,
                a.first_name,
                a.last_name,
                a.email,
                a.phone,
                a.line1,
                a.line2,
                a.city,
                a.region,
                a.postcode,
                a.country_name
            FROM user_addresses ua
            INNER JOIN addresses a ON a.id = ua.address_id
            WHERE ua.user_id = :user_id
            ORDER BY ua.is_default_shipping DESC, ua.is_default_billing DESC, ua.created_at DESC
        ");

        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countForUser(int $userId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM user_addresses
            WHERE user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);

        return (int)$stmt->fetchColumn();
    }
}
