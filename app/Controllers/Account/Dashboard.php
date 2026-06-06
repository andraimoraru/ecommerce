<?php
declare(strict_types=1);

namespace App\Controllers\Account;

use App\Core\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;

final class Dashboard extends Controller
{
    // Render the account dashboard inside the main site layout.
    public function index(): void
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $account = $userId > 0 ? (new User())->findCustomerById($userId) : null;

        $this->render('account/index', [
            'title' => 'My Account',
            'first_name' => (string)($account['first_name'] ?? $_SESSION['user_name'] ?? ''),
            'account' => $account,
            'addresses' => $userId > 0 ? (new Address())->allForUser($userId) : [],
        ], 'main');
    }

    // Render the logged-in customer's order history.
    public function orders(): void
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);

        $this->render('account/orders', [
            'title' => 'My Orders',
            'orders' => $userId > 0 ? (new Order())->findForUser($userId) : [],
        ], 'main');
    }

    // Show one order after confirming it belongs to the logged-in customer.
    public function showOrder(array $params): void
    {
        $orderId = (int)($params['id'] ?? 0);
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $orderModel = new Order();
        $order = ($orderId > 0 && $userId > 0)
            ? $orderModel->findForUserById($orderId, $userId)
            : null;

        if (!$order) {
            http_response_code(404);
            echo 'Order not found';
            return;
        }

        $addresses = $orderModel->findAddressesByOrderId($orderId);
        $shippingAddress = null;
        $billingAddress = null;

        foreach ($addresses as $address) {
            if ($address['type'] === 'SHIPPING') {
                $shippingAddress = $address;
            }

            if ($address['type'] === 'BILLING') {
                $billingAddress = $address;
            }
        }

        $this->render('account/order_show', [
            'title' => 'Order ' . $order['order_number'],
            'order' => $order,
            'items' => $orderModel->findItemsByOrderId($orderId),
            'shipping_address' => $shippingAddress,
            'billing_address' => $billingAddress,
        ], 'main');
    }
}
