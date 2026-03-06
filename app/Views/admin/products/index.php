<h1><?= htmlspecialchars($data['title'] ?? 'Products') ?></h1>

<p>
  <a href="<?= URLROOT ?>/admin/products/create">+ Add product</a>
</p>

<?php $products = $data['products'] ?? []; ?>

<?php if (count($products) === 0): ?>
  <p>No products yet. Create your first one.</p>
<?php else: ?>
  <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse;">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>SKU</th>
        <th>Category</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Images</th>
        <th>Active</th>
        <th>Created</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $p): ?>
        <tr>
          <td><?= (int)$p['id'] ?></td>
          <td>
            <strong><?= htmlspecialchars((string)$p['name']) ?></strong><br>
            <small><?= htmlspecialchars((string)$p['slug']) ?></small>
          </td>
          <td><?= htmlspecialchars((string)$p['sku']) ?></td>
          <td><?= htmlspecialchars((string)($p['category_name'] ?? '—')) ?></td>
          <td>
            <?= htmlspecialchars((string)($p['currency'] ?? 'GBP')) ?>
            <?= number_format(((int)$p['price_minor']) / 100, 2) ?>
          </td>
          <td>
            On hand: <?= (int)($p['stock_on_hand'] ?? 0) ?><br>
            <small>Reserved: <?= (int)($p['stock_reserved'] ?? 0) ?></small>
          </td>
          <td style="padding:10px;">
                <?php if (!empty($p['primary_image'])): ?>
                    <img src="<?= htmlspecialchars($p['primary_image']) ?>"
                        alt=""
                        style="width:60px; height:60px; object-fit:cover; border-radius:4px;">
                <?php else: ?>
                    <div style="
                        width:60px;
                        height:60px;
                        background:#eee;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        font-size:12px;
                        color:#999;
                        border-radius:4px;">
                        No Image
                    </div>
                <?php endif; ?>
          </td>
          <td><?= ((int)$p['is_active'] === 1) ? 'Yes' : 'No' ?></td>
          <td><?= htmlspecialchars((string)$p['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
