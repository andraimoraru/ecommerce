<?php
$cartCount = 0;

if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum(array_map('intval', $_SESSION['cart']));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= htmlspecialchars($data['title'] ?? SITENAME) ?></title>
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/admin.css">
</head>

<body class="admin-body">

<header class="admin-topbar">
    <a href="<?= URLROOT ?>/admin" class="admin-topbar__brand" aria-label="Admin dashboard">
        <span class="admin-topbar__mark">B</span>
        <span class="admin-topbar__copy">
            <span class="admin-topbar__eyebrow">Admin</span>
            <span class="admin-topbar__title"><?= htmlspecialchars((string)SITENAME) ?></span>
        </span>
    </a>

    <div class="admin-topbar__actions">
        <a class="admin-icon-link" href="<?= URLROOT ?>" aria-label="View store">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 10.5 12 3l9 7.5"></path>
                <path d="M5 10v10h14V10"></path>
                <path d="M9 20v-6h6v6"></path>
            </svg>
        </a>

        <a class="admin-icon-link" href="<?= URLROOT ?>/products" aria-label="Search products">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7"></circle>
                <path d="M20 20l-3.5-3.5"></path>
            </svg>
        </a>

        <a class="admin-icon-link" href="<?= URLROOT ?>/cart" aria-label="Cart">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="9" cy="20" r="1.5"></circle>
                <circle cx="18" cy="20" r="1.5"></circle>
                <path d="M3 4h2l2.2 10.2a1 1 0 0 0 1 .8h9.9a1 1 0 0 0 1-.8L21 7H7"></path>
            </svg>
            <?php if ($cartCount > 0): ?>
                <span class="admin-cart-badge"><?= $cartCount > 99 ? '99+' : $cartCount ?></span>
            <?php endif; ?>
        </a>
    </div>

    <button type="button" class="menu-toggle" data-sidebar-toggle aria-label="Toggle admin menu" aria-controls="adminSidebar" aria-expanded="false">
        <span class="hamburger" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
        </span>
        <span class="sr-only">Toggle admin menu</span>
    </button>
</header>

<div class="admin-layout">

    <?php include APPROOT . '/Views/inc/admin_sidebar.php'; ?>

    <div class="admin-content">
        <?php require APPROOT . '/Views/inc/flash.php'; ?>

        <?= $content ?>

    </div>

</div>

<script src="<?= URLROOT ?>/assets/js/admin.js"></script>

</body>
</html>
