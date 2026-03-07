<h1><?= htmlspecialchars($data['title']) ?></h1>

<?php $old = $data['old'] ?? []; ?>
<?php $errors = $data['errors'] ?? []; ?>

<form method="post" action="<?= URLROOT ?>/admin/products" enctype="multipart/form-data">

  <fieldset>
    <legend>Core</legend>

    <div>
      <label>Name</label><br>
      <input name="name" value="<?= htmlspecialchars((string)($old['name'] ?? '')) ?>" required>
      <?php if (!empty($errors['name'])): ?>
        <p style="color:red;"><?= htmlspecialchars($errors['name']) ?></p>
      <?php endif; ?>
    </div>

    <div>
      <label>SKU</label><br>
      <input name="sku" value="<?= htmlspecialchars((string)($old['sku'] ?? '')) ?>" required>
      <?php if (!empty($errors['sku'])): ?>
        <p style="color:red;"><?= htmlspecialchars($errors['sku']) ?></p>
      <?php endif; ?>
    </div>

    <div>
      <label>Slug (optional)</label><br>
      <input name="slug" value="<?= htmlspecialchars((string)($old['slug'] ?? '')) ?>" placeholder="gold-necklace">
      <?php if (!empty($errors['slug'])): ?>
        <p style="color:red;"><?= htmlspecialchars($errors['slug']) ?></p>
      <?php endif; ?>
    </div>

    <div>
      <label>Short Description</label><br>
      <textarea name="description" rows="4" cols="60"><?= htmlspecialchars((string)($old['description'] ?? '')) ?></textarea>
    </div>

    <div>
      <label>Price (minor units)</label><br>
      <input
        type="number"
        name="price_minor"
        min="1"
        value="<?= htmlspecialchars((string)($old['price_minor'] ?? '')) ?>"
        required
      >
      <small>£19.99 = 1999</small>
      <?php if (!empty($errors['price_minor'])): ?>
        <p style="color:red;"><?= htmlspecialchars($errors['price_minor']) ?></p>
      <?php endif; ?>
    </div>

    <div>
      <label>Currency</label><br>
      <input name="currency" value="<?= htmlspecialchars((string)($old['currency'] ?? 'GBP')) ?>" maxlength="3">
    </div>

    <div>
      <label>Status</label><br>
      <select name="status">
        <?php foreach (['DRAFT', 'ACTIVE', 'ARCHIVED'] as $status): ?>
          <option value="<?= $status ?>" <?= (($old['status'] ?? 'DRAFT') === $status) ? 'selected' : '' ?>>
            <?= $status ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if (!empty($errors['status'])): ?>
        <p style="color:red;"><?= htmlspecialchars($errors['status']) ?></p>
      <?php endif; ?>
    </div>
  </fieldset>

  <fieldset>
    <legend>SEO</legend>

    <div>
      <label>Meta Title</label><br>
      <input name="meta_title" value="<?= htmlspecialchars((string)($old['meta_title'] ?? '')) ?>" style="width: 70%;">
    </div>

    <div>
      <label>Meta Description</label><br>
      <textarea name="meta_description" rows="4" cols="60"><?= htmlspecialchars((string)($old['meta_description'] ?? '')) ?></textarea>
    </div>
  </fieldset>

  <fieldset>
    <legend>Category</legend>

    <select name="category_id">
      <option value="">-- Select category --</option>
      <?php foreach (($data['categories'] ?? []) as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= ((int)($old['category_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars((string)$c['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </fieldset>

  <fieldset>
    <legend>Inventory</legend>

    <div>
      <label>Stock on hand</label><br>
      <input
        type="number"
        name="stock_on_hand"
        min="0"
        value="<?= htmlspecialchars((string)($old['stock_on_hand'] ?? 0)) ?>"
      >
      <?php if (!empty($errors['stock_on_hand'])): ?>
        <p style="color:red;"><?= htmlspecialchars($errors['stock_on_hand']) ?></p>
      <?php endif; ?>
    </div>
  </fieldset>

  <fieldset>
    <legend>Upload Images</legend>

    <?php if (!empty($errors['images'])): ?>
      <p style="color:red;"><?= htmlspecialchars($errors['images']) ?></p>
    <?php endif; ?>

    <div style="border:1px solid #ddd; padding:10px; margin-bottom:10px;">
      <label>Primary image</label><br>
      <input type="file" name="images[]" accept=".jpg,.jpeg,.png,.webp"><br><br>

      <label>Alt text</label><br>
      <input name="image_alt[]" value="<?= htmlspecialchars((string)($old['image_alt'][0] ?? '')) ?>" style="width:70%;"><br><br>

      <label>Sort order</label><br>
      <input type="number" name="image_sort[]" value="<?= htmlspecialchars((string)($old['image_sort'][0] ?? '0')) ?>">
    </div>

    <div style="border:1px solid #ddd; padding:10px; margin-bottom:10px;">
      <label>Additional image</label><br>
      <input type="file" name="images[]" accept=".jpg,.jpeg,.png,.webp"><br><br>

      <label>Alt text</label><br>
      <input name="image_alt[]" value="<?= htmlspecialchars((string)($old['image_alt'][1] ?? '')) ?>" style="width:70%;"><br><br>

      <label>Sort order</label><br>
      <input type="number" name="image_sort[]" value="<?= htmlspecialchars((string)($old['image_sort'][1] ?? '1')) ?>">
    </div>

    <div style="border:1px solid #ddd; padding:10px; margin-bottom:10px;">
      <label>Additional image</label><br>
      <input type="file" name="images[]" accept=".jpg,.jpeg,.png,.webp"><br><br>

      <label>Alt text</label><br>
      <input name="image_alt[]" value="<?= htmlspecialchars((string)($old['image_alt'][2] ?? '')) ?>" style="width:70%;"><br><br>

      <label>Sort order</label><br>
      <input type="number" name="image_sort[]" value="<?= htmlspecialchars((string)($old['image_sort'][2] ?? '2')) ?>">
    </div>

    <p><small>You can leave unused image fields empty.</small></p>
  </fieldset>

  <button type="submit">Create product</button>
</form>

<p><a href="<?= URLROOT ?>/admin/products">← Back to products</a></p>