<?php
/**
 * Authentication Controller
 */
class AuthController
{
    public function login(): void
    {
        // Redirect if already logged in
        if (Auth::check()) {
            header('Location: ' . BASE_URL . '/index.php?page=dashboard');
            exit;
        }

        $error = null;
        $timeoutMessage = Session::flash('_timeout_message');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validation
            if (empty($username) || empty($password)) {
                $error = 'Please enter both username and password.';
            } else {
                $result = Auth::attempt($username, $password);

                if ($result['success']) {
                    // Role-based redirection
                    $redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
                    if ($redirect && str_starts_with($redirect, '/')) {
                        header('Location: ' . $redirect);
                    } else {
                        header('Location: ' . BASE_URL . '/index.php?page=dashboard');
                    }
                    exit;
                } else {
                    $error = $result['message'];
                }
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