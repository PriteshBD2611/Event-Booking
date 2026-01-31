<?php
// Add these lines to see errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ... rest of your code ...
?><?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/style.css">

    <title>My Tickets</title>
    <style>
        body { font-family: sans-serif; background-color: #111827; color: #F9FAFB; padding: 20px; }
        .ticket { border: 2px dashed #9CA3AF; padding: 20px; margin: 20px 0; display: flex; align-items: center; justify-content: space-between; background: #1F2937; border-radius: 8px; color: #F9FAFB; }
        .info { width: 60%; }
        .qr { width: 30%; text-align: center; }
        h1 { color: #F9FAFB; }
        p { color: #9CA3AF; }
        a { color: #00E5FF; }
    </style>
</head>
<body>
   <div class="container">
  <div class="card"></div>

    <h1>My Purchased Tickets</h1>
    <a href="index.php">Back to Home</a> | <a href="logout.php">Logout</a>

    <?php
   $sql = "SELECT bookings.id as booking_id,bookings.seat_number, events.id as event_id, events.title, events.event_date, events.location_url, events.speaker 
        FROM bookings 
        JOIN events ON bookings.event_id = events.id 
        WHERE bookings.user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 0) {
        echo "<p>You haven't booked any seminars yet.</p>";
    }

    while ($row = mysqli_fetch_assoc($result)) {
        // Create unique data for QR Code (Booking ID + Event Name)
        $qr_data = "Ticket-ID:" . $row['booking_id'] . "-Event:" . urlencode($row['title']);
        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . $qr_data;
         echo "<div style='margin-top:15px;'>";
            echo "<strong>Venue Map:</strong><br>";
            echo "<div style='width: 100%; overflow: hidden;'>";
            echo $row['location_url']; 
            echo "</div>";
            echo "</div>";

        echo "<div class='ticket'>";
        echo "<div class='info'>";
        echo "<h2>" . $row['title'] . "</h2>";
        echo "<p><strong>Speaker:</strong> " . $row['speaker'] . "</p>";
        echo "<p><strong>Date:</strong> " . $row['event_date'] . "</p>";
        echo "<p style='font-size: 18px; color: blue;'><strong>Seat Number: #" . $row['seat_number'] . "</strong></p>";
        echo "<p><a href='" . $row['location_url'] . "'>View Location</a></p>";
        echo "<a href='rate_event.php?id=" . $row['event_id'] . "' style='background:orange; padding:5px 10px; color:white; text-decoration:none; border-radius:4px;'>Rate this Event</a>";
        echo "<small>Ticket ID: #" . $row['booking_id'] . "</small>";
        echo "</div>";
        
        echo "<div class='qr'>";
        echo "<img src='" . $qr_url . "' alt='Ticket QR'>";
        
        echo "</div>";
        echo "</div>";
    }
    ?>
    </div>  
    </div>
</body>
</html>