<h1><?= htmlspecialchars((string)($data['title'] ?? 'Edit Blog Post')) ?></h1>

<?php $post = $data['post'] ?? []; ?>
<?php $errors = $data['errors'] ?? []; ?>
<?php $statuses = $data['statuses'] ?? []; ?>

<form method="post" action="<?= URLROOT ?>/admin/blog/<?= (int)($post['id'] ?? 0) ?>/update">
    <div class="grid-2">
        <fieldset>
            <legend>Post content</legend>

            <div class="stack-sm">
                <label>Title</label><br>
                <input name="title" value="<?= htmlspecialchars((string)($post['title'] ?? '')) ?>" required>
                <?php if (!empty($errors['title'])): ?><p class="text-danger"><?= htmlspecialchars((string)$errors['title']) ?></p><?php endif; ?>
            </div>

            <div class="stack-sm">
                <label>Slug</label><br>
                <input name="slug" value="<?= htmlspecialchars((string)($post['slug'] ?? '')) ?>" required>
                <?php if (!empty($errors['slug'])): ?><p class="text-danger"><?= htmlspecialchars((string)$errors['slug']) ?></p><?php endif; ?>
            </div>

            <div class="stack-sm">
                <label>Image URL</label><br>
                <input type="url" name="image_url" value="<?= htmlspecialchars((string)($post['image_url'] ?? '')) ?>">
            </div>

            <div class="stack-sm">
                <label>Excerpt</label><br>
                <textarea name="excerpt" rows="4"><?= htmlspecialchars((string)($post['excerpt'] ?? '')) ?></textarea>
            </div>

            <div class="stack-sm">
                <label>Content</label><br>
                <textarea name="content" rows="14" required><?= htmlspecialchars((string)($post['content'] ?? '')) ?></textarea>
                <?php if (!empty($errors['content'])): ?><p class="text-danger"><?= htmlspecialchars((string)$errors['content']) ?></p><?php endif; ?>
            </div>
        </fieldset>

        <fieldset>
            <legend>Publishing</legend>

            <div class="stack-sm">
                <label>Status</label><br>
                <select name="status">
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= htmlspecialchars((string)$status) ?>" <?= ($post['status'] ?? 'DRAFT') === $status ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst(strtolower((string)$status))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['status'])): ?><p class="text-danger"><?= htmlspecialchars((string)$errors['status']) ?></p><?php endif; ?>
            </div>

            <?php if (($post['status'] ?? '') === 'PUBLISHED'): ?>
                <p class="admin-meta">
                    Public URL: <a href="<?= URLROOT ?>/blog/<?= htmlspecialchars((string)($post['slug'] ?? '')) ?>">View post</a>
                </p>
            <?php endif; ?>
        </fieldset>
    </div>

    <div class="admin-button-row">
        <button class="btn" type="submit">Save Changes</button>
        <a class="btn secondary" href="<?= URLROOT ?>/admin/blog">Cancel</a>
    </div>
</form>
