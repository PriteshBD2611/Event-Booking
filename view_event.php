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
        body { font-family: sans-serif; padding: 20px; background-color: #111827; color: #e5e7eb; }
        .nav { background: #1F2937; padding: 10px; color: #e5e7eb; margin-bottom: 20px; border-radius: 8px; }
        .nav a { color: #e5e7eb; margin-right: 15px; text-decoration: none; }
        .event-details { max-width: 800px; margin: 0 auto; background: #1F2937; padding: 20px; border-radius: 8px; color: #e5e7eb; }
        .event-image { width: 100%; height: 300px; object-fit: cover; border-radius: 8px; }
        .map { width: 100%; height: 300px; border: none; }
        .reviews { margin-top: 20px; }
        .review { border-bottom: 1px solid #9CA3AF; padding: 10px 0; color: #e5e7eb; }
        button { background-color: #6366f1; color: #040025; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #22d3ee; }
    </style>
</head>
<body>

<div class="nav">
    <strong style="margin-right: 20px;">Event Booking Site</strong>
    <a href="index.php">Home</a>
    <a href="my_bookings.php">My Tickets</a>
    <a href="add_event.php">Post Event</a>
    <?php if($_SESSION['role'] == 'admin'): ?>
        <a href="admin_dashboard.php">Admin Dashboard</a>
    <?php endif; ?>
    <a href="logout.php" style="float:right;">Logout (<?php echo $_SESSION['username']; ?>)</a>
</div>

<div class="event-details">
    <?php if (!empty($event['image_path'])): ?>
        <img src="<?php echo $event['image_path']; ?>" class="event-image" alt="Event Image">
    <?php endif; ?>
    
    <h1><?php echo $event['title']; ?> <small style="color: #9CA3AF;">(<?php echo $event['category']; ?>)</small></h1>
    
    <p><strong>Speaker:</strong> <?php echo $event['speaker'] ? $event['speaker'] : 'TBA'; ?></p>
    <p><strong>Description:</strong> <?php echo $event['description']; ?></p>
    <p><strong>Price:</strong> ₹<?php echo $event['price']; ?></p>
    <p><strong>Date:</strong> <?php echo $event['event_date']; ?></p>
    
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
                    <strong><?php echo $review['username']; ?>:</strong> <span><?php echo str_repeat('⭐', $review['rating']); ?> (<?php echo $review['rating']; ?>/5)</span><br>
                    <span><?php echo $review['comment']; ?></span>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No reviews yet.</p>
        <?php endif; ?>
    </div>
    
    <br>
    <a href="index.php"><button>Back to Home</button></a>

    <?php if (!$has_booked && $event['price'] > 0): ?>
        <div style="display:flex; gap:12px; justify-content:center; margin-top:12px;">
            <button id="openSeatMap">Select Seat & Book</button>
            <button id="showPurchaseBtn">Purchase without Seat</button>
        </div>

        <div id="purchase" style="display:none; margin-top:16px; text-align:center;">
            <label>Quantity</label>
            <input type="number" id="ticket-qty" min="1" value="1" style="width:80px; padding:8px; border-radius:8px; margin:8px 0; background:#020617; color:#e5e7eb; border:1px solid #1e293b;">
            <br><button id="payGeneralBtn" style="margin-top:10px;">Pay Now</button>
        </div>

        <!-- Seat Selection Modal -->
        <div id="seatModal" class="modal" style="display:none;">
            <div class="modal-content">
                <h3>Select a Seat</h3>
                <div id="seat-grid">
                <?php
                    $booked_q = "SELECT seat_number FROM bookings WHERE event_id = '$event_id'";
                    $res_bs = mysqli_query($conn, $booked_q);
                    $booked_arr = [];
                    while ($b = mysqli_fetch_assoc($res_bs)) { $booked_arr[] = $b['seat_number']; }

                    for ($i=1; $i<=50; $i++) {
                        if (in_array($i, $booked_arr)) {
                            echo "<div class='seat booked' data-seat='$i'>" . $i . "</div>";
                        } else {
                            echo "<div class='seat' data-seat='$i'>" . $i . "</div>";
                        }
                    }
                ?>
                </div>

                <div style="margin-top:12px;">Selected: <strong id="selectedSeat">None</strong></div>
                <div style="margin-top:12px; display:flex; gap:8px; justify-content:center;">
                    <button id="payForSeat" style="display:none;">Pay Now</button>
                    <?php if ($event['price'] == 0): ?>
                        <button id="freeBookSeat" style="display:none;">Confirm Free Booking</button>
                    <?php endif; ?>
                    <button id="closeSeatModal">Close</button>
                </div>
            </div>
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
        var openSeat = document.getElementById('openSeatMap');
        var seatModal = document.getElementById('seatModal');
        var closeSeat = document.getElementById('closeSeatModal');
        var selectedSeatEl = document.getElementById('selectedSeat');
        var payForSeatBtn = document.getElementById('payForSeat');
        var freeBookBtn = document.getElementById('freeBookSeat');

        if(showBtn){
            showBtn.addEventListener('click', function(){
                document.getElementById('purchase').style.display='block';
                showBtn.style.display='none';
            });
        }

        if(openSeat){
            openSeat.addEventListener('click', function(){ seatModal.style.display='flex'; });
        }
        if(closeSeat){
            closeSeat.addEventListener('click', function(){
                seatModal.style.display='none';
            });
        }

        // Seat click handler
        document.querySelectorAll('#seat-grid .seat').forEach(function(el){
            el.addEventListener('click', function(){
                if (el.classList.contains('booked')) return;
                // clear previous selected
                document.querySelectorAll('#seat-grid .seat').forEach(function(s){ s.classList.remove('selected'); });
                el.classList.add('selected');
                var seat = el.getAttribute('data-seat');
                selectedSeatEl.textContent = seat;
                
                // Only show Pay button if price > 0
                if (<?php echo floatval($event['price']); ?> > 0) {
                    payForSeatBtn.style.display = 'inline-block';
                } else if (freeBookBtn) {
                    freeBookBtn.style.display = 'inline-block';
                }

                // Update Pay button click for seat
                payForSeatBtn.onclick = function() {
                    startRazorpayPayment(1, seat);
                };
            });
        });

        // Pay button (for free entries will be handled directly)
        if (freeBookBtn) {
            freeBookBtn.addEventListener('click', function(){
                var seat = document.getElementById('seat-grid').querySelector('.selected').getAttribute('data-seat');
                window.location.href = "save_booking.php?event_id=<?php echo $event['id']; ?>&seat=" + seat + "&qty=1&status=Free";
            });
        }

        // General Purchase Button
        var payGeneral = document.getElementById('payGeneralBtn');
        if(payGeneral) {
            payGeneral.addEventListener('click', function(){
                var qty = document.getElementById('ticket-qty').value;
                startRazorpayPayment(qty, 'General');
            });
        }

    });

    function startRazorpayPayment(qty, seat) {
        var price = <?php echo floatval($event['price']); ?>;
        var totalAmount = price * qty;

        // SIMULATED PAYMENT (No Account Required)
        if(confirm("Mock Payment Gateway:\n\nPay ₹" + totalAmount + " for " + qty + " ticket(s)?\n\nClick OK to simulate a successful payment.")) {
            // Redirect to save booking as if payment was successful
            // We generate a fake payment ID using random numbers
            var mockPaymentId = "pay_mock_" + Math.floor(Math.random() * 1000000);
            window.location.href = "save_booking.php?event_id=<?php echo $event['id']; ?>&seat=" + seat + "&qty=" + qty + "&status=Paid&payment_id=" + mockPaymentId;
        }
    }

    // If user came from select_seat.php with preselect parameter, auto-open modal and select
    (function(){
        const params = new URLSearchParams(window.location.search);
        const pre = params.get('preselect_seat');
        if(pre){
            document.getElementById('seatModal').style.display='flex';
            const el = document.querySelector('#seat-grid .seat[data-seat="'+pre+'"]');
            if(el) el.click();
        }
    })();

</script>
</div>

</body>
</html>