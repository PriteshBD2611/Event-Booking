<?php
/**
 * Helper Functions for Event Booking System
 * Contains common utility functions for validation, sanitization, and authentication
 */

/**
 * Load environment variables from .env file
 */
function loadEnv($filePath = __DIR__ . '/../.env') {
    if (!file_exists($filePath)) {
        throw new Exception("Environment file not found: $filePath");
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') === false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

/**
 * Get environment variable
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

/**
 * Validate email address
 * 
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * Requires at least 8 characters, 1 uppercase, 1 number, 1 special character
 * 
 * @param string $password
 * @return array ['valid' => bool, 'message' => string]
 */
function validatePassword($password) {
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => 'Password must be at least 8 characters long'];
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one uppercase letter'];
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one number'];
    }
    
    if (!preg_match('/[!@#$%^&*]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one special character (!@#$%^&*)'];
    }
    
    return ['valid' => true, 'message' => 'Password is strong'];
}

/**
 * Sanitize string input - prevents XSS attacks
 * 
 * @param string $input
 * @return string
 */
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Validate and sanitize email
 * 
 * @param string $email
 * @return string|bool
 */
function sanitizeEmail($email) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (isValidEmail($email)) {
        return $email;
    }
    return false;
}

/**
 * Validate integer input
 * 
 * @param mixed $input
 * @return int|bool
 */
function sanitizeInt($input) {
    $filtered = filter_var($input, FILTER_VALIDATE_INT);
    return $filtered !== false ? $filtered : false;
}

/**
 * Validate float/decimal input
 * 
 * @param mixed $input
 * @return float|bool
 */
function sanitizeFloat($input) {
    $filtered = filter_var($input, FILTER_VALIDATE_FLOAT);
    return $filtered !== false ? $filtered : false;
}

/**
 * Validate date format (YYYY-MM-DD)
 * 
 * @param string $date
 * @return bool
 */
function isValidDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Check if user is logged in
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * 
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . env('APP_URL') . '/login.php');
        exit();
    }
}

/**
 * Redirect to home if not admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . env('APP_URL') . '/index.php');
        exit();
    }
}

/**
 * Generate CSRF token
 * 
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token input field HTML
 * 
 * @return string
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

/**
 * Hash password using bcrypt
 * 
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 * 
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Validate file upload
 * 
 * @param array $file $_FILES array
 * @param array $allowedTypes
 * @param int $maxSize
 * @return array ['valid' => bool, 'message' => string, 'path' => string|null]
 */
function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'message' => 'File upload error'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'message' => 'File size exceeds maximum limit'];
    }
    
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, $allowedTypes)) {
        return ['valid' => false, 'message' => 'File type not allowed'];
    }
    
    return ['valid' => true, 'message' => 'File valid'];
}

/**
 * Save uploaded file
 * 
 * @param array $file $_FILES array
 * @param string $uploadDir
 * @return string|bool filename on success, false on failure
 */
function saveUploadedFile($file, $uploadDir) {
    $validation = validateFileUpload($file);
    
    if (!$validation['valid']) {
        return false;
    }
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }
    
    return false;
}

/**
 * Format date for display
 * 
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'M d, Y') {
    $timestamp = strtotime($date);
    return $timestamp ? date($format, $timestamp) : $date;
}

/**
 * Format currency
 * 
 * @param float $amount
 * @param string $currency
 * @return string
 */
function formatCurrency($amount, $currency = '$') {
    return $currency . number_format($amount, 2);
}

/**
 * Truncate string
 * 
 * @param string $string
 * @param int $length
 * @param string $append
 * @return string
 */
function truncateString($string, $length = 100, $append = '...') {
    if (strlen($string) <= $length) {
        return $string;
    }
    return substr($string, 0, $length) . $append;
}

/**
 * Get user by ID
 * 
 * @param PDO $pdo
 * @param int $userId
 * @return array|bool
 */
function getUserById($pdo, $userId) {
    $stmt = $pdo->prepare('SELECT id, username, email, role FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get user by email
 * 
 * @param PDO $pdo
 * @param string $email
 * @return array|bool
 */
function getUserByEmail($pdo, $email) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Check if email exists
 * 
 * @param PDO $pdo
 * @param string $email
 * @return bool
 */
function emailExists($pdo, $email) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Log message to file
 * 
 * @param string $message
 * @param string $level
 */
function logMessage($message, $level = 'info') {
    $logDir = env('LOG_PATH', __DIR__ . '/../logs/');
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

?>
