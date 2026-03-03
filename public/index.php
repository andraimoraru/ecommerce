<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

session_start();

$router = new \App\Core\Router();

// Public
$router->get('/', 'Pages@index');
$router->get('/about', 'Pages@about');

$router->get('/register', 'Auth@registerForm');
$router->post('/register', 'Auth@register');
$router->get('/login', 'Auth@loginForm');
$router->post('/login', 'Auth@login');
$router->post('/logout', 'Auth@logout');

// Account (protected)
$router->get('/account', 'Account\\Dashboard@index', [
    \App\Core\Middleware\AuthMiddleware::class
]);

// Admin (protected)
$adminMw = [
    \App\Core\Middleware\AuthMiddleware::class,
    \App\Core\Middleware\AdminMiddleware::class
];
$router->get('/admin', 'Admin\\Dashboard@index', $adminMw);


$router->get('/admin/categories', 'Admin\\Categories@index', $adminMw);
$router->get('/admin/categories/create', 'Admin\\Categories@createForm', $adminMw);
$router->post('/admin/categories', 'Admin\\Categories@store', $adminMw);

$router->get('/admin/products', 'Admin\\Products@index', $adminMw);
$router->get('/admin/products/create', 'Admin\\Products@createForm', $adminMw);
$router->post('/admin/products', 'Admin\\Products@store', $adminMw);

$router->dispatch();