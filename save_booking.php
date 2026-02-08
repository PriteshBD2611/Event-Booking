<?php
session_start();
include 'config/db.php';

if (isset($_GET['event_id']) && isset($_SESSION['user_id'])) {
    $event_id = $_GET['event_id'];
    $user_id = $_SESSION['user_id'];
    $seat = isset($_GET['seat']) ? mysqli_real_escape_string($conn, $_GET['seat']) : 'General';
    $status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : 'Paid';
    $qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1;

    $successCount = 0;
    for ($i=0; $i < $qty; $i++) {
        $sql = "INSERT INTO bookings (user_id, event_id, payment_status, seat_number) 
            VALUES ('$user_id', '$event_id', '$status', '$seat')";
        if (mysqli_query($conn, $sql)) { $successCount++; }
    }

    if ($successCount > 0) {
        echo "<!DOCTYPE html><html><head><title>Booking Success</title><style>body { font-family: sans-serif; background-color: #111827; color: #F9FAFB; padding: 20px; text-align: center; } h1 { color: #F9FAFB; } p { color: #9CA3AF; } a { color: #00E5FF; }</style></head><body>";
        echo "<h1>Ticket(s) Booked Successfully!</h1>";
        echo "<p>$successCount ticket(s) have been confirmed.</p>";
        echo "<a href='index.php'>Go back to Home</a>";
        echo "</body></html>";
    } else {
        echo "<!DOCTYPE html><html><head><title>Error</title><style>body { font-family: sans-serif; background-color: #111827; color: #F9FAFB; padding: 20px; }</style></head><body>";
        echo "Error saving booking: " . mysqli_error($conn);
        echo "</body></html>";
    }
} else {
    echo "<!DOCTYPE html><html><head><title>Error</title><style>body { font-family: sans-serif; background-color: #111827; color: #F9FAFB; padding: 20px; }</style></head><body>";
    echo "Error: details missing.";
    echo "</body></html>";
}
?>