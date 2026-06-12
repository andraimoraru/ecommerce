<?php $products = $data['products'] ?? []; ?>
<?php $pagination = $data['pagination'] ?? ['page' => 1, 'total_pages' => 1]; ?>
<?php $searchQuery = trim((string)($data['search_query'] ?? '')); ?>
<?php $currentPage = (int)($pagination['page'] ?? 1); ?>
<?php $totalPages = (int)($pagination['total_pages'] ?? 1); ?>
<?php $searchSuffix = $searchQuery !== '' ? '&q=' . rawurlencode($searchQuery) : ''; ?>

<section class="product-search-panel" id="productSearch">
    <div>
        <p class="eyebrow">Search the collection</p>
        <h1 class="page-title"><?= $searchQuery !== '' ? 'Search results' : 'Products' ?></h1>
        <?php if ($searchQuery !== ''): ?>
            <p class="section-copy">
                Showing products matching "<?= htmlspecialchars($searchQuery) ?>".
            </p>
        <?php else: ?>
            <p class="section-copy">
                Browse all available pieces or search by name, SKU or description.
            </p>
        <?php endif; ?>
    </div>

    <form method="get" action="<?= URLROOT ?>/products" class="product-search-form" role="search">
        <label for="productSearchInput" class="sr-only">Search products</label>
        <div class="product-search-input-wrap">
            <input
                id="productSearchInput"
                type="search"
                name="q"
                value="<?= htmlspecialchars($searchQuery) ?>"
                placeholder="Search by name, SKU or style"
                class="product-search-input"
            >
            <?php if ($searchQuery !== ''): ?>
                <a href="<?= URLROOT ?>/products" class="product-search-reset" aria-label="Clear search for <?= htmlspecialchars($searchQuery) ?>">
                    &times;
                </a>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn product-search-button">Search</button>
    </form>
</section>

<?php if (!$products): ?>
    <?php if ($searchQuery !== ''): ?>
        <div class="cart-card">
            <h2>No matching products found</h2>
            <p>Try a different product name, SKU, category style or a shorter search term.</p>
            <a href="<?= URLROOT ?>/products" class="btn secondary">View all products</a>
        </div>
    <?php else: ?>
        <p>No products available.</p>
    <?php endif; ?>
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
                <a class="btn secondary" href="<?= URLROOT ?>/products?page=<?= $currentPage - 1 ?><?= htmlspecialchars($searchSuffix) ?>">Previous</a>
            <?php endif; ?>
            <span>Page <?= $currentPage ?> of <?= $totalPages ?></span>
            <?php if ($currentPage < $totalPages): ?>
                <a class="btn secondary" href="<?= URLROOT ?>/products?page=<?= $currentPage + 1 ?><?= htmlspecialchars($searchSuffix) ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
