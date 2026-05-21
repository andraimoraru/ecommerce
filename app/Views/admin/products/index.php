<h1><?= htmlspecialchars($data['title'] ?? 'Products') ?></h1>

<div class="admin-page-actions">
    <a class="btn" href="<?= URLROOT ?>/admin/products/create">+ Add Product</a>
</div>

<?php $products = $data['products'] ?? []; ?>
<?php $pagination = $data['pagination'] ?? ['page' => 1, 'total_pages' => 1]; ?>
<?php $currentPage = (int)($pagination['page'] ?? 1); ?>
<?php $totalPages = (int)($pagination['total_pages'] ?? 1); ?>
<?php $filterCategory = $data['filter_category'] ?? null; ?>

<?php if (count($products) === 0): ?>
    <p>No products found for this view.</p>
<?php else: ?>
    <div class="card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <?php if (!empty($p['primary_image'])): ?>
                                <img src="<?= htmlspecialchars((string)$p['primary_image']) ?>"
                                     alt=""
                                     class="admin-thumb">
                            <?php else: ?>
                                <div class="admin-thumb-placeholder">
                                    No Image
                                </div>
                            <?php endif; ?>
                        </td>

                        <td>
                            <strong><?= htmlspecialchars((string)$p['name']) ?></strong><br>
                            <small class="admin-meta"><?= htmlspecialchars((string)$p['slug']) ?></small>
                        </td>

                        <td><?= htmlspecialchars((string)$p['sku']) ?></td>
                        <td><?= htmlspecialchars((string)($p['category_name'] ?? '—')) ?></td>
                        <td><?= htmlspecialchars((string)($p['currency'] ?? 'GBP')) ?> <?= number_format(((int)$p['price_minor']) / 100, 2) ?></td>
                        <td><?= (int)($p['stock_on_hand'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string)$p['status']) ?></td>

                        <td>
                            <div class="admin-actions">
                            <a class="btn secondary" href="<?= URLROOT ?>/admin/products/<?= (int)$p['id'] ?>/edit">Edit</a>

                            <?php if (($p['status'] ?? '') !== 'ARCHIVED'): ?>
                                <form action="<?= URLROOT ?>/admin/products/<?= (int)$p['id'] ?>/archive" method="post" class="admin-inline-form">
                                    <button class="btn" type="submit">Archive</button>
                                </form>
                            <?php else: ?>
                                <form action="<?= URLROOT ?>/admin/products/<?= (int)$p['id'] ?>/restore" method="post" class="admin-inline-form">
                                    <button class="btn" type="submit">Restore</button>
                                </form>
                            <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <?php if ($currentPage > 1): ?>
                <a class="btn secondary" href="<?= URLROOT ?>/admin/products?page=<?= $currentPage - 1 ?><?= $filterCategory ? '&category_id=' . (int)$filterCategory['id'] : '' ?>">Previous</a>
            <?php endif; ?>
            <span>Page <?= $currentPage ?> of <?= $totalPages ?></span>
            <?php if ($currentPage < $totalPages): ?>
                <a class="btn secondary" href="<?= URLROOT ?>/admin/products?page=<?= $currentPage + 1 ?><?= $filterCategory ? '&category_id=' . (int)$filterCategory['id'] : '' ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
