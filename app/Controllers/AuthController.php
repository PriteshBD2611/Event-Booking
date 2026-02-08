<?php
/**
 * AuthController
 * Handles user authentication (login, register, logout)
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController extends Controller {
    private $userModel;

    public function __construct($pdo, $logger = null) {
        parent::__construct($pdo, $logger);
        $this->userModel = new User($pdo);
    }

    /**
     * Show login form
     */
    public function showLogin() {
        if (isLoggedIn()) {
            $this->redirect(env('APP_URL') . '/index.php');
        }
        $this->render('auth/login', [
            'pageTitle' => 'Login'
        ]);
    }

    /**
     * Handle login submission
     */
    public function login() {
        if (!$this->isPost()) {
            $this->redirect(env('APP_URL') . '/login.php');
        }

        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('Security token expired. Please try again.', 'error');
            $this->redirect(env('APP_URL') . '/login.php');
        }

        // Get and validate inputs
        $email = sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $this->setFlash('Please enter both email and password.', 'error');
            $this->redirect(env('APP_URL') . '/login.php');
        }

        // Attempt authentication
        $user = $this->userModel->authenticate($email, $password);

        if ($user) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();

            $this->setFlash("Welcome back, {$user['username']}!", 'success');

            // Redirect based on role
            if ($user['role'] === 'admin') {
                $this->redirect(env('APP_URL') . '/admin/dashboard.php');
            } else {
                $this->redirect(env('APP_URL') . '/index.php');
            }
        } else {
            $this->setFlash('Invalid email or password.', 'error');
            $this->redirect(env('APP_URL') . '/login.php');
        }
    }

    /**
     * Show registration form
     */
    public function showRegister() {
        if (isLoggedIn()) {
            $this->redirect(env('APP_URL') . '/index.php');
        }
        $this->render('auth/register', [
            'pageTitle' => 'Register'
        ]);
    }

    /**
     * Handle registration submission
     */
    public function register() {
        if (!$this->isPost()) {
            $this->redirect(env('APP_URL') . '/register.php');
        }

        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('Security token expired. Please try again.', 'error');
            $this->redirect(env('APP_URL') . '/register.php');
        }

        // Get and validate inputs
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'user';

        // Validate all fields
        if (!$username || !$email || !$password) {
            $this->setFlash('All fields are required.', 'error');
            $this->redirect(env('APP_URL') . '/register.php');
        }

        // Validate role
        if (!in_array($role, ['user', 'admin'])) {
            $role = 'user';
        }

        // Check password confirmation
        if ($password !== $confirmPassword) {
            $this->setFlash('Passwords do not match.', 'error');
            $this->redirect(env('APP_URL') . '/register.php');
        }

        // Validate password strength
        $passwordValidation = validatePassword($password);
        if (!$passwordValidation['valid']) {
            $this->setFlash($passwordValidation['message'], 'error');
            $this->redirect(env('APP_URL') . '/register.php');
        }

        // Check if email already exists
        if (emailExists($this->pdo, $email)) {
            $this->setFlash('Email already registered. Please login or use a different email.', 'error');
            $this->redirect(env('APP_URL') . '/register.php');
        }

        // Create user
        if ($this->userModel->create($username, $email, $password, $role)) {
            $this->setFlash('Registration successful! Please login with your credentials.', 'success');
            $this->redirect(env('APP_URL') . '/login.php');
        } else {
            $this->setFlash('Registration failed. Please try again later.', 'error');
            $this->redirect(env('APP_URL') . '/register.php');
        }
    }

    /**
     * Handle logout
     */
    public function logout() {
        $username = $_SESSION['username'] ?? 'Unknown';
        
        // Log logout
        if ($this->logger) {
            $this->logger->logAuth('logout', $username);
        }

        // Destroy session
        session_destroy();

        $this->setFlash('You have been logged out successfully.', 'success');
        $this->redirect(env('APP_URL') . '/index.php');
    }

    /**
     * Check if email is available (AJAX endpoint)
     */
    public function checkEmailAvailability() {
        if (!$this->isAjax()) {
            $this->json(['error' => 'Invalid request'], 400);
        }

        $email = sanitizeEmail($_POST['email'] ?? '');
        
        if (!$email) {
            $this->json(['available' => false, 'message' => 'Invalid email']);
        }

        $exists = emailExists($this->pdo, $email);
        $this->json([
            'available' => !$exists,
            'message' => $exists ? 'Email already registered' : 'Email available'
        ]);
    }

    /**
     * Show password reset form
     */
    public function showPasswordReset() {
        $this->render('auth/forgot-password', [
            'pageTitle' => 'Reset Password'
        ]);
    }

    /**
     * Handle password reset request
     */
    public function requestPasswordReset() {
        if (!$this->isPost()) {
            $this->redirect(env('APP_URL') . '/forgot-password.php');
        }

        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('Security token expired. Please try again.', 'error');
            $this->redirect(env('APP_URL') . '/forgot-password.php');
        }

        $email = sanitizeEmail($_POST['email'] ?? '');

        if (!$email) {
            $this->setFlash('Please enter a valid email.', 'error');
            $this->redirect(env('APP_URL') . '/forgot-password.php');
        }

        // Note: In production, send reset email
        // For now, just provide feedback
        $this->setFlash('If an account with that email exists, a password reset link has been sent.', 'info');
        $this->redirect(env('APP_URL') . '/login.php');
    }
}
?>
