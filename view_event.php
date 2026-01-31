<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$event_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch event details
$sql = "SELECT * FROM events WHERE id = '$event_id'";
$result = mysqli_query($conn, $sql);
$event = mysqli_fetch_assoc($result);

if (!$event) {
    die("Event not found.");
}

// Check if user has booked
$booked_sql = "SELECT * FROM bookings WHERE user_id = '$user_id' AND event_id = '$event_id'";
$booked_result = mysqli_query($conn, $booked_sql);
$has_booked = mysqli_num_rows($booked_result) > 0;

// Fetch reviews
$reviews_sql = "SELECT reviews.rating, reviews.comment, users.username FROM reviews JOIN users ON reviews.user_id = users.id WHERE reviews.event_id = '$event_id'";
$reviews_result = mysqli_query($conn, $reviews_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title><?php echo $event['title']; ?> - Details</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #111827; color: #F9FAFB; }
        .nav { background: #1F2937; padding: 10px; color: white; margin-bottom: 20px; }
        .nav a { color: #F9FAFB; margin-right: 15px; text-decoration: none; }
        .event-details { max-width: 800px; margin: 0 auto; background: #1F2937; padding: 20px; border-radius: 8px; color: #F9FAFB; }
        .event-image { width: 100%; height: 300px; object-fit: cover; border-radius: 8px; }
        .map { width: 100%; height: 300px; border: none; }
        .reviews { margin-top: 20px; }
        .review { border-bottom: 1px solid #9CA3AF; padding: 10px 0; color: #F9FAFB; }
        button { background-color: #8B5CF6; color: #F9FAFB; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #00E5FF; }
    </style>
    <script src="https://www.paypal.com/sdk/js?client-id=Ad3PJuzwknTqCnmIMI767plher_kOKbRB2R3JK_dooW8GNFt0Gh4o3GBsaYCyI09CBhzNFdvlLTzc_UK&currency=INR"></script>
</head>
<body>

<div class="nav">
    <strong>CONNECT</strong>
    <a href="index.php">Home</a>
    <a href="my_bookings.php">My Tickets</a>
    <?php if($_SESSION['role'] == 'admin'): ?>
        <a href="admin_dashboard.php">Admin Dashboard</a>
        <a href="add_event.php">Post Event</a>
    <?php endif; ?>
    <a href="logout.php" style="float:right;">Logout (<?php echo $_SESSION['username']; ?>)</a>
</div>

<div class="event-details">
    <?php if (!empty($event['image_path'])): ?>
        <img src="<?php echo $event['image_path']; ?>" class="event-image" alt="Event Image">
    <?php endif; ?>
    
    <h1><?php echo $event['title']; ?> <small style="color: #9CA3AF;">(<?php echo $event['category']; ?>)</small></h1>
    
    <p style="color: #9CA3AF;"><strong style="color: #F9FAFB;">Speaker:</strong> <?php echo $event['speaker'] ? $event['speaker'] : 'TBA'; ?></p>
    <p style="color: #9CA3AF;"><strong style="color: #F9FAFB;">Description:</strong> <?php echo $event['description']; ?></p>
    <p style="color: #9CA3AF;"><strong style="color: #F9FAFB;">Price:</strong> ₹<?php echo $event['price']; ?></p>
    <p style="color: #9CA3AF;"><strong style="color: #F9FAFB;">Date:</strong> <?php echo $event['event_date']; ?></p>
    
    <?php if ($has_booked): ?>
        <p style="color: green;"><strong>You have booked this event!</strong></p>
    <?php endif; ?>
    
    <h3 style="color: #F9FAFB;">Venue Map</h3>
    <div><?php echo $event['location_url']; ?></div>
    
    <div class="reviews">
        <h3 style="color: #F9FAFB;">Reviews</h3>
        <?php if (mysqli_num_rows($reviews_result) > 0): ?>
            <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                <div class="review">
                    <strong style="color: #F9FAFB;"><?php echo $review['username']; ?>:</strong> <span style="color: #F9FAFB;"><?php echo str_repeat('⭐', $review['rating']); ?> (<?php echo $review['rating']; ?>/5)</span><br>
                    <span style="color: #9CA3AF;"><?php echo $review['comment']; ?></span>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No reviews yet.</p>
        <?php endif; ?>
    </div>
    
    <br>
    <a href="index.php"><button>Back to Home</button></a>
    <?php if (!$has_booked && $event['price'] > 0): ?>
        <button id="showPurchaseBtn">Purchase & Book</button>
        <div id="purchase" style="display:none; margin-top:16px;">
            <label style="color:#9CA3AF;">Quantity</label>
            <input type="number" id="ticket-qty" min="1" value="1" style="width:80px; padding:8px; border-radius:8px; margin:8px 0; background:#020617; color:#e5e7eb; border:1px solid #1e293b;">
            <div id="paypal-button-container"></div>
        </div>
    <?php elseif (!$has_booked && $event['price'] == 0): ?>
        <form method="GET" action="save_booking.php" style="display:inline;">
            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
            <input type="hidden" name="seat" value="General">
            <input type="hidden" name="qty" value="1">
            <input type="hidden" name="status" value="Free">
            <button type="submit">Free Entry</button>
        </form>
    <?php endif; ?>
    <?php if ($has_booked): ?>
        <a href="rate_event.php?id=<?php echo $event['id']; ?>"><button>Rate this Event</button></a>
    <?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function(){
        var showBtn = document.getElementById('showPurchaseBtn');
        if(showBtn){
            showBtn.addEventListener('click', function(){
                document.getElementById('purchase').style.display='block';
                showBtn.style.display='none';
                renderPayPal();
            });
        }
    });

    function renderPayPal(){
        var price = <?php echo floatval($event['price']); ?>;
        var pxid = 'paypal-button-container';
        paypal.Buttons({
            createOrder: function(data, actions) {
                var qty = parseInt(document.getElementById('ticket-qty').value) || 1;
                var total = (price * qty).toFixed(2);
                return actions.order.create({
                    purchase_units: [{
                        amount: { currency_code: 'INR', value: total }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    var qty = parseInt(document.getElementById('ticket-qty').value) || 1;
                    // Redirect to save booking with quantity
                    window.location.href = "save_booking.php?event_id=<?php echo $event['id']; ?>&seat=General&qty=" + qty + "&status=Paid";
                });
            },
            onError: function(err){
                console.error('Payment error:', err);
                alert('Payment error. Please try again.');
            }
        }).render('#' + pxid);
    }
</script>
</div>

</body>
</html>