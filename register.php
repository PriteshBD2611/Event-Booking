<?php
include 'db_connect.php';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role']; // 'admin' or 'user'

    // Encrypt the password (Security best practice)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed_password', '$role')";

    if (mysqli_query($conn, $sql)) {
        echo "Registration Successful! <a href='login.php'>Login here</a>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
<title>Register - Event Booking Site</title>
<style>
body { font-family: sans-serif; background-color: #111827; color: #F9FAFB; padding: 20px; }
.container { background: #1F2937; padding: 20px; border-radius: 8px; max-width: 400px; margin: 0 auto; }
h2 { color: #F9FAFB; }
input, select { display: block; margin: 10px 0; padding: 8px; width: 100%; background: #111827; color: #F9FAFB; border: 1px solid #9CA3AF; }
button { background: #8B5CF6; color: #F9FAFB; border: none; padding: 10px; width: 100%; cursor: pointer; }
button:hover { background: #00E5FF; }
p { color: #9CA3AF; }
a { color: #00E5FF; }
</style>
</head>
<body>
    <div class="container">
    <div class="card">
    <h2>Create an Account</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Full Name" required><br><br>
        <input type="email" name="email" placeholder="Email Address" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        
        <label>I am a:</label>
        <select name="role">
            <option value="user">Student/User</option>
            <option value="admin">Event Organizer (Admin)</option>
        </select><br><br>

        <button type="submit" name="register">Register</button>
    </form>
    </div>
    </div>
</body>
</html>