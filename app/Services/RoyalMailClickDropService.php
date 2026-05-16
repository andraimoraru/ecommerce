<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class RoyalMailClickDropService
{
    private const API_BASE = 'https://api.parcel.royalmail.com/api/v1';

    private string $apiKey;

    // Load the Click & Drop API key from the environment.
    public function __construct()
    {
        $this->apiKey = trim((string)env('ROYAL_MAIL_CLICK_DROP_API_KEY', ''));
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    // Create one or more Click & Drop orders from a JSON payload.
    public function createOrders(array $payload): array
    {
        $this->assertConfigured();

        return $this->request('POST', '/Orders', $payload);
    }

    // Ensure the API key exists before making requests.
    private function assertConfigured(): void
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('Royal Mail Click & Drop is not configured. Add ROYAL_MAIL_CLICK_DROP_API_KEY to your .env file.');
        }
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    // Send one authenticated JSON request to Click & Drop.
    private function request(string $method, string $path, array $payload): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The cURL extension is required for Royal Mail Click & Drop.');
        }

        $curl = curl_init(self::API_BASE . $path);
        if ($curl === false) {
            throw new RuntimeException('Unable to initialise the Royal Mail API request.');
        }

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (!is_string($json)) {
            throw new RuntimeException('Unable to encode the Royal Mail shipment request.');
        }

        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($curl);
        $statusCode = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if (!is_string($response)) {
            throw new RuntimeException('Royal Mail request failed: ' . ($curlError !== '' ? $curlError : 'unknown error'));
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Royal Mail returned an invalid JSON response.');
        }

        if ($statusCode >= 400) {
            throw new RuntimeException($this->extractErrorMessage($decoded, $statusCode));
        }

        return $decoded;
    }

    /**
     * @param array<string,mixed> $response
     */
    // Extract a readable error message from the API response payload.
    private function extractErrorMessage(array $response, int $statusCode): string
    {
        if (!empty($response['message']) && is_string($response['message'])) {
            return $response['message'];
        }

        if (!empty($response['failedOrders'][0]['errors'][0]['message']) && is_string($response['failedOrders'][0]['errors'][0]['message'])) {
            return $response['failedOrders'][0]['errors'][0]['message'];
        }

        if (!empty($response[0][0]['message']) && is_string($response[0][0]['message'])) {
            return $response[0][0]['message'];
        }

        return 'Royal Mail request failed with status ' . $statusCode . '.';
    }
}
