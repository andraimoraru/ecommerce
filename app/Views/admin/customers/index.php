<h1><?= htmlspecialchars($data['title'] ?? 'Customers') ?></h1>

<?php $customers = $data['customers'] ?? []; ?>
<?php $pagination = $data['pagination'] ?? ['page' => 1, 'total_pages' => 1]; ?>
<?php $currentPage = (int)($pagination['page'] ?? 1); ?>
<?php $totalPages = (int)($pagination['total_pages'] ?? 1); ?>

<?php if ($customers === []): ?>
    <p>No customers found.</p>
<?php else: ?>
    <div class="card">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="padding:10px; text-align:left;">Customer</th>
                    <th style="padding:10px; text-align:left;">Email</th>
                    <th style="padding:10px; text-align:left;">Phone</th>
                    <th style="padding:10px; text-align:left;">Orders</th>
                    <th style="padding:10px; text-align:left;">Status</th>
                    <th style="padding:10px; text-align:left;">Joined</th>
                    <th style="padding:10px; text-align:left;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr style="border-bottom:1px solid #ddd;">
                        <td style="padding:10px;">
                            <strong><?= htmlspecialchars(trim((string)$customer['first_name'] . ' ' . (string)$customer['last_name'])) ?></strong><br>
                            <small style="color:#777;">#<?= (int)$customer['id'] ?></small>
                        </td>
                        <td style="padding:10px;"><?= htmlspecialchars((string)$customer['email']) ?></td>
                        <td style="padding:10px;"><?= htmlspecialchars((string)($customer['phone'] ?: '—')) ?></td>
                        <td style="padding:10px;"><?= (int)$customer['order_count'] ?></td>
                        <td style="padding:10px;"><?= ((int)$customer['is_active'] === 1) ? 'Active' : 'Inactive' ?></td>
                        <td style="padding:10px;"><?= htmlspecialchars((string)$customer['created_at']) ?></td>
                        <td style="padding:10px;">
                            <a class="btn secondary" href="<?= URLROOT ?>/admin/customers/<?= (int)$customer['id'] ?>">View</a>
                            <a class="btn secondary" href="<?= URLROOT ?>/admin/customers/<?= (int)$customer['id'] ?>/edit">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div style="display:flex; gap:10px; align-items:center; margin-top:16px;">
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
