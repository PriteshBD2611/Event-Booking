<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['id'])) {
    die("Event ID missing.");
}

$event_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 1. Get List of Already Booked Seats
$booked_seats = [];
$sql = "SELECT seat_number FROM bookings WHERE event_id = '$event_id'";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $booked_seats[] = $row['seat_number']; // Add occupied seat to array
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">

    <title>Select Seat</title>
    <style>
        .seat-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr); /* 10 seats per row */
            gap: 10px;
            max-width: 600px;
            margin: 20px auto;
        }
        .seat {
            padding: 15px;
            text-align: center;
            border: 1px solid #ccc;
            background-color: #2ecc71; /* Green for Available */
            cursor: pointer;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-weight: bold;
        }
        .seat.taken {
            background-color: #e74c3c; /* Red for Taken */
            cursor: not-allowed;
            pointer-events: none; /* User can't click it */
        }
        .screen {
            background: #333; color: white; text-align: center; padding: 10px; margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Select Your Seat</h2>
    <div class="screen">STAGE / SCREEN</div>

    <div class="seat-grid">
        <?php
        // Generate 50 Seats
        for ($i = 1; $i <= 50; $i++) {
            // Check if this seat number is in the "booked" array
            if (in_array($i, $booked_seats)) {
                // Seat is Taken
                echo "<div class='seat taken'>$i</div>";
            } else {
                // Seat is Available -> Link to Payment Page with seat number
                echo "<a href='buy_ticket.php?id=$event_id&seat=$i' class='seat'>$i</a>";
            }
        }
        ?>
    </div>
    <button class="seat">A1</button>
     <button class="seat booked" disabled>A1</button>
    <p style="text-align:center;">Green = Available | Red = Booked</p>
</body>
</html>