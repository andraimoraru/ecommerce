<?php
declare(strict_types=1);

function env(string $key, $default = null) {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }
    $v = getenv($key);
    if ($v !== false && $v !== '') {
        return $v;
    }
    return $default;
}

define('DB_HOST', env('DB_HOST') ?? throw new RuntimeException('DB_HOST not set'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', ''));

define('APPROOT', dirname(__DIR__));
define('URLROOT', rtrim(env('APP_URL', 'http://localhost:8000'), '/'));
define('SITENAME', env('SITE_NAME', 'Modular E-commerce'));