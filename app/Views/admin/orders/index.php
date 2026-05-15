<h1><?= htmlspecialchars($data['title'] ?? 'Orders') ?></h1>

<?php $orders = $data['orders'] ?? []; ?>

<?php if (count($orders) === 0): ?>
    <p>No orders have been placed yet.</p>
<?php else: ?>
    <div class="card">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="padding:10px; text-align:left;">Order</th>
                    <th style="padding:10px; text-align:left;">Customer</th>
                    <th style="padding:10px; text-align:left;">Items</th>
                    <th style="padding:10px; text-align:left;">Total</th>
                    <th style="padding:10px; text-align:left;">Status</th>
                    <th style="padding:10px; text-align:left;">Placed</th>
                    <th style="padding:10px; text-align:left;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr style="border-bottom:1px solid #ddd;">
                        <td style="padding:10px;">
                            <strong><?= htmlspecialchars((string)$order['order_number']) ?></strong><br>
                            <small style="color:#777;">#<?= (int)$order['id'] ?></small>
                        </td>
                        <td style="padding:10px;">
                            <?= htmlspecialchars(trim((string)$order['customer_first_name'] . ' ' . (string)$order['customer_last_name'])) ?><br>
                            <small style="color:#777;"><?= htmlspecialchars((string)$order['customer_email']) ?></small>
                        </td>
                        <td style="padding:10px;"><?= (int)$order['item_count'] ?></td>
                        <td style="padding:10px;">
                            <?= htmlspecialchars((string)($order['currency'] ?? 'GBP')) ?>
                            <?= number_format(((int)$order['total_minor']) / 100, 2) ?>
                        </td>
                        <td style="padding:10px;"><?= htmlspecialchars((string)$order['status']) ?></td>
                        <td style="padding:10px;"><?= htmlspecialchars((string)($order['placed_at'] ?? $order['created_at'] ?? '—')) ?></td>
                        <td style="padding:10px;">
                            <a class="btn secondary" href="<?= URLROOT ?>/admin/orders/<?= (int)$order['id'] ?>">View</a>
                            <a class="btn secondary" href="<?= URLROOT ?>/admin/orders/<?= (int)$order['id'] ?>/edit">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
