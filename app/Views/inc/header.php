<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= htmlspecialchars(SITENAME) ?></title>
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/storefront.css">

</head>

<body>

<header class="site-header-legacy">
    <div class="site-header-legacy__inner">
    <div class="site-brand">
        <a href="<?= URLROOT ?>" class="link-reset">
            <?= SITENAME ?>
        </a>
    </div>

    <div class="site-nav">
        <?php include __DIR__ . '/nav/router.php'; ?>
    </div>

    <button class="menu-toggle" type="button" data-menu-toggle>Menu</button>
    </div>
    <div id="mobileMenu" class="mobile-nav">
    <?php include __DIR__ . '/nav/router.php'; ?>
    </div>
</header>

<div class="legacy-container">

<script src="<?= URLROOT ?>/assets/js/storefront.js"></script>
