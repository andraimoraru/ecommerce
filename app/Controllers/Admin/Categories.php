<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Category;

final class Categories extends Controller
{
    // Render the admin category list.
    public function index(): void
    {
        $categories = (new \App\Models\Category())->all();

        $data = [
            'title' => 'Categories',
            'categories' => $categories,
        ];

        $this->render('admin/categories/index', $data, 'admin');
    }

    // Render the category creation form.
    public function createForm(): void
    {
        $parents = (new \App\Models\Category())->allActive();

        $data = [
            'title' => 'Add Category',
            'parents' => $parents,
            'errors' => [],
            'old' => [],
        ];

        $this->render('admin/categories/create', $data, 'admin');
    }

    // Validate and persist a new category.
    public function store(): void
    {
        $old = [
            'name' => trim((string)($_POST['name'] ?? '')),
            'slug' => trim((string)($_POST['slug'] ?? '')),
            'parent_id' => (int)($_POST['parent_id'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        if ($old['slug'] === '') {
            $old['slug'] = $this->slugify($old['name']);
        }

        $errors = [];

        if ($old['name'] === '' || mb_strlen($old['name']) < 2) {
            $errors['name'] = 'Name must be at least 2 characters.';
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $old['slug'])) {
            $errors['slug'] = 'Slug must be lowercase and dash-separated.';
        }

        $categoryModel = new Category();

        if (!$errors && $categoryModel->slugExists($old['slug'])) {
            $errors['slug'] = 'Slug already exists.';
        }

        if ($errors) {
            $parents = $categoryModel->allActive();

            $this->view('admin/categories/create', [
                'title' => 'Add Category',
                'parents' => $parents,
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        $categoryModel->create($old);

        header('Location: ' . URLROOT . '/admin/categories');
        exit;
    }

    // Convert a category name into a simple URL-friendly slug.
    private function slugify(string $name): string
    {
        $s = mb_strtolower(trim($name));
        $s = preg_replace('/[^a-z0-9]+/u', '-', $s) ?? '';
        $s = trim($s, '-');
        return $s !== '' ? $s : 'category';
    }
}
