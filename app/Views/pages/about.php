<?php require APPROOT . '/Views/inc/header.php'; ?>

<h1><?= htmlspecialchars($data['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h1>

<?php require APPROOT . '/Views/inc/footer.php'; ?>