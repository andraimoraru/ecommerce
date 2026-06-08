<h1><?= htmlspecialchars($data['title'] ?? 'Your Cart') ?></h1>

<?php $cart = $data['cart'] ?? ['items' => [], 'total_minor' => 0]; ?>
<?php $items = $cart['items'] ?? []; ?>
<?php $totalMinor = (int)($cart['total_minor'] ?? 0); ?>

<?php if (!$items): ?>
    <p>Your cart is empty.</p>
    <p><a href="<?= URLROOT ?>/products">Continue shopping</a></p>
<?php else: ?>

<div class="cart-layout">

    <div class="cart-main">

        <div class="cart-card">
            <h2>Your Items</h2>

            <?php foreach ($items as $item): ?>
                <div class="cart-item">

                    <div class="cart-item-image-wrap">
                        <?php if (!empty($item['primary_image'])): ?>
                            <img
                                src="<?= htmlspecialchars((string)$item['primary_image']) ?>"
                                alt="<?= htmlspecialchars((string)$item['name']) ?>"
                                class="cart-item-image"
                            >
                        <?php else: ?>
                            <div class="cart-item-placeholder">No image</div>
                        <?php endif; ?>
                    </div>

                    <div class="cart-item-details">
                        <h3 class="item-title">
                            <a href="<?= URLROOT ?>/products/<?= htmlspecialchars((string)$item['slug']) ?>" class="link-reset">
                                <?= htmlspecialchars((string)$item['name']) ?>
                            </a>
                        </h3>

                        <p class="item-copy">
                            Unit price:
                            <strong>
                                <?= htmlspecialchars((string)($item['currency'] ?? 'GBP')) ?>
                                <?= number_format(((int)$item['price_minor']) / 100, 2) ?>
                            </strong>
                        </p>

                        <p class="item-copy item-copy--spaced">
                            Total:
                            <strong>
                                <?= htmlspecialchars((string)($item['currency'] ?? 'GBP')) ?>
                                <?= number_format(((int)$item['line_total_minor']) / 100, 2) ?>
                            </strong>
                        </p>

                        <div class="inline-actions">

                            <form method="post" action="<?= URLROOT ?>/cart/update">
                                <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                                <label>Qty</label><br>
                                <input
                                    type="number"
                                    name="quantity"
                                    min="1"
                                    max="<?= max(1, (int)($item['available_qty'] ?? 1)) ?>"
                                    value="<?= (int)$item['qty'] ?>"
                                    class="qty-input qty-input--small"
                                >
                                <small class="item-copy">In stock: <?= (int)($item['available_qty'] ?? 0) ?></small><br>
                                <button type="submit" class="small-btn">Update</button>
                            </form>

                            <form method="post" action="<?= URLROOT ?>/cart/remove" data-confirm="This item will be removed from your cart. You can add it again later if you change your mind.">
                                <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                                <button type="submit" class="small-btn danger-btn" aria-label="Remove <?= htmlspecialchars((string)$item['name']) ?> from your cart">Remove</button>
                            </form>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <div class="cart-sidebar">
        <div class="cart-card">
            <h2>Summary</h2>

            <p>
                Subtotal:
                <strong>GBP <?= number_format($totalMinor / 100, 2) ?></strong>
            </p>

            <p>
                Shipping:
                <strong>Calculated at checkout</strong>
            </p>

            <hr>

            <p class="summary-total">
                Total:
                <strong>GBP <?= number_format($totalMinor / 100, 2) ?></strong>
            </p>

            <a class="add-cart-btn button-link button-link--block" href="<?= URLROOT ?>/checkout">
                Proceed to Checkout
            </a>
        </div>
    </div>

</div>

<?php endif; ?>
