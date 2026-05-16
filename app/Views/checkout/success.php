<h1><?= htmlspecialchars($data['title'] ?? 'Order placed') ?></h1>

<?php $order = $data['order'] ?? null; ?>
<?php $items = $data['items'] ?? []; ?>

<?php if (!$order): ?>
    <p>Your order has been placed successfully.</p>
    <p><a href="<?= URLROOT ?>/products">Continue shopping</a></p>
<?php else: ?>

    <div class="cart-layout">

        <div class="cart-main">

            <div class="cart-card">
                <h2>Thank you for your order</h2>

                <p>
                    Your payment was successful and your order has been confirmed.
                </p>

                <p>
                    <strong>Order Number:</strong>
                    <?= htmlspecialchars((string)$order['order_number']) ?>
                </p>

                <p>
                    <strong>Status:</strong>
                    <?= htmlspecialchars((string)$order['status']) ?>
                </p>

                <p>
                    <strong>Placed at:</strong>
                    <?= htmlspecialchars((string)$order['placed_at']) ?>
                </p>

                <p>
                    <strong>Customer:</strong>
                    <?= htmlspecialchars((string)$order['customer_first_name']) ?>
                    <?= htmlspecialchars((string)$order['customer_last_name']) ?>
                </p>

                <p>
                    <strong>Email:</strong>
                    <?= htmlspecialchars((string)$order['customer_email']) ?>
                </p>

                <?php if (!empty($order['customer_phone'])): ?>
                    <p>
                        <strong>Phone:</strong>
                        <?= htmlspecialchars((string)$order['customer_phone']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="cart-card">
                <h2>Order Items</h2>

                <?php if (!$items): ?>
                    <p>No order items found.</p>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-details">
                                <h3 style="margin:0 0 8px 0;">
                                    <?= htmlspecialchars((string)$item['product_name']) ?>
                                </h3>

                                <?php if (!empty($item['sku'])): ?>
                                    <p style="margin:0 0 6px 0;">
                                        SKU: <?= htmlspecialchars((string)$item['sku']) ?>
                                    </p>
                                <?php endif; ?>

                                <p style="margin:0 0 6px 0;">
                                    Qty: <?= (int)$item['quantity'] ?>
                                </p>

                                <p style="margin:0 0 6px 0;">
                                    Unit price:
                                    <?= htmlspecialchars((string)($order['currency'] ?? 'GBP')) ?>
                                    <?= number_format(((int)$item['unit_price_minor']) / 100, 2) ?>
                                </p>

                                <p style="margin:0;">
                                    <strong>
                                        Line total:
                                        <?= htmlspecialchars((string)($order['currency'] ?? 'GBP')) ?>
                                        <?= number_format(((int)$item['line_total_minor']) / 100, 2) ?>
                                    </strong>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>

        <div class="cart-sidebar">
            <div class="cart-card">
                <h2>Order Total</h2>

                <p>
                    Subtotal:
                    <strong>
                        <?= htmlspecialchars((string)$order['currency']) ?>
                        <?= number_format(((int)$order['subtotal_minor']) / 100, 2) ?>
                    </strong>
                </p>

                <p>
                    Shipping:
                    <strong>
                        <?= htmlspecialchars((string)$order['currency']) ?>
                        <?= number_format(((int)$order['shipping_minor']) / 100, 2) ?>
                    </strong>
                </p>

                <p>
                    Tax:
                    <strong>
                        <?= htmlspecialchars((string)$order['currency']) ?>
                        <?= number_format(((int)$order['tax_minor']) / 100, 2) ?>
                    </strong>
                </p>

                <p>
                    Discount:
                    <strong>
                        -<?= htmlspecialchars((string)$order['currency']) ?>
                        <?= number_format(((int)$order['discount_minor']) / 100, 2) ?>
                    </strong>
                </p>

                <hr>

                <p style="font-size:18px;">
                    Total:
                    <strong>
                        <?= htmlspecialchars((string)$order['currency']) ?>
                        <?= number_format(((int)$order['total_minor']) / 100, 2) ?>
                    </strong>
                </p>

                <a class="add-cart-btn" href="<?= URLROOT ?>/products" style="display:block;text-align:center;text-decoration:none;">
                    Continue shopping
                </a>
            </div>
        </div>

    </div>

<?php endif; ?>
