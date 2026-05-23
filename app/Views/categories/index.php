<h1 class="page-title"><?= htmlspecialchars($data['title']) ?></h1>

<?php $categories = $data['categories'] ?? []; ?>

<?php if (!$categories): ?>
    <p>No categories available yet.</p>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($categories as $category): ?>
            <div class="product-card">
                <a href="<?= URLROOT ?>/categories/<?= htmlspecialchars((string)$category['slug']) ?>" class="link-reset">
                    <div class="product-image-wrap">
                        <?php if (!empty($category['featured_image'])): ?>
                            <img
                                src="<?= htmlspecialchars((string)$category['featured_image']) ?>"
                                alt="<?= htmlspecialchars((string)$category['name']) ?>"
                                class="product-image"
                                loading="lazy"
                            >
                        <?php else: ?>
                            <div class="product-placeholder">No image</div>
                        <?php endif; ?>
                    </div>

                </a>

                <h3 class="product-title">
                    <?= htmlspecialchars((string)$category['name']) ?>
                </h3>

                <p class="product-price">
                    Products: <?= (int)$category['product_count'] ?>
                </p>

                <a class="add-cart-btn"
                   href="<?= URLROOT ?>/categories/<?= htmlspecialchars((string)$category['slug']) ?>"
                   class="button-link">
                    View Category
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
