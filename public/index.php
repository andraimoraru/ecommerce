<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
session_start();

$router = new \App\Core\Router();

// Public
$router->get('/', 'Pages@index');
$router->get('/about', 'Pages@about');


$router->dispatch();