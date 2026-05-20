<?php $category = $data['category'] ?? null; ?>
<?php $parents = $data['parents'] ?? []; ?>
<?php $errors = $data['errors'] ?? []; ?>

<?php if (!$category): ?>
  <p>Category not found.</p>
<?php else: ?>
  <p style="margin:0 0 18px 0;">
    <a href="<?= URLROOT ?>/admin/categories">← Back to categories</a>
  </p>

  <h1><?= htmlspecialchars($data['title'] ?? 'Edit Category') ?></h1>

  <div class="card" style="max-width:780px;">
    <form method="post" action="<?= URLROOT ?>/admin/categories/<?= (int)$category['id'] ?>">
      <div style="margin-bottom:16px;">
        <label>Name</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars((string)$category['name']) ?>" style="width:100%;">
        <?php if (!empty($errors['name'])): ?><p style="color:red;"><?= htmlspecialchars((string)$errors['name']) ?></p><?php endif; ?>
      </div>

      <div style="margin-bottom:16px;">
        <label>Slug</label><br>
        <input type="text" name="slug" value="<?= htmlspecialchars((string)$category['slug']) ?>" style="width:100%;">
        <?php if (!empty($errors['slug'])): ?><p style="color:red;"><?= htmlspecialchars((string)$errors['slug']) ?></p><?php endif; ?>
      </div>

      <div style="margin-bottom:16px;">
        <label>Parent Category</label><br>
        <select name="parent_id" style="width:100%;">
          <option value="0">None</option>
          <?php foreach ($parents as $parent): ?>
            <option value="<?= (int)$parent['id'] ?>" <?= ((int)($category['parent_id'] ?? 0) === (int)$parent['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars((string)$parent['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['parent_id'])): ?><p style="color:red;"><?= htmlspecialchars((string)$errors['parent_id']) ?></p><?php endif; ?>
      </div>

      <div style="margin-bottom:16px;">
        <label>
          <input type="checkbox" name="is_active" value="1" <?= ((int)($category['is_active'] ?? 0) === 1) ? 'checked' : '' ?>>
          Active
        </label>
      </div>

      <div style="display:flex; gap:12px;">
        <button class="btn" type="submit">Save Category</button>
        <a class="btn secondary" href="<?= URLROOT ?>/admin/categories">Cancel</a>
      </div>
    </form>
  </div>
<?php endif; ?>
