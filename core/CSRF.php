<?php
/**
 * CSRF Protection
 */
class CSRF
{
    private const TOKEN_KEY = '_csrf_token';
    private const TOKEN_EXPIRY = 3600; // 1 hour

    public static function generateToken(): string
    {
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
            $_SESSION[self::TOKEN_KEY . '_time'] = time();
        }

        // Regenerate if expired
        if (time() - ($_SESSION[self::TOKEN_KEY . '_time'] ?? 0) > self::TOKEN_EXPIRY) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
            $_SESSION[self::TOKEN_KEY . '_time'] = time();
        }

        return $_SESSION[self::TOKEN_KEY];
    }

    public static function getToken(): string
    {
        return self::generateToken();
    }

    public static function validateToken(?string $token): bool
    {
        if (empty($token) || empty($_SESSION[self::TOKEN_KEY])) {
            return false;
        }

        return hash_equals($_SESSION[self::TOKEN_KEY], $token);
    }

    public static function field(): string
    {
        $token = self::generateToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verify CSRF token from POST request
     */
    public static function verify(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

            if (!self::validateToken($token)) {
                http_response_code(403);
                if (self::isAjax()) {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Invalid security token. Please refresh and try again.']);
                } else {
                    die('Security token validation failed. Please go back and try again.');
                }
                exit;
            }
        }
    }

    private static function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}