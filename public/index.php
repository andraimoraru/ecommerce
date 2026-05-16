<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

session_start();

$router = new \App\Core\Router();

// Public
$router->get('/', 'Pages@index');
$router->get('/about', 'Pages@about');
$router->get('/products', 'Products@index');
$router->get('/products/{slug}', 'Products@show');

$router->get('/categories', 'Categories@index');
$router->get('/categories/{slug}', 'Categories@show');

$router->get('/products/{slug}', 'Products@show');

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

$router->get('/admin/products/{id}/edit', 'Admin\\Products@editForm', $adminMw);
$router->post('/admin/products/{id}', 'Admin\\Products@update', $adminMw);

$router->post('/admin/products/{id}/archive', 'Admin\\Products@archive', $adminMw);
$router->post('/admin/products/{id}/restore', 'Admin\\Products@restore', $adminMw);


$router->post('/admin/products/{id}/images/{imageId}/update', 'Admin\\ProductImages@update', $adminMw);
$router->post('/admin/products/{id}/images/{imageId}/delete', 'Admin\\ProductImages@delete', $adminMw);
$router->post('/admin/products/{id}/images/upload', 'Admin\\ProductImages@upload', $adminMw);

$router->get('/admin/orders', 'Admin\\Orders@index', $adminMw);
$router->get('/admin/orders/{id}', 'Admin\\Orders@show', $adminMw);
$router->get('/admin/orders/{id}/edit', 'Admin\\Orders@editForm', $adminMw);
$router->post('/admin/orders/{id}', 'Admin\\Orders@update', $adminMw);
$router->post('/admin/orders/{id}/status', 'Admin\\Orders@updateStatus', $adminMw);
$router->post('/admin/orders/{id}/shipping-label', 'Admin\\Orders@createShippingLabel', $adminMw);

$router->get('/cart', 'Cart@index');
$router->post('/cart/items', 'Cart@add');
$router->post('/cart/update', 'Cart@update');
$router->post('/cart/remove', 'Cart@remove');

$router->get('/checkout', 'Checkout@index');
$router->post('/checkout', 'Checkout@store');
$router->get('/checkout/success', 'Checkout@success');
$router->get('/checkout/cancel', 'Checkout@cancel');
$router->post('/payments/stripe/webhook', 'Webhooks@stripe');
$router->dispatch();
