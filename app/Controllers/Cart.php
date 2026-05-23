<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart as CartModel;

final class Cart extends Controller
{
    // Store a one-time cart notice for the next page load.
    private function setNotice(string $message): void
    {
        $_SESSION['cart_notice'] = $message;
    }

    // Render the current session cart.
    public function index(): void
    {
        $cart = (new CartModel())->getFull();
        $notice = (string)($_SESSION['cart_notice'] ?? '');
        unset($_SESSION['cart_notice']);

        $data = [
            'title' => 'Your Cart',
            'cart' => $cart,
            'notice' => $notice,
        ];

        $this->render('cart/index', $data, 'main');
    }

    // Add a product to the session cart and redirect back to the cart page.
    public function add(): void
    {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        $cartModel = new CartModel();

        if ($productId <= 0) {
            header('Location: ' . URLROOT);
            exit;
        }

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (!isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = 0;
        }

        $availableQty = $cartModel->findAvailableQuantity($productId);

        if ($availableQty <= 0) {
            $this->setNotice('This product is currently out of stock.');
            header('Location: ' . URLROOT . '/cart');
            exit;
        }

        $requestedQty = (int)$_SESSION['cart'][$productId] + $qty;
        $finalQty = min($requestedQty, $availableQty);
        $_SESSION['cart'][$productId] = $finalQty;

        if ($finalQty < $requestedQty) {
            $this->setNotice('Cart quantity was limited to the available stock for this product.');
        }

        header('Location: ' . URLROOT . '/cart');
        exit;
    }

    // Update the quantity for a single cart line.
    public function update(): void
    {
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        $cartModel = new CartModel();

        if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$productId]);
            } else {
                $availableQty = $cartModel->findAvailableQuantity($productId);

                if ($availableQty <= 0) {
                    unset($_SESSION['cart'][$productId]);
                    $this->setNotice('This product is currently out of stock and was removed from your cart.');
                } else {
                    $finalQty = min($quantity, $availableQty);
                    $_SESSION['cart'][$productId] = $finalQty;

                    if ($finalQty < $quantity) {
                        $this->setNotice('Cart quantity was reduced to match the available stock.');
                    }
                }
            }
        }

        header('Location: ' . URLROOT . '/cart');
        exit;
    }

    // Remove a product from the session cart.
    public function remove(): void
    {
        $productId = (int)($_POST['product_id'] ?? 0);

        if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }

        header('Location: ' . URLROOT . '/cart');
        exit;
    }
}
