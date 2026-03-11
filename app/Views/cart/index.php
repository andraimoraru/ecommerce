<h1><?= htmlspecialchars($data['title']) ?></h1>

<?php $items = $data['items'] ?? []; ?>
<?php $subtotalMinor = (int)($data['subtotal_minor'] ?? 0); ?>

<?php if (!$items): ?>
    <p>Your cart is empty.</p>
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

                            <p style="margin:0 0 10px 0;">
                                Unit price:
                                <?= htmlspecialchars((string)$item['currency']) ?>
                                <?= number_format(((int)$item['price_minor']) / 100, 2) ?>
                            </p>

                            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                                <form method="post" action="<?= URLROOT ?>/cart/update">
                                    <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                                    <label>Qty</label><br>
                                    <input type="number" name="quantity" min="1" value="<?= (int)$item['quantity'] ?>" style="width:80px;">
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

            <div class="cart-card">
                <h2>Shipping Address</h2>

                <div class="checkout-grid">
                    <div>
                        <label>First Name</label><br>
                        <input type="text" name="shipping_first_name" style="width:100%;">
                    </div>

                    <div>
                        <label>Last Name</label><br>
                        <input type="text" name="shipping_last_name" style="width:100%;">
                    </div>

                    <div>
                        <label>Email</label><br>
                        <input type="email" name="shipping_email" style="width:100%;">
                    </div>

                    <div>
                        <label>Phone</label><br>
                        <input type="text" name="shipping_phone" style="width:100%;">
                    </div>

                    <div style="grid-column:1 / -1;">
                        <label>Address Line 1</label><br>
                        <input type="text" name="shipping_line1" style="width:100%;">
                    </div>

                    <div style="grid-column:1 / -1;">
                        <label>Address Line 2</label><br>
                        <input type="text" name="shipping_line2" style="width:100%;">
                    </div>

                    <div>
                        <label>City</label><br>
                        <input type="text" name="shipping_city" style="width:100%;">
                    </div>

                    <div>
                        <label>Region / County</label><br>
                        <input type="text" name="shipping_region" style="width:100%;">
                    </div>

                    <div>
                        <label>Postcode</label><br>
                        <input type="text" name="shipping_postcode" style="width:100%;">
                    </div>

                    <div>
                        <label>Country</label><br>
                        <input type="text" name="shipping_country" value="GB" style="width:100%;">
                    </div>
                </div>
            </div>

    </div>
           <div class="cart-sidebar">
            <div class="cart-card">
                <h2>Summary</h2>

                <p>
                    Subtotal:
                    <strong>GBP <?= number_format($subtotalMinor / 100, 2) ?></strong>
                </p>

                <p>
                    Shipping:
                    <strong>To be calculated</strong>
                </p>

                <hr>

                <p style="font-size:18px;">
                    Total:
                    <strong>GBP <?= number_format($subtotalMinor / 100, 2) ?></strong>
                </p>

                <button class="add-cart-btn" type="button">
                    Continue to Payment
                </button>
            </div>
        </div>

<?php endif; ?>