<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<div class="card">
    <h2>Create an Account</h2>
    
    <form method="POST" action="register.php">
        <div class="form-group">
            <label for="username">Full Name</label>
            <input type="text" id="username" name="username" placeholder="Enter your full name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Minimum 8 characters with uppercase, number, and special character" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
        </div>
        
        <div class="form-group">
            <label for="role">Account Type</label>
            <select id="role" name="role" style="width: 100%; padding: 10px; background: #111827; color: #e5e7eb; border: 1px solid #9CA3AF; border-radius: 4px;">
                <option value="user">I'm a Student/User</option>
                <option value="admin">I'm an Event Organizer (Admin)</option>
            </select>
        </div>
        
        <!-- CSRF Token -->
        <?php echo csrfField(); ?>
        
        <button type="submit">Register</button>
    </form>
    
    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
