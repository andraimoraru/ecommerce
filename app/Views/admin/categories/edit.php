<?php $category = $data['category'] ?? null; ?>
<?php $parents = $data['parents'] ?? []; ?>
<?php $errors = $data['errors'] ?? []; ?>

<?php if (!$category): ?>
  <p>Category not found.</p>
<?php else: ?>
  <p class="admin-back-row">
    <a href="<?= URLROOT ?>/admin/categories">← Back to categories</a>
  </p>

  <h1><?= htmlspecialchars($data['title'] ?? 'Edit Category') ?></h1>

  <div class="card admin-form-card">
    <form method="post" action="<?= URLROOT ?>/admin/categories/<?= (int)$category['id'] ?>">
      <div class="admin-field">
        <label>Name</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars((string)$category['name']) ?>">
        <?php if (!empty($errors['name'])): ?><p class="text-danger"><?= htmlspecialchars((string)$errors['name']) ?></p><?php endif; ?>
      </div>

      <div class="admin-field">
        <label>Slug</label><br>
        <input type="text" name="slug" value="<?= htmlspecialchars((string)$category['slug']) ?>">
        <?php if (!empty($errors['slug'])): ?><p class="text-danger"><?= htmlspecialchars((string)$errors['slug']) ?></p><?php endif; ?>
      </div>

      <div class="admin-field">
        <label>Parent Category</label><br>
        <select name="parent_id">
          <option value="0">None</option>
          <?php foreach ($parents as $parent): ?>
            <option value="<?= (int)$parent['id'] ?>" <?= ((int)($category['parent_id'] ?? 0) === (int)$parent['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars((string)$parent['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['parent_id'])): ?><p class="text-danger"><?= htmlspecialchars((string)$errors['parent_id']) ?></p><?php endif; ?>
      </div>

      <div class="admin-field">
        <label>
          <input type="checkbox" name="is_active" value="1" <?= ((int)($category['is_active'] ?? 0) === 1) ? 'checked' : '' ?>>
          Active
        </label>
      </div>

      <div class="admin-form-actions">
        <button class="btn" type="submit">Save Category</button>
        <a class="btn secondary" href="<?= URLROOT ?>/admin/categories">Cancel</a>
      </div>
    </form>
  </div>
<?php endif; ?>
