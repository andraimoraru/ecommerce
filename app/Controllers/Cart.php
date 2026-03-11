<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

final class Cart extends Controller
{
    public function index(): void
    {
        $sessionCart = $_SESSION['cart'] ?? [];

        $items = [];
        $subtotalMinor = 0;

        if (!empty($sessionCart)) {
            $productIds = array_map('intval', array_keys($sessionCart));
            $products = (new Product())->findManyForCart($productIds);

            $productsById = [];
            foreach ($products as $product) {
                $productsById[(int)$product['id']] = $product;
            }

            foreach ($sessionCart as $productId => $qty) {
                $productId = (int)$productId;
                $qty = (int)$qty;

                if (!isset($productsById[$productId])) {
                    continue;
                }

                $product = $productsById[$productId];
                $lineTotalMinor = ((int)$product['price_minor']) * $qty;
                $subtotalMinor += $lineTotalMinor;

                $items[] = [
                    'product_id' => $productId,
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'primary_image' => $product['primary_image'],
                    'currency' => $product['currency'],
                    'price_minor' => (int)$product['price_minor'],
                    'quantity' => $qty,
                    'line_total_minor' => $lineTotalMinor,
                ];
            }
        }

        $data = [
            'title' => 'Your Cart',
            'items' => $items,
            'subtotal_minor' => $subtotalMinor,
        ];

        $this->render('cart/index', $data, 'main');
    }

    public function add(): void
    {
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        if ($productId <= 0) {
            header('Location: ' . URLROOT . '/products');
            exit;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (!isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = 0;
        }

        $_SESSION['cart'][$productId] += $quantity;

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