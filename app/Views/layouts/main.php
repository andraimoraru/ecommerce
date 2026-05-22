<?php
$pageTitle = $data['title'] ?? SITENAME;
$pageStyles = $data['styles'] ?? [];
$bodyClass = $data['body_class'] ?? '';
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
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars((string)$pageTitle) ?></title>
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/storefront.css">
<?php foreach ($pageStyles as $style): ?>
    <link rel="stylesheet" href="<?= URLROOT ?>/assets/css/<?= htmlspecialchars((string)$style) ?>.css">
<?php endforeach; ?>
</head>
<body class="<?= htmlspecialchars((string)$bodyClass) ?>">

<div class="announcement-bar">Free fast delivery in the UK - next working day.</div>

<header class="site-header">
    <div class="site-header__inner">
        <a class="site-brand" href="<?= URLROOT ?>">
            <span class="site-brand__mark">B</span>
            <span class="site-brand__text"><?= htmlspecialchars((string)SITENAME) ?></span>
        </a>

        <div class="site-header__actions">
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

        <nav id="mobileMenu" class="mobile-nav" aria-hidden="true">
            <a href="<?= URLROOT ?>/products">Search</a>
            <a href="<?= htmlspecialchars($accountHref) ?>">Account</a>
            <a href="<?= URLROOT ?>/cart">Cart<?= $cartCount > 0 ? ' (' . ($cartCount > 99 ? '99+' : $cartCount) . ')' : '' ?></a>
            <?php require APPROOT . '/Views/inc/nav/router.php'; ?>
        </nav>

        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobileMenu" data-menu-toggle>Menu</button>
    </div>
</header>

<main class="page-shell">
    <?= $content ?>
</main>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
<script src="<?= URLROOT ?>/assets/js/storefront.js"></script>

</body>
</html>
