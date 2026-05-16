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
                            <h3 style="margin:0 0 8px 0;">
                                <?= htmlspecialchars((string)$item['name']) ?>
                            </h3>

                            <p style="margin:0 0 8px 0;">
                                Qty: <?= (int)$item['qty'] ?>
                            </p>

                            <p style="margin:0;">
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
                        <input name="shipping_first_name" value="<?= htmlspecialchars((string)($old['shipping_first_name'] ?? '')) ?>" style="width:100%;">
                        <?php if (!empty($errors['shipping_first_name'])): ?><p style="color:red;"><?= htmlspecialchars($errors['shipping_first_name']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Last Name</label><br>
                        <input name="shipping_last_name" value="<?= htmlspecialchars((string)($old['shipping_last_name'] ?? '')) ?>" style="width:100%;">
                        <?php if (!empty($errors['shipping_last_name'])): ?><p style="color:red;"><?= htmlspecialchars($errors['shipping_last_name']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Email</label><br>
                        <input name="shipping_email" value="<?= htmlspecialchars((string)($old['shipping_email'] ?? '')) ?>" style="width:100%;">
                        <?php if (!empty($errors['shipping_email'])): ?><p style="color:red;"><?= htmlspecialchars($errors['shipping_email']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Phone</label><br>
                        <input name="shipping_phone" value="<?= htmlspecialchars((string)($old['shipping_phone'] ?? '')) ?>" style="width:100%;">
                    </div>

                    <div style="grid-column:1 / -1;">
                        <label>Address line 1</label><br>
                        <input name="shipping_line1" value="<?= htmlspecialchars((string)($old['shipping_line1'] ?? '')) ?>" style="width:100%;">
                        <?php if (!empty($errors['shipping_line1'])): ?><p style="color:red;"><?= htmlspecialchars($errors['shipping_line1']) ?></p><?php endif; ?>
                    </div>

                    <div style="grid-column:1 / -1;">
                        <label>Address line 2</label><br>
                        <input name="shipping_line2" value="<?= htmlspecialchars((string)($old['shipping_line2'] ?? '')) ?>" style="width:100%;">
                    </div>

                    <div>
                        <label>City</label><br>
                        <input name="shipping_city" value="<?= htmlspecialchars((string)($old['shipping_city'] ?? '')) ?>" style="width:100%;">
                        <?php if (!empty($errors['shipping_city'])): ?><p style="color:red;"><?= htmlspecialchars($errors['shipping_city']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Region</label><br>
                        <input name="shipping_region" value="<?= htmlspecialchars((string)($old['shipping_region'] ?? '')) ?>" style="width:100%;">
                    </div>

                    <div>
                        <label>Postcode</label><br>
                        <input name="shipping_postcode" value="<?= htmlspecialchars((string)($old['shipping_postcode'] ?? '')) ?>" style="width:100%;">
                        <?php if (!empty($errors['shipping_postcode'])): ?><p style="color:red;"><?= htmlspecialchars($errors['shipping_postcode']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Country</label><br>
                        <input name="shipping_country" value="<?= htmlspecialchars((string)($old['shipping_country'] ?? '')) ?>" style="width:100%;">
                        <?php if (!empty($errors['shipping_country'])): ?><p style="color:red;"><?= htmlspecialchars($errors['shipping_country']) ?></p><?php endif; ?>
                    </div>

                </div>

                <div style="margin-top:16px;">
                    <label>
                        <input
                            type="checkbox"
                            name="billing_same_as_shipping"
                            value="1"
                            <?= !empty($old['billing_same_as_shipping']) ? 'checked' : '' ?>
                            onchange="toggleBilling()"
                        >
                        Billing address same as shipping
                    </label>
                </div>

                <?php if (!empty($_SESSION['user_id'])): ?>
                    <div style="margin-top:10px;">
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

            <div class="cart-card" id="billingCard">
                <h2>Billing Address</h2>

                <div class="checkout-grid">

                    <div>
                        <label>First Name</label><br>
                        <input name="billing_first_name" value="<?= htmlspecialchars((string)($old['billing_first_name'] ?? '')) ?>" style="width:100%;">
                        <?php if (!empty($errors['billing_first_name'])): ?><p style="color:red;"><?= htmlspecialchars($errors['billing_first_name']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Last Name</label><br>
                        <input name="billing_last_name" value="<?= htmlspecialchars((string)($old['billing_last_name'] ?? '')) ?>" style="width:100%;">
                        <?php if (!empty($errors['billing_last_name'])): ?><p style="color:red;"><?= htmlspecialchars($errors['billing_last_name']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Email</label><br>
                        <input name="billing_email" value="<?= htmlspecialchars((string)($old['billing_email'] ?? '')) ?>" style="width:100%;">
                        <?php if (!empty($errors['billing_email'])): ?><p style="color:red;"><?= htmlspecialchars($errors['billing_email']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Phone</label><br>
                        <input name="billing_phone" value="<?= htmlspecialchars((string)($old['billing_phone'] ?? '')) ?>" style="width:100%;">
                    </div>

                    <div style="grid-column:1 / -1;">
                        <label>Address line 1</label><br>
                        <input name="billing_line1" value="<?= htmlspecialchars((string)($old['billing_line1'] ?? '')) ?>" style="width:100%;">
                        <?php if (!empty($errors['billing_line1'])): ?><p style="color:red;"><?= htmlspecialchars($errors['billing_line1']) ?></p><?php endif; ?>
                    </div>

                    <div style="grid-column:1 / -1;">
                        <label>Address line 2</label><br>
                        <input name="billing_line2" value="<?= htmlspecialchars((string)($old['billing_line2'] ?? '')) ?>" style="width:100%;">
                    </div>

                    <div>
                        <label>City</label><br>
                        <input name="billing_city" value="<?= htmlspecialchars((string)($old['billing_city'] ?? '')) ?>" style="width:100%;">
                        <?php if (!empty($errors['billing_city'])): ?><p style="color:red;"><?= htmlspecialchars($errors['billing_city']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Region</label><br>
                        <input name="billing_region" value="<?= htmlspecialchars((string)($old['billing_region'] ?? '')) ?>" style="width:100%;">
                    </div>

                    <div>
                        <label>Postcode</label><br>
                        <input name="billing_postcode" value="<?= htmlspecialchars((string)($old['billing_postcode'] ?? '')) ?>" style="width:100%;">
                        <?php if (!empty($errors['billing_postcode'])): ?><p style="color:red;"><?= htmlspecialchars($errors['billing_postcode']) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label>Country</label><br>
                        <input name="billing_country" value="<?= htmlspecialchars((string)($old['billing_country'] ?? '')) ?>" style="width:100%;">
                        <?php if (!empty($errors['billing_country'])): ?><p style="color:red;"><?= htmlspecialchars($errors['billing_country']) ?></p><?php endif; ?>
                    </div>

                </div>
            </div>

        </div>

        <div class="cart-sidebar">
            <div class="cart-card">
                <h2>Summary</h2>

                <?php if (!empty($errors['payment'])): ?>
                    <p style="color:red;"><?= htmlspecialchars($errors['payment']) ?></p>
                <?php endif; ?>

                <?php if (!$stripeConfigured): ?>
                    <p style="color:#9a6700;">Stripe is not configured yet. Add your Stripe keys to the `.env` file before taking payments.</p>
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

                <p style="font-size:18px;">
                    Total:
                    <strong>GBP <?= number_format($totalMinor / 100, 2) ?></strong>
                </p>

                <button class="add-cart-btn" type="submit">Continue to secure payment</button>
            </div>
        </div>

    </div>
</form>

<script>
function toggleBilling() {
    const box = document.querySelector('[name="billing_same_as_shipping"]');
    const billing = document.getElementById('billingCard');
    if (!box || !billing) return;
    billing.style.display = box.checked ? 'none' : 'block';
}

document.addEventListener('DOMContentLoaded', toggleBilling);
</script>

<?php endif; ?>
