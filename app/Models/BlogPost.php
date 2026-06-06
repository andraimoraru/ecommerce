<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class BlogPost
{
    private PDO $pdo;

    // Bootstrap a PDO-backed blog post model.
    public function __construct()
    {
        $db = new Database();
        $pdo = $db->getPdo();

        if (!$pdo instanceof PDO) {
            throw new \RuntimeException('DB connection not available: ' . ($db->getError() ?? 'unknown'));
        }

        $this->pdo = $pdo;
    }

    /** @return array<int,array<string,mixed>> */
    // Return published posts for the public blog listing.
    public function getPublished(int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, title, slug, image_url, excerpt, status, created_at, updated_at
            FROM blog_posts
            WHERE status = 'PUBLISHED'
            ORDER BY created_at DESC, id DESC
            LIMIT :lim OFFSET :off
        ");

        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Count published posts for public pagination.
    public function countPublished(): int
    {
        $stmt = $this->pdo->query("
            SELECT COUNT(*)
            FROM blog_posts
            WHERE status = 'PUBLISHED'
        ");

        return (int)$stmt->fetchColumn();
    }

    // Fetch one published post by slug for clean public URLs.
    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, title, slug, image_url, excerpt, content, status, created_at, updated_at
            FROM blog_posts
            WHERE slug = :slug
              AND status = 'PUBLISHED'
            LIMIT 1
        ");

        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /** @return array<int,array<string,mixed>> */
    // Return all posts for admin management, optionally filtered by status.
    public function getAll(?string $status = null, int $limit = 25, int $offset = 0): array
    {
        $sql = "
            SELECT id, title, slug, image_url, excerpt, status, created_at, updated_at
            FROM blog_posts
        ";

        if ($status !== null) {
            $sql .= " WHERE status = :status";
        }

        $sql .= " ORDER BY created_at DESC, id DESC LIMIT :lim OFFSET :off";

        $stmt = $this->pdo->prepare($sql);

        if ($status !== null) {
            $stmt->bindValue(':status', $status);
        }

        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Count admin-visible posts, optionally filtered by status.
    public function countAll(?string $status = null): int
    {
        $sql = "SELECT COUNT(*) FROM blog_posts";

        if ($status !== null) {
            $sql .= " WHERE status = :status";
        }

        $stmt = $this->pdo->prepare($sql);

        if ($status !== null) {
            $stmt->bindValue(':status', $status);
        }

        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    // Fetch one post by id for admin editing.
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, title, slug, image_url, excerpt, content, status, created_at, updated_at
            FROM blog_posts
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    // Check whether a slug exists, optionally excluding one post.
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->pdo->prepare("
                SELECT 1
                FROM blog_posts
                WHERE slug = :slug
                  AND id != :id
                LIMIT 1
            ");
            $stmt->execute(['slug' => $slug, 'id' => $excludeId]);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT 1
                FROM blog_posts
                WHERE slug = :slug
                LIMIT 1
            ");
            $stmt->execute(['slug' => $slug]);
        }

        return (bool)$stmt->fetchColumn();
    }

    /**
     * @param array<string,string> $data
     */
    // Create a new blog post.
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO blog_posts (
                title, slug, image_url, excerpt, content, status, created_at, updated_at
            ) VALUES (
                :title, :slug, :image_url, :excerpt, :content, :status, NOW(), NOW()
            )
        ");

        $stmt->execute([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'image_url' => $data['image_url'] !== '' ? $data['image_url'] : null,
            'excerpt' => $data['excerpt'] !== '' ? $data['excerpt'] : null,
            'content' => $data['content'],
            'status' => $data['status'],
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * @param array<string,string> $data
     */
    // Update editable fields for an existing blog post.
    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE blog_posts
            SET
                title = :title,
                slug = :slug,
                image_url = :image_url,
                excerpt = :excerpt,
                content = :content,
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'slug' => $data['slug'],
            'image_url' => $data['image_url'] !== '' ? $data['image_url'] : null,
            'excerpt' => $data['excerpt'] !== '' ? $data['excerpt'] : null,
            'content' => $data['content'],
            'status' => $data['status'],
        ]);
    }

    // Publish one post.
    public function publish(int $id): void
    {
        $this->setStatus($id, 'PUBLISHED');
    }

    // Revert one post to draft.
    public function draft(int $id): void
    {
        $this->setStatus($id, 'DRAFT');
    }

    // Delete one blog post.
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM blog_posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    // Apply a lifecycle status change.
    private function setStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE blog_posts
            SET status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);
    }
}
