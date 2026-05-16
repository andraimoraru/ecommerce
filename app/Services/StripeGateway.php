<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class StripeGateway
{
    private string $secretKey;
    private string $webhookSecret;

    // Load the Stripe credentials from the environment.
    public function __construct()
    {
        $this->secretKey = trim((string)env('STRIPE_SECRET_KEY', ''));
        $this->webhookSecret = trim((string)env('STRIPE_WEBHOOK_SECRET', ''));
    }

    // Report whether Checkout API calls can be made.
    public function isConfigured(): bool
    {
        return $this->secretKey !== '';
    }

    /**
     * @param array<string,mixed> $order
     * @param array<int,array<string,mixed>> $items
     * @return array<string,mixed>
     */
    // Create a hosted Stripe Checkout Session for one order.
    public function createCheckoutSession(
        array $order,
        array $items,
        string $successUrl,
        string $cancelUrl,
        string $customerEmail
    ): array {
        $this->assertApiConfigured();

        $payload = [
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string)$order['id'],
            'customer_email' => $customerEmail,
            'metadata' => [
                'order_id' => (string)$order['id'],
                'order_number' => (string)$order['order_number'],
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'order_id' => (string)$order['id'],
                    'order_number' => (string)$order['order_number'],
                ],
            ],
        ];

        foreach ($items as $index => $item) {
            $payload['line_items'][$index] = [
                'price_data' => [
                    'currency' => strtolower((string)($order['currency'] ?? 'GBP')),
                    'unit_amount' => (int)$item['unit_price_minor'],
                    'product_data' => [
                        'name' => (string)$item['product_name'],
                        'metadata' => [
                            'product_id' => (string)$item['product_id'],
                            'sku' => (string)($item['sku'] ?? ''),
                        ],
                    ],
                ],
                'quantity' => (int)$item['quantity'],
            ];
        }

        return $this->request('POST', '/v1/checkout/sessions', $payload);
    }

    /**
     * @return array<string,mixed>
     */
    // Fetch a Checkout Session from Stripe after redirect.
    public function retrieveCheckoutSession(string $sessionId): array
    {
        $this->assertApiConfigured();

        return $this->request('GET', '/v1/checkout/sessions/' . rawurlencode($sessionId));
    }

    /**
     * @return array<string,mixed>
     */
    // Validate the signed webhook payload and decode the event.
    public function verifyWebhookEvent(string $payload, string $signatureHeader, int $tolerance = 300): array
    {
        if ($this->webhookSecret === '') {
            throw new RuntimeException('Stripe webhook secret is not configured.');
        }

        $parts = $this->parseSignatureHeader($signatureHeader);
        $timestamp = (int)($parts['t'] ?? 0);
        $signatures = $parts['v1'] ?? [];

        if ($timestamp <= 0 || $signatures === []) {
            throw new RuntimeException('Stripe signature header is invalid.');
        }

        if (abs(time() - $timestamp) > $tolerance) {
            throw new RuntimeException('Stripe webhook signature is outside the allowed tolerance.');
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $this->webhookSecret);
        $verified = false;

        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                $verified = true;
                break;
            }
        }

        if (!$verified) {
            throw new RuntimeException('Stripe webhook signature verification failed.');
        }

        $event = json_decode($payload, true);
        if (!is_array($event)) {
            throw new RuntimeException('Stripe webhook payload is not valid JSON.');
        }

        return $event;
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    // Send one authenticated request to Stripe's REST API.
    private function request(string $method, string $path, array $payload = []): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The cURL extension is required for Stripe payments.');
        }

        $url = 'https://api.stripe.com' . $path;
        $body = '';

        if ($method === 'GET' && $payload !== []) {
            $url .= '?' . http_build_query($payload, '', '&', PHP_QUERY_RFC3986);
        }

        if ($method !== 'GET') {
            $body = http_build_query($payload, '', '&', PHP_QUERY_RFC3986);
        }

        $curl = curl_init($url);
        if ($curl === false) {
            throw new RuntimeException('Unable to initialise a Stripe API request.');
        }

        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->secretKey,
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($curl);
        $statusCode = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if (!is_string($response)) {
            throw new RuntimeException('Stripe API request failed: ' . ($curlError !== '' ? $curlError : 'unknown error'));
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Stripe API response was not valid JSON.');
        }

        if ($statusCode >= 400) {
            $message = $decoded['error']['message'] ?? 'Stripe API request failed.';
            throw new RuntimeException((string)$message);
        }

        return $decoded;
    }

    // Ensure secret-key based Stripe requests are enabled.
    private function assertApiConfigured(): void
    {
        if ($this->secretKey === '') {
            throw new RuntimeException('Stripe is not configured. Add STRIPE_SECRET_KEY to your .env file.');
        }
    }

    /**
     * @return array<string,mixed>
     */
    // Parse the comma-separated Stripe-Signature header values.
    private function parseSignatureHeader(string $signatureHeader): array
    {
        $parsed = [
            'v1' => [],
        ];

        foreach (explode(',', $signatureHeader) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($key === null || $value === null) {
                continue;
            }

            if ($key === 'v1') {
                $parsed['v1'][] = $value;
                continue;
            }

            $parsed[$key] = $value;
        }

        return $parsed;
    }
}
