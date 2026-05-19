<?php $customer = $data['customer'] ?? null; ?>
<?php $orders = $data['orders'] ?? []; ?>
<?php $addresses = $data['addresses'] ?? []; ?>
<?php $deleteError = $data['delete_error'] ?? ''; ?>

<?php if (!$customer): ?>
    <p>Customer not found.</p>
<?php else: ?>
    <p style="margin:0 0 18px 0;">
        <a href="<?= URLROOT ?>/admin/customers">← Back to customers</a>
        <a class="btn secondary" href="<?= URLROOT ?>/admin/customers/<?= (int)$customer['id'] ?>/edit" style="margin-left:12px;">Edit customer</a>
    </p>

    <h1 style="margin-top:0;"><?= htmlspecialchars(trim((string)$customer['first_name'] . ' ' . (string)$customer['last_name'])) ?></h1>

    <div class="grid-2">
        <div>
            <div class="card">
                <h2 style="margin-top:0;">Customer Details</h2>
                <p><strong>Email:</strong> <?= htmlspecialchars((string)$customer['email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars((string)($customer['phone'] ?: '—')) ?></p>
                <p><strong>Status:</strong> <?= ((int)$customer['is_active'] === 1) ? 'Active' : 'Inactive' ?></p>
                <p><strong>Joined:</strong> <?= htmlspecialchars((string)$customer['created_at']) ?></p>
            </div>

            <div class="card">
                <h2 style="margin-top:0;">Orders</h2>
                <?php if ($orders === []): ?>
                    <p>No orders for this customer yet.</p>
                <?php else: ?>
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f5f5f5;">
                                <th style="padding:10px; text-align:left;">Order</th>
                                <th style="padding:10px; text-align:left;">Status</th>
                                <th style="padding:10px; text-align:left;">Total</th>
                                <th style="padding:10px; text-align:left;">Placed</th>
                                <th style="padding:10px; text-align:left;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr style="border-bottom:1px solid #ddd;">
                                    <td style="padding:10px;"><?= htmlspecialchars((string)$order['order_number']) ?></td>
                                    <td style="padding:10px;"><?= htmlspecialchars((string)$order['status']) ?></td>
                                    <td style="padding:10px;"><?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['total_minor']) / 100, 2) ?></td>
                                    <td style="padding:10px;"><?= htmlspecialchars((string)($order['placed_at'] ?? $order['created_at'])) ?></td>
                                    <td style="padding:10px;"><a class="btn secondary" href="<?= URLROOT ?>/admin/orders/<?= (int)$order['id'] ?>">View order</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="card">
                <h2 style="margin-top:0;">Saved Addresses</h2>
                <?php if ($addresses === []): ?>
                    <p>No saved addresses.</p>
                <?php else: ?>
                    <?php foreach ($addresses as $address): ?>
                        <div style="padding:0 0 12px 0; margin-bottom:12px; border-bottom:1px solid #eee;">
                            <p style="margin:0 0 6px 0;"><strong><?= htmlspecialchars((string)($address['label'] ?: 'Address')) ?></strong></p>
                            <p style="margin:0;"><?= htmlspecialchars((string)$address['first_name']) ?> <?= htmlspecialchars((string)$address['last_name']) ?></p>
                            <p style="margin:0;"><?= htmlspecialchars((string)$address['line1']) ?></p>
                            <?php if (!empty($address['line2'])): ?><p style="margin:0;"><?= htmlspecialchars((string)$address['line2']) ?></p><?php endif; ?>
                            <p style="margin:0;"><?= htmlspecialchars((string)$address['city']) ?><?= !empty($address['region']) ? ', ' . htmlspecialchars((string)$address['region']) : '' ?></p>
                            <p style="margin:0;"><?= htmlspecialchars((string)$address['postcode']) ?></p>
                            <p style="margin:0;"><?= htmlspecialchars((string)$address['country_name']) ?></p>
                            <p style="margin:6px 0 0 0; color:#666;">
                                <?= !empty($address['is_default_shipping']) ? 'Default shipping' : '' ?>
                                <?= (!empty($address['is_default_shipping']) && !empty($address['is_default_billing'])) ? ' • ' : '' ?>
                                <?= !empty($address['is_default_billing']) ? 'Default billing' : '' ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2 style="margin-top:0;">Delete Customer</h2>
                <?php if ($deleteError !== ''): ?>
                    <p style="color:red;"><?= htmlspecialchars((string)$deleteError) ?></p>
                <?php endif; ?>
                <p style="color:#666;">Customers can only be deleted when they have no orders.</p>
                <form method="post" action="<?= URLROOT ?>/admin/customers/<?= (int)$customer['id'] ?>/delete" onsubmit="return confirm('Delete this customer?');">
                    <button class="btn" type="submit" style="background:#9b1c1c;border-color:#9b1c1c;">Delete customer</button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
