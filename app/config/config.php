<?php
declare(strict_types=1);

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ?: '');

define('APPROOT', dirname(__DIR__));

define('URLROOT', rtrim(getenv('APP_URL') ?: 'http://localhost:8000', '/'));
define('SITENAME', getenv('SITE_NAME') ?: 'Modular E-commerce');