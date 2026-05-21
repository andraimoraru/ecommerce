<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= htmlspecialchars($data['title'] ?? SITENAME) ?></title>
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/admin.css">
</head>

<body class="admin-body">

<div class="menu-toggle" data-sidebar-toggle>
☰ Admin Menu
</div>

<div class="admin-layout">

    <?php include APPROOT . '/Views/inc/admin_sidebar.php'; ?>

    <div class="admin-content">

        <?= $content ?>

    </div>

</div>

<script src="<?= URLROOT ?>/assets/js/admin.js"></script>

</body>
</html>
