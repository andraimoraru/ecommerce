<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class MarketingSettings
{
    private PDO $pdo;

    // Bootstrap a PDO-backed marketing settings model.
    public function __construct()
    {
        $db = new Database();
        $pdo = $db->getPdo();

        if (!$pdo instanceof PDO) {
            throw new \RuntimeException('DB connection not available: ' . ($db->getError() ?? 'unknown'));
        }

        $this->pdo = $pdo;
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    // Return settings keyed by social channel for admin display/editing.
    public function allByChannel(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, channel, profile_url, username, page_id, access_token, created_at, updated_at
            FROM marketing_settings
            WHERE channel IN ('INSTAGRAM', 'FACEBOOK')
            ORDER BY channel ASC
        ");

        $settings = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $settings[(string)$row['channel']] = $row;
        }

        return $settings;
    }

    /**
     * @param array<string,string|null> $data
     */
    // Create or update one channel without exposing the saved token back to the form.
    public function saveForChannel(string $channel, array $data): void
    {
        $existing = $this->findByChannel($channel);

        if ($existing) {
            $sql = "
                UPDATE marketing_settings
                SET
                    profile_url = :profile_url,
                    username = :username,
                    page_id = :page_id,
            ";

            if (($data['access_token'] ?? '') !== '') {
                $sql .= " access_token = :access_token,";
            }

            $sql .= "
                    updated_at = NOW()
                WHERE id = :id
            ";

            $stmt = $this->pdo->prepare($sql);
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO marketing_settings (
                    channel,
                    profile_url,
                    username,
                    page_id,
                    access_token,
                    created_at,
                    updated_at
                ) VALUES (
                    :channel,
                    :profile_url,
                    :username,
                    :page_id,
                    :access_token,
                    NOW(),
                    NOW()
                )
            ");
        }

        $params = [
            'profile_url' => $data['profile_url'] !== '' ? $data['profile_url'] : null,
            'username' => $data['username'] !== '' ? $data['username'] : null,
            'page_id' => $data['page_id'] !== '' ? $data['page_id'] : null,
        ];

        if ($existing) {
            $params['id'] = (int)$existing['id'];
        } else {
            $params['channel'] = $channel;
        }

        if (!$existing || ($data['access_token'] ?? '') !== '') {
            $params['access_token'] = $data['access_token'] !== '' ? $data['access_token'] : null;
        }

        $stmt->execute($params);
    }

    // Fetch one settings row by channel.
    private function findByChannel(string $channel): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, channel, profile_url, username, page_id, access_token, created_at, updated_at
            FROM marketing_settings
            WHERE channel = :channel
            LIMIT 1
        ");

        $stmt->execute(['channel' => $channel]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}
