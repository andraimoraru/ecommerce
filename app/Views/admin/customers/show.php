<?php $customer = $data['customer'] ?? null; ?>
<?php $orders = $data['orders'] ?? []; ?>
<?php $addresses = $data['addresses'] ?? []; ?>
<?php $deleteError = $data['delete_error'] ?? ''; ?>

<?php if (!$customer): ?>
    <p>Customer not found.</p>
<?php else: ?>
    <p class="admin-back-row admin-back-row--actions">
        <a href="<?= URLROOT ?>/admin/customers">← Back to customers</a>
        <a class="btn secondary" href="<?= URLROOT ?>/admin/customers/<?= (int)$customer['id'] ?>/edit">Edit customer</a>
    </p>

    <h1 class="admin-title-reset"><?= htmlspecialchars(trim((string)$customer['first_name'] . ' ' . (string)$customer['last_name'])) ?></h1>

    <div class="grid-2">
        <div>
            <div class="card">
                <h2 class="admin-section-title">Customer Details</h2>
                <p><strong>Email:</strong> <?= htmlspecialchars((string)$customer['email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars((string)($customer['phone'] ?: '—')) ?></p>
                <p><strong>Status:</strong> <?= ((int)$customer['is_active'] === 1) ? 'Active' : 'Inactive' ?></p>
                <p><strong>Joined:</strong> <?= htmlspecialchars((string)$customer['created_at']) ?></p>
            </div>

            <div class="card">
                <h2 class="admin-section-title">Orders</h2>
                <?php if ($orders === []): ?>
                    <p>No orders for this customer yet.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Placed</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string)$order['order_number']) ?></td>
                                    <td><?= htmlspecialchars((string)$order['status']) ?></td>
                                    <td><?= htmlspecialchars((string)$order['currency']) ?> <?= number_format(((int)$order['total_minor']) / 100, 2) ?></td>
                                    <td><?= htmlspecialchars((string)($order['placed_at'] ?? $order['created_at'])) ?></td>
                                    <td><a class="btn secondary" href="<?= URLROOT ?>/admin/orders/<?= (int)$order['id'] ?>">View order</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="card">
                <h2 class="admin-section-title">Saved Addresses</h2>
                <?php if ($addresses === []): ?>
                    <p>No saved addresses.</p>
                <?php else: ?>
                    <?php foreach ($addresses as $address): ?>
                        <div class="admin-address">
                            <p class="admin-address__label"><strong><?= htmlspecialchars((string)($address['label'] ?: 'Address')) ?></strong></p>
                            <p class="admin-address__line"><?= htmlspecialchars((string)$address['first_name']) ?> <?= htmlspecialchars((string)$address['last_name']) ?></p>
                            <p class="admin-address__line"><?= htmlspecialchars((string)$address['line1']) ?></p>
                            <?php if (!empty($address['line2'])): ?><p class="admin-address__line"><?= htmlspecialchars((string)$address['line2']) ?></p><?php endif; ?>
                            <p class="admin-address__line"><?= htmlspecialchars((string)$address['city']) ?><?= !empty($address['region']) ? ', ' . htmlspecialchars((string)$address['region']) : '' ?></p>
                            <p class="admin-address__line"><?= htmlspecialchars((string)$address['postcode']) ?></p>
                            <p class="admin-address__line"><?= htmlspecialchars((string)$address['country_name']) ?></p>
                            <p class="admin-address__meta">
                                <?= !empty($address['is_default_shipping']) ? 'Default shipping' : '' ?>
                                <?= (!empty($address['is_default_shipping']) && !empty($address['is_default_billing'])) ? ' • ' : '' ?>
                                <?= !empty($address['is_default_billing']) ? 'Default billing' : '' ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2 class="admin-section-title">Delete Customer</h2>
                <?php if ($deleteError !== ''): ?>
                    <p class="text-danger"><?= htmlspecialchars((string)$deleteError) ?></p>
                <?php endif; ?>
                <p class="admin-note">Customers can only be deleted when they have no orders.</p>
                <form method="post" action="<?= URLROOT ?>/admin/customers/<?= (int)$customer['id'] ?>/delete" data-confirm="Delete this customer?">
                    <button class="btn admin-danger-button" type="submit">Delete customer</button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
