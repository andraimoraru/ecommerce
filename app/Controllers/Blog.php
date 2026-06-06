<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\BlogPost;

final class Blog extends Controller
{
    // Render published blog posts with public pagination.
    public function index(): void
    {
        $blogModel = new BlogPost();
        $perPage = 10;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $totalPosts = $blogModel->countPublished();
        $totalPages = max(1, (int)ceil($totalPosts / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $this->render('blog/index', [
            'title' => 'Blog',
            'posts' => $blogModel->getPublished($perPage, $offset),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalPosts,
                'total_pages' => $totalPages,
            ],
        ], 'main');
    }

    // Render a single published post by its clean slug.
    public function show(array $params): void
    {
        $slug = (string)($params['slug'] ?? '');
        $post = (new BlogPost())->getBySlug($slug);

        if (!$post) {
            http_response_code(404);
            echo 'Blog post not found';
            return;
        }

        $this->render('blog/show', [
            'title' => $post['title'],
            'meta_description' => $post['excerpt'] ?? '',
            'post' => $post,
        ], 'main');
    }
}
