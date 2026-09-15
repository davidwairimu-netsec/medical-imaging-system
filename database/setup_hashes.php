<?php
/**
 * Password Hash Generator
 * 
 * Run this script once after importing database.sql to set proper bcrypt
 * password hashes for the demo users.
 *
 * Usage (browser): http://localhost/System%202/database/setup_hashes.php
 * Usage (CLI):     php database/setup_hashes.php
 *
 * IMPORTANT: Delete this file after use, or move it outside the web root.
 */

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

$password = 'ChangeMe123!';
$users = ['admin', 'radiologist', 'technician', 'doctor', 'records'];

$isCli = (php_sapi_name() === 'cli');

if ($isCli) {
    echo "Password Hash Generator\n";
    echo "=======================\n\n";
    echo "Password: {$password}\n\n";
} else {
    echo "<pre>";
    echo "Password Hash Generator\n";
    echo "=======================\n\n";
    echo "Password: " . htmlspecialchars($password, ENT_QUOTES, 'UTF-8') . "\n\n";
}

echo "Run the following SQL statements in phpMyAdmin:\n\n";

foreach ($users as $username) {
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    $verified = password_verify($password, $hash) ? 'OK' : 'FAILED';

    if ($isCli) {
        echo "-- {$username} (verify: {$verified})\n";
        echo "UPDATE users SET password_hash = '{$hash}', account_status = 'active', failed_login_attempts = 0 WHERE username = '{$username}';\n\n";
    } else {
        $safeHash = htmlspecialchars($hash, ENT_QUOTES, 'UTF-8');
        $safeUser = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        echo "-- {$safeUser} (verify: {$verified})\n";
        echo "UPDATE users SET password_hash = '{$safeHash}', account_status = 'active', failed_login_attempts = 0 WHERE username = '{$safeUser}';\n\n";
    }
}

echo "\nNotes:\n";
echo "------\n";
echo "1. Copy each UPDATE statement into phpMyAdmin's SQL tab.\n";
echo "2. After running them, log in with any of the usernames above.\n";
echo "3. Password for all accounts is: {$password}\n";
echo "4. Delete this file after use to avoid exposing security internals.\n";

if (!$isCli) {
    echo "</pre>";
}
