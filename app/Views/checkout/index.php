<h1><?= htmlspecialchars($data['title'] ?? 'Checkout') ?></h1>

<?php $cart = $data['cart'] ?? ['items' => [], 'total_minor' => 0]; ?>
<?php $items = $cart['items'] ?? []; ?>
<?php $totalMinor = (int)($cart['total_minor'] ?? 0); ?>
<?php $old = $data['old'] ?? []; ?>
<?php $errors = $data['errors'] ?? []; ?>
<?php $stripeConfigured = !empty($data['stripe_configured']); ?>

<?php if (!$items): ?>
    <p>Your cart is empty.</p>
    <p><a href="<?= URLROOT ?>/products">Continue shopping</a></p>
<?php else: ?>

<form method="post" action="<?= URLROOT ?>/checkout">

    <div class="cart-layout">

        <div class="cart-main">

            <div class="cart-card">
                <h2>Order Summary</h2>

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
                                <?= htmlspecialchars((string)$item['name']) ?>
                            </h3>

                            <p class="item-copy">
                                Qty: <?= (int)$item['qty'] ?>
                            </p>

                            <p class="item-copy">
                                <strong>
                                    <?= htmlspecialchars((string)($item['currency'] ?? 'GBP')) ?>
                                    <?= number_format(((int)$item['line_total_minor']) / 100, 2) ?>
                                </strong>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-card">
                <h2>Shipping Address</h2>

                <div class="checkout-grid">

                    <div>
                        <label>First Name</label><br>
                        <input name="shipping_first_name" value="<?= htmlspecialchars((string)($old['shipping_first_name'] ?? '')) ?>">
                        <?php if (!empty($errors['shipping_first_name'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_first_name']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Last Name</label><br>
                        <input name="shipping_last_name" value="<?= htmlspecialchars((string)($old['shipping_last_name'] ?? '')) ?>">
                        <?php if (!empty($errors['shipping_last_name'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_last_name']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Email</label><br>
                        <input name="shipping_email" value="<?= htmlspecialchars((string)($old['shipping_email'] ?? '')) ?>">
                        <?php if (!empty($errors['shipping_email'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_email']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Phone</label><br>
                        <input name="shipping_phone" value="<?= htmlspecialchars((string)($old['shipping_phone'] ?? '')) ?>">
                    </div>

                    <div class="checkout-field-full">
                        <label>Address line 1</label><br>
                        <input name="shipping_line1" value="<?= htmlspecialchars((string)($old['shipping_line1'] ?? '')) ?>">
                        <?php if (!empty($errors['shipping_line1'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_line1']) ?></p><?php endif; ?>
                    </div>

                    <div class="checkout-field-full">
                        <label>Address line 2</label><br>
                        <input name="shipping_line2" value="<?= htmlspecialchars((string)($old['shipping_line2'] ?? '')) ?>">
                    </div>

                    <div>
                        <label>City</label><br>
                        <input name="shipping_city" value="<?= htmlspecialchars((string)($old['shipping_city'] ?? '')) ?>">
                        <?php if (!empty($errors['shipping_city'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_city']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Region</label><br>
                        <input name="shipping_region" value="<?= htmlspecialchars((string)($old['shipping_region'] ?? '')) ?>">
                    </div>

                    <div>
                        <label>Postcode</label><br>
                        <input name="shipping_postcode" value="<?= htmlspecialchars((string)($old['shipping_postcode'] ?? '')) ?>">
                        <?php if (!empty($errors['shipping_postcode'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_postcode']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Country</label><br>
                        <input name="shipping_country" value="<?= htmlspecialchars((string)($old['shipping_country'] ?? '')) ?>">
                        <?php if (!empty($errors['shipping_country'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_country']) ?></p><?php endif; ?>
                    </div>

                </div>

                <div class="stack-md">
                    <label>
                        <input
                            type="checkbox"
                            name="billing_same_as_shipping"
                            value="1"
                            <?= !empty($old['billing_same_as_shipping']) ? 'checked' : '' ?>
                            data-billing-toggle
                        >
                        Billing address same as shipping
                    </label>
                </div>

                <?php if (!empty($_SESSION['user_id'])): ?>
                    <div class="stack-sm">
                        <label>
                            <input
                                type="checkbox"
                                name="save_address"
                                value="1"
                                <?= !empty($old['save_address']) ? 'checked' : '' ?>
                            >
                            Save this address for future orders
                        </label>
                    </div>
                <?php endif; ?>
            </div>

            <div class="cart-card" id="billingCard" data-billing-card>
                <h2>Billing Address</h2>

                <div class="checkout-grid">

                    <div>
                        <label>First Name</label><br>
                        <input name="billing_first_name" value="<?= htmlspecialchars((string)($old['billing_first_name'] ?? '')) ?>">
                        <?php if (!empty($errors['billing_first_name'])): ?><p class="text-danger"><?= htmlspecialchars($errors['billing_first_name']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Last Name</label><br>
                        <input name="billing_last_name" value="<?= htmlspecialchars((string)($old['billing_last_name'] ?? '')) ?>">
                        <?php if (!empty($errors['billing_last_name'])): ?><p class="text-danger"><?= htmlspecialchars($errors['billing_last_name']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Email</label><br>
                        <input name="billing_email" value="<?= htmlspecialchars((string)($old['billing_email'] ?? '')) ?>">
                        <?php if (!empty($errors['billing_email'])): ?><p class="text-danger"><?= htmlspecialchars($errors['billing_email']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Phone</label><br>
                        <input name="billing_phone" value="<?= htmlspecialchars((string)($old['billing_phone'] ?? '')) ?>">
                    </div>

                    <div class="checkout-field-full">
                        <label>Address line 1</label><br>
                        <input name="billing_line1" value="<?= htmlspecialchars((string)($old['billing_line1'] ?? '')) ?>">
                        <?php if (!empty($errors['billing_line1'])): ?><p class="text-danger"><?= htmlspecialchars($errors['billing_line1']) ?></p><?php endif; ?>
                    </div>

                    <div class="checkout-field-full">
                        <label>Address line 2</label><br>
                        <input name="billing_line2" value="<?= htmlspecialchars((string)($old['billing_line2'] ?? '')) ?>">
                    </div>

                    <div>
                        <label>City</label><br>
                        <input name="billing_city" value="<?= htmlspecialchars((string)($old['billing_city'] ?? '')) ?>">
                        <?php if (!empty($errors['billing_city'])): ?><p class="text-danger"><?= htmlspecialchars($errors['billing_city']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Region</label><br>
                        <input name="billing_region" value="<?= htmlspecialchars((string)($old['billing_region'] ?? '')) ?>">
                    </div>

                    <div>
                        <label>Postcode</label><br>
                        <input name="billing_postcode" value="<?= htmlspecialchars((string)($old['billing_postcode'] ?? '')) ?>">
                        <?php if (!empty($errors['billing_postcode'])): ?><p class="text-danger"><?= htmlspecialchars($errors['billing_postcode']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Country</label><br>
                        <input name="billing_country" value="<?= htmlspecialchars((string)($old['billing_country'] ?? '')) ?>">
                        <?php if (!empty($errors['billing_country'])): ?><p class="text-danger"><?= htmlspecialchars($errors['billing_country']) ?></p><?php endif; ?>
                    </div>

                </div>
            </div>

        </div>

        <div class="cart-sidebar">
            <div class="cart-card">
                <h2>Summary</h2>

                <?php if (!empty($errors['payment'])): ?>
                    <p class="text-danger"><?= htmlspecialchars($errors['payment']) ?></p>
                <?php endif; ?>

                <?php if (!$stripeConfigured): ?>
                    <p class="text-warning">Stripe is not configured yet. Add your Stripe keys to the `.env` file before taking payments.</p>
                <?php endif; ?>

                <p>
                    Subtotal:
                    <strong>GBP <?= number_format($totalMinor / 100, 2) ?></strong>
                </p>

                <p>
                    Shipping:
                    <strong>GBP 0.00</strong>
                </p>

                <hr>

                <p class="summary-total">
                    Total:
                    <strong>GBP <?= number_format($totalMinor / 100, 2) ?></strong>
                </p>

                <button class="add-cart-btn" type="submit">Continue to secure payment</button>
            </div>
        </div>

    </div>
</form>

<?php endif; ?>
