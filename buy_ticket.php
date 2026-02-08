<?php
session_start();
include 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please <a href='login.php'>login</a> to buy tickets.");
}

$user_id = $_SESSION['user_id'];

// Handle both POST (from seat selection) and GET (direct link)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Multiple seats from seat selection form
    $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
    $seats_json = isset($_POST['seats']) ? $_POST['seats'] : '';
    
    if (!$event_id || empty($seats_json)) {
        die("Invalid event or seat selection.");
    }
    
    $selected_seats = json_decode($seats_json, true);
    if (!is_array($selected_seats) || empty($selected_seats)) {
        die("Invalid seat selection.");
    }
    
    $seat_numbers = $selected_seats;
} else {
    // Single seat from GET (legacy support)
    $event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $seat_number = isset($_GET['seat']) ? htmlspecialchars($_GET['seat']) : '';
    
    if ($event_id <= 0 || empty($seat_number)) {
        die("Invalid event or seat selection.");
    }
    
    $seat_numbers = [$seat_number];
}

// Fetch event details
$stmt = $conn->prepare("SELECT id, title, price FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Event not found.");
}
$price_per_seat = $event['price'];
$total_price = $price_per_seat * count($seat_numbers);

// Mock Razorpay Key for testing
$razorpay_key = "rzp_test_mock_test_key_12345";
$use_mock_payment = true;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Buy Tickets - Checkout</title>
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
        h1 {
            color: #e5e7eb;
            margin-bottom: 20px;
            text-align: center;
        }
        .checkout-details {
            background-color: #1f2937;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
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
            text-align: right;
        }
        .seats-list {
            background: #111827;
            padding: 12px;
            border-radius: 4px;
            margin-top: 8px;
            word-break: break-word;
        }
        .price-section {
            background: #064e3b;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #86efac;
        }
        .price-row:last-child {
            border-top: 1px solid #10b981;
            padding-top: 10px;
            font-size: 18px;
            font-weight: bold;
        }
        .button-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        button {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s;
        }
        #rzp-button1 {
            background: linear-gradient(135deg, #6366f1, #22d3ee);
            color: #040025;
            flex: 1;
            min-width: 200px;
        }
        #rzp-button1:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }
        .back-btn {
            background: #6b7280;
            color: white;
            text-decoration: none;
            flex: 1;
            min-width: 150px;
            text-align: center;
            display: inline-block;
        }
        .back-btn:hover {
            background: #4b5563;
        }
        .info-box {
            background: #0c4a6e;
            border-left: 4px solid #0284c7;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #7dd3fc;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎫 Checkout</h1>
        
        <div class="info-box">
            ℹ️ Review your booking details below and proceed to payment
        </div>

        <div class="checkout-details">
            <div class="detail-row">
                <span class="detail-label">Event</span>
                <span class="detail-value"><?php echo htmlspecialchars($event['title']); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Number of Seats</span>
                <span class="detail-value"><?php echo count($seat_numbers); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Selected Seats</span>
                <span class="detail-value" style="text-align: left;">
                    <div class="seats-list">
                        <?php echo implode(', ', array_map(function($s) { return "Seat $s"; }, $seat_numbers)); ?>
                    </div>
                </span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Price per Seat</span>
                <span class="detail-value">₹<?php echo number_format($price_per_seat, 2); ?></span>
            </div>
        </div>

        <div class="price-section">
            <div class="price-row">
                <span>Subtotal (<?php echo count($seat_numbers); ?> seat<?php echo count($seat_numbers) !== 1 ? 's' : ''; ?>)</span>
                <span>₹<?php echo number_format($total_price, 2); ?></span>
            </div>
            <div class="price-row">
                <span>Processing Fee</span>
                <span>₹0.00</span>
            </div>
            <div class="price-row">
                <span>Total Amount</span>
                <span>₹<?php echo number_format($total_price, 2); ?></span>
            </div>
        </div>

        <div class="button-group">
            <button id="rzp-button1">💳 Pay with Razorpay</button>
            <a href="select_seat.php?id=<?php echo $event_id; ?>" class="back-btn">← Back to Seats</a>
        </div>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        var useMockPayment = <?php echo $use_mock_payment ? 'true' : 'false'; ?>;
        var eventId = <?php echo $event_id; ?>;
        var totalPrice = <?php echo $total_price; ?>;
        var seats = <?php echo json_encode($seat_numbers); ?>;

        document.getElementById('rzp-button1').onclick = function(e){
            e.preventDefault();
            
            if (useMockPayment) {
                mockPaymentPopup();
            } else {
                realRazorpayPayment();
            }
        }

        function mockPaymentPopup() {
            var modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0,0,0,0.7);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                font-family: sans-serif;
            `;

            var popup = document.createElement('div');
            popup.style.cssText = `
                background: white;
                padding: 30px;
                border-radius: 10px;
                width: 90%;
                max-width: 400px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            `;

            var seatsText = seats.map(s => 'Seat ' + s).join(', ');

            popup.innerHTML = `
                <div style="text-align: center;">
                    <h2 style="color: #1f2937; margin-bottom: 10px;">🏦 Mock Payment Gateway</h2>
                    <p style="color: #6b7280; margin-bottom: 20px;">Testing Mode - No Real Payment</p>
                    
                    <div style="background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: left;">
                        <p style="color: #374151; margin: 5px 0;"><strong>Event:</strong> <?php echo htmlspecialchars($event['title']); ?></p>
                        <p style="color: #374151; margin: 5px 0;"><strong>Seats:</strong> ` + seatsText + `</p>
                        <p style="color: #374151; margin: 5px 0;"><strong>Amount:</strong> ₹` + totalPrice.toFixed(2) + `</p>
                    </div>

                    <p style="color: #d97706; font-size: 14px; margin-bottom: 20px;">
                        ⚠️ This is a test payment. Select an option below:
                    </p>

                    <button id="mock-pay-success" style="
                        width: 100%;
                        padding: 12px;
                        margin-bottom: 10px;
                        background-color: #10b981;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 16px;
                        font-weight: bold;
                    ">✓ Simulate Success</button>

                    <button id="mock-pay-failure" style="
                        width: 100%;
                        padding: 12px;
                        margin-bottom: 10px;
                        background-color: #ef4444;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 16px;
                        font-weight: bold;
                    ">✗ Simulate Failure</button>

                    <button id="mock-pay-cancel" style="
                        width: 100%;
                        padding: 12px;
                        background-color: #6b7280;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 16px;
                    ">Cancel</button>
                </div>
            `;

            modal.appendChild(popup);
            document.body.appendChild(modal);

            document.getElementById('mock-pay-success').onclick = function() {
                var mockPaymentId = "pay_mock_" + generateRandomId();
                modal.remove();
                
                // Submit form to save booking
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'save_booking.php';
                
                var eventInput = document.createElement('input');
                eventInput.type = 'hidden';
                eventInput.name = 'event_id';
                eventInput.value = eventId;
                form.appendChild(eventInput);
                
                var seatsInput = document.createElement('input');
                seatsInput.type = 'hidden';
                seatsInput.name = 'seats';
                seatsInput.value = JSON.stringify(seats);
                form.appendChild(seatsInput);
                
                var paymentInput = document.createElement('input');
                paymentInput.type = 'hidden';
                paymentInput.name = 'payment_id';
                paymentInput.value = mockPaymentId;
                form.appendChild(paymentInput);
                
                var statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = 'Paid';
                form.appendChild(statusInput);
                
                document.body.appendChild(form);
                form.submit();
            };

            document.getElementById('mock-pay-failure').onclick = function() {
                modal.remove();
                alert("❌ Payment Failed - Test Mode\n\nThis is a simulated failure for testing purposes.");
            };

            document.getElementById('mock-pay-cancel').onclick = function() {
                modal.remove();
                alert("Payment cancelled.");
            };
        }

        function realRazorpayPayment() {
            if (typeof Razorpay === 'undefined') {
                alert("Error: Razorpay SDK not loaded. Check internet connection.");
                return;
            }

            var razorpayKey = "<?php echo $razorpay_key; ?>";
            
            var options = {
                "key": razorpayKey,
                "amount": Math.round(totalPrice * 100),
                "currency": "INR",
                "name": "Event Booking",
                "description": "Tickets for <?php echo htmlspecialchars($event['title']); ?>",
                "handler": function (response){
                    if(response.razorpay_payment_id) {
                        var form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'save_booking.php';
                        
                        var eventInput = document.createElement('input');
                        eventInput.type = 'hidden';
                        eventInput.name = 'event_id';
                        eventInput.value = eventId;
                        form.appendChild(eventInput);
                        
                        var seatsInput = document.createElement('input');
                        seatsInput.type = 'hidden';
                        seatsInput.name = 'seats';
                        seatsInput.value = JSON.stringify(seats);
                        form.appendChild(seatsInput);
                        
                        var paymentInput = document.createElement('input');
                        paymentInput.type = 'hidden';
                        paymentInput.name = 'payment_id';
                        paymentInput.value = response.razorpay_payment_id;
                        form.appendChild(paymentInput);
                        
                        var statusInput = document.createElement('input');
                        statusInput.type = 'hidden';
                        statusInput.name = 'status';
                        statusInput.value = 'Paid';
                        form.appendChild(statusInput);
                        
                        document.body.appendChild(form);
                        form.submit();
                    }
                },
                "prefill": {
                    "name": "<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>",
                    "email": "<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>"
                },
                "theme": {
                    "color": "#6366f1"
                }
            };
            
            var rzp1 = new Razorpay(options);
            rzp1.open();
        }

        function generateRandomId() {
            return Math.random().toString(36).substr(2, 9);
        }
    </script>
</body>
</html>
    
    <div class="checkout-details">
        <p><strong>Event:</strong> <?php echo htmlspecialchars($event['title']); ?></p>
        <p><strong>Seat:</strong> <?php echo htmlspecialchars($seat_number); ?></p>
        <p><strong>Price:</strong> ₹<?php echo number_format($price, 2); ?></p>
    </div>

    <button id="rzp-button1">Pay Now with Razorpay</button>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        var useMockPayment = <?php echo $use_mock_payment ? 'true' : 'false'; ?>;

        document.getElementById('rzp-button1').onclick = function(e){
            e.preventDefault();
            
            if (useMockPayment) {
                // Mock Payment Popup for Testing (No Real Razorpay Account Required)
                mockPaymentPopup();
            } else {
                // Real Razorpay Payment
                realRazorpayPayment();
            }
        }

        function mockPaymentPopup() {
            // Create mock modal
            var modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0,0,0,0.7);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                font-family: sans-serif;
            `;

            var popup = document.createElement('div');
            popup.style.cssText = `
                background: white;
                padding: 30px;
                border-radius: 10px;
                width: 90%;
                max-width: 400px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            `;

            popup.innerHTML = `
                <div style="text-align: center;">
                    <h2 style="color: #1f2937; margin-bottom: 10px;">🏦 Mock Payment Gateway</h2>
                    <p style="color: #6b7280; margin-bottom: 20px;">Testing Mode - No Real Payment</p>
                    
                    <div style="background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <p style="color: #374151; margin: 5px 0;"><strong>Amount:</strong> ₹<?php echo $price; ?></p>
                        <p style="color: #374151; margin: 5px 0;"><strong>Event:</strong> <?php echo htmlspecialchars($event['title']); ?></p>
                        <p style="color: #374151; margin: 5px 0;"><strong>Seat:</strong> <?php echo htmlspecialchars($seat_number); ?></p>
                    </div>

                    <p style="color: #d97706; font-size: 14px; margin-bottom: 20px;">
                        ⚠️ This is a test payment. Select an option below:
                    </p>

                    <button id="mock-pay-success" style="
                        width: 100%;
                        padding: 12px;
                        margin-bottom: 10px;
                        background-color: #10b981;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 16px;
                        font-weight: bold;
                    ">✓ Simulate Success</button>

                    <button id="mock-pay-failure" style="
                        width: 100%;
                        padding: 12px;
                        margin-bottom: 10px;
                        background-color: #ef4444;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 16px;
                        font-weight: bold;
                    ">✗ Simulate Failure</button>

                    <button id="mock-pay-cancel" style="
                        width: 100%;
                        padding: 12px;
                        background-color: #6b7280;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 16px;
                    ">Cancel</button>
                </div>
            `;

            modal.appendChild(popup);
            document.body.appendChild(modal);

            // Handle buttons
            document.getElementById('mock-pay-success').onclick = function() {
                var mockPaymentId = "pay_mock_" + generateRandomId();
                modal.remove();
                window.location.href = "save_booking.php?event_id=<?php echo $event_id; ?>&seat=<?php echo urlencode($seat_number); ?>&status=Paid&payment_id=" + mockPaymentId;
            };

            document.getElementById('mock-pay-failure').onclick = function() {
                modal.remove();
                alert("❌ Payment Failed - Test Mode\n\nThis is a simulated failure for testing purposes.");
            };

            document.getElementById('mock-pay-cancel').onclick = function() {
                modal.remove();
                alert("Payment cancelled.");
            };
        }

        function realRazorpayPayment() {
            // Check if Razorpay is loaded
            if (typeof Razorpay === 'undefined') {
                alert("Error: Razorpay SDK not loaded. Check internet connection.");
                return;
            }

            var razorpayKey = "<?php echo $razorpay_key; ?>";
            
            var options = {
                "key": razorpayKey,
                "amount": <?php echo intval($price * 100); ?>,
                "currency": "INR",
                "name": "Event Booking",
                "description": "Ticket for <?php echo htmlspecialchars($event['title']); ?>",
                "handler": function (response){
                    if(response.razorpay_payment_id) {
                        window.location.href = "save_booking.php?event_id=<?php echo $event_id; ?>&seat=<?php echo urlencode($seat_number); ?>&status=Paid&payment_id=" + response.razorpay_payment_id;
                    }
                },
                "prefill": {
                    "name": "<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>",
                    "email": "<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>"
                },
                "theme": {
                    "color": "#3b82f6"
                }
            };
            
            var rzp1 = new Razorpay(options);
            rzp1.open();
        }

        function generateRandomId() {
            return Math.random().toString(36).substr(2, 9);
        }
    </script>
</body>
</html>