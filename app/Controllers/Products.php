<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

final class Products extends Controller
{
    // Render the main product listing page.
    public function index(): void
    {
        $productModel = new Product();
        $perPage = 24;
        $query = trim((string)($_GET['q'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $totalProducts = $productModel->countActiveForStorefront($query);
        $totalPages = max(1, (int)ceil($totalProducts / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;
        $products = $productModel->allActiveForStorefront($perPage, $offset, $query);

        $data = [
            'title' => $query !== '' ? 'Search results for "' . $query . '"' : 'Products',
            'products' => $products,
            'search_query' => $query,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalProducts,
                'total_pages' => $totalPages,
            ],
        ];

        $this->render('products/index', $data, 'main');
    }

    // Render a single active product by its slug.
    public function show(array $params): void
    {
        $slug = (string)($params['slug'] ?? '');

        $productModel = new Product();
        $product = $productModel->findActiveBySlug($slug);

        if (!$product) {
            $this->notFound('We could not find this product. It may no longer be available.');
            return;
        }

        $images = $productModel->allImagesForProduct((int)$product['id']);

        $data = [
            'title' => $product['meta_title'] ?: $product['name'],
            'product' => $product,
            'images' => $images,
        ];

        $this->render('products/show', $data, 'main');
    }
}
