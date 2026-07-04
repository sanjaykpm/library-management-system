<?php
/**
 * Main Entry Point
 */

// Load basic helpers
require_once 'helpers/session_helper.php';
require_once 'helpers/redirect_helper.php';
require_once 'helpers/csrf_helper.php';

// Load Core classes (Simple Autoloader)
spl_autoload_register(function($className) {
    $paths = ['core/', 'models/', 'controllers/', 'middleware/'];
    foreach($paths as $path) {
        $file = __DIR__ . '/' . $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Load Config
require_once 'config/config.php';
require_once 'config/constants.php';
require_once 'config/database.php';

// Initialize App
$app = new App();
