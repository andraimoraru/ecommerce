<?php $category = $data['category'] ?? null; ?>
<?php $products = $data['products'] ?? []; ?>

<a class="category-back" href="<?= URLROOT ?>/categories">← Back to categories</a>

<div class="category-header">
    <h1 class="category-title">
        <?= htmlspecialchars((string)($category['name'] ?? 'Category')) ?>
    </h1>
    <p class="category-subtitle">
        Browse products in this category.
    </p>
</div>

<?php if (!$products): ?>
    <p>No active products found in this category yet.</p>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">

                <a href="<?= URLROOT ?>/products/<?= htmlspecialchars((string)$product['slug']) ?>" class="link-reset">
                    <div class="product-image-wrap">
                        <?php if (!empty($product['primary_image'])): ?>
                            <img
                                src="<?= htmlspecialchars((string)$product['primary_image']) ?>"
                                alt="<?= htmlspecialchars((string)$product['name']) ?>"
                                class="product-image"
                            >
                        <?php else: ?>
                            <div class="product-placeholder">No image</div>
                        <?php endif; ?>
                    </div>

                    <h3 class="product-title"><?= htmlspecialchars((string)$product['name']) ?></h3>
                </a>
                <p class="product-price">
                    <?= htmlspecialchars((string)($product['currency'] ?? 'GBP')) ?>
                    <?= number_format(((int)$product['price_minor']) / 100, 2) ?>
                </p>

                <form method="post" action="<?= URLROOT ?>/cart/items">
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="add-cart-btn">Add to cart</button>
                </form>

            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
