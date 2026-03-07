<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

final class Products extends Controller
{
    public function index(): void
    {
        $products = (new Product())->allActiveForStorefront(48);

        $data = [
            'title' => 'Products',
            'products' => $products,
        ];

        $this->render('products/index', $data, 'main');
    }

    public function show(array $params): void
    {
        $slug = (string)($params['slug'] ?? '');

        $product = (new Product())->findActiveBySlug($slug);

        if (!$product) {
            http_response_code(404);
            echo 'Product not found';
            return;
        }

        $data = [
            'title' => $product['meta_title'] ?? $product['name'],
            'product' => $product,
        ];

        $this->render('products/show', $data, 'main');
    }
}