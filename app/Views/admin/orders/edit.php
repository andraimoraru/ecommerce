<?php $order = $data['order'] ?? null; ?>
<?php $shipping = $data['shipping_address'] ?? null; ?>
<?php $items = $data['items'] ?? []; ?>
<?php $productOptions = $data['product_options'] ?? []; ?>
<?php $errors = $data['errors'] ?? []; ?>

<?php if (!$order || !$shipping): ?>
    <p>Order not found.</p>
<?php else: ?>
    <p class="admin-back-row">
        <a href="<?= URLROOT ?>/admin/orders/<?= (int)$order['id'] ?>">← Back to order</a>
    </p>

    <h1 class="admin-title-reset"><?= htmlspecialchars($data['title'] ?? 'Edit Order') ?></h1>

    <form method="post" action="<?= URLROOT ?>/admin/orders/<?= (int)$order['id'] ?>">
        <div data-order-editor-products="<?= htmlspecialchars((string)json_encode($productOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="grid-2">
            <div>
                <div class="card">
                    <h2 class="admin-section-title">Items</h2>

                    <p><strong>Order Number:</strong> <?= htmlspecialchars((string)$order['order_number']) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars((string)$order['status']) ?></p>
                    <p><strong>Currency:</strong> <?= htmlspecialchars((string)$order['currency']) ?></p>

                    <?php if (!empty($errors['items'])): ?>
                        <p class="text-danger"><?= htmlspecialchars($errors['items']) ?></p>
                    <?php endif; ?>

                    <?php if (!$items): ?>
                        <p>No items currently in this order.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product</th>
                                    <th>Unit Price</th>
                                    <th>Qty</th>
                                    <th>Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $index => $item): ?>
                                    <tr>
                                        <td>
                                            <?php $thumb = $item['primary_image'] ?? null; ?>
                                            <?php if (!empty($thumb)): ?>
                                                <img src="<?= htmlspecialchars((string)$thumb) ?>" alt="" class="admin-thumb admin-thumb-placeholder--small">
                                            <?php else: ?>
                                                <div class="admin-thumb-placeholder admin-thumb-placeholder--small">
                                                    No image
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars((string)$item['product_name']) ?></strong><br>
                                            <small class="admin-meta"><?= htmlspecialchars((string)($item['sku'] ?? '')) ?></small>
                                            <input type="hidden" name="existing_item_id[]" value="<?= (int)$item['id'] ?>">
                                        </td>
                                        <td>
                                            <?= htmlspecialchars((string)$order['currency']) ?>
                                            <?= number_format(((int)$item['unit_price_minor']) / 100, 2) ?>
                                        </td>
                                        <td>
                                            <input type="number" min="0" name="existing_quantity[]" value="<?= (int)$item['quantity'] ?>" class="admin-qty-input">
                                            <?php if (!empty($errors['item_' . $index])): ?><p class="text-danger"><?= htmlspecialchars($errors['item_' . $index]) ?></p><?php endif; ?>
                                        </td>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="remove_item[]" value="<?= (int)$item['id'] ?>">
                                                Remove
                                            </label>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h2 class="admin-section-title">Add Products</h2>

                    <div class="admin-field">
                        <label for="skuSearch">Search by SKU</label><br>
                        <input id="skuSearch" type="text" placeholder="Type SKU...">
                    </div>

                    <div id="skuSearchResults" class="admin-sku-results"></div>

                    <div id="pendingAdditions" class="admin-sku-pending"></div>
                </div>

                <div class="card">
                    <h2 class="admin-section-title">Shipping Address</h2>

                    <div class="checkout-grid">
                        <div>
                            <label>First Name</label><br>
                            <input name="shipping_first_name" value="<?= htmlspecialchars((string)$shipping['first_name']) ?>">
                            <?php if (!empty($errors['shipping_first_name'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_first_name']) ?></p><?php endif; ?>
                        </div>

                        <div>
                            <label>Last Name</label><br>
                            <input name="shipping_last_name" value="<?= htmlspecialchars((string)$shipping['last_name']) ?>">
                            <?php if (!empty($errors['shipping_last_name'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_last_name']) ?></p><?php endif; ?>
                        </div>

                        <div>
                            <label>Phone</label><br>
                            <input name="shipping_phone" value="<?= htmlspecialchars((string)($shipping['phone'] ?? '')) ?>">
                        </div>

                        <div class="checkout-field-full">
                            <label>Address line 1</label><br>
                            <input name="shipping_line1" value="<?= htmlspecialchars((string)$shipping['line1']) ?>">
                            <?php if (!empty($errors['shipping_line1'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_line1']) ?></p><?php endif; ?>
                        </div>

                        <div class="checkout-field-full">
                            <label>Address line 2</label><br>
                            <input name="shipping_line2" value="<?= htmlspecialchars((string)($shipping['line2'] ?? '')) ?>">
                        </div>

                        <div>
                            <label>City</label><br>
                            <input name="shipping_city" value="<?= htmlspecialchars((string)$shipping['city']) ?>">
                            <?php if (!empty($errors['shipping_city'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_city']) ?></p><?php endif; ?>
                        </div>

                        <div>
                            <label>Region</label><br>
                            <input name="shipping_region" value="<?= htmlspecialchars((string)($shipping['region'] ?? '')) ?>">
                        </div>

                        <div>
                            <label>Postcode</label><br>
                            <input name="shipping_postcode" value="<?= htmlspecialchars((string)$shipping['postcode']) ?>">
                            <?php if (!empty($errors['shipping_postcode'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_postcode']) ?></p><?php endif; ?>
                        </div>

                        <div>
                            <label>Country</label><br>
                            <input name="shipping_country_name" value="<?= htmlspecialchars((string)$shipping['country_name']) ?>">
                            <?php if (!empty($errors['shipping_country_name'])): ?><p class="text-danger"><?= htmlspecialchars($errors['shipping_country_name']) ?></p><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="card">
                    <h2 class="admin-section-title">Pricing</h2>

                    <div class="admin-field">
                        <label>Discount (%)</label><br>
                        <input type="number" min="0" max="100" step="0.01" name="discount_percent" value="<?= htmlspecialchars(number_format((float)($order['discount_percent'] ?? 0), 2, '.', '')) ?>">
                        <?php if (!empty($errors['discount_percent'])): ?><p class="text-danger"><?= htmlspecialchars($errors['discount_percent']) ?></p><?php endif; ?>
                    </div>

                    <p>The discount is calculated from the current item subtotal.</p>
                    <p>Shipping amount is preserved from the existing order.</p>
                    <p>Tax amount is preserved from the existing order.</p>
                </div>

                <div class="card">
                    <h2 class="admin-section-title">Calculated Totals</h2>

                    <p><strong>Subtotal:</strong> <?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['subtotal_minor']) / 100, 2) ?></p>
                    <p><strong>Shipping:</strong> <?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['shipping_minor']) / 100, 2) ?></p>
                    <p><strong>Tax:</strong> <?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['tax_minor']) / 100, 2) ?></p>
                    <p><strong>Discount:</strong> -<?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['discount_minor']) / 100, 2) ?></p>
                    <hr>
                    <p class="summary-total"><strong>Total:</strong> <?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['total_minor']) / 100, 2) ?></p>
                    <p class="admin-note">Totals will be recalculated automatically when you save.</p>
                </div>

                <div class="admin-form-actions">
                    <button class="btn" type="submit">Save Order</button>
                    <a class="btn secondary" href="<?= URLROOT ?>/admin/orders/<?= (int)$order['id'] ?>">Cancel</a>
                </div>
            </div>
        </div>
    </form>

<?php endif; ?>
