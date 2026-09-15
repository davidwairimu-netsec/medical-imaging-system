<?php
/**
 * Digital Medical Imaging Management System
 * Main Entry Point / Router
 */

// Define application root
define('APP_ROOT', __DIR__);

// Load configuration
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/constants.php';

// Autoload core classes
spl_autoload_register(function ($class) {
    $paths = [
        APP_ROOT . '/core/',
        APP_ROOT . '/models/',
        APP_ROOT . '/controllers/',
        APP_ROOT . '/middleware/',
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Start secure session
Session::start();

// CSRF verification for POST requests
CSRF::verify();

// Global error handler
set_exception_handler(function ($exception) {
    error_log('Uncaught exception: ' . $exception->getMessage() . 
              ' in ' . $exception->getFile() . ':' . $exception->getLine());

    if (APP_ENV === 'development') {
        echo '<pre>';
        echo 'Exception: ' . htmlspecialchars($exception->getMessage()) . "\n";
        echo 'File: ' . htmlspecialchars($exception->getFile()) . ':' . $exception->getLine() . "\n";
        echo 'Trace: ' . htmlspecialchars($exception->getTraceAsString());
        echo '</pre>';
    } else {
        http_response_code(500);
        require APP_ROOT . '/views/errors/500.php';
    }
    exit;
});

// Check authentication (except for login)
$publicPages = ['login'];

$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

if (!in_array($page, $publicPages, true) && !Auth::check()) {
    $redirect = $_SERVER['REQUEST_URI'] ?? '';
    header('Location: ' . BASE_URL . '/login.php' . ($redirect ? '?redirect=' . urlencode($redirect) : ''));
    exit;
}

// Route to controller
try {
    switch ($page) {
        case 'login':
            $controller = new AuthController();
            $controller->login();
            break;

        case 'logout':
            $controller = new AuthController();
            $controller->logout();
            break;

        case 'dashboard':
            $controller = new DashboardController();
            $controller->index();
            break;

        case 'patients':
            $controller = new PatientController();
            switch ($action) {
                case 'create':  $controller->create(); break;
                case 'edit':    $controller->edit(); break;
                case 'view':    $controller->view(); break;
                case 'history': $controller->history(); break;
                case 'archive': $controller->archive(); break;
                default:        $controller->index(); break;
            }
            break;

        case 'imaging':
            $controller = new ImagingController();
            switch ($action) {
                case 'upload':   $controller->upload(); break;
                case 'view':     $controller->view(); break;
                case 'serve':    $controller->serve(); break;
                case 'download': $controller->download(); break;
                case 'archive':  $controller->archive(); break;
                default:         $controller->index(); break;
            }
            break;

        case 'users':
            $controller = new UserController();
            switch ($action) {
                case 'create':       $controller->create(); break;
                case 'edit':         $controller->edit(); break;
                case 'toggleStatus': $controller->toggleStatus(); break;
                case 'resetPassword':$controller->resetPassword(); break;
                default:             $controller->index(); break;
            }
            break;

        case 'head_nurse':
            $c = new HeadNurseController();
            match($action) {
                'nurses'              => $c->nurses(),
                'createNurse'         => $c->createNurse(),
                'editNurse'           => $c->editNurse(),
                'assignments'         => $c->assignments(),
                'assignNursePatient'  => $c->assignNursePatient(),
                'assignNurseDoctor'   => $c->assignNurseDoctor(),
                'endAssignment'       => $c->endAssignment(),
                'workload'            => $c->workload(),
                'shifts'              => $c->shifts(),
                'createShift'         => $c->createShift(),
                'reports'             => $c->reports(),
                default                 => $c->index(),
            };
            break;

        case 'audit':
            $controller = new AuditController();
            $controller->index();
            break;

        case 'backups':
            $controller = new BackupController();
            switch ($action) {
                case 'create':   $controller->create(); break;
                case 'download': $controller->download(); break;
                default:         $controller->index(); break;
            }
            break;

        default:
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            break;
    }
} catch (Exception $e) {
    error_log('Routing error: ' . $e->getMessage());
    if (APP_ENV === 'development') {
        throw $e;
    }
    http_response_code(500);
    require APP_ROOT . '/views/errors/500.php';
}