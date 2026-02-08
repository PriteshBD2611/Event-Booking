<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<div class="card">
    <h2>Login to Your Account</h2>
    
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>
        
        <!-- CSRF Token -->
        <?php echo csrfField(); ?>
        
        <button type="submit">Login</button>
    </form>
    
    <p>Don't have an account? <a href="register.php">Register here</a></p>
    <p><a href="forgot-password.php">Forgot your password?</a></p>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
