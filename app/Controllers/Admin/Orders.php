<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Order;

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
        $order = $orderModel->findAdminById($id);

        if (!$order) {
            http_response_code(404);
            echo 'Order not found';
            return;
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

        $this->render('admin/orders/show', [
            'title' => 'Order ' . $order['order_number'],
            'order' => $order,
            'items' => $items,
            'shipping_address' => $shippingAddress,
            'billing_address' => $billingAddress,
            'allowed_statuses' => self::ALLOWED_STATUSES,
        ], 'admin');
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
}
