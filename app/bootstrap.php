<?php
// Load Config
require_once 'config/config.php';


//Autoload Core Classes
spl_autoload_register(function($className) {
    require_once 'Core/' . $className . '.php';
});

require_once dirname(__DIR__) . '/vendor/autoload.php';