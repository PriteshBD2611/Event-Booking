<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = $_GET['id'];

// Check if the form was submitted
if (isset($_POST['submit_review'])) {
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // Insert the review
    $sql = "INSERT INTO reviews (user_id, event_id, rating, comment) VALUES ('$user_id', '$event_id', '$rating', '$comment')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Thank you for your review!'); window.location.href='my_bookings.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Get Event Name for display
$event_sql = "SELECT title FROM events WHERE id = '$event_id'";
$event_result = mysqli_query($conn, $event_sql);
$event = mysqli_fetch_assoc($event_result);
?>

<?php
// Add these lines to see errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ... rest of your code ...
?><!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">

    <title>Rate Event</title>
    <style>
        body { font-family: sans-serif; background-color: #111827; color: #F9FAFB; padding: 20px; }
        h2 { color: #F9FAFB; }
        form { background: #1F2937; padding: 20px; border-radius: 8px; max-width: 400px; margin: 0 auto; }
        label { color: #F9FAFB; display: block; margin-top: 10px; }
        select, textarea { display: block; margin: 5px 0 15px; padding: 8px; width: 100%; background: #111827; color: #F9FAFB; border: 1px solid #9CA3AF; }
        button { background: #8B5CF6; color: #F9FAFB; border: none; padding: 10px; width: 100%; cursor: pointer; }
        button:hover { background: #00E5FF; }
        a { color: #00E5FF; }
    </style>
</head>
<body>
    <h2>Rate Event: <?php echo $event['title']; ?></h2>
    
    <form method="POST">
        <label>Rate this seminar (1 to 5 Stars):</label><br>
        <select name="rating" required>
            <option value="5">⭐⭐⭐⭐⭐ (Excellent)</option>
            <option value="4">⭐⭐⭐⭐ (Good)</option>
            <option value="3">⭐⭐⭐ (Average)</option>
            <option value="2">⭐⭐ (Poor)</option>
            <option value="1">⭐ (Terrible)</option>
        </select>
        <br><br>

        <label>Your Review:</label><br>
        <textarea name="comment" rows="4" placeholder="What did you learn?"></textarea>
        <br><br>

        <button type="submit" name="submit_review">Submit Review</button>
    </form>
    <br>
    <a href="my_bookings.php">Cancel</a>
</body>
</html>