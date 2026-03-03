<?php require APPROOT . '/Views/inc/header.php'; ?>
<h1><?= htmlspecialchars($data['title']) ?></h1>

<?php $old = $data['old'] ?? []; $errors = $data['errors'] ?? []; ?>

<form method="post" action="<?= URLROOT ?>/admin/products">
  <fieldset>
    <legend>Core</legend>

    <div>
      <label>Name</label><br>
      <input name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
      <?php if (!empty($errors['name'])): ?><p style="color:red;"><?= htmlspecialchars($errors['name']) ?></p><?php endif; ?>
    </div>

    <div>
      <label>SKU</label><br>
      <input name="sku" value="<?= htmlspecialchars($old['sku'] ?? '') ?>" required>
      <?php if (!empty($errors['sku'])): ?><p style="color:red;"><?= htmlspecialchars($errors['sku']) ?></p><?php endif; ?>
    </div>

    <div>
      <label>Slug (optional)</label><br>
      <input name="slug" value="<?= htmlspecialchars($old['slug'] ?? '') ?>" placeholder="gold-necklace">
      <?php if (!empty($errors['slug'])): ?><p style="color:red;"><?= htmlspecialchars($errors['slug']) ?></p><?php endif; ?>
    </div>

    <div>
      <label>Description</label><br>
      <textarea name="description" rows="5" cols="60"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
    </div>

    <div>
      <label>Price (minor units)</label><br>
      <input type="number" name="price_minor" min="1" value="<?= htmlspecialchars((string)($old['price_minor'] ?? '')) ?>" required>
      <small>£19.99 = 1999</small>
      <?php if (!empty($errors['price_minor'])): ?><p style="color:red;"><?= htmlspecialchars($errors['price_minor']) ?></p><?php endif; ?>
    </div>

    <div>
      <label>Currency</label><br>
      <input name="currency" value="<?= htmlspecialchars($old['currency'] ?? 'GBP') ?>" maxlength="3">
    </div>

    <label>
      <input type="checkbox" name="is_active" value="1" <?= (!isset($old['is_active']) || (int)$old['is_active'] === 1) ? 'checked' : '' ?>>
      Active
    </label>
  </fieldset>

  <fieldset>
    <legend>Category</legend>
    <select name="category_id">
      <option value="">-- Select category --</option>
      <?php foreach (($data['categories'] ?? []) as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= ((int)($old['category_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($c['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </fieldset>

  <fieldset>
    <legend>Inventory</legend>
    <div>
      <label>Stock on hand</label><br>
      <input type="number" name="stock_on_hand" min="0" value="<?= htmlspecialchars((string)($old['stock_on_hand'] ?? 0)) ?>">
      <?php if (!empty($errors['stock_on_hand'])): ?><p style="color:red;"><?= htmlspecialchars($errors['stock_on_hand']) ?></p><?php endif; ?>
    </div>
  </fieldset>

  <fieldset>
    <legend>Images (URLs)</legend>

    <?php if (!empty($errors['images'])): ?><p style="color:red;"><?= htmlspecialchars($errors['images']) ?></p><?php endif; ?>

    <?php $imgs = $old['images'] ?? [['url'=>'','alt_text'=>'','sort_order'=>0]]; ?>
    <?php foreach ($imgs as $i => $img): ?>
      <div style="border:1px solid #ddd; padding:10px; margin-bottom:10px;">
        <label>Image URL</label><br>
        <input name="image_url[]" value="<?= htmlspecialchars((string)($img['url'] ?? '')) ?>" style="width:70%;">
        <br><br>

        <label>Alt text</label><br>
        <input name="image_alt[]" value="<?= htmlspecialchars((string)($img['alt_text'] ?? '')) ?>" style="width:70%;">
        <br><br>

        <label>Sort order</label><br>
        <input type="number" name="image_sort[]" value="<?= htmlspecialchars((string)($img['sort_order'] ?? $i)) ?>">
      </div>
    <?php endforeach; ?>

    <p><small>MVP: paste URLs. Next step: file upload + storage.</small></p>
  </fieldset>

  <button type="submit">Create product</button>
</form>

<p><a href="<?= URLROOT ?>/admin/products">← Back to products</a></p>
<?php require APPROOT . '/Views/inc/footer.php'; ?>