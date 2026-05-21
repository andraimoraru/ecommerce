<?php $order = $data['order'] ?? null; ?>
<?php $items = $data['items'] ?? []; ?>
<?php $shipping = $data['shipping_address'] ?? null; ?>
<?php $billing = $data['billing_address'] ?? null; ?>
<?php $allowedStatuses = $data['allowed_statuses'] ?? []; ?>
<?php $shipment = $data['shipment'] ?? null; ?>
<?php $shippingDefaults = $data['shipping_defaults'] ?? []; ?>
<?php $shippingSuccess = $data['shipping_success'] ?? ''; ?>
<?php $shippingErrors = $data['shipping_errors'] ?? []; ?>
<?php $shippingOld = $data['shipping_old'] ?? []; ?>

<?php if (!$order): ?>
    <p>Order not found.</p>
<?php else: ?>
    <p class="admin-back-row admin-back-row--actions">
        <a href="<?= URLROOT ?>/admin/orders">← Back to orders</a>
        <a class="btn secondary" href="<?= URLROOT ?>/admin/orders/<?= (int)$order['id'] ?>/edit">Edit order</a>
    </p>

    <h1 class="admin-title-reset"><?= htmlspecialchars((string)$order['order_number']) ?></h1>

    <div class="grid-2">
        <div>
            <div class="card">
                <h2 class="admin-section-title">Order Summary</h2>

                <p><strong>Status:</strong> <?= htmlspecialchars((string)$order['status']) ?></p>
                <p><strong>Placed:</strong> <?= htmlspecialchars((string)($order['placed_at'] ?? '—')) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars((string)$order['customer_email']) ?></p>
                <p><strong>Customer:</strong> <?= htmlspecialchars(trim((string)$order['customer_first_name'] . ' ' . (string)$order['customer_last_name'])) ?></p>

                <?php if (!empty($order['customer_phone'])): ?>
                    <p><strong>Phone:</strong> <?= htmlspecialchars((string)$order['customer_phone']) ?></p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2 class="admin-section-title">Items</h2>

                <?php if (!$items): ?>
                    <p>No items found for this order.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($item['primary_image'])): ?>
                                            <img
                                                src="<?= htmlspecialchars((string)$item['primary_image']) ?>"
                                                alt=""
                                                class="admin-thumb admin-thumb-placeholder--small"
                                            >
                                        <?php else: ?>
                                            <div class="admin-thumb-placeholder admin-thumb-placeholder--small">
                                                No image
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars((string)$item['product_name']) ?></td>
                                    <td><?= htmlspecialchars((string)($item['sku'] ?? '—')) ?></td>
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
        </div>

        <div>
            <div class="card">
                <h2 class="admin-section-title">Update Status</h2>

                <form method="post" action="<?= URLROOT ?>/admin/orders/<?= (int)$order['id'] ?>/status">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="admin-field">
                        <?php foreach ($allowedStatuses as $status): ?>
                            <option value="<?= htmlspecialchars((string)$status) ?>" <?= ($status === ($order['status'] ?? '')) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)$status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button class="btn" type="submit">Save Status</button>
                </form>
            </div>

            <div class="card">
                <h2 class="admin-section-title">Totals</h2>
                <p><strong>Subtotal:</strong> <?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['subtotal_minor']) / 100, 2) ?></p>
                <p><strong>Shipping:</strong> <?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['shipping_minor']) / 100, 2) ?></p>
                <p><strong>Tax:</strong> <?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['tax_minor']) / 100, 2) ?></p>
                <p><strong>Discount:</strong> -<?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['discount_minor']) / 100, 2) ?></p>
                <hr>
                <p class="summary-total"><strong>Total:</strong> <?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['total_minor']) / 100, 2) ?></p>
            </div>

            <div class="card">
                <h2 class="admin-section-title">Royal Mail Label</h2>

                <?php if ($shippingSuccess !== ''): ?>
                    <p class="flash-success"><?= htmlspecialchars((string)$shippingSuccess) ?></p>
                <?php endif; ?>

                <?php if (!empty($shippingErrors['shipping'])): ?>
                    <p class="text-danger"><?= htmlspecialchars((string)$shippingErrors['shipping']) ?></p>
                <?php endif; ?>

                <?php if (!empty($shipment)): ?>
                    <p><strong>Status:</strong> <?= htmlspecialchars((string)($shipment['status'] ?? '—')) ?></p>
                    <p><strong>Service:</strong> <?= htmlspecialchars((string)($shipment['service_code'] ?? '—')) ?></p>
                    <?php if (!empty($shipment['royal_mail_shipment_id'])): ?>
                        <p><strong>Click & Drop Ref:</strong> <?= htmlspecialchars((string)$shipment['royal_mail_shipment_id']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($shipment['tracking_number'])): ?>
                        <p><strong>Tracking:</strong> <?= htmlspecialchars((string)$shipment['tracking_number']) ?></p>
                    <?php endif; ?>
                    <hr>
                <?php endif; ?>

                <?php if (empty($shippingDefaults['configured'])): ?>
                    <p>Royal Mail Click & Drop is not configured yet. Add `ROYAL_MAIL_CLICK_DROP_API_KEY` to your `.env` file first.</p>
                <?php else: ?>
                    <form method="post" action="<?= URLROOT ?>/admin/orders/<?= (int)$order['id'] ?>/shipping-label">
                        <div class="admin-field">
                            <label for="service_code">Service Code</label><br>
                            <input
                                id="service_code"
                                name="service_code"
                                value="<?= htmlspecialchars((string)($shippingOld['service_code'] ?? $shipment['service_code'] ?? $shippingDefaults['service_code'] ?? '')) ?>"
                            >
                            <?php if (!empty($shippingErrors['service_code'])): ?><p class="text-danger"><?= htmlspecialchars((string)$shippingErrors['service_code']) ?></p><?php endif; ?>
                        </div>

                        <div class="admin-field">
                            <label for="package_format_identifier">Package Format</label><br>
                            <input
                                id="package_format_identifier"
                                name="package_format_identifier"
                                value="<?= htmlspecialchars((string)($shippingOld['package_format_identifier'] ?? $shipment['package_format_identifier'] ?? $shippingDefaults['package_format_identifier'] ?? 'Parcel')) ?>"
                            >
                        </div>

                        <div class="admin-field">
                            <label for="weight_grams">Weight (grams)</label><br>
                            <input
                                id="weight_grams"
                                type="number"
                                min="1"
                                name="weight_grams"
                                value="<?= htmlspecialchars((string)($shippingOld['weight_grams'] ?? $shipment['weight_grams'] ?? $shippingDefaults['weight_grams'] ?? 1000)) ?>"
                            >
                            <?php if (!empty($shippingErrors['weight_grams'])): ?><p class="text-danger"><?= htmlspecialchars((string)$shippingErrors['weight_grams']) ?></p><?php endif; ?>
                        </div>

                        <p class="admin-note">This version creates the shipment in Click & Drop only. The label is managed in your Royal Mail account.</p>
                        <button class="btn" type="submit"><?= !empty($shipment) ? 'Recreate Shipment' : 'Create Shipment' ?></button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2 class="admin-section-title">Shipping Address</h2>
                <?php if (!$shipping): ?>
                    <p>No shipping address stored.</p>
                <?php else: ?>
                    <p><?= htmlspecialchars((string)$shipping['first_name']) ?> <?= htmlspecialchars((string)$shipping['last_name']) ?></p>
                    <p><?= htmlspecialchars((string)$shipping['line1']) ?></p>
                    <?php if (!empty($shipping['line2'])): ?><p><?= htmlspecialchars((string)$shipping['line2']) ?></p><?php endif; ?>
                    <p><?= htmlspecialchars((string)$shipping['city']) ?><?= !empty($shipping['region']) ? ', ' . htmlspecialchars((string)$shipping['region']) : '' ?></p>
                    <p><?= htmlspecialchars((string)$shipping['postcode']) ?></p>
                    <p><?= htmlspecialchars((string)$shipping['country_name']) ?></p>
                    <?php if (!empty($shipping['phone'])): ?><p><?= htmlspecialchars((string)$shipping['phone']) ?></p><?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2 class="admin-section-title">Billing Address</h2>
                <?php if (!$billing): ?>
                    <p>No billing address stored.</p>
                <?php else: ?>
                    <p><?= htmlspecialchars((string)$billing['first_name']) ?> <?= htmlspecialchars((string)$billing['last_name']) ?></p>
                    <p><?= htmlspecialchars((string)$billing['line1']) ?></p>
                    <?php if (!empty($billing['line2'])): ?><p><?= htmlspecialchars((string)$billing['line2']) ?></p><?php endif; ?>
                    <p><?= htmlspecialchars((string)$billing['city']) ?><?= !empty($billing['region']) ? ', ' . htmlspecialchars((string)$billing['region']) : '' ?></p>
                    <p><?= htmlspecialchars((string)$billing['postcode']) ?></p>
                    <p><?= htmlspecialchars((string)$billing['country_name']) ?></p>
                    <?php if (!empty($billing['phone'])): ?><p><?= htmlspecialchars((string)$billing['phone']) ?></p><?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
