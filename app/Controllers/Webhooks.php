<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\PaymentEvent;
use App\Services\StripeGateway;
use RuntimeException;

final class Webhooks
{
    private const PAYMENT_EVENT_TYPES = [
        'checkout.session.completed',
        'payment_intent.succeeded',
        'payment_intent.payment_failed',
        'charge.succeeded',
        'charge.failed',
        'charge.refunded',
    ];

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

        $eventId = trim((string)($event['id'] ?? ''));
        $eventType = trim((string)($event['type'] ?? ''));

        if ($eventId === '' || $eventType === '') {
            http_response_code(400);
            echo 'Stripe webhook event is missing an id or type.';
            return;
        }

        if (!in_array($eventType, self::PAYMENT_EVENT_TYPES, true)) {
            http_response_code(200);
            echo 'ok';
            return;
        }

        $paymentEventModel = new PaymentEvent();

        if ($paymentEventModel->exists('STRIPE', $eventId)) {
            http_response_code(200);
            echo 'ok';
            return;
        }

        // Persist the raw event first; this gives us an audit trail and prevents duplicate processing.
        $paymentEventModel->create('STRIPE', $eventId, $eventType, $payload);

        if (($event['type'] ?? '') === 'checkout.session.completed') {
            $session = $event['data']['object'] ?? [];
            $orderId = (int)($session['metadata']['order_id'] ?? $session['client_reference_id'] ?? 0);
            $paymentStatus = (string)($session['payment_status'] ?? '');
            $sessionId = (string)($session['id'] ?? '');
            $paymentIntentId = !empty($session['payment_intent']) ? (string)$session['payment_intent'] : null;
            $amountTotal = (int)($session['amount_total'] ?? 0);
            $currency = strtoupper((string)($session['currency'] ?? 'GBP'));

            if ($orderId > 0 && $paymentStatus === 'paid') {
                (new OrderPayment())->upsertStripePayment(
                    $orderId,
                    'SUCCEEDED',
                    $amountTotal,
                    $currency,
                    $sessionId,
                    $paymentIntentId
                );

                (new Order())->updateStatus($orderId, 'PAID');
            }
        }

        http_response_code(200);
        echo 'ok';
    }
}
