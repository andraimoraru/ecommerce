<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

$root = realpath(dirname(__DIR__));
if ($root === false) {
    throw new RuntimeException('Project root not found.');
}

$envFile = $root . '/.env';
if (!file_exists($envFile)) {
    throw new RuntimeException('.env not found at: ' . $envFile);
}

$dotenv = Dotenv\Dotenv::createImmutable($root);
$dotenv->safeLoad();

require_once __DIR__ . '/config/config.php';