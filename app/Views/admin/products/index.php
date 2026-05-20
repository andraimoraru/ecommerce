<h1><?= htmlspecialchars($data['title'] ?? 'Products') ?></h1>

<div style="margin-bottom:20px;">
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
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="padding:10px; text-align:left;">Image</th>
                    <th style="padding:10px; text-align:left;">Name</th>
                    <th style="padding:10px; text-align:left;">SKU</th>
                    <th style="padding:10px; text-align:left;">Category</th>
                    <th style="padding:10px; text-align:left;">Price</th>
                    <th style="padding:10px; text-align:left;">Stock</th>
                    <th style="padding:10px; text-align:left;">Status</th>
                    <th style="padding:10px; text-align:left;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr style="border-bottom:1px solid #ddd;">
                        <td style="padding:10px;">
                            <?php if (!empty($p['primary_image'])): ?>
                                <img src="<?= htmlspecialchars((string)$p['primary_image']) ?>"
                                     alt=""
                                     style="width:60px;height:60px;object-fit:cover;border-radius:4px;">
                            <?php else: ?>
                                <div style="width:60px;height:60px;background:#eee;display:flex;align-items:center;justify-content:center;font-size:12px;color:#999;border-radius:4px;">
                                    No Image
                                </div>
                            <?php endif; ?>
                        </td>

                        <td style="padding:10px;">
                            <strong><?= htmlspecialchars((string)$p['name']) ?></strong><br>
                            <small style="color:#777;"><?= htmlspecialchars((string)$p['slug']) ?></small>
                        </td>

                        <td style="padding:10px;"><?= htmlspecialchars((string)$p['sku']) ?></td>
                        <td style="padding:10px;"><?= htmlspecialchars((string)($p['category_name'] ?? '—')) ?></td>
                        <td style="padding:10px;"><?= htmlspecialchars((string)($p['currency'] ?? 'GBP')) ?> <?= number_format(((int)$p['price_minor']) / 100, 2) ?></td>
                        <td style="padding:10px;"><?= (int)($p['stock_on_hand'] ?? 0) ?></td>
                        <td style="padding:10px;"><?= htmlspecialchars((string)$p['status']) ?></td>

                        <td style="padding:10px;">
                            <a class="btn secondary" href="<?= URLROOT ?>/admin/products/<?= (int)$p['id'] ?>/edit">Edit</a>

                            <?php if (($p['status'] ?? '') !== 'ARCHIVED'): ?>
                                <form action="<?= URLROOT ?>/admin/products/<?= (int)$p['id'] ?>/archive" method="post" style="display:inline;">
                                    <button class="btn" type="submit">Archive</button>
                                </form>
                            <?php else: ?>
                                <form action="<?= URLROOT ?>/admin/products/<?= (int)$p['id'] ?>/restore" method="post" style="display:inline;">
                                    <button class="btn" type="submit">Restore</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div style="display:flex; gap:10px; align-items:center; margin-top:16px;">
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
