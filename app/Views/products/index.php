<h1 class="page-title">Products</h1>

<?php $products = $data['products'] ?? []; ?>
<?php $pagination = $data['pagination'] ?? ['page' => 1, 'total_pages' => 1]; ?>
<?php $currentPage = (int)($pagination['page'] ?? 1); ?>
<?php $totalPages = (int)($pagination['total_pages'] ?? 1); ?>

<?php if (!$products): ?>
    <p>No products available.</p>
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

                    <input type="number" name="quantity" value="1" min="1">

                    <button class="add-cart-btn">
                        Add to Cart
                    </button>

                </form>

            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <?php if ($currentPage > 1): ?>
                <a class="btn secondary" href="<?= URLROOT ?>/products?page=<?= $currentPage - 1 ?>">Previous</a>
            <?php endif; ?>
            <span>Page <?= $currentPage ?> of <?= $totalPages ?></span>
            <?php if ($currentPage < $totalPages): ?>
                <a class="btn secondary" href="<?= URLROOT ?>/products?page=<?= $currentPage + 1 ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
