

<h1><?= htmlspecialchars($data['title']) ?></h1>

<div style="margin-bottom:20px;">
    <a class="btn" href="<?= URLROOT ?>/admin/categories/create">+ Add Category</a>
</div>

<?php $categories = $data['categories'] ?? []; ?>

<?php if (count($categories) === 0): ?>
  <p>No categories yet.</p>
<?php else: ?>
<table border="1" cellpadding="8" cellspacing="0" style="width:100%;">
  <thead>
    <tr>
      <th>Name</th>
      <th>Slug</th>
      <th>Parent</th>
      <th>Active</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($categories as $c): ?>
    <tr>
      <td><?= htmlspecialchars($c['name']) ?></td>
      <td><?= htmlspecialchars($c['slug']) ?></td>
      <td><?= $c['parent_id'] ? (int)$c['parent_id'] : '—' ?></td>
      <td><?= ((int)$c['is_active'] === 1) ? 'Yes' : 'No' ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
