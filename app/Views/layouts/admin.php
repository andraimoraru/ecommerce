<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= htmlspecialchars($data['title'] ?? SITENAME) ?></title>

<style>
/* Base */
body{
  margin:0;
  font-family:Arial, sans-serif;
  background:#f6f6f6;
}

/* Layout */
.admin-layout{
  display:flex;
  min-height:100vh;
}

/* Sidebar */
.sidebar{
  width:240px;
  background:#111;
  color:#fff;
  padding-top:20px;
}

.sidebar h2{
  padding:0 20px;
  margin:0 0 12px 0;
}

.sidebar a{
  display:block;
  padding:12px 20px;
  color:#fff;
  text-decoration:none;
}

.sidebar a:hover{
  background:#333;
}

/* Content */
.admin-content{
  flex:1;
  padding:30px;
}

/* Mobile toggle */
.menu-toggle{
  display:none;
  background:#111;
  color:#fff;
  padding:12px 16px;
  cursor:pointer;
}

@media (max-width:768px){
  .sidebar{
    position:fixed;
    left:-240px;
    top:0;
    height:100%;
    transition:0.25s;
    z-index:999;
  }
  .sidebar.show{ left:0; }
  .menu-toggle{ display:block; }
  .admin-content{ padding:18px; }
}

/* Your existing UI helpers */
.card{
  background:#fff;
  border:1px solid #e6e6e6;
  border-radius:12px;
  padding:16px;
  margin-bottom:16px;
}

.grid-2{
  display:grid;
  grid-template-columns: 2fr 1fr;
  gap:16px;
}

@media (max-width:900px){
  .grid-2{ grid-template-columns: 1fr; }
}

.btn{
  display:inline-block;
  padding:10px 14px;
  border-radius:8px;
  text-decoration:none;
  border:1px solid #ddd;
  background:#111;
  color:#fff;
}

.btn.secondary{
  background:#fff;
  color:#111;
}
</style>
</head>

<body>

<div class="menu-toggle" onclick="toggleSidebar()">
☰ Admin Menu
</div>

<div class="admin-layout">

    <?php include APPROOT . '/Views/inc/admin_sidebar.php'; ?>

    <div class="admin-content">

        <?= $content ?>

    </div>

</div>

<script>

function toggleSidebar(){
    document.querySelector('.sidebar').classList.toggle('show');
}

</script>

</body>
</html>