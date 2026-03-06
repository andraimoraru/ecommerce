
<h1><?= htmlspecialchars($data['title']) ?></h1>

<?php $old = $data['old'] ?? []; $errors = $data['errors'] ?? []; ?>

<form method="post" action="<?= URLROOT ?>/admin/categories">

  <div>
    <label>Name</label><br>
    <input name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
    <?php if (!empty($errors['name'])): ?>
      <p style="color:red;"><?= htmlspecialchars($errors['name']) ?></p>
    <?php endif; ?>
  </div>

  <div>
    <label>Slug (optional)</label><br>
    <input name="slug" value="<?= htmlspecialchars($old['slug'] ?? '') ?>">
    <?php if (!empty($errors['slug'])): ?>
      <p style="color:red;"><?= htmlspecialchars($errors['slug']) ?></p>
    <?php endif; ?>
  </div>

  <div>
    <label>Parent Category</label><br>
    <select name="parent_id">
      <option value="">-- None --</option>
      <?php foreach (($data['parents'] ?? []) as $p): ?>
        <option value="<?= (int)$p['id'] ?>"
          <?= ((int)($old['parent_id'] ?? 0) === (int)$p['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($p['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div>
    <label>
      <input type="checkbox" name="is_active" value="1"
        <?= (!isset($old['is_active']) || (int)$old['is_active'] === 1) ? 'checked' : '' ?>>
      Active
    </label>
  </div>

  <button type="submit">Create Category</button>
</form>

<p><a href="<?= URLROOT ?>/admin/categories">← Back</a></p>
