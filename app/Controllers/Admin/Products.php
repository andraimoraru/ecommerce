<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;

final class Products extends Controller
{
    // Render the admin product table with inventory and category metadata.
    public function index(): void
    {
        $perPage = 25;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        $categoryId = (int)($_GET['category_id'] ?? 0);
        $productModel = new Product();
        $category = $categoryId > 0 ? (new Category())->findById($categoryId) : null;
        $filterCategoryId = $categoryId > 0 ? $categoryId : null;
        $products = $productModel->allAdminWithMeta($filterCategoryId, $perPage, $offset);
        $totalItems = $productModel->countAdmin($filterCategoryId);

        $data = [
            'title' => $category ? ('Products in ' . $category['name']) : 'Products',
            'products' => $products,
            'filter_category' => $category,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalItems,
                'total_pages' => max(1, (int)ceil($totalItems / $perPage)),
            ],
        ];

        $this->render('admin/products/index', $data, 'admin');
    }

    // Render the public product detail page from the admin area.
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

    // Render the product creation form.
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

    // Validate and create a product together with any uploaded images.
    public function store(): void
    {
        $old = $this->collectInput();

        if ($old['slug'] === '') {
            $old['slug'] = $this->slugify($old['name']);
        }

        $errors = $this->validate($old);
        $errors = array_merge($errors, $this->validateUploadedImages());

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

        $productId = $productModel->createFull($old);
        $this->processUploadedImages($productId);

        header('Location: ' . URLROOT . '/admin/products');
        exit;
    }

    // Render the edit form for an existing product.
    public function editForm(array $params): void
    {
        $id = (int)($params['id'] ?? 0);

        $product = (new Product())->findById($id);

        if (!$product) {
            http_response_code(404);
            echo 'Product not found';
            return;
        }

        $images = (new ProductImage())->forProduct($id);
        $categories = (new Category())->allActive();

        $this->render('admin/products/edit', [
            'title' => 'Edit Product',
            'product' => $product,
            'categories' => $categories,
            'images' => $images,
            'errors' => [],
        ], 'admin');
    }

    // Validate and update changes to an existing product.
    public function update(array $params): void
    {
        $id = (int)($params['id'] ?? 0);

        $productModel = new Product();
        $existing = $productModel->findById($id);

        if (!$existing) {
            http_response_code(404);
            echo 'Product not found';
            return;
        }

        $images = (new ProductImage())->forProduct($id);
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

    // Soft-archive a product by updating its status.
    public function archive(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        (new Product())->setStatus($id, 'ARCHIVED');

        header('Location: ' . URLROOT . '/admin/products');
        exit;
    }

    // Restore an archived product to the active storefront.
    public function restore(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        (new Product())->setStatus($id, 'ACTIVE');

        header('Location: ' . URLROOT . '/admin/products');
        exit;
    }

    // Gather the editable product fields from the request.
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
            'image_alt' => $_POST['image_alt'] ?? [],
            'image_sort' => $_POST['image_sort'] ?? [],
        ];
    }

    // Apply the base validation rules for product fields.
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

    // Validate any uploaded product images before saving the product.
    private function validateUploadedImages(): array
    {
        $errors = [];

        if (!isset($_FILES['images'])) {
            return $errors;
        }

        $allowedMime = [
            'image/jpeg',
            'image/png',
            'image/webp',
        ];

        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
            $error = $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;

            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($error !== UPLOAD_ERR_OK) {
                $errors['images'] = 'One or more images failed to upload.';
                break;
            }

            $mime = $finfo->file($tmpName);
            if (!in_array($mime, $allowedMime, true)) {
                $errors['images'] = 'Only JPG, PNG, and WEBP images are allowed.';
                break;
            }

            $size = (int)($_FILES['images']['size'][$i] ?? 0);
            if ($size > 5 * 1024 * 1024) {
                $errors['images'] = 'Each image must be 5MB or smaller.';
                break;
            }
        }

        return $errors;
    }

    // Convert a product name into a URL-friendly slug.
    private function slugify(string $name): string
    {
        $s = mb_strtolower(trim($name));
        $s = preg_replace('/[^a-z0-9]+/u', '-', $s) ?? '';
        $s = trim($s, '-');
        return $s !== '' ? $s : 'product';
    }

    // Move valid uploads into place and register them in the database.
    private function processUploadedImages(int $productId): void
    {
        if (!isset($_FILES['images'])) {
            return;
        }

        $files = $_FILES['images'];
        $alts = $_POST['image_alt'] ?? [];
        $sorts = $_POST['image_sort'] ?? [];

        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        $uploadDir = APPROOT . '/../public/uploads/products/' . $productId;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $imageModel = new ProductImage();
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        foreach ($files['tmp_name'] as $i => $tmpName) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if (($files['error'][$i] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                continue;
            }

            $mime = $finfo->file($tmpName);
            if (!isset($allowedMime[$mime])) {
                continue;
            }

            if (($files['size'][$i] ?? 0) > 5 * 1024 * 1024) {
                continue;
            }

            $extension = $allowedMime[$mime];
            $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
            $destination = $uploadDir . '/' . $fileName;

            if (!move_uploaded_file($tmpName, $destination)) {
                continue;
            }

            $relativePath = '/uploads/products/' . $productId . '/' . $fileName;
            $altText = trim((string)($alts[$i] ?? ''));
            $sortOrder = (int)($sorts[$i] ?? $i);

            $imageModel->create($productId, $relativePath, $altText, $sortOrder);
        }
    }
}
