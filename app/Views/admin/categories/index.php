

<h1><?= htmlspecialchars($data['title']) ?></h1>

<div class="admin-page-actions">
    <a class="btn" href="<?= URLROOT ?>/admin/categories/create">+ Add Category</a>
</div>

<?php $deleteError = $_SESSION['admin_category_delete_error'] ?? ''; ?>
<?php unset($_SESSION['admin_category_delete_error']); ?>
<?php $deleteSuccess = $_SESSION['admin_category_delete_success'] ?? ''; ?>
<?php unset($_SESSION['admin_category_delete_success']); ?>

<?php if ($deleteError !== ''): ?>
  <div class="card">
    <p class="flash-error"><?= htmlspecialchars($deleteError) ?></p>
  </div>
<?php endif; ?>

<?php if ($deleteSuccess !== ''): ?>
  <div class="card">
    <p class="flash-success"><?= htmlspecialchars($deleteSuccess) ?></p>
  </div>
<?php endif; ?>

<?php $categories = $data['categories'] ?? []; ?>

<?php if (count($categories) === 0): ?>
  <p>No categories yet.</p>
<?php else: ?>
<table class="admin-table admin-table--bordered">
  <thead>
    <tr>
      <th>Name</th>
      <th>Slug</th>
      <th>Parent</th>
      <th>Active</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($categories as $c): ?>
    <tr>
      <td><?= htmlspecialchars($c['name']) ?></td>
      <td><?= htmlspecialchars($c['slug']) ?></td>
      <td><?= $c['parent_id'] ? (int)$c['parent_id'] : '—' ?></td>
      <td><?= ((int)$c['is_active'] === 1) ? 'Yes' : 'No' ?></td>
      <td>
        <a class="btn secondary" href="<?= URLROOT ?>/admin/products?category_id=<?= (int)$c['id'] ?>">View products</a>
        <a class="btn secondary" href="<?= URLROOT ?>/admin/categories/<?= (int)$c['id'] ?>/edit">Edit</a>
        <form method="post" action="<?= URLROOT ?>/admin/categories/<?= (int)$c['id'] ?>/delete" class="admin-inline-form" data-confirm="Delete this category?">
          <button class="btn secondary" type="submit">Delete</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
