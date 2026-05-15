<?php $order = $data['order'] ?? null; ?>
<?php $shipping = $data['shipping_address'] ?? null; ?>
<?php $items = $data['items'] ?? []; ?>
<?php $productOptions = $data['product_options'] ?? []; ?>
<?php $errors = $data['errors'] ?? []; ?>

<?php if (!$order || !$shipping): ?>
    <p>Order not found.</p>
<?php else: ?>
    <p style="margin:0 0 18px 0;">
        <a href="<?= URLROOT ?>/admin/orders/<?= (int)$order['id'] ?>">← Back to order</a>
    </p>

    <h1 style="margin-top:0;"><?= htmlspecialchars($data['title'] ?? 'Edit Order') ?></h1>

    <form method="post" action="<?= URLROOT ?>/admin/orders/<?= (int)$order['id'] ?>">
        <div class="grid-2">
            <div>
                <div class="card">
                    <h2 style="margin-top:0;">Items</h2>

                    <p><strong>Order Number:</strong> <?= htmlspecialchars((string)$order['order_number']) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars((string)$order['status']) ?></p>
                    <p><strong>Currency:</strong> <?= htmlspecialchars((string)$order['currency']) ?></p>

                    <?php if (!empty($errors['items'])): ?>
                        <p style="color:red;"><?= htmlspecialchars($errors['items']) ?></p>
                    <?php endif; ?>

                    <?php if (!$items): ?>
                        <p>No items currently in this order.</p>
                    <?php else: ?>
                        <table style="width:100%; border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f5f5f5;">
                                    <th style="padding:10px; text-align:left;">Image</th>
                                    <th style="padding:10px; text-align:left;">Product</th>
                                    <th style="padding:10px; text-align:left;">Unit Price</th>
                                    <th style="padding:10px; text-align:left;">Qty</th>
                                    <th style="padding:10px; text-align:left;">Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $index => $item): ?>
                                    <tr style="border-bottom:1px solid #ddd;">
                                        <td style="padding:10px;">
                                            <?php $thumb = $item['primary_image'] ?? null; ?>
                                            <?php if (!empty($thumb)): ?>
                                                <img src="<?= htmlspecialchars((string)$thumb) ?>" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:6px;">
                                            <?php else: ?>
                                                <div style="width:56px;height:56px;background:#eee;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#999;font-size:12px;">
                                                    No image
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding:10px;">
                                            <strong><?= htmlspecialchars((string)$item['product_name']) ?></strong><br>
                                            <small style="color:#777;"><?= htmlspecialchars((string)($item['sku'] ?? '')) ?></small>
                                            <input type="hidden" name="existing_item_id[]" value="<?= (int)$item['id'] ?>">
                                        </td>
                                        <td style="padding:10px;">
                                            <?= htmlspecialchars((string)$order['currency']) ?>
                                            <?= number_format(((int)$item['unit_price_minor']) / 100, 2) ?>
                                        </td>
                                        <td style="padding:10px;">
                                            <input type="number" min="0" name="existing_quantity[]" value="<?= (int)$item['quantity'] ?>" style="width:90px;">
                                            <?php if (!empty($errors['item_' . $index])): ?><p style="color:red;"><?= htmlspecialchars($errors['item_' . $index]) ?></p><?php endif; ?>
                                        </td>
                                        <td style="padding:10px;">
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
                    <h2 style="margin-top:0;">Add Products</h2>

                    <div style="margin-bottom:12px;">
                        <label for="skuSearch">Search by SKU</label><br>
                        <input id="skuSearch" type="text" placeholder="Type SKU..." style="width:100%;padding:10px;">
                    </div>

                    <div id="skuSearchResults" style="display:flex;flex-direction:column;gap:10px;"></div>

                    <div id="pendingAdditions" style="margin-top:16px;"></div>
                </div>

                <div class="card">
                    <h2 style="margin-top:0;">Shipping Address</h2>

                    <div class="checkout-grid">
                        <div>
                            <label>First Name</label><br>
                            <input name="shipping_first_name" value="<?= htmlspecialchars((string)$shipping['first_name']) ?>" style="width:100%;">
                            <?php if (!empty($errors['shipping_first_name'])): ?><p style="color:red;"><?= htmlspecialchars($errors['shipping_first_name']) ?></p><?php endif; ?>
                        </div>

                        <div>
                            <label>Last Name</label><br>
                            <input name="shipping_last_name" value="<?= htmlspecialchars((string)$shipping['last_name']) ?>" style="width:100%;">
                            <?php if (!empty($errors['shipping_last_name'])): ?><p style="color:red;"><?= htmlspecialchars($errors['shipping_last_name']) ?></p><?php endif; ?>
                        </div>

                        <div>
                            <label>Phone</label><br>
                            <input name="shipping_phone" value="<?= htmlspecialchars((string)($shipping['phone'] ?? '')) ?>" style="width:100%;">
                        </div>

                        <div style="grid-column:1 / -1;">
                            <label>Address line 1</label><br>
                            <input name="shipping_line1" value="<?= htmlspecialchars((string)$shipping['line1']) ?>" style="width:100%;">
                            <?php if (!empty($errors['shipping_line1'])): ?><p style="color:red;"><?= htmlspecialchars($errors['shipping_line1']) ?></p><?php endif; ?>
                        </div>

                        <div style="grid-column:1 / -1;">
                            <label>Address line 2</label><br>
                            <input name="shipping_line2" value="<?= htmlspecialchars((string)($shipping['line2'] ?? '')) ?>" style="width:100%;">
                        </div>

                        <div>
                            <label>City</label><br>
                            <input name="shipping_city" value="<?= htmlspecialchars((string)$shipping['city']) ?>" style="width:100%;">
                            <?php if (!empty($errors['shipping_city'])): ?><p style="color:red;"><?= htmlspecialchars($errors['shipping_city']) ?></p><?php endif; ?>
                        </div>

                        <div>
                            <label>Region</label><br>
                            <input name="shipping_region" value="<?= htmlspecialchars((string)($shipping['region'] ?? '')) ?>" style="width:100%;">
                        </div>

                        <div>
                            <label>Postcode</label><br>
                            <input name="shipping_postcode" value="<?= htmlspecialchars((string)$shipping['postcode']) ?>" style="width:100%;">
                            <?php if (!empty($errors['shipping_postcode'])): ?><p style="color:red;"><?= htmlspecialchars($errors['shipping_postcode']) ?></p><?php endif; ?>
                        </div>

                        <div>
                            <label>Country</label><br>
                            <input name="shipping_country_name" value="<?= htmlspecialchars((string)$shipping['country_name']) ?>" style="width:100%;">
                            <?php if (!empty($errors['shipping_country_name'])): ?><p style="color:red;"><?= htmlspecialchars($errors['shipping_country_name']) ?></p><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="card">
                    <h2 style="margin-top:0;">Pricing</h2>

                    <div style="margin-bottom:12px;">
                        <label>Discount (%)</label><br>
                        <input type="number" min="0" max="100" step="0.01" name="discount_percent" value="<?= htmlspecialchars(number_format((float)($order['discount_percent'] ?? 0), 2, '.', '')) ?>" style="width:100%;">
                        <?php if (!empty($errors['discount_percent'])): ?><p style="color:red;"><?= htmlspecialchars($errors['discount_percent']) ?></p><?php endif; ?>
                    </div>

                    <p>The discount is calculated from the current item subtotal.</p>
                    <p>Shipping amount is preserved from the existing order.</p>
                    <p>Tax amount is preserved from the existing order.</p>
                </div>

                <div class="card">
                    <h2 style="margin-top:0;">Calculated Totals</h2>

                    <p><strong>Subtotal:</strong> <?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['subtotal_minor']) / 100, 2) ?></p>
                    <p><strong>Shipping:</strong> <?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['shipping_minor']) / 100, 2) ?></p>
                    <p><strong>Tax:</strong> <?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['tax_minor']) / 100, 2) ?></p>
                    <p><strong>Discount:</strong> -<?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['discount_minor']) / 100, 2) ?></p>
                    <hr>
                    <p style="font-size:18px;"><strong>Total:</strong> <?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['total_minor']) / 100, 2) ?></p>
                    <p style="color:#666;">Totals will be recalculated automatically when you save.</p>
                </div>

                <div style="display:flex;gap:12px;">
                    <button class="btn" type="submit">Save Order</button>
                    <a class="btn secondary" href="<?= URLROOT ?>/admin/orders/<?= (int)$order['id'] ?>">Cancel</a>
                </div>
            </div>
        </div>
    </form>

    <script>
    const orderEditorProducts = <?= json_encode($productOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

    function renderSkuSearchResults(query) {
        const results = document.getElementById('skuSearchResults');
        if (!results) return;

        const normalized = query.trim().toLowerCase();
        if (normalized === '') {
            results.innerHTML = '';
            return;
        }

        const matches = orderEditorProducts
            .filter((product) => {
                const sku = String(product.sku || '').toLowerCase();
                const name = String(product.name || '').toLowerCase();
                return sku.includes(normalized) || name.includes(normalized);
            })
            .slice(0, 8);

        if (matches.length === 0) {
            results.innerHTML = '<p style="margin:0;color:#666;">No matching products found.</p>';
            return;
        }

        results.innerHTML = matches.map((product) => {
            const thumbnail = product.primary_image
                ? `<img src="${escapeHtml(product.primary_image)}" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:6px;">`
                : '<div style="width:56px;height:56px;background:#eee;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#999;font-size:12px;">No image</div>';

            return `
                <div style="display:grid;grid-template-columns:56px 1fr auto auto;gap:12px;align-items:center;padding:10px;border:1px solid #ddd;border-radius:8px;">
                    <div>${thumbnail}</div>
                    <div>
                        <strong>${escapeHtml(product.name || '')}</strong><br>
                        <small style="color:#777;">${escapeHtml(product.sku || '')}</small>
                    </div>
                    <div>
                        <input type="number" min="1" value="1" data-qty-for="${product.id}" style="width:80px;">
                    </div>
                    <button class="btn secondary" type="button" onclick="addSearchedProduct(${Number(product.id)})">Add</button>
                </div>
            `;
        }).join('');
    }

    function addSearchedProduct(productId) {
        const product = orderEditorProducts.find((item) => Number(item.id) === Number(productId));
        const qtyInput = document.querySelector(`[data-qty-for="${productId}"]`);
        const quantity = qtyInput ? Number(qtyInput.value || 0) : 0;
        const container = document.getElementById('pendingAdditions');

        if (!product || !container || quantity <= 0) return;

        const row = document.createElement('div');
        row.style.cssText = 'display:grid;grid-template-columns:56px 1fr auto auto;gap:12px;align-items:center;padding:10px;margin-bottom:10px;border:1px solid #ddd;border-radius:8px;';

        const thumbnail = product.primary_image
            ? `<img src="${escapeHtml(product.primary_image)}" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:6px;">`
            : '<div style="width:56px;height:56px;background:#eee;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#999;font-size:12px;">No image</div>';

        row.innerHTML = `
            <div>${thumbnail}</div>
            <div>
                <strong>${escapeHtml(product.name || '')}</strong><br>
                <small style="color:#777;">${escapeHtml(product.sku || '')}</small>
                <input type="hidden" name="new_product_id[]" value="${Number(product.id)}">
            </div>
            <div>
                <input type="number" min="1" name="new_quantity[]" value="${quantity}" style="width:80px;">
            </div>
            <button class="btn secondary" type="button">Remove</button>
        `;

        row.querySelector('button').addEventListener('click', () => row.remove());
        container.appendChild(row);
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    document.getElementById('skuSearch')?.addEventListener('input', function (event) {
        renderSkuSearchResults(event.target.value || '');
    });
    </script>
<?php endif; ?>
