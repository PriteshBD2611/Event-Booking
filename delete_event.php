<?php
session_start();
include 'db_connect.php';

// Check if user is Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access Denied. Admins Only.");
}

$event_id = $_GET['id'];

if (isset($event_id)) {
    // Delete related bookings first to avoid foreign key issues
    mysqli_query($conn, "DELETE FROM bookings WHERE event_id = '$event_id'");
    mysqli_query($conn, "DELETE FROM reviews WHERE event_id = '$event_id'");
    
    $sql = "DELETE FROM events WHERE id = '$event_id'";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Event Deleted Successfully!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "No event ID provided.";
}
?>