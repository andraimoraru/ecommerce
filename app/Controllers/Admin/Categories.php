<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Category;

final class Categories extends Controller
{
    // Store a one-time admin flash message for the next page load.
    private function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }

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

    // Render the category edit form.
    public function editForm(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $categoryModel = new Category();
        $category = $categoryModel->findById($id);

        if (!$category) {
            $this->notFound('We could not find that category.');
            return;
        }

        $this->render('admin/categories/edit', [
            'title' => 'Edit Category',
            'category' => $category,
            'parents' => $this->filterParents($categoryModel->allActive(), $id),
            'errors' => [],
        ], 'admin');
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

    // Validate and persist an existing category.
    public function update(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $categoryModel = new Category();
        $category = $categoryModel->findById($id);

        if (!$category) {
            $this->notFound('We could not find that category.');
            return;
        }

        $data = [
            'name' => trim((string)($_POST['name'] ?? '')),
            'slug' => trim((string)($_POST['slug'] ?? '')),
            'parent_id' => (int)($_POST['parent_id'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        if ($data['slug'] === '') {
            $data['slug'] = $this->slugify($data['name']);
        }

        $errors = $this->validateCategoryInput($data);

        if ($data['parent_id'] === $id) {
            $errors['parent_id'] = 'A category cannot be its own parent.';
        }

        if (!$errors && $categoryModel->slugExistsForAnotherCategory($data['slug'], $id)) {
            $errors['slug'] = 'Slug already exists.';
        }

        if ($errors) {
            $this->render('admin/categories/edit', [
                'title' => 'Edit Category',
                'category' => array_merge($category, $data),
                'parents' => $this->filterParents($categoryModel->allActive(), $id),
                'errors' => $errors,
            ], 'admin');
            return;
        }

        $categoryModel->update($id, $data);

        header('Location: ' . URLROOT . '/admin/categories');
        exit;
    }

    // Delete a category unless products are still assigned to it.
    public function delete(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $categoryModel = new Category();
        $category = $categoryModel->findById($id);

        if (!$category) {
            $this->notFound('We could not find that category.');
            return;
        }

        if ($categoryModel->hasProducts($id)) {
            $this->setFlash('error', 'This category still has products linked to it, so it can’t be deleted yet.');
            header('Location: ' . URLROOT . '/admin/categories');
            exit;
        }

        $categoryModel->delete($id);
        $this->setFlash('success', 'Category deleted successfully.');

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

    /**
     * @param array<string,mixed> $data
     * @return array<string,string>
     */
    // Apply shared validation rules for category create/edit.
    private function validateCategoryInput(array $data): array
    {
        $errors = [];

        if ($data['name'] === '' || mb_strlen($data['name']) < 2) {
            $errors['name'] = 'Name must be at least 2 characters.';
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $data['slug'])) {
            $errors['slug'] = 'Slug must be lowercase and dash-separated.';
        }

        return $errors;
    }

    /**
     * @param array<int,array<string,mixed>> $parents
     * @return array<int,array<string,mixed>>
     */
    // Remove the current category from the parent options.
    private function filterParents(array $parents, int $currentId): array
    {
        return array_values(array_filter($parents, static fn(array $parent): bool => (int)$parent['id'] !== $currentId));
    }
}
