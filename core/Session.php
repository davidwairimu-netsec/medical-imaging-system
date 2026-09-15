<?php
/**
 * Secure Session Management
 */
class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Secure session configuration
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_samesite', 'Strict');

            if (APP_ENV === 'production') {
                ini_set('session.cookie_secure', 1);
            }

            session_name(SESSION_NAME);
            session_start();

            // Regenerate session ID periodically
            if (!isset($_SESSION['_last_regeneration'])) {
                $_SESSION['_last_regeneration'] = time();
            } elseif (time() - $_SESSION['_last_regeneration'] > 300) {
                session_regenerate_id(true);
                $_SESSION['_last_regeneration'] = time();
            }

            // Session timeout check
            if (isset($_SESSION['_last_activity'])) {
                if (time() - $_SESSION['_last_activity'] > SESSION_LIFETIME) {
                    self::destroy();
                    session_start();
                    $_SESSION['_timeout_message'] = 'Your session has expired. Please log in again.';
                }
            }
            $_SESSION['_last_activity'] = time();
        }
    }

    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Flash messages
     */
    public static function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }

        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }
}