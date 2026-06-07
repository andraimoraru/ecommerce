<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Address;
use App\Models\OrderPayment;
use App\Services\StripeGateway;
use RuntimeException;

final class Checkout extends Controller
{
    // Render the checkout page, prefilled from saved addresses when available.
    public function index(): void
    {
        $cart = (new Cart())->getFull();

        if (!$cart['items']) {
            header('Location: ' . URLROOT . '/cart');
            exit;
        }

        $old = [
            'shipping_first_name' => '',
            'shipping_last_name' => '',
            'shipping_email' => '',
            'shipping_phone' => '',
            'shipping_line1' => '',
            'shipping_line2' => '',
            'shipping_city' => '',
            'shipping_region' => '',
            'shipping_postcode' => '',
            'shipping_country' => '',

            'billing_first_name' => '',
            'billing_last_name' => '',
            'billing_email' => '',
            'billing_phone' => '',
            'billing_line1' => '',
            'billing_line2' => '',
            'billing_city' => '',
            'billing_region' => '',
            'billing_postcode' => '',
            'billing_country' => '',

            'billing_same_as_shipping' => '',
            'save_address' => '',
        ];

        if (!empty($_SESSION['user_id'])) {
            $addressModel = new Address();

            $shipping = $addressModel->getDefaultShippingForUser((int)$_SESSION['user_id']);
            if ($shipping) {
                $old['shipping_first_name'] = (string)($shipping['first_name'] ?? '');
                $old['shipping_last_name'] = (string)($shipping['last_name'] ?? '');
                $old['shipping_email'] = (string)($shipping['email'] ?? '');
                $old['shipping_phone'] = (string)($shipping['phone'] ?? '');
                $old['shipping_line1'] = (string)($shipping['line1'] ?? '');
                $old['shipping_line2'] = (string)($shipping['line2'] ?? '');
                $old['shipping_city'] = (string)($shipping['city'] ?? '');
                $old['shipping_region'] = (string)($shipping['region'] ?? '');
                $old['shipping_postcode'] = (string)($shipping['postcode'] ?? '');
                $old['shipping_country'] = (string)($shipping['country_name'] ?? '');
            }

            $billing = $addressModel->getDefaultBillingForUser((int)$_SESSION['user_id']);
            if ($billing) {
                $old['billing_first_name'] = (string)($billing['first_name'] ?? '');
                $old['billing_last_name'] = (string)($billing['last_name'] ?? '');
                $old['billing_email'] = (string)($billing['email'] ?? '');
                $old['billing_phone'] = (string)($billing['phone'] ?? '');
                $old['billing_line1'] = (string)($billing['line1'] ?? '');
                $old['billing_line2'] = (string)($billing['line2'] ?? '');
                $old['billing_city'] = (string)($billing['city'] ?? '');
                $old['billing_region'] = (string)($billing['region'] ?? '');
                $old['billing_postcode'] = (string)($billing['postcode'] ?? '');
                $old['billing_country'] = (string)($billing['country_name'] ?? '');
            }
        }
        $this->renderCheckout($cart, [], $old);

    }

    // Validate checkout input and start Stripe Checkout without creating an order yet.
    public function store(): void
    {
        $cartModel = new Cart();
        $cart = $cartModel->getFull();

        if (!$cart['items']) {
            header('Location: ' . URLROOT . '/cart');
            exit;
        }

        $old = $this->collectCheckoutInput();
        $errors = $this->validateCheckoutInput($old);

        if ($errors) {
            $this->renderCheckout($cart, $errors, $old);
            return;
        }

        $billingSame = !empty($old['billing_same_as_shipping']);

        if ($billingSame) {
            $old['billing_first_name'] = $old['shipping_first_name'];
            $old['billing_last_name'] = $old['shipping_last_name'];
            $old['billing_email'] = $old['shipping_email'];
            $old['billing_phone'] = $old['shipping_phone'];
            $old['billing_line1'] = $old['shipping_line1'];
            $old['billing_line2'] = $old['shipping_line2'];
            $old['billing_city'] = $old['shipping_city'];
            $old['billing_region'] = $old['shipping_region'];
            $old['billing_postcode'] = $old['shipping_postcode'];
            $old['billing_country'] = $old['shipping_country'];
        }

        $shipping = [
            'first_name' => $old['shipping_first_name'],
            'last_name' => $old['shipping_last_name'],
            'phone' => $old['shipping_phone'] ?: null,
            'line1' => $old['shipping_line1'],
            'line2' => $old['shipping_line2'] ?: null,
            'city' => $old['shipping_city'],
            'region' => $old['shipping_region'] ?: null,
            'postcode' => $old['shipping_postcode'],
            'country_name' => $old['shipping_country'],
        ];

        $billing = [
            'first_name' => $old['billing_first_name'],
            'last_name' => $old['billing_last_name'],
            'phone' => $old['billing_phone'] ?: null,
            'line1' => $old['billing_line1'],
            'line2' => $old['billing_line2'] ?: null,
            'city' => $old['billing_city'],
            'region' => $old['billing_region'] ?: null,
            'postcode' => $old['billing_postcode'],
            'country_name' => $old['billing_country'],
        ];

        $items = $cart['items'];
        $subtotalMinor = (int)($cart['total_minor'] ?? 0);
        $shippingMinor = $this->calculateShippingMinor($old['shipping_country']);
        $totalMinor = $subtotalMinor + $shippingMinor;

        $mappedItems = array_map(static function (array $item): array {
            return [
                'product_id' => (int)$item['product_id'],
                'sku' => (string)($item['sku'] ?? ''),
                'product_name' => (string)($item['name'] ?? ''),
                'unit_price_minor' => (int)$item['price_minor'],
                'quantity' => (int)$item['qty'],
                'line_total_minor' => (int)$item['line_total_minor'],
            ];
        }, $items);

        $checkoutReference = 'checkout_' . bin2hex(random_bytes(12));
        $orderData = [
            'order_number' => $checkoutReference,
            'user_id' => $_SESSION['user_id'] ?? null,
            'status' => 'PAID',
            'currency' => 'GBP',
            'subtotal_minor' => $subtotalMinor,
            'shipping_minor' => $shippingMinor,
            'tax_minor' => 0,
            'discount_minor' => 0,
            'total_minor' => $totalMinor,
            'customer_email' => $old['shipping_email'],
            'customer_first_name' => $old['shipping_first_name'],
            'customer_last_name' => $old['shipping_last_name'],
            'customer_phone' => $old['shipping_phone'] ?: null,
        ];

        $stripe = new StripeGateway();

        try {
            $checkoutSession = $stripe->createCheckoutSession(
                array_merge($orderData, ['id' => $checkoutReference]),
                $mappedItems,
                URLROOT . '/checkout/success?session_id={CHECKOUT_SESSION_ID}',
                URLROOT . '/checkout/cancel',
                $old['shipping_email']
            );
        } catch (RuntimeException $exception) {
            $errors['payment'] = $exception->getMessage();
            $this->renderCheckout($cart, $errors, $old);
            return;
        }

        $checkoutUrl = (string)($checkoutSession['url'] ?? '');
        $checkoutSessionId = (string)($checkoutSession['id'] ?? '');

        if ($checkoutUrl === '') {
            $errors['payment'] = 'Stripe did not return a checkout URL.';
            $this->renderCheckout($cart, $errors, $old);
            return;
        }

        if ($checkoutSessionId === '') {
            $errors['payment'] = 'Stripe did not return a checkout session id.';
            $this->renderCheckout($cart, $errors, $old);
            return;
        }

        $_SESSION['pending_checkout_sessions'][$checkoutSessionId] = [
            'order_data' => $orderData,
            'items' => $mappedItems,
            'shipping' => $shipping,
            'billing' => $billing,
            'save_address' => [
                'enabled' => !empty($_SESSION['user_id']) && !empty($old['save_address']),
                'billing_same_as_shipping' => $billingSame,
                'shipping_email' => $old['shipping_email'],
                'shipping' => $shipping,
            ],
        ];

        header('Location: ' . $checkoutUrl);
        exit;
    }

    // Confirm the Stripe session result before showing the order summary.
    public function success(): void
    {
        $sessionId = trim((string)($_GET['session_id'] ?? ''));
        $orderId = 0;

        if ($sessionId !== '') {
            $orderPaymentModel = new OrderPayment();
            $existingOrderId = $orderPaymentModel->findOrderIdByCheckoutSessionId($sessionId);

            if ($existingOrderId !== null) {
                $orderId = $existingOrderId;
            }

            try {
                $session = (new StripeGateway())->retrieveCheckoutSession($sessionId);
            } catch (RuntimeException $exception) {
                http_response_code(400);
                echo $exception->getMessage();
                return;
            }

            $paymentStatus = (string)($session['payment_status'] ?? '');
            $amountTotal = (int)($session['amount_total'] ?? 0);
            $currency = strtoupper((string)($session['currency'] ?? ''));

            if ($paymentStatus !== 'paid') {
                header('Location: ' . URLROOT . '/checkout/cancel');
                exit;
            }

            if ($orderId <= 0) {
                $pendingSessions = $_SESSION['pending_checkout_sessions'] ?? [];
                $pending = is_array($pendingSessions) ? ($pendingSessions[$sessionId] ?? null) : null;

                if (!is_array($pending)) {
                    http_response_code(400);
                    echo 'This paid checkout session could not be matched to your basket. Please contact us with your Stripe payment reference.';
                    return;
                }

                $orderData = $pending['order_data'] ?? [];
                $mappedItems = $pending['items'] ?? [];
                $shipping = $pending['shipping'] ?? [];
                $billing = $pending['billing'] ?? [];

                if (!is_array($orderData) || !is_array($mappedItems) || !is_array($shipping) || !is_array($billing)) {
                    http_response_code(400);
                    echo 'Checkout details are incomplete.';
                    return;
                }

                if ($amountTotal !== (int)$orderData['total_minor'] || $currency !== strtoupper((string)$orderData['currency'])) {
                    http_response_code(422);
                    echo 'Stripe payment total does not match the checkout total.';
                    return;
                }

                $orderModel = new Order();
                $orderData['order_number'] = $orderModel->nextOrderNumber();
                $orderData['status'] = 'PAID';
                $orderId = $orderModel->createFull($orderData, $mappedItems, $shipping, $billing);

                $paymentIntentId = !empty($session['payment_intent']) ? (string)$session['payment_intent'] : null;
                $orderPaymentModel->upsertStripePayment(
                    $orderId,
                    'SUCCEEDED',
                    $amountTotal,
                    $currency,
                    $sessionId,
                    $paymentIntentId
                );

                $this->saveCheckoutAddressIfRequested($pending['save_address'] ?? null);
                (new Cart())->clear();
                unset($_SESSION['pending_checkout_sessions'][$sessionId]);
            }
        }

        if ($orderId <= 0) {
            header('Location: ' . URLROOT . '/products');
            exit;
        }

        $orderModel = new Order();
        $order = $orderModel->findSummaryById($orderId);
        $items = $orderModel->findItemsByOrderId($orderId);

        unset($_SESSION['pending_checkout_save_address']);
        unset($_SESSION['last_order_id']);

        $this->render('checkout/success', [
            'title' => 'Order placed',
            'order' => $order,
            'items' => $items,
        ], 'main');
    }

    // Show a simple return screen when Stripe Checkout is cancelled.
    public function cancel(): void
    {
        $this->render('checkout/cancel', [
            'title' => 'Payment cancelled',
        ], 'main');
    }

    // Pull the expected checkout fields from the posted form.
    private function collectCheckoutInput(): array
    {
        $fields = [
            'shipping_first_name',
            'shipping_last_name',
            'shipping_email',
            'shipping_phone',
            'shipping_line1',
            'shipping_line2',
            'shipping_city',
            'shipping_region',
            'shipping_postcode',
            'shipping_country',

            'billing_first_name',
            'billing_last_name',
            'billing_email',
            'billing_phone',
            'billing_line1',
            'billing_line2',
            'billing_city',
            'billing_region',
            'billing_postcode',
            'billing_country',

            'billing_same_as_shipping',
            'save_address',
        ];

        $data = [];

        foreach ($fields as $f) {
            $data[$f] = trim((string)($_POST[$f] ?? ''));
        }

        return $data;
    }

    // Validate only the fields required to place the order.
    private function validateCheckoutInput(array $data): array
    {
        $errors = [];

        $required = [
            'shipping_first_name',
            'shipping_last_name',
            'shipping_email',
            'shipping_line1',
            'shipping_city',
            'shipping_postcode',
            'shipping_country',
        ];

        foreach ($required as $field) {
            if ($data[$field] === '') {
                $errors[$field] = 'Required';
            }
        }

        if ($data['shipping_email'] !== '' &&
            !filter_var($data['shipping_email'], FILTER_VALIDATE_EMAIL)
        ) {
            $errors['shipping_email'] = 'Invalid email';
        }

        if (empty($data['billing_same_as_shipping'])) {

            $billingRequired = [
                'billing_first_name',
                'billing_last_name',
                'billing_email',
                'billing_line1',
                'billing_city',
                'billing_postcode',
                'billing_country',
            ];

            foreach ($billingRequired as $field) {
                if ($data[$field] === '') {
                    $errors[$field] = 'Required';
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<string,mixed> $cart
     * @param array<string,string> $errors
     * @param array<string,string> $old
     */
    // Render the checkout form with a consistent view payload.
    private function renderCheckout(array $cart, array $errors, array $old): void
    {
        $subtotalMinor = (int)($cart['total_minor'] ?? 0);
        $shippingCountry = (string)($old['shipping_country'] ?? '');
        $shippingMinor = $shippingCountry !== ''
            ? $this->calculateShippingMinor($shippingCountry)
            : 0;

        $this->render('checkout/index', [
            'title' => 'Checkout',
            'cart' => $cart,
            'errors' => $errors,
            'old' => $old,
            'shipping_minor' => $shippingMinor,
            'total_minor' => $subtotalMinor + $shippingMinor,
            'stripe_configured' => (new StripeGateway())->isConfigured(),
        ], 'main');
    }

    // Apply the temporary flat shipping rule by destination country.
    private function calculateShippingMinor(string $country): int
    {
        $normalized = strtoupper(trim($country));

        if (in_array($normalized, ['UK', 'UNITED KINGDOM', 'GB', 'GREAT BRITAIN'], true)) {
            return 299;
        }

        return 1099;
    }

    // Persist the optional saved address only after a confirmed payment.
    private function saveCheckoutAddressIfRequested(?array $state = null): void
    {
        $state ??= $_SESSION['pending_checkout_save_address'] ?? null;

        if (!is_array($state) || empty($state['enabled']) || empty($_SESSION['user_id'])) {
            return;
        }

        $shipping = $state['shipping'] ?? null;
        if (!is_array($shipping)) {
            return;
        }

        $addressModel = new Address();
        $addressId = $addressModel->create([
            'first_name' => $shipping['first_name'] ?? '',
            'last_name' => $shipping['last_name'] ?? '',
            'email' => $state['shipping_email'] ?? '',
            'phone' => $shipping['phone'] ?? null,
            'line1' => $shipping['line1'] ?? '',
            'line2' => $shipping['line2'] ?? null,
            'city' => $shipping['city'] ?? '',
            'region' => $shipping['region'] ?? null,
            'postcode' => $shipping['postcode'] ?? '',
            'country_name' => $shipping['country_name'] ?? '',
        ]);

        $addressModel->linkToUser(
            (int)$_SESSION['user_id'],
            $addressId,
            'Checkout address',
            true,
            !empty($state['billing_same_as_shipping'])
        );
    }
}
