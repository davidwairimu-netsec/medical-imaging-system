<?php
/**
 * Logout Handler
 */
define('APP_ROOT', __DIR__);
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/constants.php';

spl_autoload_register(function ($class) {
    $paths = [APP_ROOT . '/core/', APP_ROOT . '/models/', APP_ROOT . '/controllers/'];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

Session::start();

$controller = new AuthController();
$controller->logout();