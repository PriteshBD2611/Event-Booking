# MVC Refactoring Guide for Event Booking System

## Overview
This document provides a comprehensive guide to the refactored Event Booking System with MVC pattern, PDO, environment configuration, and logging.

## What Has Been Added

### 1. **Environment Configuration (.env)**
- Located at: `.env`
- Contains all configuration variables (database, app settings, logging, security)
- Never commit this file to version control
- Create your own `.env` file with your actual credentials

**Usage:**
```php
require_once 'includes/helpers.php';
loadEnv();
$dbHost = env('DB_HOST');
$debugMode = env('APP_DEBUG') === 'true';
```

### 2. **Helper Functions (includes/helpers.php)**

#### Input Validation & Sanitization
```php
// Email validation
isValidEmail($email);                    // Returns bool
sanitizeEmail($email);                   // Returns sanitized email or false
sanitizeInput($input);                   // XSS prevention

// Password handling
validatePassword($password);             // Checks strength, returns array
hashPassword($password);                 // BCrypt hashing
verifyPassword($password, $hash);        // Password verification

// Number validation
sanitizeInt($input);                     // Returns int or false
sanitizeFloat($input);                   // Returns float or false

// Date validation
isValidDate($date);                      // Validates YYYY-MM-DD format
```

#### Authentication & Authorization
```php
isLoggedIn();                            // Check if user is authenticated
isAdmin();                               // Check if user is admin
requireLogin();                          // Redirect if not logged in
requireAdmin();                          // Redirect if not admin
```

#### CSRF Protection
```php
generateCSRFToken();                     // Generate/retrieve CSRF token
verifyCSRFToken($token);                 // Verify CSRF token
csrfField();                             // HTML hidden field for forms
```

#### File Operations
```php
validateFileUpload($file, $allowedTypes, $maxSize);
saveUploadedFile($file, $uploadDir);
```

#### User Database Operations
```php
getUserById($pdo, $userId);
getUserByEmail($pdo, $email);
emailExists($pdo, $email);
```

#### Logging
```php
logMessage($message, $level);            // Log to file
```

---

### 3. **Database Configuration (config/db.php)**

**Before:**
```php
$conn = mysqli_connect($servername, $username, $password, $dbname);
```

**After (PDO):**
```php
require_once 'includes/helpers.php';
loadEnv();

$conn = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
```

**Benefits:**
- Prepared statements prevent SQL injection
- PDO supports multiple database types
- Better error handling
- Configuration from environment variables

---

### 4. **MVC Directory Structure**

```
app/
├── Models/
│   ├── Model.php          # Base model class
│   ├── User.php           # User operations
│   ├── Event.php          # Event operations
│   └── Booking.php        # Booking operations
├── Controllers/
│   ├── Controller.php      # Base controller
│   ├── AuthController.php  # Authentication logic
│   ├── EventController.php # Event management
│   └── BookingController.php # Booking management
└── Views/                  # To be created for templates
    ├── auth/
    │   ├── login.php
    │   ├── register.php
    │   └── forgot-password.php
    ├── admin/
    │   ├── create-event.php
    │   └── edit-event.php
    ├── bookings/
    │   ├── select-seat.php
    │   ├── confirm.php
    │   └── my-bookings.php
    └── events/
        ├── index.php
        ├── view.php
        └── search.php
```

---

### 5. **Models (CRUD Operations)**

#### Base Model Class
All models extend this class and get these methods automatically:

```php
$model = new User($pdo);

// Find operations
$model->find($id);                       // Get by ID
$model->findBy($column, $value);         // Get by column
$model->getAll($limit, $offset);         // Get all with pagination
$model->count();                         // Count total records

// Create/Update/Delete
$model->insert($data);                   // Insert record
$model->update($id, $data);              // Update record
$model->delete($id);                     // Delete record

// Transactions
$model->beginTransaction();
$model->commit();
$model->rollback();
```

#### User Model
```php
$user = new User($pdo);

// Create user with validation
$user->create($username, $email, $password, $role);

// Authenticate
$userData = $user->authenticate($email, $password);

// Update profile
$user->updateProfile($userId, $data);

// Change password
$user->changePassword($userId, $oldPassword, $newPassword);

// Get user with stats
$user->getUserWithBookings($userId);

// Get all admins
$user->getAdmins();
```

#### Event Model
```php
$event = new Event($pdo);

// Create event
$eventId = $event->createEvent($data, $userId);

// Retrieve events
$event->getEventWithCreator($eventId);
$event->getByCategory($category, $limit, $offset);
$event->search($keyword, $limit, $offset);
$event->getUpcoming($limit, $offset);

// Update/Delete
$event->updateEvent($eventId, $data, $userId);
$event->deleteEvent($eventId, $userId);

// Get user's events
$event->getByCreator($userId);
```

#### Booking Model
```php
$booking = new Booking($pdo);

// Create booking
$bookingId = $booking->createBooking($userId, $eventId, $seat, $status);

// Retrieve bookings
$booking->getUserBookings($userId);
$booking->getEventBookings($eventId);
$booking->getBookingDetails($bookingId);

// Update payment
$booking->updatePaymentStatus($bookingId, $status);

// Cancel booking
$booking->cancelBooking($bookingId, $userId);

// Bulk operations
$booking->bulkCreate($userId, $eventId, $qty, $seat, $status);

// Statistics
$booking->getEventStats($eventId);
```

---

### 6. **Controllers (Business Logic)**

#### Base Controller
Common functionality for all controllers:

```php
class AuthController extends Controller {
    public function __construct($pdo, $logger) {
        parent::__construct($pdo, $logger);
    }
}
```

**Available Methods:**
```php
// View rendering
$this->render('view/path', $data);

// Redirects
$this->redirect($url);

// JSON responses
$this->json(['key' => 'value'], 200);

// Request checking
$this->isPost();
$this->isGet();
$this->isAjax();
$this->getMethod();

// Flash messages
$this->setFlash('Message', 'success');
$flash = $this->getFlash();
```

#### AuthController
```php
$auth = new AuthController($pdo, $logger);

// Authentication
$auth->login();          // Handle login
$auth->register();       // Handle registration
$auth->logout();         // Handle logout

// Utilities
$auth->showLogin();      // Show login form
$auth->showRegister();   // Show register form
$auth->checkEmailAvailability();  // AJAX endpoint
```

#### EventController
```php
$event = new EventController($pdo, $logger);

// CRUD Operations
$event->create();
$event->update();
$event->delete();

// Retrieval
$event->index();         // List events
$event->view();          // View single event
$event->search();        // Search events
$event->filterByCategory();  // Filter by category

// Forms
$event->showCreate();
$event->showEdit();
```

#### BookingController
```php
$booking = new BookingController($pdo, $logger);

// Booking operations
$booking->processBooking();
$booking->cancelBooking();

// Display
$booking->showSeatSelection();
$booking->showConfirmation();
$booking->myBookings();
$booking->viewBooking();

// Admin features
$booking->getEventStats();
$booking->exportBookings();
```

---

### 7. **Logging System (includes/Logger.php)**

#### Basic Usage
```php
$logger = new Logger($logDir);

$logger->log($message, $level, $context);
$logger->debug($message, $context);
$logger->info($message, $context);
$logger->warning($message, $context);
$logger->error($message, $context);
$logger->critical($message, $context);
```

#### Specialized Logging
```php
// Security events
$logger->security('Suspicious login attempt', ['email' => $email]);

// Authentication events
$logger->logAuth('login', $userEmail, ['ip' => $ipAddress]);

// Database queries
$logger->logQuery($query, $params, $executionTime);
```

#### Log Management
```php
// Retrieve logs
$logs = $logger->getLogs($date, $level);

// Clean old logs
$deleted = $logger->clearOldLogs($daysOld = 30);
```

**Log Files Structure:**
```
logs/
├── 2024-01-15-debug.log
├── 2024-01-15-info.log
├── 2024-01-15-warning.log
├── 2024-01-15-error.log
└── debug.log (combined for development)
```

---

## Migration Guide: Converting Old Code to New System

### Before (MySQLi with SQL Injection vulnerabilities):
```php
<?php
include 'config/db.php';

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email='$email'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    header("Location: index.php");
} else {
    echo "Invalid Email or Password!";
}
?>
```

### After (PDO with Controllers and Models):
```php
<?php
session_start();
require_once 'config/db.php';
require_once 'includes/helpers.php';
require_once 'includes/Logger.php';
require_once 'app/Controllers/AuthController.php';

$logger = new Logger();
$auth = new AuthController($conn, $logger);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->login();
}

$auth->showLogin();
?>
```

---

## Security Improvements

### 1. **SQL Injection Prevention**
- All database queries use prepared statements
- User input is parameterized

### 2. **XSS (Cross-Site Scripting) Prevention**
- All user input is sanitized using `htmlspecialchars()`
- CSRF tokens protect forms

### 3. **CSRF Protection**
- All forms include CSRF tokens
- Server verifies tokens before processing

### 4. **Password Security**
- Passwords use BCrypt hashing (cost = 12)
- Password strength validation enforced
- Old passwords verified before changing

### 5. **File Upload Security**
- File type validation
- File size limits
- Unique filenames with timestamps

### 6. **Access Control**
- Authentication checks (requireLogin)
- Authorization checks (requireAdmin)
- Authorization checks in models

---

## Implementation Steps

### Step 1: Set Up Environment
1. Copy `.env` file from repo
2. Update `.env` with your credentials
3. Ensure `logs/` directory exists

### Step 2: Update Database Connection
1. Existing `config/db.php` now uses PDO
2. All mysqli code automatically uses new connection
3. Test database connectivity

### Step 3: Refactor Page by Page

#### Login Page (login.php)
```php
<?php
session_start();
require_once 'config/db.php';
require_once 'includes/helpers.php';
require_once 'includes/Logger.php';
require_once 'app/Controllers/AuthController.php';

$logger = new Logger();
$auth = new AuthController($conn, $logger);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->login();
}

$auth->showLogin();
?>
```

#### Register Page (register.php)
```php
<?php
session_start();
require_once 'config/db.php';
require_once 'includes/helpers.php';
require_once 'includes/Logger.php';
require_once 'app/Controllers/AuthController.php';

$logger = new Logger();
$auth = new AuthController($conn, $logger);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->register();
}

$auth->showRegister();
?>
```

### Step 4: Create View Templates
Create view files in `app/Views/` matching the controller render calls.

---

## Best Practices

### 1. Always Validate Input
```php
$email = sanitizeEmail($_POST['email'] ?? '');
$age = sanitizeInt($_POST['age'] ?? 0);
$price = sanitizeFloat($_POST['price'] ?? 0);
```

### 2. Always Use Prepared Statements
```php
// ✅ Good
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// ❌ Never do this
$sql = "SELECT * FROM users WHERE email = '$email'";
```

### 3. Always Log Important Events
```php
$logger->info("User registered", ['email' => $email]);
$logger->security("Failed login attempt", ['email' => $email]);
```

### 4. Always Verify User Authorization
```php
// Check ownership
$event = $eventModel->find($eventId);
if ($event['created_by'] !== $_SESSION['user_id']) {
    $logger->warning("Unauthorized access attempt");
    return false;
}
```

### 5. Always Use CSRF Tokens
```php
// In forms
<?php echo csrfField(); ?>

// In controller
if (!verifyCSRFToken($_POST['csrf_token'])) {
    $this->setFlash('Invalid token', 'error');
    return;
}
```

---

## Testing

### Test Login
1. Navigate to `/login.php`
2. Use test credentials
3. Check `logs/` folder for login events

### Test Registration
1. Navigate to `/register.php`
2. Try weak password (should fail)
3. Complete registration
4. Check logs for registration event

### Test Booking
1. Login as user
2. Navigate to event
3. Book tickets
4. Check database for booking record
5. Check logs for booking event

---

## Troubleshooting

### Database Connection Fails
- Check `.env` credentials
- Verify MySQL is running
- Check `logs/error.log` for details

### CSRF Token Invalid
- Ensure session is started before rendering form
- Check token generation/verification in helpers

### File Upload Fails
- Check `uploads/` directory permissions
- Verify file size in `.env`
- Check file type in validation

---

## Next Steps

1. **Email Verification**: Add email verification during registration
2. **Password Reset**: Implement forgot password flow
3. **Rate Limiting**: Add rate limiting on login attempts
4. **API Layer**: Build REST API for mobile apps
5. **Payment Integration**: Add payment gateway integration
6. **Notifications**: Add email/SMS notifications
7. **Analytics**: Add user behavior tracking

---

## Additional Resources

- PDO Documentation: https://www.php.net/manual/en/book.pdo.php
- OWASP Security: https://owasp.org/www-project-top-ten/
- PHP Password Security: https://www.php.net/manual/en/function.password-hash.php

