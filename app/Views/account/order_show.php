<?php $order = $data['order'] ?? null; ?>
<?php $items = $data['items'] ?? []; ?>
<?php $shipping = $data['shipping_address'] ?? null; ?>
<?php $billing = $data['billing_address'] ?? null; ?>

<div class="account-layout">
    <aside class="account-sidebar">
        <h2 class="account-sidebar__title">My Account</h2>
        <nav class="account-sidebar__nav">
            <a href="<?= URLROOT ?>/account" class="account-sidebar__link">Overview</a>
            <a href="<?= URLROOT ?>/account/orders" class="account-sidebar__link is-active">Orders</a>
            <a href="<?= URLROOT ?>/products" class="account-sidebar__link">Continue shopping</a>
            <form action="<?= URLROOT ?>/logout" method="POST" class="account-sidebar__form">
                <button type="submit" class="account-sidebar__button">Logout</button>
            </form>
        </nav>
    </aside>

    <section class="account-panel">
        <p class="account-back-row">
            <a href="<?= URLROOT ?>/account/orders">Back to orders</a>
        </p>

        <?php if (!$order): ?>
            <h1 class="page-title">Order not found</h1>
            <div class="account-panel__card">
                <p>We could not find that order in your account.</p>
            </div>
        <?php else: ?>
            <h1 class="page-title">Order <?= htmlspecialchars((string)$order['order_number']) ?></h1>
            <p class="account-panel__lead">
                Placed <?= htmlspecialchars((string)($order['placed_at'] ?? $order['created_at'] ?? '')) ?>
                · Status: <?= htmlspecialchars((string)$order['status']) ?>
            </p>

            <div class="account-detail-grid">
                <div class="account-panel__card">
                    <h2 class="account-section-title">Items</h2>

                    <?php if (!$items): ?>
                        <p>No items were found for this order.</p>
                    <?php else: ?>
                        <table class="account-order-table account-order-table--items">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="account-order-product">
                                                <?php if (!empty($item['primary_image'])): ?>
                                                    <img src="<?= htmlspecialchars((string)$item['primary_image']) ?>" alt="">
                                                <?php else: ?>
                                                    <span class="account-order-product__placeholder">No image</span>
                                                <?php endif; ?>
                                                <span><?= htmlspecialchars((string)$item['product_name']) ?></span>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars((string)($item['sku'] ?? '')) ?></td>
                                        <td><?= (int)$item['quantity'] ?></td>
                                        <td>
                                            <?= htmlspecialchars((string)$order['currency']) ?>
                                            <?= number_format(((int)$item['unit_price_minor']) / 100, 2) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars((string)$order['currency']) ?>
                                            <?= number_format(((int)$item['line_total_minor']) / 100, 2) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="account-panel__card">
                    <h2 class="account-section-title">Totals</h2>
                    <p><strong>Subtotal:</strong> <?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['subtotal_minor']) / 100, 2) ?></p>
                    <p><strong>Shipping:</strong> <?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['shipping_minor']) / 100, 2) ?></p>
                    <p><strong>Tax:</strong> <?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['tax_minor']) / 100, 2) ?></p>
                    <p><strong>Discount:</strong> -<?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['discount_minor']) / 100, 2) ?></p>
                    <hr>
                    <p class="summary-total"><strong>Total:</strong> <?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['total_minor']) / 100, 2) ?></p>
                </div>

                <div class="account-panel__card">
                    <h2 class="account-section-title">Shipping Address</h2>
                    <?php if (!$shipping): ?>
                        <p>No shipping address is stored for this order.</p>
                    <?php else: ?>
                        <p><?= htmlspecialchars((string)$shipping['first_name']) ?> <?= htmlspecialchars((string)$shipping['last_name']) ?></p>
                        <p><?= htmlspecialchars((string)$shipping['line1']) ?></p>
                        <?php if (!empty($shipping['line2'])): ?><p><?= htmlspecialchars((string)$shipping['line2']) ?></p><?php endif; ?>
                        <p><?= htmlspecialchars((string)$shipping['city']) ?><?= !empty($shipping['region']) ? ', ' . htmlspecialchars((string)$shipping['region']) : '' ?></p>
                        <p><?= htmlspecialchars((string)$shipping['postcode']) ?></p>
                        <p><?= htmlspecialchars((string)$shipping['country_name']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="account-panel__card">
                    <h2 class="account-section-title">Billing Address</h2>
                    <?php if (!$billing): ?>
                        <p>No billing address is stored for this order.</p>
                    <?php else: ?>
                        <p><?= htmlspecialchars((string)$billing['first_name']) ?> <?= htmlspecialchars((string)$billing['last_name']) ?></p>
                        <p><?= htmlspecialchars((string)$billing['line1']) ?></p>
                        <?php if (!empty($billing['line2'])): ?><p><?= htmlspecialchars((string)$billing['line2']) ?></p><?php endif; ?>
                        <p><?= htmlspecialchars((string)$billing['city']) ?><?= !empty($billing['region']) ? ', ' . htmlspecialchars((string)$billing['region']) : '' ?></p>
                        <p><?= htmlspecialchars((string)$billing['postcode']) ?></p>
                        <p><?= htmlspecialchars((string)$billing['country_name']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>
