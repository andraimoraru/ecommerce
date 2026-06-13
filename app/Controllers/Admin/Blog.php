<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\BlogPost;

final class Blog extends Controller
{
    private const STATUSES = ['DRAFT', 'PUBLISHED'];

    // Store a one-time admin flash message for the next page load.
    private function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    // Render the admin blog post list with optional status filtering.
    public function index(): void
    {
        $status = strtoupper(trim((string)($_GET['status'] ?? '')));
        $statusFilter = in_array($status, self::STATUSES, true) ? $status : null;
        $perPage = 25;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        $blogModel = new BlogPost();
        $totalItems = $blogModel->countAll($statusFilter);
        $totalPages = max(1, (int)ceil($totalItems / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $this->render('admin/blog/index', [
            'title' => 'Blog',
            'posts' => $blogModel->getAll($statusFilter, $perPage, $offset),
            'status_filter' => $statusFilter,
            'statuses' => self::STATUSES,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalItems,
                'total_pages' => $totalPages,
            ],
        ], 'admin');
    }

    // Render the blog post creation form.
    public function createForm(): void
    {
        $this->render('admin/blog/create', [
            'title' => 'Add Blog Post',
            'old' => $this->blankPost(),
            'errors' => [],
            'statuses' => self::STATUSES,
        ], 'admin');
    }

    // Validate and create a blog post.
    public function store(): void
    {
        $old = $this->collectInput();
        $errors = $this->validate($old);
        $blogModel = new BlogPost();

        if (!$errors && $blogModel->slugExists($old['slug'])) {
            $errors['slug'] = 'Slug already exists.';
        }

        if ($errors) {
            $this->render('admin/blog/create', [
                'title' => 'Add Blog Post',
                'old' => $old,
                'errors' => $errors,
                'statuses' => self::STATUSES,
            ], 'admin');
            return;
        }

        $blogModel->create($old);
        $this->setFlash('success', 'Blog post created successfully.');

        header('Location: ' . URLROOT . '/admin/blog');
        exit;
    }

    // Render the blog post edit form.
    public function editForm(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $post = (new BlogPost())->findById($id);

        if (!$post) {
            $this->notFound('We could not find that blog post.');
            return;
        }

        $this->render('admin/blog/edit', [
            'title' => 'Edit Blog Post',
            'post' => $post,
            'errors' => [],
            'statuses' => self::STATUSES,
        ], 'admin');
    }

    // Validate and update a blog post.
    public function update(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $blogModel = new BlogPost();
        $existing = $blogModel->findById($id);

        if (!$existing) {
            $this->notFound('We could not find that blog post.');
            return;
        }

        $old = $this->collectInput();
        $errors = $this->validate($old);

        if (!$errors && $blogModel->slugExists($old['slug'], $id)) {
            $errors['slug'] = 'Slug already exists.';
        }

        if ($errors) {
            $this->render('admin/blog/edit', [
                'title' => 'Edit Blog Post',
                'post' => array_merge($existing, $old),
                'errors' => $errors,
                'statuses' => self::STATUSES,
            ], 'admin');
            return;
        }

        $blogModel->update($id, $old);
        $this->setFlash('success', 'Blog post updated successfully.');

        header('Location: ' . URLROOT . '/admin/blog');
        exit;
    }

    // Publish one blog post.
    public function publish(array $params): void
    {
        (new BlogPost())->publish((int)($params['id'] ?? 0));
        $this->setFlash('success', 'Blog post published.');

        header('Location: ' . URLROOT . '/admin/blog');
        exit;
    }

    // Revert one blog post to draft.
    public function draft(array $params): void
    {
        (new BlogPost())->draft((int)($params['id'] ?? 0));
        $this->setFlash('success', 'Blog post moved back to draft.');

        header('Location: ' . URLROOT . '/admin/blog');
        exit;
    }

    // Delete one blog post.
    public function delete(array $params): void
    {
        (new BlogPost())->delete((int)($params['id'] ?? 0));
        $this->setFlash('success', 'Blog post deleted.');

        header('Location: ' . URLROOT . '/admin/blog');
        exit;
    }

    /**
     * @return array<string,string>
     */
    // Collect editable blog post fields from the request.
    private function collectInput(): array
    {
        $data = [
            'title' => trim((string)($_POST['title'] ?? '')),
            'slug' => trim((string)($_POST['slug'] ?? '')),
            'image_url' => trim((string)($_POST['image_url'] ?? '')),
            'excerpt' => trim((string)($_POST['excerpt'] ?? '')),
            'content' => trim((string)($_POST['content'] ?? '')),
            'status' => strtoupper(trim((string)($_POST['status'] ?? 'DRAFT'))),
        ];

        if ($data['slug'] === '') {
            $data['slug'] = $this->slugify($data['title']);
        }

        return $data;
    }

    /**
     * @param array<string,string> $data
     * @return array<string,string>
     */
    // Apply blog post validation rules.
    private function validate(array $data): array
    {
        $errors = [];

        if ($data['title'] === '') {
            $errors['title'] = 'Title is required.';
        }

        if ($data['slug'] === '') {
            $errors['slug'] = 'Slug is required.';
        } elseif (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $data['slug'])) {
            $errors['slug'] = 'Slug must be lowercase and dash-separated.';
        }

        if ($data['content'] === '') {
            $errors['content'] = 'Content is required.';
        }

        if (!in_array($data['status'], self::STATUSES, true)) {
            $errors['status'] = 'Status must be Draft or Published.';
        }

        return $errors;
    }

    // Convert a post title into a simple URL-friendly slug.
    private function slugify(string $title): string
    {
        $slug = mb_strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9]+/u', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'blog-post';
    }

    /**
     * @return array<string,string>
     */
    // Provide default form values for new posts.
    private function blankPost(): array
    {
        return [
            'title' => '',
            'slug' => '',
            'image_url' => '',
            'excerpt' => '',
            'content' => '',
            'status' => 'DRAFT',
        ];
    }
}
