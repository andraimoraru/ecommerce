<?php $post = $data['post'] ?? []; ?>

<article class="blog-post">
    <a class="category-back" href="<?= URLROOT ?>/blog">Back to blog</a>

    <header class="blog-post__header">
        <p class="blog-date">
            <?= htmlspecialchars(date('j M Y', strtotime((string)($post['created_at'] ?? 'now')))) ?>
        </p>
        <h1 class="blog-post__title"><?= htmlspecialchars((string)($post['title'] ?? 'Blog post')) ?></h1>
    </header>

    <?php if (!empty($post['image_url'])): ?>
        <div class="blog-post__image-wrap">
            <img
                src="<?= htmlspecialchars((string)$post['image_url']) ?>"
                alt="<?= htmlspecialchars((string)($post['title'] ?? 'Blog post')) ?>"
                class="blog-post__image"
            >
        </div>
    <?php endif; ?>

    <div class="blog-post__content">
        <?= nl2br(htmlspecialchars((string)($post['content'] ?? ''))) ?>
    </div>
</article>
