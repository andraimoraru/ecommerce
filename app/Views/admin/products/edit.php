<h1><?= htmlspecialchars($data['title']) ?></h1>

<?php $p = $data['product']; ?>
<?php $errors = $data['errors'] ?? []; ?>

<form method="post" action="<?= URLROOT ?>/admin/products/<?= (int)$p['id'] ?>">

    <div class="grid-2">

        <div>
            <div class="card">
                <h2>Core</h2>

                <label>Name</label><br>
                <input name="name" value="<?= htmlspecialchars((string)$p['name']) ?>" style="width:100%;">
                <?php if (!empty($errors['name'])): ?><p style="color:red;"><?= htmlspecialchars($errors['name']) ?></p><?php endif; ?>

                <br><br>

                <label>SKU</label><br>
                <input name="sku" value="<?= htmlspecialchars((string)$p['sku']) ?>" style="width:100%;">
                <?php if (!empty($errors['sku'])): ?><p style="color:red;"><?= htmlspecialchars($errors['sku']) ?></p><?php endif; ?>

                <br><br>

                <label>Slug</label><br>
                <input name="slug" value="<?= htmlspecialchars((string)$p['slug']) ?>" style="width:100%;">
                <?php if (!empty($errors['slug'])): ?><p style="color:red;"><?= htmlspecialchars($errors['slug']) ?></p><?php endif; ?>

                <br><br>

                <label>Description</label><br>
                <textarea name="description" rows="4" style="width:100%;"><?= htmlspecialchars((string)($p['description'] ?? '')) ?></textarea>

                <br><br>

                <label>Description HTML</label><br>
                <textarea name="description_html" rows="8" style="width:100%;"><?= htmlspecialchars((string)($p['description_html'] ?? '')) ?></textarea>
            </div>

            <div class="card">
                <h2>SEO</h2>

                <label>Meta title</label><br>
                <input name="meta_title" value="<?= htmlspecialchars((string)($p['meta_title'] ?? '')) ?>" style="width:100%;">

                <br><br>

                <label>Meta description</label><br>
                <textarea name="meta_description" rows="4" style="width:100%;"><?= htmlspecialchars((string)($p['meta_description'] ?? '')) ?></textarea>
            </div>

            <div class="card">
                <h2>Images</h2>

                <?php $images = $data['images'] ?? []; ?>

                <?php if (!$images): ?>
                    <p>No images yet.</p>
                <?php else: ?>
                    <?php foreach ($images as $img): ?>
                        <div style="border:1px solid #ddd; padding:12px; border-radius:8px; margin-bottom:12px;">
                            <div style="display:flex; gap:16px; align-items:flex-start; flex-wrap:wrap;">

                                <div>
                                    <img src="<?= htmlspecialchars((string)$img['url']) ?>"
                                        alt="<?= htmlspecialchars((string)($img['alt_text'] ?? '')) ?>"
                                        style="width:100px;height:100px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                                </div>

                                <div style="flex:1; min-width:260px;">
                                    <form method="post"
                                        action="<?= URLROOT ?>/admin/products/<?= (int)$p['id'] ?>/images/upload"
                                        enctype="multipart/form-data">

                                        <label>Image file</label><br>
                                        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required><br><br>

                                        <label>Alt text</label><br>
                                        <input type="text" name="alt_text"><br><br>

                                        <label>Sort order</label><br>
                                        <input type="number" name="sort_order" value="0"><br><br>

                                        <button class="btn" type="submit">Upload Image</button>
                                    </form>
                                </div>

                                <div>
                                    <form method="post" action="<?= URLROOT ?>/admin/products/<?= (int)$p['id'] ?>/images/<?= (int)$img['id'] ?>/delete" onsubmit="return confirm('Delete this image?');">
                                        <button class="btn" type="submit">Delete</button>
                                    </form>
                                </div>

                            </div>

                            <?php if ((int)$img['sort_order'] === 0): ?>
                                <p style="margin-top:8px; color:#666;"><small>Primary image (sort_order = 0)</small></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <hr style="margin:18px 0;">

                <h3>Add New Image</h3>

                    <form method="post"
                        action="<?= URLROOT ?>/admin/products/<?= (int)$p['id'] ?>/images/upload"
                        enctype="multipart/form-data">

                        <label>Image file</label><br>
                        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required><br><br>

                        <label>Alt text</label><br>
                        <input type="text" name="alt_text"><br><br>

                        <label>Sort order</label><br>
                        <input type="number" name="sort_order" value="0"><br><br>

                        <button class="btn" type="submit">Upload Image</button>
                    </form>
            </div>
        <div>
            <div class="card">
                <h2>Pricing & Status</h2>

                <label>Price (minor units)</label><br>
                <input type="number" name="price_minor" min="1" value="<?= htmlspecialchars((string)$p['price_minor']) ?>" style="width:100%;">
                <?php if (!empty($errors['price_minor'])): ?><p style="color:red;"><?= htmlspecialchars($errors['price_minor']) ?></p><?php endif; ?>

                <br><br>

                <label>Currency</label><br>
                <input name="currency" value="<?= htmlspecialchars((string)$p['currency']) ?>" maxlength="3" style="width:100%;">

                <br><br>

                <label>Status</label><br>
                <select name="status" style="width:100%;">
                    <?php foreach (['DRAFT','ACTIVE','ARCHIVED'] as $status): ?>
                        <option value="<?= $status ?>" <?= (($p['status'] ?? '') === $status) ? 'selected' : '' ?>>
                            <?= $status ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="card">
                <h2>Category & Inventory</h2>

                <label>Category</label><br>
                <select name="category_id" style="width:100%;">
                    <option value="">-- None --</option>
                    <?php foreach (($data['categories'] ?? []) as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= ((int)($p['category_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string)$c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <br><br>

                <label>Stock on hand</label><br>
                <input type="number" name="stock_on_hand" min="0" value="<?= htmlspecialchars((string)($p['stock_on_hand'] ?? 0)) ?>" style="width:100%;">
            </div>

            <div class="card">
                <button class="btn" type="submit">Save Changes</button>
                <a class="btn secondary" href="<?= URLROOT ?>/admin/products">Cancel</a>
            </div>
        </div>

    </div>
</form>