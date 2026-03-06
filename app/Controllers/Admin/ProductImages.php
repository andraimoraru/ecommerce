<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Product;
use App\Models\ProductImage;

final class ProductImages extends Controller
{
    public function store(array $params): void
    {
        $productId = (int)($params['id'] ?? 0);

        $product = (new Product())->findById($productId);
        if (!$product) {
            http_response_code(404);
            echo 'Product not found';
            return;
        }

        $url = trim((string)($_POST['url'] ?? ''));
        $altText = trim((string)($_POST['alt_text'] ?? ''));
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
            (new ProductImage())->create($productId, $url, $altText, $sortOrder);
        }

        header('Location: ' . URLROOT . '/admin/products/' . $productId . '/edit');
        exit;
    }

    public function update(array $params): void
    {
        $productId = (int)($params['id'] ?? 0);
        $imageId = (int)($params['imageId'] ?? 0);

        $product = (new Product())->findById($productId);
        if (!$product) {
            http_response_code(404);
            echo 'Product not found';
            return;
        }

        $url = trim((string)($_POST['url'] ?? ''));
        $altText = trim((string)($_POST['alt_text'] ?? ''));
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
            (new ProductImage())->update($imageId, $productId, $url, $altText, $sortOrder);
        }

        header('Location: ' . URLROOT . '/admin/products/' . $productId . '/edit');
        exit;
    }

    public function delete(array $params): void
    {
        $productId = (int)($params['id'] ?? 0);
        $imageId = (int)($params['imageId'] ?? 0);

        (new ProductImage())->delete($imageId, $productId);

        header('Location: ' . URLROOT . '/admin/products/' . $productId . '/edit');
        exit;
    }
    public function upload(array $params): void
    {
        $productId = (int)($params['id'] ?? 0);

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            header('Location: ' . URLROOT . '/admin/products/' . $productId . '/edit');
            exit;
        }

        $file = $_FILES['image'];

        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!isset($allowedMime[$mime])) {
            die('Invalid image type.');
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            die('Image too large. Max 5MB.');
        }

        $extension = $allowedMime[$mime];
        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;

        $uploadDir = APPROOT . '/../public/uploads/products/' . $productId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $destination = $uploadDir . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            die('Failed to move uploaded file.');
        }

        $relativePath = '/uploads/products/' . $productId . '/' . $fileName;

        $altText = trim((string)($_POST['alt_text'] ?? ''));
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        (new \App\Models\ProductImage())->create($productId, $relativePath, $altText, $sortOrder);

        header('Location: ' . URLROOT . '/admin/products/' . $productId . '/edit');
        exit;
    }
}