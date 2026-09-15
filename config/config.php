<?php
/**
 * Application Configuration
 * Digital Medical Imaging Management System
 */

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Application settings
define('APP_NAME', 'MediImaging DMS');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development | production

// ------------------------------------------------------------
// BASE URL — Auto-detected to work with ANY folder name
// (including "System 2" with a space, or any other name)
// ------------------------------------------------------------
if (!defined('BASE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Path of the entry script relative to web root
    // e.g. "/System 2/index.php" -> "/System 2"
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath   = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    define('BASE_URL', $scheme . '://' . $host . $basePath);
}

// Session settings
define('SESSION_LIFETIME', 1800);
define('SESSION_NAME', 'MEDIMG_SESSION');

// Security settings
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900);
define('PASSWORD_MIN_LENGTH', 8);

// File upload settings
define('UPLOAD_DIR', APP_ROOT . '/uploads/medical_images/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png']);
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png']);

// Backup settings
define('BACKUP_DIR', APP_ROOT . '/backups/');

// Pagination
define('RECORDS_PER_PAGE', 15);

date_default_timezone_set('Africa/Nairobi');

if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', APP_ROOT . '/logs/error.log');
}