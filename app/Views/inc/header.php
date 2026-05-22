<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= htmlspecialchars(SITENAME) ?></title>
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/storefront.css">

</head>

<body>
<?php
$cartCount = 0;

if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum(array_map('intval', $_SESSION['cart']));
}

$accountHref = URLROOT . '/login';

if (!empty($_SESSION['user_id'])) {
    $accountHref = (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN')
        ? URLROOT . '/admin'
        : URLROOT . '/account';
}
?>

<header class="site-header-legacy">
    <div class="site-header-legacy__inner">
    <div class="site-brand">
        <a href="<?= URLROOT ?>" class="link-reset">
            <?= SITENAME ?>
        </a>
    </div>

    <div class="site-header-legacy__actions">
        <a class="icon-link" href="<?= URLROOT ?>/products" aria-label="Search products">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7"></circle>
                <path d="M20 20l-3.5-3.5"></path>
            </svg>
        </a>
        <a class="icon-link" href="<?= htmlspecialchars($accountHref) ?>" aria-label="Account">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20 21a8 8 0 0 0-16 0"></path>
                <circle cx="12" cy="8" r="4"></circle>
            </svg>
        </a>
        <a class="icon-link" href="<?= URLROOT ?>/cart" aria-label="Cart">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="9" cy="20" r="1.5"></circle>
                <circle cx="18" cy="20" r="1.5"></circle>
                <path d="M3 4h2l2.2 10.2a1 1 0 0 0 1 .8h9.9a1 1 0 0 0 1-.8L21 7H7"></path>
            </svg>
            <?php if ($cartCount > 0): ?>
                <span class="cart-badge"><?= $cartCount > 99 ? '99+' : $cartCount ?></span>
            <?php endif; ?>
        </a>
    </div>

    <button class="menu-toggle" type="button" data-menu-toggle>Menu</button>
    </div>
    <div id="mobileMenu" class="mobile-nav">
    <a href="<?= URLROOT ?>/products">Search</a>
    <a href="<?= htmlspecialchars($accountHref) ?>">Account</a>
    <a href="<?= URLROOT ?>/cart">Cart<?= $cartCount > 0 ? ' (' . ($cartCount > 99 ? '99+' : $cartCount) . ')' : '' ?></a>
    <?php include __DIR__ . '/nav/router.php'; ?>
    </div>
</header>

<div class="legacy-container">

<script src="<?= URLROOT ?>/assets/js/storefront.js"></script>
