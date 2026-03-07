<h1 style="margin-bottom:20px;"><?= htmlspecialchars($data['title']) ?></h1>

<?php $categories = $data['categories'] ?? []; ?>

<?php if (!$categories): ?>
    <p>No categories available yet.</p>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($categories as $category): ?>
            <div class="product-card">
                <h3 class="product-title">
                    <?= htmlspecialchars((string)$category['name']) ?>
                </h3>

                <p class="product-desc">
                    Slug: <?= htmlspecialchars((string)$category['slug']) ?>
                </p>

                <p class="product-price">
                    Products: <?= (int)$category['product_count'] ?>
                </p>

                <a class="add-cart-btn"
                   href="<?= URLROOT ?>/categories/<?= htmlspecialchars((string)$category['slug']) ?>"
                   style="display:inline-block; text-align:center; text-decoration:none;">
                    View Category
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>