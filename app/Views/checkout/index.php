<h1><?= htmlspecialchars($data['title'] ?? 'Checkout') ?></h1>

<?php $cart = $data['cart'] ?? ['items' => [], 'total_minor' => 0]; ?>
<?php $items = $cart['items'] ?? []; ?>
<?php $totalMinor = (int)($cart['total_minor'] ?? 0); ?>
<?php $shippingMinor = (int)($data['shipping_minor'] ?? 0); ?>
<?php $grandTotalMinor = (int)($data['total_minor'] ?? ($totalMinor + $shippingMinor)); ?>
<?php $old = $data['old'] ?? []; ?>
<?php $errors = $data['errors'] ?? []; ?>
<?php $stripeConfigured = !empty($data['stripe_configured']); ?>
<?php $shippingCountry = trim((string)($old['shipping_country'] ?? '')); ?>
<?php $shippingKnown = $shippingCountry !== ''; ?>

<?php if (!$items): ?>
    <p>Your cart is empty.</p>
    <p><a href="<?= URLROOT ?>/products">Continue shopping</a></p>
<?php else: ?>

<form method="post" action="<?= URLROOT ?>/checkout">

    <div class="cart-layout">

        <div class="cart-main">

            <div class="cart-card checkout-summary-card">
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
                        <input name="shipping_first_name" autocomplete="shipping given-name" value="<?= htmlspecialchars((string)($old['shipping_first_name'] ?? '')) ?>">
                        <?php if (!empty($errors['shipping_first_name'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_first_name']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Last Name</label><br>
                        <input name="shipping_last_name" autocomplete="shipping family-name" value="<?= htmlspecialchars((string)($old['shipping_last_name'] ?? '')) ?>">
                        <?php if (!empty($errors['shipping_last_name'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_last_name']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Email</label><br>
                        <input type="email" name="shipping_email" autocomplete="shipping email" inputmode="email" value="<?= htmlspecialchars((string)($old['shipping_email'] ?? '')) ?>">
                        <?php if (!empty($errors['shipping_email'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_email']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Phone</label><br>
                        <input type="tel" name="shipping_phone" autocomplete="shipping tel" inputmode="tel" value="<?= htmlspecialchars((string)($old['shipping_phone'] ?? '')) ?>">
                    </div>

                    <div class="checkout-field-full">
                        <label>Address line 1</label><br>
                        <input name="shipping_line1" autocomplete="shipping address-line1" value="<?= htmlspecialchars((string)($old['shipping_line1'] ?? '')) ?>">
                        <?php if (!empty($errors['shipping_line1'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_line1']) ?></p><?php endif; ?>
                    </div>

                    <div class="checkout-field-full">
                        <label>Address line 2</label><br>
                        <input name="shipping_line2" autocomplete="shipping address-line2" value="<?= htmlspecialchars((string)($old['shipping_line2'] ?? '')) ?>">
                    </div>

                    <div>
                        <label>City</label><br>
                        <input name="shipping_city" autocomplete="shipping address-level2" value="<?= htmlspecialchars((string)($old['shipping_city'] ?? '')) ?>">
                        <?php if (!empty($errors['shipping_city'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_city']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Region</label><br>
                        <input name="shipping_region" autocomplete="shipping address-level1" value="<?= htmlspecialchars((string)($old['shipping_region'] ?? '')) ?>">
                    </div>

                    <div>
                        <label>Postcode</label><br>
                        <input name="shipping_postcode" autocomplete="shipping postal-code" value="<?= htmlspecialchars((string)($old['shipping_postcode'] ?? '')) ?>">
                        <?php if (!empty($errors['shipping_postcode'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_postcode']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Country</label><br>
                        <input
                            name="shipping_country"
                            autocomplete="shipping country-name"
                            value="<?= htmlspecialchars((string)($old['shipping_country'] ?? '')) ?>"
                            placeholder="United Kingdom"
                            data-shipping-country
                        >
                        <?php if (!empty($errors['shipping_country'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_country']) ?></p><?php endif; ?>
                        <p class="text-muted checkout-help">Enter your shipping country to confirm delivery cost before payment.</p>
                    </div>

                </div>

                <div class="stack-md">
                    <label class="checkout-option">
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
                        <label class="checkout-option">
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
                        <input name="billing_first_name" autocomplete="billing given-name" value="<?= htmlspecialchars((string)($old['billing_first_name'] ?? '')) ?>">
                        <?php if (!empty($errors['billing_first_name'])): ?><p class="text-danger"><?= htmlspecialchars($errors['billing_first_name']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Last Name</label><br>
                        <input name="billing_last_name" autocomplete="billing family-name" value="<?= htmlspecialchars((string)($old['billing_last_name'] ?? '')) ?>">
                        <?php if (!empty($errors['billing_last_name'])): ?><p class="text-danger"><?= htmlspecialchars($errors['billing_last_name']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Email</label><br>
                        <input type="email" name="billing_email" autocomplete="billing email" inputmode="email" value="<?= htmlspecialchars((string)($old['billing_email'] ?? '')) ?>">
                        <?php if (!empty($errors['billing_email'])): ?><p class="text-danger"><?= htmlspecialchars($errors['billing_email']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Phone</label><br>
                        <input type="tel" name="billing_phone" autocomplete="billing tel" inputmode="tel" value="<?= htmlspecialchars((string)($old['billing_phone'] ?? '')) ?>">
                    </div>

                    <div class="checkout-field-full">
                        <label>Address line 1</label><br>
                        <input name="billing_line1" autocomplete="billing address-line1" value="<?= htmlspecialchars((string)($old['billing_line1'] ?? '')) ?>">
                        <?php if (!empty($errors['billing_line1'])): ?><p class="text-danger"><?= htmlspecialchars($errors['billing_line1']) ?></p><?php endif; ?>
                    </div>

                    <div class="checkout-field-full">
                        <label>Address line 2</label><br>
                        <input name="billing_line2" autocomplete="billing address-line2" value="<?= htmlspecialchars((string)($old['billing_line2'] ?? '')) ?>">
                    </div>

                    <div>
                        <label>City</label><br>
                        <input name="billing_city" autocomplete="billing address-level2" value="<?= htmlspecialchars((string)($old['billing_city'] ?? '')) ?>">
                        <?php if (!empty($errors['billing_city'])): ?><p class="text-danger"><?= htmlspecialchars($errors['billing_city']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Region</label><br>
                        <input name="billing_region" autocomplete="billing address-level1" value="<?= htmlspecialchars((string)($old['billing_region'] ?? '')) ?>">
                    </div>

                    <div>
                        <label>Postcode</label><br>
                        <input name="billing_postcode" autocomplete="billing postal-code" value="<?= htmlspecialchars((string)($old['billing_postcode'] ?? '')) ?>">
                        <?php if (!empty($errors['billing_postcode'])): ?><p class="text-danger"><?= htmlspecialchars($errors['billing_postcode']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Country</label><br>
                        <input name="billing_country" autocomplete="billing country-name" value="<?= htmlspecialchars((string)($old['billing_country'] ?? '')) ?>">
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
                    <strong data-checkout-subtotal-minor="<?= $totalMinor ?>">GBP <?= number_format($totalMinor / 100, 2) ?></strong>
                </p>

                <p>
                    Shipping:
                    <strong data-checkout-shipping>
                        <?php if ($shippingKnown): ?>
                            GBP <?= number_format($shippingMinor / 100, 2) ?>
                        <?php else: ?>
                            Enter shipping country
                        <?php endif; ?>
                    </strong>
                </p>

                <p class="item-copy" data-checkout-shipping-note>
                    <?php if ($shippingKnown): ?>
                        <?= preg_match('/^(UK|UNITED KINGDOM|GB|GREAT BRITAIN)$/i', $shippingCountry) ? 'UK delivery rate applied.' : 'International delivery rate applied.' ?>
                    <?php else: ?>
                        UK delivery is GBP 2.99. International delivery is GBP 10.99.
                    <?php endif; ?>
                </p>

                <hr>

                <p class="summary-total">
                    Total:
                    <strong data-checkout-total>
                        GBP <?= number_format($grandTotalMinor / 100, 2) ?>
                    </strong>
                </p>

                <button class="add-cart-btn" type="submit">Continue to secure payment</button>
            </div>
        </div>

    </div>
</form>

<?php endif; ?>
