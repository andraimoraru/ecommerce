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
                        <h3 style="margin:0 0 8px 0;">
                            <a href="<?= URLROOT ?>/products/<?= htmlspecialchars((string)$item['slug']) ?>" style="text-decoration:none;color:inherit;">
                                <?= htmlspecialchars((string)$item['name']) ?>
                            </a>
                        </h3>

                        <p style="margin:0 0 8px 0;">
                            Unit price:
                            <strong>
                                <?= htmlspecialchars((string)($item['currency'] ?? 'GBP')) ?>
                                <?= number_format(((int)$item['price_minor']) / 100, 2) ?>
                            </strong>
                        </p>

                        <p style="margin:0 0 10px 0;">
                            Line total:
                            <strong>
                                <?= htmlspecialchars((string)($item['currency'] ?? 'GBP')) ?>
                                <?= number_format(((int)$item['line_total_minor']) / 100, 2) ?>
                            </strong>
                        </p>

                        <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">

                            <form method="post" action="<?= URLROOT ?>/cart/update">
                                <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                                <label>Qty</label><br>
                                <input
                                    type="number"
                                    name="quantity"
                                    min="1"
                                    value="<?= (int)$item['qty'] ?>"
                                    style="width:80px;"
                                >
                                <button type="submit" class="small-btn">Update</button>
                            </form>

                            <form method="post" action="<?= URLROOT ?>/cart/remove" onsubmit="return confirm('Remove this item from cart?');">
                                <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                                <button type="submit" class="small-btn danger-btn">Remove</button>
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

            <p style="font-size:18px;">
                Total:
                <strong>GBP <?= number_format($totalMinor / 100, 2) ?></strong>
            </p>

            <a class="add-cart-btn" href="<?= URLROOT ?>/checkout" style="display:block;text-align:center;text-decoration:none;">
                Proceed to Checkout
            </a>
        </div>
    </div>

</div>

<?php endif; ?>