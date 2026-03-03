<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;

final class Products extends Controller
{
    public function index(): void
    {
        $products = (new \App\Models\Product())->allAdminWithMeta();

        $this->view('admin/products/index', [
            'title' => 'Products',
            'products' => $products,
        ]);
    }
    public function createForm(): void
    {
        $categories = (new Category())->allActive();

        $this->view('admin/products/create', [
            'title' => 'Add Product',
            'categories' => $categories,
            'errors' => [],
            'old' => [
                'name' => '',
                'sku' => '',
                'slug' => '',
                'description' => '',
                'price_minor' => '',
                'currency' => 'GBP',
                'is_active' => 1,
                'category_id' => '',
                'stock_on_hand' => 0,
                'images' => [
                    ['url' => '', 'alt_text' => '', 'sort_order' => 0],
                ],
            ],
        ]);
    }

    public function store(): void
    {
        // Build images array from POST: image_url[], image_alt[], image_sort[]
        $imageUrls = $_POST['image_url'] ?? [];
        $imageAlts = $_POST['image_alt'] ?? [];
        $imageSort = $_POST['image_sort'] ?? [];

        $images = [];
        if (is_array($imageUrls)) {
            foreach ($imageUrls as $i => $u) {
                $images[] = [
                    'url' => (string)$u,
                    'alt_text' => (string)($imageAlts[$i] ?? ''),
                    'sort_order' => (int)($imageSort[$i] ?? $i),
                ];
            }
        }

        $old = [
            'name' => trim((string)($_POST['name'] ?? '')),
            'sku' => trim((string)($_POST['sku'] ?? '')),
            'slug' => trim((string)($_POST['slug'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'price_minor' => (int)($_POST['price_minor'] ?? 0),
            'currency' => trim((string)($_POST['currency'] ?? 'GBP')) ?: 'GBP',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'stock_on_hand' => (int)($_POST['stock_on_hand'] ?? 0),
            'images' => $images,
        ];

        if ($old['slug'] === '') {
            $old['slug'] = $this->slugify($old['name']);
        }

        $errors = $this->validate($old);

        $productModel = new Product();
        if (!$errors && $productModel->skuExists($old['sku'])) $errors['sku'] = 'SKU already exists.';
        if (!$errors && $productModel->slugExists($old['slug'])) $errors['slug'] = 'Slug already exists.';

        // Validate image URLs lightly
        foreach ($old['images'] as $idx => $img) {
            $u = trim((string)$img['url']);
            if ($u === '') continue;
            if (!filter_var($u, FILTER_VALIDATE_URL)) {
                $errors['images'] = 'One or more image URLs are invalid.';
                break;
            }
        }

        if ($errors) {
            $categories = (new Category())->allActive();
            $this->view('admin/products/create', [
                'title' => 'Add Product',
                'categories' => $categories,
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        $productId = $productModel->createFull($old);

        header('Location: ' . URLROOT . '/admin/products'); // later: /admin/products/{id}/edit
        exit;
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['name'] === '' || mb_strlen($data['name']) < 3) $errors['name'] = 'Name must be at least 3 characters.';
        if ($data['sku'] === '' || mb_strlen($data['sku']) < 3) $errors['sku'] = 'SKU must be at least 3 characters.';
        if ($data['slug'] === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $data['slug'])) {
            $errors['slug'] = 'Slug must be lowercase and dash-separated.';
        }
        if ($data['price_minor'] <= 0) $errors['price_minor'] = 'Price (minor units) must be > 0.';
        if ($data['stock_on_hand'] < 0) $errors['stock_on_hand'] = 'Stock cannot be negative.';

        return $errors;
    }

    private function slugify(string $name): string
    {
        $s = mb_strtolower(trim($name));
        $s = preg_replace('/[^a-z0-9]+/u', '-', $s) ?? '';
        $s = trim($s, '-');
        return $s !== '' ? $s : 'product';
    }
}