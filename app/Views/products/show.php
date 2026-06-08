<?php $product = $data['product'] ?? []; ?>
<?php $images = $data['images'] ?? []; ?>
<?php $availableQty = max(0, (int)($product['available_qty'] ?? 0)); ?>

<div class="product-page">
    <div class="product-page-grid">

        <!-- LEFT: IMAGES -->
        <div>
            <div class="product-main-image-wrap">
                <?php if (!empty($product['primary_image'])): ?>
                    <img
                        src="<?= htmlspecialchars((string)$product['primary_image']) ?>"
                        alt="<?= htmlspecialchars((string)$product['name']) ?>"
                        class="product-main-image"
                        data-product-main-image
                    >
                <?php else: ?>
                    <div class="product-placeholder">No image</div>
                <?php endif; ?>
            </div>

            <?php if ($images): ?>
                <div class="product-thumb-grid">
                    <?php foreach ($images as $img): ?>
                        <?php
                            $imageUrl = (string)$img['url'];
                            $imageAlt = (string)($img['alt_text'] ?? $product['name']);
                            $isActive = !empty($product['primary_image']) && $imageUrl === (string)$product['primary_image'];
                        ?>
                        <button
                            type="button"
                            class="product-thumb-wrap<?= $isActive ? ' is-active' : '' ?>"
                            data-product-thumb
                            data-image-src="<?= htmlspecialchars($imageUrl) ?>"
                            data-image-alt="<?= htmlspecialchars($imageAlt) ?>"
                            aria-label="View <?= htmlspecialchars($imageAlt) ?>"
                            aria-pressed="<?= $isActive ? 'true' : 'false' ?>"
                        >
                            <img
                                src="<?= htmlspecialchars($imageUrl) ?>"
                                alt="<?= htmlspecialchars($imageAlt) ?>"
                                class="product-thumb"
                            >
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: DETAILS -->
        <div>
            <h1 class="product-page-title">
                <?= htmlspecialchars((string)$product['name']) ?>
            </h1>

            <p class="product-page-price">
                <?= htmlspecialchars((string)($product['currency'] ?? 'GBP')) ?>
                <?= number_format(((int)($product['price_minor'] ?? 0)) / 100, 2) ?>
            </p>

            <?php if ($availableQty > 0): ?>
                <form method="post" action="<?= URLROOT ?>/cart/items" class="product-add-form">
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

                    <label for="quantity">Quantity</label><br>
                    <input
                        id="quantity"
                        type="number"
                        name="quantity"
                        min="1"
                        max="<?= $availableQty ?>"
                        value="1"
                        class="qty-input"
                    >
                    <p class="product-stock-note">In stock: <?= $availableQty ?></p>

                    <button type="submit" class="add-cart-btn">Add to cart</button>
                </form>
            <?php else: ?>
                <p class="product-stock-note product-stock-note--sold-out">Out of stock</p>
            <?php endif; ?>
            <br>
            <?php if (!empty($product['description_html'])): ?>
                <div class="product-html-card">
                    <h2>Product Details</h2>
                    <div class="product-html-content">
                        <?= $product['description_html'] ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
