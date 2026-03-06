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
        $products = (new Product())->allAdminWithMeta();

        $data = [
            'title' => 'Products',
            'products' => $products,
        ];

        $this->render('admin/products/index', $data, 'admin');
    }

    public function createForm(): void
    {
        $categories = (new Category())->allActive();

        $data = [
            'title' => 'Add Product',
            'categories' => $categories,
            'errors' => [],
            'old' => [
                'name' => '',
                'sku' => '',
                'slug' => '',
                'description' => '',
                'description_html' => '',
                'meta_title' => '',
                'meta_description' => '',
                'price_minor' => '',
                'currency' => 'GBP',
                'status' => 'DRAFT',
                'category_id' => '',
                'stock_on_hand' => 0,
            ],
        ];

        $this->render('admin/products/create', $data, 'admin');
    }

    public function store(): void
    {
        $old = $this->collectInput();

        if ($old['slug'] === '') {
            $old['slug'] = $this->slugify($old['name']);
        }

        $errors = $this->validate($old);

        $productModel = new Product();

        if (!$errors && $productModel->skuExists($old['sku'])) {
            $errors['sku'] = 'SKU already exists.';
        }

        if (!$errors && $productModel->slugExists($old['slug'])) {
            $errors['slug'] = 'Slug already exists.';
        }

        if ($errors) {
            $categories = (new Category())->allActive();
            $this->render('admin/products/create', [
                'title' => 'Add Product',
                'categories' => $categories,
                'errors' => $errors,
                'old' => $old,
            ], 'admin');
            return;
        }

        $productModel->createFull($old);

        header('Location: ' . URLROOT . '/admin/products');
        exit;
    }

    public function editForm(array $params): void
    {
        $id = (int)($params['id'] ?? 0);

        $product = (new Product())->findById($id);
        $images = (new \App\Models\ProductImage())->forProduct($id);

        if (!$product) {
            http_response_code(404);
            echo 'Product not found';
            return;
        }

        $categories = (new Category())->allActive();

        $this->render('admin/products/edit', [
            'title' => 'Edit Product',
            'product' => $product,
            'categories' => $categories,
            'images' => $images,
            'errors' => [],
        ], 'admin');
    }

    public function update(array $params): void
    {
        $id = (int)($params['id'] ?? 0);

        $productModel = new Product();
        $existing = $productModel->findById($id);
        $images = (new \App\Models\ProductImage())->forProduct($id);

        if (!$existing) {
            http_response_code(404);
            echo 'Product not found';
            return;
        }

        $old = $this->collectInput();

        if ($old['slug'] === '') {
            $old['slug'] = $this->slugify($old['name']);
        }

        $errors = $this->validate($old);

        if (!$errors && $productModel->skuExists($old['sku'], $id)) {
            $errors['sku'] = 'SKU already exists.';
        }

        if (!$errors && $productModel->slugExists($old['slug'], $id)) {
            $errors['slug'] = 'Slug already exists.';
        }

        if ($errors) {
            $categories = (new Category())->allActive();

            $product = array_merge($existing, $old);

            $this->render('admin/products/edit', [
                'title' => 'Edit Product',
                'product' => $product,
                'categories' => $categories,
                'images' => $images,
                'errors' => $errors,
            ], 'admin');
            return;
        }

        $productModel->updateFull($id, $old);

        header('Location: ' . URLROOT . '/admin/products');
        exit;
    }

    public function archive(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        (new Product())->setStatus($id, 'ARCHIVED');

        header('Location: ' . URLROOT . '/admin/products');
        exit;
    }

    public function restore(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        (new Product())->setStatus($id, 'ACTIVE');

        header('Location: ' . URLROOT . '/admin/products');
        exit;
    }

    private function collectInput(): array
    {
        return [
            'name' => trim((string)($_POST['name'] ?? '')),
            'sku' => trim((string)($_POST['sku'] ?? '')),
            'slug' => trim((string)($_POST['slug'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'description_html' => trim((string)($_POST['description_html'] ?? '')),
            'meta_title' => trim((string)($_POST['meta_title'] ?? '')),
            'meta_description' => trim((string)($_POST['meta_description'] ?? '')),
            'price_minor' => (int)($_POST['price_minor'] ?? 0),
            'currency' => trim((string)($_POST['currency'] ?? 'GBP')) ?: 'GBP',
            'status' => trim((string)($_POST['status'] ?? 'DRAFT')) ?: 'DRAFT',
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'stock_on_hand' => (int)($_POST['stock_on_hand'] ?? 0),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['name'] === '' || mb_strlen($data['name']) < 3) {
            $errors['name'] = 'Name must be at least 3 characters.';
        }

        if ($data['sku'] === '' || mb_strlen($data['sku']) < 3) {
            $errors['sku'] = 'SKU must be at least 3 characters.';
        }

        if ($data['slug'] === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $data['slug'])) {
            $errors['slug'] = 'Slug must be lowercase and dash-separated.';
        }

        if ($data['price_minor'] <= 0) {
            $errors['price_minor'] = 'Price must be greater than 0.';
        }

        if (!in_array($data['status'], ['DRAFT', 'ACTIVE', 'ARCHIVED'], true)) {
            $errors['status'] = 'Invalid status.';
        }

        if ($data['stock_on_hand'] < 0) {
            $errors['stock_on_hand'] = 'Stock cannot be negative.';
        }

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