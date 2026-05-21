<h1><?= htmlspecialchars($data['title'] ?? 'Customers') ?></h1>

<?php $customers = $data['customers'] ?? []; ?>
<?php $pagination = $data['pagination'] ?? ['page' => 1, 'total_pages' => 1]; ?>
<?php $currentPage = (int)($pagination['page'] ?? 1); ?>
<?php $totalPages = (int)($pagination['total_pages'] ?? 1); ?>

<?php if ($customers === []): ?>
    <p>No customers found.</p>
<?php else: ?>
    <div class="card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars(trim((string)$customer['first_name'] . ' ' . (string)$customer['last_name'])) ?></strong><br>
                            <small class="admin-meta">#<?= (int)$customer['id'] ?></small>
                        </td>
                        <td><?= htmlspecialchars((string)$customer['email']) ?></td>
                        <td><?= htmlspecialchars((string)($customer['phone'] ?: '—')) ?></td>
                        <td><?= (int)$customer['order_count'] ?></td>
                        <td><?= ((int)$customer['is_active'] === 1) ? 'Active' : 'Inactive' ?></td>
                        <td><?= htmlspecialchars((string)$customer['created_at']) ?></td>
                        <td>
                            <a class="btn secondary" href="<?= URLROOT ?>/admin/customers/<?= (int)$customer['id'] ?>">View</a>
                            <a class="btn secondary" href="<?= URLROOT ?>/admin/customers/<?= (int)$customer['id'] ?>/edit">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <?php if ($currentPage > 1): ?>
                <a class="btn secondary" href="<?= URLROOT ?>/admin/customers?page=<?= $currentPage - 1 ?>">Previous</a>
            <?php endif; ?>
            <span>Page <?= $currentPage ?> of <?= $totalPages ?></span>
            <?php if ($currentPage < $totalPages): ?>
                <a class="btn secondary" href="<?= URLROOT ?>/admin/customers?page=<?= $currentPage + 1 ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
