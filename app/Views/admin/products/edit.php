<h1><?= htmlspecialchars($data['title']) ?></h1>

<?php $p = $data['product']; ?>
<?php $errors = $data['errors'] ?? []; ?>
<?php $images = $data['images'] ?? []; ?>

<div class="grid-2">

    <!-- LEFT COLUMN -->
    <div>

        <!-- MAIN PRODUCT EDIT FORM -->
        <form method="post" action="<?= URLROOT ?>/admin/products/<?= (int)$p['id'] ?>">

            <div class="card">
                <h2>Core</h2>

                <label>Name</label><br>
                <input name="name" value="<?= htmlspecialchars((string)$p['name']) ?>">
                <?php if (!empty($errors['name'])): ?><p class="text-danger"><?= htmlspecialchars($errors['name']) ?></p><?php endif; ?>

                <br><br>

                <label>SKU</label><br>
                <input name="sku" value="<?= htmlspecialchars((string)$p['sku']) ?>">
                <?php if (!empty($errors['sku'])): ?><p class="text-danger"><?= htmlspecialchars($errors['sku']) ?></p><?php endif; ?>

                <br><br>

                <label>Slug</label><br>
                <input name="slug" value="<?= htmlspecialchars((string)$p['slug']) ?>">
                <?php if (!empty($errors['slug'])): ?><p class="text-danger"><?= htmlspecialchars($errors['slug']) ?></p><?php endif; ?>

                <br><br>

                <label>Description</label><br>
                <textarea name="description" rows="4"><?= htmlspecialchars((string)($p['description'] ?? '')) ?></textarea>

                <br><br>

                <label>Description HTML</label><br>
                <textarea name="description_html" rows="8"><?= htmlspecialchars((string)($p['description_html'] ?? '')) ?></textarea>
            </div>

            <div class="card">
                <h2>SEO</h2>

                <label>Meta title</label><br>
                <input name="meta_title" value="<?= htmlspecialchars((string)($p['meta_title'] ?? '')) ?>">

                <br><br>

                <label>Meta description</label><br>
                <textarea name="meta_description" rows="4"><?= htmlspecialchars((string)($p['meta_description'] ?? '')) ?></textarea>
            </div>

            <div class="card">
                <h2>Pricing & Status</h2>

                <label>Price (minor units)</label><br>
                <input type="number" name="price_minor" min="1" value="<?= htmlspecialchars((string)$p['price_minor']) ?>">
                <?php if (!empty($errors['price_minor'])): ?><p class="text-danger"><?= htmlspecialchars($errors['price_minor']) ?></p><?php endif; ?>

                <br><br>

                <label>Currency</label><br>
                <input name="currency" value="<?= htmlspecialchars((string)$p['currency']) ?>" maxlength="3">

                <br><br>

                <label>Status</label><br>
                <select name="status">
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
                <select name="category_id">
                    <option value="">-- None --</option>
                    <?php foreach (($data['categories'] ?? []) as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= ((int)($p['category_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string)$c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <br><br>

                <label>Stock on hand</label><br>
                <input type="number" name="stock_on_hand" min="0" value="<?= htmlspecialchars((string)($p['stock_on_hand'] ?? 0)) ?>">
            </div>

            <div class="card">
                <button class="btn" type="submit">Save Changes</button>
                <a class="btn secondary" href="<?= URLROOT ?>/admin/products">Cancel</a>
            </div>

        </form>
        <!-- END MAIN PRODUCT FORM -->

    </div>

    <!-- RIGHT COLUMN -->
    <div>

        <div class="card">
            <h2>Images</h2>

            <?php if (!$images): ?>
                <p>No images yet.</p>
            <?php else: ?>
                <?php foreach ($images as $img): ?>
                    <div class="admin-image-panel">
                        <div class="admin-image-panel__row">

                            <div>
                                <img src="<?= htmlspecialchars((string)$img['url']) ?>"
                                     alt="<?= htmlspecialchars((string)($img['alt_text'] ?? '')) ?>"
                                     class="admin-thumb admin-thumb--large">
                            </div>

                            <div class="admin-image-panel__body">
                                <form method="post" action="<?= URLROOT ?>/admin/products/<?= (int)$p['id'] ?>/images/<?= (int)$img['id'] ?>/update">
                                    <label>Alt text</label><br>
                                    <input name="alt_text" value="<?= htmlspecialchars((string)($img['alt_text'] ?? '')) ?>"><br><br>

                                    <label>Sort order</label><br>
                                    <input type="number" name="sort_order" value="<?= (int)$img['sort_order'] ?>" class="admin-qty-input"><br><br>

                                    <button class="btn secondary" type="submit">Update Image</button>
                                </form>
                            </div>

                            <div>
                                <form method="post"
                                      action="<?= URLROOT ?>/admin/products/<?= (int)$p['id'] ?>/images/<?= (int)$img['id'] ?>/delete"
                                      data-confirm="Delete this image?">
                                    <button class="btn" type="submit">Delete</button>
                                </form>
                            </div>

                        </div>

                        <?php if ((int)$img['sort_order'] === 0): ?>
                            <p class="admin-meta-note"><small>Primary image (lowest sort order)</small></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Upload New Image</h2>

            <form method="post"
                  action="<?= URLROOT ?>/admin/products/<?= (int)$p['id'] ?>/images/upload"
                  enctype="multipart/form-data">

                <label>Image file</label><br>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required><br><br>

                <label>Alt text</label><br>
                <input type="text" name="alt_text"><br><br>

                <label>Sort order</label><br>
                <input type="number" name="sort_order" value="0" class="admin-qty-input"><br><br>

                <button class="btn" type="submit">Upload Image</button>
            </form>
        </div>

    </div>

</div>
