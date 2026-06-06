<h1><?= htmlspecialchars((string)($data['title'] ?? 'Blog')) ?></h1>

<div class="admin-page-actions">
    <a class="btn" href="<?= URLROOT ?>/admin/blog/create">+ Add Blog Post</a>
</div>

<?php $posts = $data['posts'] ?? []; ?>
<?php $statuses = $data['statuses'] ?? []; ?>
<?php $statusFilter = $data['status_filter'] ?? null; ?>
<?php $pagination = $data['pagination'] ?? ['page' => 1, 'total_pages' => 1]; ?>
<?php $currentPage = (int)($pagination['page'] ?? 1); ?>
<?php $totalPages = (int)($pagination['total_pages'] ?? 1); ?>
<?php $filterQuery = $statusFilter ? '&status=' . urlencode((string)$statusFilter) : ''; ?>

<form method="get" action="<?= URLROOT ?>/admin/blog" class="admin-filter-form">
    <label>Status</label>
    <select name="status">
        <option value="">All posts</option>
        <?php foreach ($statuses as $status): ?>
            <option value="<?= htmlspecialchars((string)$status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
                <?= htmlspecialchars(ucfirst(strtolower((string)$status))) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button class="btn secondary" type="submit">Filter</button>
</form>

<?php if (!$posts): ?>
    <p>No blog posts found.</p>
<?php else: ?>
    <div class="card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Published URL</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td>
                            <?php if (!empty($post['image_url'])): ?>
                                <img src="<?= htmlspecialchars((string)$post['image_url']) ?>" alt="" class="admin-thumb">
                            <?php else: ?>
                                <div class="admin-thumb-placeholder">No Image</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars((string)$post['title']) ?></strong><br>
                            <small class="admin-meta"><?= htmlspecialchars((string)$post['slug']) ?></small>
                        </td>
                        <td><?= htmlspecialchars((string)$post['status']) ?></td>
                        <td>
                            <?php if (($post['status'] ?? '') === 'PUBLISHED'): ?>
                                <a href="<?= URLROOT ?>/blog/<?= htmlspecialchars((string)$post['slug']) ?>">View post</a>
                            <?php else: ?>
                                <span class="admin-meta">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars((string)($post['updated_at'] ?? $post['created_at'] ?? '')) ?></td>
                        <td>
                            <div class="admin-actions">
                                <a class="btn secondary" href="<?= URLROOT ?>/admin/blog/<?= (int)$post['id'] ?>/edit">Edit</a>

                                <?php if (($post['status'] ?? '') === 'PUBLISHED'): ?>
                                    <form method="post" action="<?= URLROOT ?>/admin/blog/<?= (int)$post['id'] ?>/draft" class="admin-inline-form">
                                        <button class="btn secondary" type="submit">Draft</button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" action="<?= URLROOT ?>/admin/blog/<?= (int)$post['id'] ?>/publish" class="admin-inline-form">
                                        <button class="btn secondary" type="submit">Publish</button>
                                    </form>
                                <?php endif; ?>

                                <form method="post" action="<?= URLROOT ?>/admin/blog/<?= (int)$post['id'] ?>/delete" class="admin-inline-form" data-confirm="Delete this blog post? This cannot be undone.">
                                    <button class="btn secondary" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <?php if ($currentPage > 1): ?>
                <a class="btn secondary" href="<?= URLROOT ?>/admin/blog?page=<?= $currentPage - 1 ?><?= $filterQuery ?>">Previous</a>
            <?php endif; ?>
            <span>Page <?= $currentPage ?> of <?= $totalPages ?></span>
            <?php if ($currentPage < $totalPages): ?>
                <a class="btn secondary" href="<?= URLROOT ?>/admin/blog?page=<?= $currentPage + 1 ?><?= $filterQuery ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
