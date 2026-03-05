<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= htmlspecialchars(SITENAME) ?></title>

<style>

body{
    margin:0;
    font-family: Arial, sans-serif;
}

/* NAVBAR */

.navbar{
    background:#111;
    color:#fff;
    padding:14px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.nav-brand{
    font-weight:bold;
    font-size:18px;
}

.nav-links{
    display:flex;
    gap:18px;
}

.nav-links a{
    color:white;
    text-decoration:none;
}

.nav-links a:hover{
    text-decoration:underline;
}

/* MOBILE MENU */

.hamburger{
    display:none;
    font-size:22px;
    cursor:pointer;
}

.mobile-menu{
    display:none;
    flex-direction:column;
    background:#111;
}

.mobile-menu a{
    padding:12px 20px;
    border-top:1px solid #333;
    color:white;
    text-decoration:none;
}

/* RESPONSIVE */

@media (max-width:768px){

    .nav-links{
        display:none;
    }

    .hamburger{
        display:block;
    }

    .mobile-menu.show{
        display:flex;
    }

}

.container{
    padding:25px;
}

</style>

</head>

<body>

<nav class="navbar">

    <div class="nav-brand">
        <a href="<?= URLROOT ?>" style="color:white;text-decoration:none;">
            <?= SITENAME ?>
        </a>
    </div>

    <div class="nav-links">
        <?php include __DIR__ . '/nav/router.php'; ?>
    </div>

    <div class="hamburger" onclick="toggleMenu()">☰</div>

</nav>

<div id="mobileMenu" class="mobile-menu">
    <?php include __DIR__ . '/nav/router.php'; ?>
</div>

<div class="container">

<script>
function toggleMenu(){
    document.getElementById("mobileMenu").classList.toggle("show");
}
</script>