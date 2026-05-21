<?php
$pageTitle = $data['title'] ?? SITENAME;
$pageStyles = $data['styles'] ?? [];
$bodyClass = $data['body_class'] ?? '';
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

        <nav class="site-nav">
            <?php require APPROOT . '/Views/inc/nav/router.php'; ?>
            <a href="<?= URLROOT ?>/cart">Cart</a>
        </nav>

        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobileMenu" data-menu-toggle>Menu</button>
    </div>

    <nav id="mobileMenu" class="mobile-nav" aria-hidden="true">
        <?php require APPROOT . '/Views/inc/nav/router.php'; ?>
        <a href="<?= URLROOT ?>/cart">Cart</a>
    </nav>
</header>

<main class="page-shell">
    <?= $content ?>
</main>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
<script src="<?= URLROOT ?>/assets/js/storefront.js"></script>

</body>
</html>
