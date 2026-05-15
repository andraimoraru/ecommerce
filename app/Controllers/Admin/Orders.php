<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Product;

final class Orders extends Controller
{
    /** @var array<int, string> */
    private const ALLOWED_STATUSES = [
        'PENDING_PAYMENT',
        'PAID',
        'PROCESSING',
        'SHIPPED',
        'COMPLETED',
        'CANCELLED',
        'REFUNDED',
    ];

    // Render the admin orders list.
    public function index(): void
    {
        $orders = (new Order())->allAdmin();

        $this->render('admin/orders/index', [
            'title' => 'Orders',
            'orders' => $orders,
        ], 'admin');
    }

    // Render the detail page for a single order.
    public function show(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $orderModel = new Order();
        [$order, $shippingAddress, $billingAddress, $items] = $this->loadOrderContext($orderModel, $id);

        if (!$order) {
            http_response_code(404);
            echo 'Order not found';
            return;
        }

        $this->render('admin/orders/show', [
            'title' => 'Order ' . $order['order_number'],
            'order' => $order,
            'items' => $items,
            'shipping_address' => $shippingAddress,
            'billing_address' => $billingAddress,
            'allowed_statuses' => self::ALLOWED_STATUSES,
        ], 'admin');
    }

    // Render the editable order form.
    public function editForm(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $orderModel = new Order();
        [$order, $shippingAddress, $billingAddress, $items] = $this->loadOrderContext($orderModel, $id);

        if (!$order || !$shippingAddress || !$billingAddress) {
            http_response_code(404);
            echo 'Order not found';
            return;
        }

        $this->render('admin/orders/edit', [
            'title' => 'Edit Order ' . $order['order_number'],
            'order' => array_merge($order, [
                'discount_percent' => $this->calculateDiscountPercent(
                    (int)($order['subtotal_minor'] ?? 0),
                    (int)($order['discount_minor'] ?? 0)
                ),
            ]),
            'items' => $items,
            'shipping_address' => $shippingAddress,
            'product_options' => (new Product())->allSellableForOrderEditor(),
            'errors' => [],
        ], 'admin');
    }

    // Validate and persist editable order fields.
    public function update(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $orderModel = new Order();
        [$order, $shippingAddress, $billingAddress, $existingItems] = $this->loadOrderContext($orderModel, $id);

        if (!$order || !$shippingAddress || !$billingAddress) {
            http_response_code(404);
            echo 'Order not found';
            return;
        }

        $shipping = $this->collectAddressInput('shipping_', $shippingAddress);
        $discountPercent = (float)($_POST['discount_percent'] ?? 0);
        $items = $this->collectItemInput($existingItems);
        $errors = $this->validateOrderInput($shipping, $items, $discountPercent);

        if ($errors) {
            $totals = $this->calculateTotals($order, $items, $discountPercent);
            $this->render('admin/orders/edit', [
                'title' => 'Edit Order ' . $order['order_number'],
                'order' => array_merge($order, $totals, [
                    'discount_percent' => $discountPercent,
                ]),
                'items' => $items,
                'shipping_address' => $shipping,
                'product_options' => (new Product())->allSellableForOrderEditor(),
                'errors' => $errors,
            ], 'admin');
            return;
        }

        $totals = $this->calculateTotals($order, $items, $discountPercent);
        $orderModel->updateEditableParts($id, $totals, $shipping, $items);

        header('Location: ' . URLROOT . '/admin/orders/' . $id);
        exit;
    }

    // Update the status of an order from the detail page.
    public function updateStatus(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $status = trim((string)($_POST['status'] ?? ''));
        $orderModel = new Order();
        $order = $orderModel->findAdminById($id);

        if (!$order) {
            http_response_code(404);
            echo 'Order not found';
            return;
        }

        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            http_response_code(422);
            echo 'Invalid order status';
            return;
        }

        $orderModel->updateStatus($id, $status);

        header('Location: ' . URLROOT . '/admin/orders/' . $id);
        exit;
    }

    /**
     * @return array{0:?array,1:?array,2:?array,3:array<int,array<string,mixed>>}
     */
    // Load the shared order detail context used by the show/edit screens.
    private function loadOrderContext(Order $orderModel, int $id): array
    {
        $order = $orderModel->findAdminById($id);

        if (!$order) {
            return [null, null, null, []];
        }

        $addresses = $orderModel->findAddressesByOrderId($id);
        $items = $orderModel->findItemsByOrderId($id);
        $shippingAddress = null;
        $billingAddress = null;

        foreach ($addresses as $address) {
            if (($address['type'] ?? '') === 'SHIPPING') {
                $shippingAddress = $address;
            }

            if (($address['type'] ?? '') === 'BILLING') {
                $billingAddress = $address;
            }
        }

        return [$order, $shippingAddress, $billingAddress, $items];
    }

    /**
     * @param array<string,mixed> $address
     * @return array<string,mixed>
     */
    // Gather one editable address block from the request.
    private function collectAddressInput(string $prefix, array $address): array
    {
        return [
            'id' => (int)$address['id'],
            'type' => $address['type'],
            'first_name' => trim((string)($_POST[$prefix . 'first_name'] ?? '')),
            'last_name' => trim((string)($_POST[$prefix . 'last_name'] ?? '')),
            'phone' => trim((string)($_POST[$prefix . 'phone'] ?? '')),
            'line1' => trim((string)($_POST[$prefix . 'line1'] ?? '')),
            'line2' => trim((string)($_POST[$prefix . 'line2'] ?? '')),
            'city' => trim((string)($_POST[$prefix . 'city'] ?? '')),
            'region' => trim((string)($_POST[$prefix . 'region'] ?? '')),
            'postcode' => trim((string)($_POST[$prefix . 'postcode'] ?? '')),
            'country_name' => trim((string)($_POST[$prefix . 'country_name'] ?? '')),
        ];
    }

    /**
     * @param array<string,mixed> $shipping
     * @param array<int,array<string,mixed>> $items
     * @return array<string,string>
     */
    // Apply the validation rules for the editable order fields.
    private function validateOrderInput(array $shipping, array $items, float $discountPercent): array
    {
        $errors = [];

        if ($discountPercent < 0 || $discountPercent > 100) {
            $errors['discount_percent'] = 'Discount must be between 0 and 100.';
        }

        foreach (['first_name', 'last_name', 'line1', 'city', 'postcode', 'country_name'] as $field) {
            if ($shipping[$field] === '') {
                $errors['shipping_' . $field] = 'Required.';
            }
        }

        if ($items === []) {
            $errors['items'] = 'An order must contain at least one item.';
        }

        foreach ($items as $index => $item) {
            if ((int)$item['quantity'] <= 0) {
                $errors['item_' . $index] = 'Quantity must be at least 1.';
            }
        }

        return $errors;
    }

    /**
     * @param array<int,array<string,mixed>> $existingItems
     * @return array<int,array<string,mixed>>
     */
    // Gather existing and newly added line items from the request.
    private function collectItemInput(array $existingItems): array
    {
        $existingById = [];
        foreach ($existingItems as $item) {
            $existingById[(int)$item['id']] = $item;
        }

        $items = [];
        $existingIds = $_POST['existing_item_id'] ?? [];
        $existingQtys = $_POST['existing_quantity'] ?? [];
        $removeIds = array_map('intval', $_POST['remove_item'] ?? []);

        foreach ($existingIds as $index => $rawId) {
            $itemId = (int)$rawId;
            if ($itemId <= 0 || in_array($itemId, $removeIds, true) || !isset($existingById[$itemId])) {
                continue;
            }

            $existing = $existingById[$itemId];
            $quantity = max(0, (int)($existingQtys[$index] ?? $existing['quantity']));

            $items[] = [
                'id' => $itemId,
                'product_id' => (int)$existing['product_id'],
                'sku' => (string)$existing['sku'],
                'product_name' => (string)$existing['product_name'],
                'unit_price_minor' => (int)$existing['unit_price_minor'],
                'quantity' => $quantity,
                'line_total_minor' => ((int)$existing['unit_price_minor']) * $quantity,
            ];
        }

        $newProductIds = $_POST['new_product_id'] ?? [];
        $newQuantities = $_POST['new_quantity'] ?? [];
        if ($newProductIds !== []) {
            $productModel = new Product();
            foreach ($newProductIds as $index => $rawProductId) {
                $productId = (int)$rawProductId;
                $quantity = (int)($newQuantities[$index] ?? 0);

                if ($productId <= 0 || $quantity <= 0) {
                    continue;
                }

                $product = $productModel->findById($productId);
                if (!$product || ($product['status'] ?? '') === 'ARCHIVED') {
                    continue;
                }

                $unitPriceMinor = (int)$product['price_minor'];
                $items[] = [
                    'id' => 0,
                    'product_id' => $productId,
                    'sku' => (string)($product['sku'] ?? ''),
                    'product_name' => (string)$product['name'],
                    'unit_price_minor' => $unitPriceMinor,
                    'quantity' => $quantity,
                    'line_total_minor' => $unitPriceMinor * $quantity,
                ];
            }
        }

        return $items;
    }

    /**
     * @param array<string,mixed> $order
     * @param array<int,array<string,mixed>> $items
     * @return array<string,mixed>
     */
    // Recalculate stored totals from the editable fields.
    private function calculateTotals(array $order, array $items, float $discountPercent): array
    {
        $subtotalMinor = 0;
        foreach ($items as $item) {
            $subtotalMinor += (int)$item['line_total_minor'];
        }

        $shippingMinor = (int)($order['shipping_minor'] ?? 0);
        $taxMinor = (int)($order['tax_minor'] ?? 0);
        $normalizedDiscountPercent = max(0, min(100, $discountPercent));
        $discountMinor = (int)round($subtotalMinor * ($normalizedDiscountPercent / 100));
        $totalMinor = max(0, $subtotalMinor + $shippingMinor + $taxMinor - $discountMinor);

        return [
            'subtotal_minor' => $subtotalMinor,
            'shipping_minor' => $shippingMinor,
            'tax_minor' => $taxMinor,
            'discount_minor' => $discountMinor,
            'total_minor' => $totalMinor,
        ];
    }

    // Convert the stored monetary discount into a percentage for the edit form.
    private function calculateDiscountPercent(int $subtotalMinor, int $discountMinor): float
    {
        if ($subtotalMinor <= 0 || $discountMinor <= 0) {
            return 0.0;
        }

        return round(($discountMinor / $subtotalMinor) * 100, 2);
    }
}
