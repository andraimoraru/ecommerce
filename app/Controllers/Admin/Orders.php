<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Order;
use App\Models\OrderShipment;
use App\Models\Product;
use App\Services\RoyalMailClickDropService;
use RuntimeException;

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
        $perPage = 25;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        $orderModel = new Order();
        $orders = $orderModel->allAdmin($perPage, $offset);
        $totalItems = $orderModel->countAdmin();

        $this->render('admin/orders/index', [
            'title' => 'Orders',
            'orders' => $orders,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalItems,
                'total_pages' => max(1, (int)ceil($totalItems / $perPage)),
            ],
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
            'shipment' => (new OrderShipment())->findByOrderId($id),
            'allowed_statuses' => self::ALLOWED_STATUSES,
            'shipping_defaults' => [
                'service_code' => (string)env('ROYAL_MAIL_DEFAULT_SERVICE_CODE', ''),
                'package_format_identifier' => (string)env('ROYAL_MAIL_DEFAULT_PACKAGE_FORMAT', 'Parcel'),
                'weight_grams' => (int)env('ROYAL_MAIL_DEFAULT_WEIGHT_GRAMS', 1000),
                'configured' => (new RoyalMailClickDropService())->isConfigured(),
            ],
            'shipping_success' => (string)($_SESSION['admin_shipping_success'] ?? ''),
            'shipping_errors' => $_SESSION['admin_shipping_errors'] ?? [],
            'shipping_old' => $_SESSION['admin_shipping_old'] ?? [],
        ], 'admin');

        unset($_SESSION['admin_shipping_success'], $_SESSION['admin_shipping_errors'], $_SESSION['admin_shipping_old']);
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

    // Create a Royal Mail Click & Drop label for a paid order.
    public function createShippingLabel(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $orderModel = new Order();
        [$order, $shippingAddress, $billingAddress, $items] = $this->loadOrderContext($orderModel, $id);

        if (!$order || !$shippingAddress || !$billingAddress || $items === []) {
            http_response_code(404);
            echo 'Order not found';
            return;
        }

        $form = [
            'service_code' => strtoupper(trim((string)($_POST['service_code'] ?? ''))),
            'package_format_identifier' => trim((string)($_POST['package_format_identifier'] ?? 'Parcel')),
            'weight_grams' => (int)($_POST['weight_grams'] ?? 0),
        ];

        $errors = $this->validateShippingLabelInput($order, $shippingAddress, $form);
        if ($errors) {
            $_SESSION['admin_shipping_errors'] = $errors;
            $_SESSION['admin_shipping_old'] = $form;
            header('Location: ' . URLROOT . '/admin/orders/' . $id);
            exit;
        }

        try {
            $response = (new RoyalMailClickDropService())->createOrders(
                $this->buildRoyalMailOrderPayload($order, $shippingAddress, $billingAddress, $items, $form)
            );
            $shipment = $this->extractShipmentResponse($response, $id, $form);
            (new OrderShipment())->upsertForOrder($id, $shipment);
            $_SESSION['admin_shipping_success'] = 'Royal Mail shipment created successfully in Click & Drop.';
        } catch (RuntimeException $exception) {
            $_SESSION['admin_shipping_errors'] = ['shipping' => $exception->getMessage()];
            $_SESSION['admin_shipping_old'] = $form;
        }

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

    /**
     * @param array<string,mixed> $order
     * @param array<string,mixed> $shipping
     * @param array<string,string|int> $form
     * @return array<string,string>
     */
    // Validate the extra inputs required to create a shipping label.
    private function validateShippingLabelInput(array $order, array $shipping, array $form): array
    {
        $errors = [];

        if (($order['status'] ?? '') !== 'PAID') {
            $errors['shipping'] = 'Only paid orders can have a Royal Mail label created.';
        }

        if ($form['service_code'] === '') {
            $errors['service_code'] = 'Service code is required.';
        }

        if ((int)$form['weight_grams'] <= 0) {
            $errors['weight_grams'] = 'Weight must be greater than 0 grams.';
        }

        $countryCode = $this->mapCountryCode((string)($shipping['country_name'] ?? ''));
        if ($countryCode !== 'GB') {
            $errors['shipping'] = 'This first Royal Mail integration only supports UK destination labels.';
        }

        return $errors;
    }

    /**
     * @param array<string,mixed> $order
     * @param array<string,mixed> $shipping
     * @param array<string,mixed> $billing
     * @param array<int,array<string,mixed>> $items
     * @param array<string,string|int> $form
     * @return array<string,mixed>
     */
    // Build the Click & Drop order payload for one domestic shipment.
    private function buildRoyalMailOrderPayload(array $order, array $shipping, array $billing, array $items, array $form): array
    {
        return [
            'items' => [[
                'orderReference' => (string)$order['order_number'],
                'orderDate' => gmdate('c'),
                'recipient' => [
                    'address' => [
                        'fullName' => trim((string)$shipping['first_name'] . ' ' . (string)$shipping['last_name']),
                        'companyName' => '',
                        'addressLine1' => (string)$shipping['line1'],
                        'addressLine2' => (string)($shipping['line2'] ?? ''),
                        'addressLine3' => '',
                        'city' => (string)$shipping['city'],
                        'county' => (string)($shipping['region'] ?? ''),
                        'postcode' => (string)$shipping['postcode'],
                        'countryCode' => $this->mapCountryCode((string)$shipping['country_name']),
                    ],
                    'emailAddress' => (string)$order['customer_email'],
                    'phoneNumber' => (string)($shipping['phone'] ?? $order['customer_phone'] ?? ''),
                ],
                'billing' => [
                    'address' => [
                        'fullName' => trim((string)$billing['first_name'] . ' ' . (string)$billing['last_name']),
                        'companyName' => '',
                        'addressLine1' => (string)$billing['line1'],
                        'addressLine2' => (string)($billing['line2'] ?? ''),
                        'addressLine3' => '',
                        'city' => (string)$billing['city'],
                        'county' => (string)($billing['region'] ?? ''),
                        'postcode' => (string)$billing['postcode'],
                        'countryCode' => $this->mapCountryCode((string)$billing['country_name']),
                    ],
                    'emailAddress' => (string)$order['customer_email'],
                    'phoneNumber' => (string)($billing['phone'] ?? $order['customer_phone'] ?? ''),
                ],
                'packages' => [[
                    'packageFormatIdentifier' => (string)$form['package_format_identifier'],
                    'weightInGrams' => (int)$form['weight_grams'],
                ]],
                'postageDetails' => [
                    'serviceCode' => (string)$form['service_code'],
                ],
                'shippingCostCharged' => ((int)$order['shipping_minor']) / 100,
                'subtotal' => ((int)$order['subtotal_minor']) / 100,
                'total' => ((int)$order['total_minor']) / 100,
                'currencyCode' => (string)$order['currency'],
            ]],
        ];
    }

    /**
     * @param array<string,mixed> $response
     * @param array<string,string|int> $form
     * @return array<string,mixed>
     */
    // Shape the stored shipment record from the Royal Mail API response.
    private function extractShipmentResponse(array $response, int $orderId, array $form): array
    {
        $createdOrder = $response['createdOrders'][0] ?? null;
        if (!is_array($createdOrder)) {
            throw new RuntimeException('Royal Mail did not return a created order.');
        }

        return [
            'provider' => 'ROYAL_MAIL',
            'royal_mail_shipment_id' => (string)($createdOrder['orderIdentifier'] ?? $createdOrder['orderReference'] ?? ''),
            'tracking_number' => (string)($createdOrder['trackingNumber'] ?? ''),
            'service_code' => (string)$form['service_code'],
            'shipping_cost_minor' => (int)($orderId > 0 ? 0 : 0),
            'currency' => 'GBP',
            'label_url' => null,
            'created_by_user_id' => isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null,
            'status' => 'LABEL_CREATED',
        ];
    }

    // Map stored country names onto the ISO country codes Click & Drop expects.
    private function mapCountryCode(string $countryName): string
    {
        $normalized = strtoupper(trim($countryName));

        return match ($normalized) {
            'GB', 'UK', 'UNITED KINGDOM', 'GREAT BRITAIN', 'ENGLAND', 'SCOTLAND', 'WALES', 'NORTHERN IRELAND' => 'GB',
            default => strlen($normalized) >= 2 ? substr($normalized, 0, 2) : 'GB',
        };
    }
}
