<?php


// Composer autoload (PSR-4)
require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Load Config
require_once __DIR__ . '/config/config.php';