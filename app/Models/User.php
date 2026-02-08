<?php
/**
 * User Model
 * Handles all user-related database operations
 */

require_once __DIR__ . '/Model.php';

class User extends Model {
    protected $table = 'users';
    protected $fillable = ['username', 'email', 'password', 'role', 'created_at'];

    /**
     * Create a new user
     * 
     * @param string $username
     * @param string $email
     * @param string $password (plain text, will be hashed)
     * @param string $role
     * @return bool
     */
    public function create($username, $email, $password, $role = 'user') {
        // Validate inputs
        if (!isValidEmail($email)) {
            logMessage("Invalid email attempted: $email", 'warning');
            return false;
        }
        
        $passwordValidation = validatePassword($password);
        if (!$passwordValidation['valid']) {
            logMessage("Weak password attempted for: $email", 'warning');
            return false;
        }
        
        // Check if email already exists
        if (emailExists($this->pdo, $email)) {
            logMessage("Duplicate email registration attempt: $email", 'warning');
            return false;
        }
        
        $hashedPassword = hashPassword($password);
        $data = [
            'username' => sanitizeInput($username),
            'email' => $email,
            'password' => $hashedPassword,
            'role' => in_array($role, ['user', 'admin']) ? $role : 'user',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $result = $this->insert($data);
            if ($result) {
                logMessage("New user registered: $email", 'info');
            }
            return $result;
        } catch (Exception $e) {
            logMessage("User creation error: " . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Authenticate user by email and password
     * 
     * @param string $email
     * @param string $password
     * @return array|bool
     */
    public function authenticate($email, $password) {
        $user = $this->findBy('email', $email);
        
        if (!$user) {
            logMessage("Login attempt with non-existent email: $email", 'warning');
            return false;
        }
        
        if (!verifyPassword($password, $user['password'])) {
            logMessage("Failed login attempt for: $email", 'warning');
            return false;
        }
        
        logMessage("Successful login: $email", 'info');
        return $user;
    }

    /**
     * Update user profile
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateProfile($id, $data) {
        $allowedFields = ['username', 'email'];
        $filteredData = array_intersect_key($data, array_flip($allowedFields));
        
        foreach ($filteredData as $key => $value) {
            $filteredData[$key] = sanitizeInput($value);
        }
        
        return $this->update($id, $filteredData);
    }

    /**
     * Change user password
     * 
     * @param int $id
     * @param string $oldPassword
     * @param string $newPassword
     * @return bool
     */
    public function changePassword($id, $oldPassword, $newPassword) {
        $user = $this->find($id);
        
        if (!$user || !verifyPassword($oldPassword, $user['password'])) {
            logMessage("Password change failed for user: $id (invalid old password)", 'warning');
            return false;
        }
        
        $passwordValidation = validatePassword($newPassword);
        if (!$passwordValidation['valid']) {
            return false;
        }
        
        $hashedPassword = hashPassword($newPassword);
        $result = $this->pdo->prepare("UPDATE {$this->table} SET password = ? WHERE id = ?")->execute([$hashedPassword, $id]);
        
        if ($result) {
            logMessage("Password changed for user: $id", 'info');
        }
        
        return $result;
    }

    /**
     * Get user with bookings
     * 
     * @param int $id
     * @return array|bool
     */
    public function getUserWithBookings($id) {
        $stmt = $this->pdo->prepare("
            SELECT u.*, COUNT(b.id) as total_bookings
            FROM {$this->table} u
            LEFT JOIN bookings b ON u.id = b.user_id
            WHERE u.id = ?
            GROUP BY u.id
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all admins
     * 
     * @return array
     */
    public function getAdmins() {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE role = 'admin'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
