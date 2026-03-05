<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= htmlspecialchars($data['title'] ?? SITENAME) ?></title>

<style>

body{
    margin:0;
    font-family:Arial, sans-serif;
}

/* NAVBAR */

.navbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:14px 20px;
    background:#111;
    color:white;
}

.nav-links{
    display:flex;
    gap:18px;
}

.nav-links a{
    color:white;
    text-decoration:none;
}

/* MOBILE */

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
    color:white;
    padding:12px 20px;
    text-decoration:none;
    border-top:1px solid #333;
}

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
padding:30px;
}

</style>

</head>

<body>

<nav class="navbar">

<div>
<a href="<?= URLROOT ?>" style="color:white;text-decoration:none;font-weight:bold;">
<?= SITENAME ?>
</a>
</div>

<div class="nav-links">
<?php require APPROOT . '/Views/inc/nav/router.php'; ?>
</div>

<div class="hamburger" onclick="toggleMenu()">☰</div>

</nav>

<div id="mobileMenu" class="mobile-menu">
<?php require APPROOT . '/Views/inc/nav/router.php'; ?>
</div>

<div class="container">

<?= $content ?>

</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

<script>

function toggleMenu(){
document.getElementById("mobileMenu").classList.toggle("show");
}

</script>

</body>
</html>