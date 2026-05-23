<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

final class Auth extends Controller
{
    // Render the registration form.
    public function registerForm(): void
    {
        $this->render('auth/register', [
            'title' => 'Create account',
            'errors' => [],
            'old' => [],
        ], 'main');
    }

    // Create a customer account and log the user in immediately.
    public function register(): void
    {
        $old = [
            'first_name' => trim((string)($_POST['first_name'] ?? '')),
            'last_name'  => trim((string)($_POST['last_name'] ?? '')),
            'email'      => trim((string)($_POST['email'] ?? '')),
            'phone'      => trim((string)($_POST['phone'] ?? '')),
        ];
        $password = (string)($_POST['password'] ?? '');
        $confirm  = (string)($_POST['confirm_password'] ?? '');

        $errors = $this->validateRegister($old, $password, $confirm);

        $userModel = new User();

        if (!$errors && $userModel->emailExists($old['email'])) {
            $errors['email'] = 'This email is already registered.';
        }

        if ($errors) {
            $this->render('auth/register', [
                'title' => 'Create account',
                'errors' => $errors,
                'old' => $old,
            ], 'main');
            return;
        }

        $userId = $userModel->createCustomer([
            'email' => strtolower($old['email']),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'first_name' => $old['first_name'],
            'last_name' => $old['last_name'],
            'phone' => $old['phone'],
        ]);

        // Auto-login after register
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = 'CUSTOMER';
        $_SESSION['user_name'] = $old['first_name'];

        $redirect = $_SESSION['redirect_after_login'] ?? (URLROOT . '/account');
        unset($_SESSION['redirect_after_login']);

        header('Location: ' . $redirect);
        exit;
    }

    // Render the login form.
    public function loginForm(): void
    {
        $this->render('auth/login', [
            'title' => 'Login',
            'error' => null,
            'old' => [],
        ], 'main');
    }

    // Authenticate a user and send them to the right area of the site.
    public function login(): void
    {
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->render('auth/login', [
                'title' => 'Login',
                'error' => 'Email and password are required.',
                'old' => ['email' => $email],
            ], 'main');
            return;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        // Generic error message (don’t leak which field is wrong)
        if (!$user || empty($user['is_active']) || empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
            $this->render('auth/login', [
                'title' => 'Login',
                'error' => 'Invalid credentials.',
                'old' => ['email' => $email],
            ], 'main');
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_role'] = $user['role']; // 'CUSTOMER' or 'ADMIN'
        $_SESSION['user_name'] = $user['first_name'];

        $redirect = $_SESSION['redirect_after_login'] ?? null;
        unset($_SESSION['redirect_after_login']);

        if ($redirect) {
            header('Location: ' . $redirect);
            exit;
        }

        // Send admins to admin area, customers to account
        header('Location: ' . URLROOT . (($user['role'] === 'ADMIN') ? '/admin' : '/'));
        exit;
    }

    // Destroy the session and redirect to the storefront.
    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }

        session_destroy();

        header('Location: ' . URLROOT . '/');
        exit;
    }

    // Apply the basic validation rules for the registration form.
    private function validateRegister(array $old, string $password, string $confirm): array
    {
        $errors = [];

        if ($old['first_name'] === '' || mb_strlen($old['first_name']) < 2) {
            $errors['first_name'] = 'First name must be at least 2 characters.';
        }

        if ($old['last_name'] === '' || mb_strlen($old['last_name']) < 2) {
            $errors['last_name'] = 'Last name must be at least 2 characters.';
        }

        if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if (mb_strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($password !== $confirm) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        return $errors;
    }
}
