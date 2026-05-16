<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Order;
use App\Services\StripeGateway;
use RuntimeException;

final class Webhooks
{
    // Process signed Stripe events that update order payment state.
    public function stripe(): void
    {
        $payload = (string)file_get_contents('php://input');
        $signature = (string)($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');

        if ($payload === '' || $signature === '') {
            http_response_code(400);
            echo 'Missing Stripe webhook payload.';
            return;
        }

        try {
            $event = (new StripeGateway())->verifyWebhookEvent($payload, $signature);
        } catch (RuntimeException $exception) {
            http_response_code(400);
            echo $exception->getMessage();
            return;
        }

        if (($event['type'] ?? '') === 'checkout.session.completed') {
            $session = $event['data']['object'] ?? [];
            $orderId = (int)($session['metadata']['order_id'] ?? $session['client_reference_id'] ?? 0);
            $paymentStatus = (string)($session['payment_status'] ?? '');

            if ($orderId > 0 && $paymentStatus === 'paid') {
                (new Order())->updateStatus($orderId, 'PAID');
            }
        }

        http_response_code(200);
        echo 'ok';
    }
}
