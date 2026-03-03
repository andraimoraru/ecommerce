<?php require APPROOT . '/Views/inc/header.php'; ?>

<h1><?= htmlspecialchars($data['title'] ?? 'Login') ?></h1>

<?php if (!empty($data['error'])): ?>
  <p style="color:red;"><?= htmlspecialchars($data['error']) ?></p>
<?php endif; ?>

<form method="post" action="<?= URLROOT ?>/login">
  <div>
    <label>Email</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($data['old']['email'] ?? '') ?>" required>
  </div>

  <div>
    <label>Password</label><br>
    <input type="password" name="password" required>
  </div>

  <button type="submit">Login</button>
</form>

<p>Don't have an account? <a href="<?= URLROOT ?>/register">Register</a></p>

<?php require APPROOT . '/Views/inc/footer.php'; ?>