<?php
declare(strict_types=1);

// Database Config (from .env)
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'modular_ecom');

// App Root (points to /app)
define('APPROOT', dirname(__DIR__));

// URL Root (important for XAMPP folder setup)
define('URLROOT', rtrim($_ENV['APP_URL'] ?? 'http://localhost/ecommerce', '/'));

// Site Name
define('SITENAME', $_ENV['SITE_NAME'] ?? 'Modular E-commerce');