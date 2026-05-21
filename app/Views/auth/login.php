<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="auth-shell">
  <div class="auth-card">
    <h1 class="page-title"><?= htmlspecialchars($data['title'] ?? 'Login') ?></h1>

    <?php if (!empty($data['error'])): ?>
      <p class="text-danger"><?= htmlspecialchars($data['error']) ?></p>
    <?php endif; ?>

    <form method="post" action="<?= URLROOT ?>/login" class="auth-form">
      <div>
        <label>Email</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($data['old']['email'] ?? '') ?>" required>
      </div>

      <div>
        <label>Password</label><br>
        <input type="password" name="password" required>
      </div>

      <div class="auth-actions">
        <button type="submit" class="add-cart-btn">Login</button>
      </div>
    </form>

    <p>Don't have an account? <a href="<?= URLROOT ?>/register">Register</a></p>
  </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
