<?php
/**
 * Login Page - Entry Point
 */
define('APP_ROOT', __DIR__);

require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/constants.php';

// Autoloader
spl_autoload_register(function ($class) {
    $paths = [
        APP_ROOT . '/core/',
        APP_ROOT . '/models/',
        APP_ROOT . '/controllers/',
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

Session::start();

if (Auth::check()) {
    header('Location: ' . BASE_URL . '/index.php?page=dashboard');
    exit;
}

$controller = new AuthController();
$controller->login();
