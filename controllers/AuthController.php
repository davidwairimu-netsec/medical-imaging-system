<?php
/**
 * Authentication Controller
 */
class AuthController
{
            public function login(): void
    {
        // Already logged in → redirect
        if (Auth::check()) {
            header('Location: ' . BASE_URL . '/?page=dashboard');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF verification
            if (!CSRF::verify()) {
                $error = 'csrf';
                require APP_ROOT . '/views/auth/login.php';
                return;
            }

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($username === '' || $password === '') {
                $error = 'empty';
                require APP_ROOT . '/views/auth/login.php';
                return;
            }

            $result = Auth::attempt($username, $password);

            switch ($result['status']) {
                case 'success':
                    header('Location: ' . BASE_URL . '/?page=dashboard');
                    exit;

                case 'invalid_password':
                    $error = 'invalid_password';
                    break;

                case 'invalid_username':
                    $error = 'invalid_username';
                    break;

                case 'rate_limited':
                    $error = 'rate_limited';
                    break;

                default:
                    $error = 'generic';
                    break;
            }
        }

        require APP_ROOT . '/views/auth/login.php';
    }

    public function logout(): void
    {
        Auth::logout();
        Session::start();
        Session::flash('_logout_message', 'You have been logged out successfully.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}