<?php
session_start();
include 'db_connect.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        // Login Success: Save user info in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        header("Location: index.php"); // Redirect to home page
    } else {
        echo "Invalid Email or Password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
<title>Login - Connect</title>
<style>
body { font-family: sans-serif; background-color: #111827; color: #F9FAFB; padding: 20px; }
h2 { color: #F9FAFB; }
form { background: #1F2937; padding: 20px; border-radius: 8px; max-width: 300px; margin: 0 auto; }
input { display: block; margin: 10px 0; padding: 8px; width: 100%; background: #111827; color: #F9FAFB; border: 1px solid #9CA3AF; }
button { background: #8B5CF6; color: #F9FAFB; border: none; padding: 10px; width: 100%; cursor: pointer; }
button:hover { background: #00E5FF; }
p { color: #9CA3AF; }
a { color: #00E5FF; }
</style>
</head>
<body>
    <div class="container">
  <div class="card">
    <h2>Login</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit" name="login">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
     </div>
</div>
    
</body>
</html>