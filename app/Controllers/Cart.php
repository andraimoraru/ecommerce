<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart as CartModel;

final class Cart extends Controller
{
    public function index(): void
    {
        $cart = (new CartModel())->getFull();

        $data = [
            'title' => 'Your Cart',
            'cart' => $cart,
        ];

        $this->render('cart/index', $data, 'main');
    }

    public function add(): void
    {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

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

        $_SESSION['cart'][$productId] += $qty;

        header('Location: ' . URLROOT . '/cart');
        exit;
    }

    public function update(): void
    {
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$productId]);
            } else {
                $_SESSION['cart'][$productId] = $quantity;
            }
        }

        header('Location: ' . URLROOT . '/cart');
        exit;
    }

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