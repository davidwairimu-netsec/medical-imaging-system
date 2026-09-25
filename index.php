<?php
/**
 * Digital Medical Imaging Management System
 * Main Entry Point / Router
 */

define('APP_ROOT', __DIR__);

require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/constants.php';

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

Session::start();
CSRF::verify();

set_exception_handler(function ($exception) {
    error_log('Uncaught exception: ' . $exception->getMessage() .
              ' in ' . $exception->getFile() . ':' . $exception->getLine());

    if (APP_ENV === 'development') {
        echo '<pre>';
        echo 'Exception: ' . htmlspecialchars($exception->getMessage()) . "\n";
        echo 'File: ' . htmlspecialchars($exception->getFile()) . ':' . $exception->getLine() . "\n";
        echo '</pre>';
    } else {
        http_response_code(500);
        require APP_ROOT . '/views/errors/500.php';
    }
    exit;
});

$publicPages = ['login', 'auth'];

$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

if (!in_array($page, $publicPages, true) && !Auth::check()) {
    $redirect = $_SERVER['REQUEST_URI'] ?? '';
    header('Location: ' . BASE_URL . '/login.php' . ($redirect ? '?redirect=' . urlencode($redirect) : ''));
    exit;
}

try {
    switch ($page) {

        case 'login':
            (new AuthController())->login();
            break;

        case 'logout':
            (new AuthController())->logout();
            break;

        case 'auth':
            $controller = new ForgotPasswordController();
            switch ($action) {
                case 'forgot': $controller->forgot(); break;
                case 'reset':  $controller->reset();  break;
                default:       $controller->forgot(); break;
            }
            break;

        case 'dashboard':
            (new DashboardController())->index();
            break;

        case 'patients':
            $controller = new PatientController();
            switch ($action) {
                case 'create':  $controller->create();  break;
                case 'edit':    $controller->edit();    break;
                case 'view':    $controller->view();    break;
                case 'history': $controller->history(); break;
                case 'archive': $controller->archive(); break;
                default:        $controller->index();   break;
            }
            break;

        case 'imaging':
            $controller = new ImagingController();
            switch ($action) {
                case 'upload':           $controller->upload();           break;
                case 'view':             $controller->view();             break;
                case 'serve':            $controller->serve();            break;
                case 'download':         $controller->download();         break;
                case 'archive':          $controller->archive();          break;
                case 'delete':           $controller->delete();           break;
                case 'requestDelete':    $controller->requestDelete();    break;
                case 'deletionRequests': $controller->deletionRequests(); break;
                case 'reviewDeletion':   $controller->reviewDeletion();   break;
                case 'recentlyDeleted':  $controller->recentlyDeleted();  break;
                case 'archived':         $controller->archived();         break;
                case 'restore':          $controller->restore();          break;
                default:                 $controller->index();            break;
            }
            break;

        case 'users':
            $controller = new UserController();
            switch ($action) {
                case 'create':        $controller->create();        break;
                case 'edit':          $controller->edit();          break;
                case 'toggleStatus':  $controller->toggleStatus();  break;
                case 'resetPassword': $controller->resetPassword(); break;
                default:              $controller->index();         break;
            }
            break;

        case 'audit':
            $controller = new AuditController();
            switch ($action) {
                case 'integrity':     $controller->integrity();     break;
                case 'fixIntegrity':  $controller->fixIntegrity();  break;
                case 'verifyChain':   $controller->verifyChainView(); break;
                default:              $controller->index();         break;
            }
            break;

        case 'backups':
            $controller = new BackupController();
            switch ($action) {
                case 'create':   $controller->create();   break;
                case 'download': $controller->download(); break;
                default:         $controller->index();    break;
            }
            break;

        case 'head_nurse':
            $controller = new HeadNurseController();
            switch ($action) {
                case 'nurses':             $controller->nurses();             break;
                case 'createNurse':        $controller->createNurse();        break;
                case 'editNurse':          $controller->editNurse();          break;
                case 'assignments':        $controller->assignments();        break;
                case 'assignNursePatient': $controller->assignNursePatient(); break;
                case 'assignNurseDoctor':  $controller->assignNurseDoctor();  break;
                case 'endAssignment':      $controller->endAssignment();      break;
                case 'workload':           $controller->workload();           break;
                case 'shifts':             $controller->shifts();             break;
                case 'createShift':        $controller->createShift();        break;
                case 'reports':            $controller->reports();            break;
                case 'createNurseLogin':   $controller->createNurseLogin();   break;
                case 'resetNursePassword': $controller->resetNursePassword(); break;
                default:                   $controller->index();              break;
            }
            break;

        case 'nurse':
            $controller = new NurseController();
            switch ($action) {
                case 'patient': $controller->patient(); break;
                default:        $controller->index();   break;
            }
            break;

        case 'profile':
            $controller = new ProfileController();
            switch ($action) {
                case 'changePassword': $controller->changePassword(); break;
                default:               $controller->changePassword(); break;
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
