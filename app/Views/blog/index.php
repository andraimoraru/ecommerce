<h1 class="page-title">Blog</h1>

<?php $posts = $data['posts'] ?? []; ?>
<?php $pagination = $data['pagination'] ?? ['page' => 1, 'total_pages' => 1]; ?>
<?php $currentPage = (int)($pagination['page'] ?? 1); ?>
<?php $totalPages = (int)($pagination['total_pages'] ?? 1); ?>

<?php if (!$posts): ?>
    <p>No published posts yet.</p>
<?php else: ?>
    <div class="blog-list">
        <?php foreach ($posts as $post): ?>
            <article class="blog-card">
                <a class="link-reset" href="<?= URLROOT ?>/blog/<?= htmlspecialchars((string)$post['slug']) ?>">
                    <div class="blog-card__image-wrap">
                        <?php if (!empty($post['image_url'])): ?>
                            <img
                                src="<?= htmlspecialchars((string)$post['image_url']) ?>"
                                alt="<?= htmlspecialchars((string)$post['title']) ?>"
                                class="blog-card__image"
                                loading="lazy"
                            >
                        <?php else: ?>
                            <div class="product-placeholder">Blog</div>
                        <?php endif; ?>
                    </div>

                    <div class="blog-card__body">
                        <p class="blog-date">
                            <?= htmlspecialchars(date('j M Y', strtotime((string)$post['created_at']))) ?>
                        </p>
                        <h2 class="blog-card__title"><?= htmlspecialchars((string)$post['title']) ?></h2>

                        <?php if (!empty($post['excerpt'])): ?>
                            <p class="blog-card__excerpt"><?= htmlspecialchars((string)$post['excerpt']) ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <?php if ($currentPage > 1): ?>
                <a class="btn secondary" href="<?= URLROOT ?>/blog?page=<?= $currentPage - 1 ?>">Previous</a>
            <?php endif; ?>
            <span>Page <?= $currentPage ?> of <?= $totalPages ?></span>
            <?php if ($currentPage < $totalPages): ?>
                <a class="btn secondary" href="<?= URLROOT ?>/blog?page=<?= $currentPage + 1 ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
