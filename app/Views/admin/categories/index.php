

<h1><?= htmlspecialchars($data['title']) ?></h1>

<p>
  <a href="<?= URLROOT ?>/admin/categories/create">+ Add Category</a>
</p>

<?php $categories = $data['categories'] ?? []; ?>

<?php if (count($categories) === 0): ?>
  <p>No categories yet.</p>
<?php else: ?>
<table border="1" cellpadding="8" cellspacing="0" style="width:100%;">
  <thead>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Slug</th>
      <th>Parent</th>
      <th>Active</th>
      <th>Created</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($categories as $c): ?>
    <tr>
      <td><?= (int)$c['id'] ?></td>
      <td><?= htmlspecialchars($c['name']) ?></td>
      <td><?= htmlspecialchars($c['slug']) ?></td>
      <td><?= $c['parent_id'] ? (int)$c['parent_id'] : '—' ?></td>
      <td><?= ((int)$c['is_active'] === 1) ? 'Yes' : 'No' ?></td>
      <td><?= htmlspecialchars($c['created_at']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
