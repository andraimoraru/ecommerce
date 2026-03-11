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

.product-page{
    margin-top: 10px;
}

.product-page-grid{
    display:grid;
    grid-template-columns: 1.2fr 1fr;
    gap:30px;
    align-items:start;
    margin-bottom:30px;
}

.product-main-image-wrap{
    width:100%;
    aspect-ratio:1 / 1;
    background:#f1f1f1;
    border-radius:12px;
    overflow:hidden;
    border:1px solid #e6e6e6;
}

.product-main-image{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

.product-thumb-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(90px, 1fr));
    gap:12px;
    margin-top:14px;
}

.product-thumb-wrap{
    aspect-ratio:1 / 1;
    border-radius:10px;
    overflow:hidden;
    background:#f1f1f1;
    border:1px solid #e6e6e6;
}

.product-thumb{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

.product-page-title{
    margin-top:0;
    margin-bottom:10px;
    font-size:32px;
    line-height:1.2;
}

.product-page-price{
    font-size:24px;
    font-weight:bold;
    margin:0 0 18px 0;
}

.product-page-summary{
    color:#555;
    line-height:1.6;
    margin-bottom:24px;
}

.product-add-form{
    margin-top:20px;
}

.qty-input{
    width:90px;
    padding:8px;
    border:1px solid #ddd;
    border-radius:8px;
}

.product-html-card{
    border:1px solid #e6e6e6;
    border-radius:12px;
    padding:20px;
    background:#fff;
}

.product-html-content{
    line-height:1.7;
    color:#333;
}

.product-html-content h2,
.product-html-content h3,
.product-html-content h4{
    margin-top:1.2em;
}

.product-html-content p{
    margin-bottom:1em;
}

.product-html-content ul,
.product-html-content ol{
    padding-left:20px;
}

@media (max-width: 900px){
    .product-page-grid{
        grid-template-columns:1fr;
    }
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
.cart-layout{
    display:grid;
    grid-template-columns: 2fr 1fr;
    gap:24px;
    align-items:start;
}

.cart-main{
    display:flex;
    flex-direction:column;
    gap:20px;
}

.cart-sidebar{
    position:sticky;
    top:20px;
}

.cart-card{
    background:#fff;
    border:1px solid #e6e6e6;
    border-radius:12px;
    padding:18px;
}

.cart-item{
    display:flex;
    gap:16px;
    padding:16px 0;
    border-bottom:1px solid #eee;
}

.cart-item:last-child{
    border-bottom:none;
}

.cart-item-image-wrap{
    width:90px;
    height:90px;
    border-radius:10px;
    overflow:hidden;
    background:#f1f1f1;
    flex-shrink:0;
}

.cart-item-image{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

.cart-item-placeholder{
    width:100%;
    height:100%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:12px;
    color:#999;
}

.cart-item-details{
    flex:1;
}

.small-btn{
    margin-top:6px;
    padding:8px 12px;
    background:#111;
    color:#fff;
    border:none;
    border-radius:8px;
    cursor:pointer;
}

.danger-btn{
    background:#8b1e1e;
}

.checkout-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
}

@media (max-width: 900px){
    .cart-layout{
        grid-template-columns:1fr;
    }

    .cart-sidebar{
        position:static;
    }

    .checkout-grid{
        grid-template-columns:1fr;
    }
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