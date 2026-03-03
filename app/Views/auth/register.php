<?php require APPROOT . '/Views/inc/header.php'; ?>

<h1><?= htmlspecialchars($data['title'] ?? 'Register') ?></h1>

<form method="post" action="<?= URLROOT ?>/register">
  <div>
    <label>First name</label><br>
    <input name="first_name" value="<?= htmlspecialchars($data['old']['first_name'] ?? '') ?>" required>
    <?php if (!empty($data['errors']['first_name'])): ?>
      <p style="color:red;"><?= htmlspecialchars($data['errors']['first_name']) ?></p>
    <?php endif; ?>
  </div>

  <div>
    <label>Last name</label><br>
    <input name="last_name" value="<?= htmlspecialchars($data['old']['last_name'] ?? '') ?>" required>
    <?php if (!empty($data['errors']['last_name'])): ?>
      <p style="color:red;"><?= htmlspecialchars($data['errors']['last_name']) ?></p>
    <?php endif; ?>
  </div>

  <div>
    <label>Email</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($data['old']['email'] ?? '') ?>" required>
    <?php if (!empty($data['errors']['email'])): ?>
      <p style="color:red;"><?= htmlspecialchars($data['errors']['email']) ?></p>
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
      <p style="color:red;"><?= htmlspecialchars($data['errors']['password']) ?></p>
    <?php endif; ?>
  </div>

  <div>
    <label>Confirm password</label><br>
    <input type="password" name="confirm_password" required>
    <?php if (!empty($data['errors']['confirm_password'])): ?>
      <p style="color:red;"><?= htmlspecialchars($data['errors']['confirm_password']) ?></p>
    <?php endif; ?>
  </div>

  <button type="submit">Create account</button>
</form>

<p>Already have an account? <a href="<?= URLROOT ?>/login">Login</a></p>

<?php require APPROOT . '/Views/inc/footer.php'; ?>