<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;

final class Categories extends Controller
{
    public function index(): void
    {
        $categories = (new Category())->allActiveForStorefront();

        $data = [
            'title' => 'Categories',
            'categories' => $categories,
        ];

        $this->render('categories/index', $data, 'main');
    }

        public function show(array $params): void
    {
        $slug = (string)($params['slug'] ?? '');

        $category = (new Category())->findActiveBySlug($slug);

        if (!$category) {
            http_response_code(404);
            echo 'Category not found';
            return;
        }

        $products = (new Product())->allActiveByCategoryId((int)$category['id'], 48);

        $data = [
            'title' => $category['name'],
            'category' => $category,
            'products' => $products,
        ];

        $this->render('categories/show', $data, 'main');
    }
}