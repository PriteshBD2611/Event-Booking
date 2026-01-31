<?php
session_start();
include 'db_connect.php';

// Security: Only Admins can see this
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access Denied. Admins Only.");
}

// 1. Calculate Total Revenue
$revenue_query = "SELECT SUM(events.price) as total_revenue 
                  FROM bookings 
                  JOIN events ON bookings.event_id = events.id 
                  WHERE bookings.payment_status = 'Paid'";
$revenue_result = mysqli_query($conn, $revenue_query);
$revenue_data = mysqli_fetch_assoc($revenue_result);
$total_revenue = $revenue_data['total_revenue'] ? $revenue_data['total_revenue'] : 0;

// 2. Count Total Tickets Sold
$tickets_query = "SELECT COUNT(*) as total_sold FROM bookings";
$tickets_result = mysqli_query($conn, $tickets_query);
$tickets_data = mysqli_fetch_assoc($tickets_result);
$total_sold = $tickets_data['total_sold'];

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">

    <title>Admin Financial Dashboard</title>
    <style>
        body { font-family: sans-serif; background-color: #111827; color: #F9FAFB; padding: 20px; }
        .stats-box { display: flex; gap: 20px; }
        .card { background: #1F2937; padding: 20px; border-radius: 8px; width: 200px; text-align: center; color: #F9FAFB; }
        .card h2 { color: #F9FAFB; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; color: #F9FAFB; }
        th, td { border: 1px solid #9CA3AF; padding: 10px; text-align: left; }
        th { background-color: #1F2937; color: #F9FAFB; }
        a { color: #00E5FF; }
    </style>
</head>
<body>
    <div class="container">
  <div class="card"></div>
    <h1>Admin Analytics Dashboard</h1>
    
    <div class="stats-box">
        <div class="card">
            <h3>Total Revenue</h3>
            <h2>$<?php echo number_format($total_revenue, 2); ?></h2>
        </div>
        <div class="card">
            <h3>Tickets Sold</h3>
            <h2><?php echo $total_sold; ?></h2>
        </div>
    </div>

    <h3>Recent Transactions</h3>
    <table>
        <tr>
            <th>Booking ID</th>
            <th>Customer Name</th>
            <th>Event</th>
            <th>Amount</th>
            <th>Status</th>
        </tr>
        <?php
        $sql = "SELECT bookings.id, users.username, events.title, events.price, bookings.payment_status 
                FROM bookings 
                JOIN users ON bookings.user_id = users.id 
                JOIN events ON bookings.event_id = events.id
                ORDER BY bookings.id DESC";
        $result = mysqli_query($conn, $sql);

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>#" . $row['id'] . "</td>";
            echo "<td>" . $row['username'] . "</td>";
            echo "<td>" . $row['title'] . "</td>";
            echo "<td>₹" . $row['price'] . "</td>";
            echo "<td style='color:green; font-weight:bold;'>" . $row['payment_status'] . "</td>";
            echo "</tr>";
        }
        ?>
    </table>
    
    <h3>Manage Events</h3>
    <table>
        <tr>
            <th>Title</th>
            <th>Category</th>
            <th>Price</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
        <?php
        $events_sql = "SELECT * FROM events ORDER BY event_date DESC";
        $events_result = mysqli_query($conn, $events_sql);
        while ($event = mysqli_fetch_assoc($events_result)) {
            echo "<tr>";
            echo "<td>" . $event['title'] . "</td>";
            echo "<td>" . $event['category'] . "</td>";
            echo "<td>₹" . $event['price'] . "</td>";
            echo "<td>" . $event['event_date'] . "</td>";
            echo "<td><a href='edit_event.php?id=" . $event['id'] . "'>Edit</a> | <a href='delete_event.php?id=" . $event['id'] . "' onclick='return confirm(\"Are you sure you want to delete this event?\")'>Delete</a></td>";
            echo "</tr>";
        }
        ?>
    </table>
    </div>
    </div>
    <br>
    <a href="add_event.php">Post New Event</a> | <a href="index.php">View Website</a> | <a href="logout.php">Logout</a>
</body>
</html>