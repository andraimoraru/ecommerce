<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Address;

final class Checkout extends Controller
{
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
        $this->render('checkout/index', [
            'title' => 'Checkout',
            'cart' => $cart,
            'errors' => [],
            'old' => $old,
        ], 'main');

    }

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
            $this->render('checkout/index', [
                'title' => 'Checkout',
                'cart' => $cart,
                'errors' => $errors,
                'old' => $old,
            ], 'main');
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

        $orderModel = new Order();

        $orderId = $orderModel->createFull(
            [
                'order_number' => $orderModel->nextOrderNumber(),
                'user_id' => $_SESSION['user_id'] ?? null,
                'status' => 'PENDING_PAYMENT',
                'currency' => 'GBP',
                'subtotal_minor' => $subtotalMinor,
                'shipping_minor' => 0,
                'tax_minor' => 0,
                'discount_minor' => 0,
                'total_minor' => $subtotalMinor,
                'customer_email' => $old['shipping_email'],
                'customer_first_name' => $old['shipping_first_name'],
                'customer_last_name' => $old['shipping_last_name'],
                'customer_phone' => $old['shipping_phone'] ?: null,
            ],
            $mappedItems,
            $shipping,
            $billing
        );

        $cartModel->clear();

        if (!empty($_SESSION['user_id']) && !empty($old['save_address'])) {
            $addressModel = new Address();

            $addressId = $addressModel->create([
                'first_name' => $shipping['first_name'],
                'last_name' => $shipping['last_name'],
                'email' => $old['shipping_email'],
                'phone' => $shipping['phone'],
                'line1' => $shipping['line1'],
                'line2' => $shipping['line2'],
                'city' => $shipping['city'],
                'region' => $shipping['region'],
                'postcode' => $shipping['postcode'],
                'country_name' => $shipping['country_name'],
            ]);

            $addressModel->linkToUser(
                (int)$_SESSION['user_id'],
                $addressId,
                'Checkout address',
                true,
                $billingSame
            );
        }

        $_SESSION['last_order_id'] = $orderId;
        header('Location: ' . URLROOT . '/checkout/success');
        exit;
    }

    public function success(): void
    {
        $orderId = (int)($_SESSION['last_order_id'] ?? 0);

        if ($orderId <= 0) {
            header('Location: ' . URLROOT . '/products');
            exit;
        }

        $orderModel = new Order();
        $order = $orderModel->findSummaryById($orderId);
        $items = $orderModel->findItemsByOrderId($orderId);

        unset($_SESSION['last_order_id']);

        $this->render('checkout/success', [
            'title' => 'Order placed',
            'order' => $order,
            'items' => $items,
        ], 'main');
    }

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
}