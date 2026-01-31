<?php
// Add these lines to see errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ... rest of your code ...
?>
<?php
session_start();
include 'db_connect.php';

// Check if user is Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access Denied. Admins Only.");
}

if (isset($_POST['submit_event'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $price = $_POST['price'];
    $date = $_POST['date'];
    $speaker = mysqli_real_escape_string($conn, $_POST['speaker']);
    $category = $_POST['category'];
    $user_id = $_SESSION['user_id'];

    // --- IMAGE UPLOAD LOGIC ---
    $target_dir = "uploads/";
    $image_name = basename($_FILES["event_image"]["name"]);
    $target_file = $target_dir . time() . "_" . $image_name; // Adding time() makes the name unique
    $uploadOk = 1;

    // Check if image file is a actual image
    if(isset($_FILES["event_image"]["tmp_name"]) && !empty($_FILES["event_image"]["tmp_name"])) {
        if (move_uploaded_file($_FILES["event_image"]["tmp_name"], $target_file)) {
            // Image uploaded successfully
        } else {
            echo "Sorry, there was an error uploading your file.";
            $target_file = ""; // If failed, save nothing
        }
    } else {
        $target_file = ""; // No image uploaded
    }
    // --------------------------

    $sql = "INSERT INTO events (title, image_path, description, location_url, price, event_date, created_by, speaker, category) 
            VALUES ('$title', '$target_file', '$desc', '$location', '$price', '$date', '$user_id', '$speaker', '$category')";

    if (mysqli_query($conn, $sql)) {
        echo "<h3>Event Posted Successfully! <a href='index.php'>View Website</a></h3>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Post Financial Seminar</title>
    <style>
        body { font-family: sans-serif; background-color: #111827; color: #000000; padding: 20px; }
        .container { background: #1F2937; padding: 20px; border-radius: 8px; max-width: 600px; margin: 0 auto; }
        h2 { color: #000000; }
        label { color: #000000; display: block; margin-top: 10px; }
        input, select, textarea { display: block; margin: 5px 0 15px; padding: 8px; width: 100%; background: #111827; color: #000000; border: 1px solid #9CA3AF; }
        button { background: #8B5CF6; color: #000000; border: none; padding: 10px; width: 100%; cursor: pointer; }
        button:hover { background: #00E5FF; }
        a { color: #00E5FF; }
    </style>
</head>
<body>
    <a href="logout.php" style="float:right;">Logout</a>
    <div class="container">
    <div class="card">
    <h2>Post a New Financial Seminar</h2>
    <form method="POST" enctype="multipart/form-data">
        
        <label>Seminar Title:</label><br>
        <input type="text" name="title" required><br><br>

        <label>Event Banner Image:</label><br>
        <input type="file" name="event_image" accept="image/*" required><br><br>

        <label>Speaker Name:</label><br>
        <input type="text" name="speaker" placeholder="e.g. Warren Buffett"><br><br>

        <label>Category:</label><br>
        <select name="category">
            <option value="Stock Market">Stock Market</option>
            <option value="Crypto">Crypto</option>
            <option value="Real Estate">Real Estate</option>
            <option value="Banking">Banking</option>
            <option value="Finance">Finance</option>
            <option value="Networking">Networking</option>
            <option value="Technology">Technology</option>
            <option value="Career Guidance">Career Guidance</option>
        </select><br><br>

        <label>Description:</label><br>
        <textarea name="description" required></textarea><br><br>

      <label>Location (Paste "Embed Map" HTML here):</label><br>
<textarea name="location" placeholder='Paste the <iframe...> code from Google Maps here' rows="4" required></textarea><br><br>
        <label>Ticket Price (Enter 0 for Free):</label><br>
        <input type="number" name="price" step="0.01"><br><br>

        <label>Date & Time:</label><br>
        <input type="datetime-local" name="date" required><br><br>

        <button type="submit" name="submit_event">Post Event</button>
    </form>
    </div>
    </div>
</body>
</html>