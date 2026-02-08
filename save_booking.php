<?php
session_start();
include 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get data from POST or GET
$event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : intval($_GET['event_id'] ?? 0);
$seats_json = isset($_POST['seats']) ? $_POST['seats'] : $_GET['seats'] ?? '[]';
$payment_id = isset($_POST['payment_id']) ? htmlspecialchars($_POST['payment_id']) : htmlspecialchars($_GET['payment_id'] ?? '');
$status = isset($_POST['status']) ? htmlspecialchars($_POST['status']) : htmlspecialchars($_GET['status'] ?? 'Pending');

// Validate inputs
if (!$event_id) {
    die("Event ID missing.");
}

$seat_numbers = [];
if (!empty($seats_json)) {
    $seat_numbers = json_decode($seats_json, true);
}

// Legacy support: single seat from GET
if (empty($seat_numbers) && isset($_GET['seat'])) {
    $seat_numbers = [htmlspecialchars($_GET['seat'])];
}

// Legacy support: quantity parameter
$qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1;

if (empty($seat_numbers) && $qty > 0) {
    // If no seat numbers provided, create bookings for "General" seat
    $seat_numbers = array_fill(0, $qty, 'General');
}

if (empty($seat_numbers)) {
    die("No seats selected.");
}

// Fetch event details
$event_stmt = $conn->prepare("SELECT title, price FROM events WHERE id = ?");
$event_stmt->execute([$event_id]);
$event = $event_stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Event not found.");
}

// Start transaction
try {
    $conn->beginTransaction();
    $success_count = 0;
    $failed_seats = [];

    // Insert each booking
    foreach ($seat_numbers as $seat_number) {
        $seat_number = htmlspecialchars($seat_number);
        
        // Check if seat is already booked
        $check_stmt = $conn->prepare("SELECT id FROM bookings WHERE event_id = ? AND seat_number = ? AND payment_status IN ('Paid', 'Pending')");
        $check_stmt->execute([$event_id, $seat_number]);
        
        if ($check_stmt->fetch()) {
            $failed_seats[] = $seat_number;
            continue;
        }
        
        $payment_status = htmlspecialchars($status);
        $insert_stmt = $conn->prepare("INSERT INTO bookings (user_id, event_id, seat_number, payment_status) VALUES (?, ?, ?, ?)");
        
        if ($insert_stmt->execute([$user_id, $event_id, $seat_number, $payment_status])) {
            $success_count++;
        } else {
            $failed_seats[] = $seat_number;
        }
    }

    // Commit transaction
    $conn->commit();

} catch (Exception $e) {
    $conn->rollBack();
    die("Error processing booking: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #111827;
            color: #e5e7eb;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        .success-card {
            background: linear-gradient(135deg, #064e3b, #10b981);
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            border-left: 4px solid #059669;
        }
        .success-card h1 {
            color: #86efac;
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .success-card p {
            color: #d1fae5;
            margin: 5px 0;
        }
        .confirmation-details {
            background: #1F2937;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #374151;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #9CA3AF;
            font-weight: 600;
        }
        .detail-value {
            color: #e5e7eb;
        }
        .seats-booked {
            background: #111827;
            padding: 12px;
            border-radius: 4px;
            margin-top: 8px;
            word-break: break-word;
        }
        .error-card {
            background: #7f1d1d;
            border-left: 4px solid #dc2626;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #fecaca;
        }
        .warning-card {
            background: #78350f;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #fdba74;
        }
        .button-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        button, a {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #22d3ee);
            color: #040025;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($success_count > 0): ?>

            <div class="success-card">
                <h1>✓ Booking Confirmed!</h1>
                <p>Your ticket<?php echo $success_count > 1 ? 's' : ''; ?> <?php echo $success_count > 1 ? 'are' : 'is'; ?> confirmed</p>
            </div>

            <div class="confirmation-details">
                <div class="detail-row">
                    <span class="detail-label">Booking ID</span>
                    <span class="detail-value">#<?php echo str_pad(rand(1000, 9999), 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Event</span>
                    <span class="detail-value"><?php echo htmlspecialchars($event['title']); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Number of Seats</span>
                    <span class="detail-value"><?php echo $success_count; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Booked Seats</span>
                    <span class="detail-value" style="text-align: right;">
                        <div class="seats-booked">
                            <?php 
                            $booked = array_slice($seat_numbers, 0, $success_count);
                            echo implode(', ', array_map(function($s) { return "Seat $s"; }, $booked)); 
                            ?>
                        </div>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Price per Seat</span>
                    <span class="detail-value">₹<?php echo number_format($event['price'], 2); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Total Amount</span>
                    <span class="detail-value" style="font-size: 18px; font-weight: bold; color: #10b981;">
                        ₹<?php echo number_format($event['price'] * $success_count, 2); ?>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Payment Status</span>
                    <span class="detail-value" style="color: #10b981; font-weight: bold;">
                        <?php echo htmlspecialchars($status); ?>
                    </span>
                </div>
            </div>

            <?php if (!empty($failed_seats)): ?>
                <div class="warning-card">
                    <strong>⚠️ Some seats could not be booked:</strong><br>
                    <?php echo implode(', ', array_map(function($s) { return "Seat $s"; }, $failed_seats)); ?><br>
                    <small>These seats may have been booked by another user. Only <?php echo $success_count; ?> seat<?php echo $success_count > 1 ? 's' : ''; ?> <?php echo $success_count > 1 ? 'were' : 'was'; ?> booked successfully.</small>
                </div>
            <?php endif; ?>

            <div class="button-group">
                <a href="my_bookings.php" class="btn-primary">📋 View My Bookings</a>
                <a href="index.php" class="btn-secondary">← Back to Home</a>
            </div>

        <?php else: ?>

            <div class="error-card">
                <h2>❌ Booking Failed</h2>
                <p>We could not complete your booking. All selected seats may have been booked by other users.</p>
            </div>

            <div class="button-group">
                <a href="select_seat.php?id=<?php echo $event_id; ?>" class="btn-primary">← Try Again</a>
                <a href="index.php" class="btn-secondary">Back to Home</a>
            </div>

        <?php endif; ?>
    </div>
</body>
</html>