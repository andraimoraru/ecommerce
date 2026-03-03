<?php require APPROOT . '/Views/inc/header.php'; ?>

<h1 style="margin-bottom:20px;">
    <?= htmlspecialchars($data['title'] ?? 'Products') ?>
</h1>

<!-- Action Buttons -->
<div style="margin-bottom:25px;">
    <a href="<?= URLROOT ?>/admin/products/create"
       style="display:inline-block;
              padding:10px 18px;
              background:#111;
              color:#fff;
              text-decoration:none;
              border-radius:4px;
              margin-right:10px;">
        + Add Product
    </a>

    <a href="<?= URLROOT ?>/admin/categories/create"
       style="display:inline-block;
              padding:10px 18px;
              background:#555;
              color:#fff;
              text-decoration:none;
              border-radius:4px;">
        + Add Category
    </a>
</div>

<?php $products = $data['products'] ?? []; ?>

<?php if (count($products) === 0): ?>
    <p>No products yet. Create your first one.</p>
<?php else: ?>

<table style="width:100%; border-collapse:collapse;">
    <thead>
        <tr style="background:#f5f5f5;">
            <th style="padding:10px; text-align:left;">ID</th>
            <th style="padding:10px; text-align:left;">Name</th>
            <th style="padding:10px; text-align:left;">SKU</th>
            <th style="padding:10px; text-align:left;">Category</th>
            <th style="padding:10px; text-align:left;">Price</th>
            <th style="padding:10px; text-align:left;">Stock</th>
            <th style="padding:10px; text-align:left;">Active</th>
            <th style="padding:10px; text-align:left;">Created</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $p): ?>
            <tr style="border-bottom:1px solid #ddd;">
                <td style="padding:10px;"><?= (int)$p['id'] ?></td>

                <td style="padding:10px;">
                    <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                    <small style="color:#777;">
                        <?= htmlspecialchars($p['slug']) ?>
                    </small>
                </td>

                <td style="padding:10px;">
                    <?= htmlspecialchars($p['sku']) ?>
                </td>

                <td style="padding:10px;">
                    <?= htmlspecialchars($p['category_name'] ?? '—') ?>
                </td>

                <td style="padding:10px;">
                    <?= htmlspecialchars($p['currency'] ?? 'GBP') ?>
                    <?= number_format(((int)$p['price_minor']) / 100, 2) ?>
                </td>

                <td style="padding:10px;">
                    <?= (int)($p['stock_on_hand'] ?? 0) ?>
                </td>

                <td style="padding:10px;">
                    <?= ((int)$p['is_active'] === 1) ? 'Yes' : 'No' ?>
                </td>

                <td style="padding:10px;">
                    <?= htmlspecialchars($p['created_at']) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php endif; ?>

<?php require APPROOT . '/Views/inc/footer.php'; ?>