<?php

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN';

if(!$isLoggedIn){
    require __DIR__ . '/guest.php';
    return;
}

if($isAdmin){
    require __DIR__ . '/admin.php';
    return;
}

require __DIR__ . '/user.php';