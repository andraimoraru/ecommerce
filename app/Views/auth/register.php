<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="auth-shell">
  <div class="auth-card">
    <h1 class="page-title"><?= htmlspecialchars($data['title'] ?? 'Register') ?></h1>

    <form method="post" action="<?= URLROOT ?>/register" class="auth-form">
      <div>
        <label>First name</label><br>
        <input name="first_name" value="<?= htmlspecialchars($data['old']['first_name'] ?? '') ?>" required>
        <?php if (!empty($data['errors']['first_name'])): ?>
          <p class="text-danger"><?= htmlspecialchars($data['errors']['first_name']) ?></p>
        <?php endif; ?>
      </div>

      <div>
        <label>Last name</label><br>
        <input name="last_name" value="<?= htmlspecialchars($data['old']['last_name'] ?? '') ?>" required>
        <?php if (!empty($data['errors']['last_name'])): ?>
          <p class="text-danger"><?= htmlspecialchars($data['errors']['last_name']) ?></p>
        <?php endif; ?>
      </div>

      <div>
        <label>Email</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($data['old']['email'] ?? '') ?>" required>
        <?php if (!empty($data['errors']['email'])): ?>
          <p class="text-danger"><?= htmlspecialchars($data['errors']['email']) ?></p>
        <?php endif; ?>
      </div>

      <div>
        <label>Phone (optional)</label><br>
        <input name="phone" value="<?= htmlspecialchars($data['old']['phone'] ?? '') ?>">
      </div>

      <div>
        <label>Password</label><br>
        <input type="password" name="password" required>
        <?php if (!empty($data['errors']['password'])): ?>
          <p class="text-danger"><?= htmlspecialchars($data['errors']['password']) ?></p>
        <?php endif; ?>
      </div>

      <div>
        <label>Confirm password</label><br>
        <input type="password" name="confirm_password" required>
        <?php if (!empty($data['errors']['confirm_password'])): ?>
          <p class="text-danger"><?= htmlspecialchars($data['errors']['confirm_password']) ?></p>
        <?php endif; ?>
      </div>

      <div class="auth-actions">
        <button type="submit" class="add-cart-btn">Create account</button>
      </div>
    </form>

    <p>Already have an account? <a href="<?= URLROOT ?>/login">Login</a></p>
  </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
