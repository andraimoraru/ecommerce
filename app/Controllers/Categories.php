<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;

final class Categories extends Controller
{
    // Render the storefront category listing.
    public function index(): void
    {
        $categories = (new Category())->allActiveForStorefront();

        $data = [
            'title' => 'Categories',
            'categories' => $categories,
        ];

        $this->render('categories/index', $data, 'main');
    }

    // Render a category page together with its active products.
    public function show(array $params): void
    {
        $slug = (string)($params['slug'] ?? '');

        $category = (new Category())->findActiveBySlug($slug);

        if (!$category) {
            $this->notFound('We could not find this category. It may have moved or no longer be available.');
            return;
        }

        $productModel = new Product();
        $perPage = 24;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $totalProducts = $productModel->countActiveByCategoryId((int)$category['id']);
        $totalPages = max(1, (int)ceil($totalProducts / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;
        $products = $productModel->allActiveByCategoryId((int)$category['id'], $perPage, $offset);

        $data = [
            'title' => $category['name'],
            'category' => $category,
            'products' => $products,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalProducts,
                'total_pages' => $totalPages,
            ],
        ];

        $this->render('categories/show', $data, 'main');
    }
}
