<h1><?= htmlspecialchars($data['title'] ?? 'Orders') ?></h1>

<?php $orders = $data['orders'] ?? []; ?>
<?php $pagination = $data['pagination'] ?? ['page' => 1, 'total_pages' => 1]; ?>
<?php $currentPage = (int)($pagination['page'] ?? 1); ?>
<?php $totalPages = (int)($pagination['total_pages'] ?? 1); ?>

<?php if (count($orders) === 0): ?>
    <p>No orders have been placed yet.</p>
<?php else: ?>
    <div class="card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Placed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars((string)$order['order_number']) ?></strong><br>
                            <small class="admin-meta">#<?= (int)$order['id'] ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars(trim((string)$order['customer_first_name'] . ' ' . (string)$order['customer_last_name'])) ?><br>
                            <small class="admin-meta"><?= htmlspecialchars((string)$order['customer_email']) ?></small>
                        </td>
                        <td><?= (int)$order['item_count'] ?></td>
                        <td>
                            <?= htmlspecialchars((string)($order['currency'] ?? 'GBP')) ?>
                            <?= number_format(((int)$order['total_minor']) / 100, 2) ?>
                        </td>
                        <td><?= htmlspecialchars((string)$order['status']) ?></td>
                        <td><?= htmlspecialchars((string)($order['placed_at'] ?? $order['created_at'] ?? '—')) ?></td>
                        <td>
                            <a class="btn secondary" href="<?= URLROOT ?>/admin/orders/<?= (int)$order['id'] ?>">View</a>
                            <a class="btn secondary" href="<?= URLROOT ?>/admin/orders/<?= (int)$order['id'] ?>/edit">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <?php if ($currentPage > 1): ?>
                <a class="btn secondary" href="<?= URLROOT ?>/admin/orders?page=<?= $currentPage - 1 ?>">Previous</a>
            <?php endif; ?>
            <span>Page <?= $currentPage ?> of <?= $totalPages ?></span>
            <?php if ($currentPage < $totalPages): ?>
                <a class="btn secondary" href="<?= URLROOT ?>/admin/orders?page=<?= $currentPage + 1 ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
