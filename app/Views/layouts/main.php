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

.product-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));
    gap:20px;
}

.product-card{
    border:1px solid #e6e6e6;
    border-radius:12px;
    padding:16px;
    background:#fff;
}

.product-image-wrap{
    width:70%;
    aspect-ratio:1 / 1;
    overflow:hidden;
    border-radius:10px;
    background:#f1f1f1;
    margin-bottom:12px;
}

.product-image{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

.product-placeholder{
    width:100%;
    height:100%;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#999;
    font-size:14px;
}

.product-title{
    margin:0 0 8px 0;
    font-size:18px;
}

.product-desc{
    color:#666;
    font-size:14px;
    min-height:44px;
}

.product-price{
    font-weight:bold;
    margin:12px 0;
}

.add-cart-btn{
    width:100%;
    padding:10px 14px;
    background:#111;
    color:#fff;
    border:none;
    border-radius:8px;
    cursor:pointer;
}

.add-cart-btn:hover{
    background:#333;
}

.category-header{
    margin-bottom: 28px;
    padding: 20px;
    border: 1px solid #e6e6e6;
    border-radius: 12px;
    background: #fafafa;
}

.category-title{
    margin: 0 0 8px 0;
    font-size: 32px;
    line-height: 1.2;
}

.category-subtitle{
    margin: 0;
    color: #666;
    font-size: 15px;
}

.category-back{
    display: inline-block;
    margin-bottom: 20px;
    color: #111;
    text-decoration: none;
    font-size: 14px;
}

.category-back:hover{
    text-decoration: underline;
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