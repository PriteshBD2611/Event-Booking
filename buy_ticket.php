<?php
session_start();
include 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please <a href='login.php'>login</a> to buy tickets.");
}

// Validate and sanitize inputs
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$seat_number = isset($_GET['seat']) ? htmlspecialchars($_GET['seat']) : '';
$user_id = $_SESSION['user_id'];

if ($event_id <= 0 || empty($seat_number)) {
    die("Invalid event or seat selection.");
}

// Fetch event details to get the price
$sql = "SELECT id, title, price FROM events WHERE id = '$event_id'";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Event not found.");
}

$event = mysqli_fetch_assoc($result);
$price = $event['price'];

// Mock Razorpay Key for testing (when you get a real account, replace this)
$razorpay_key = "rzp_test_mock_test_key_12345";
$use_mock_payment = true; // Set to false when you have real Razorpay key
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Buy Ticket</title>
    <style>
        body { font-family: sans-serif; background-color: #111827; color: #e5e7eb; padding: 20px; }
        h2 { color: #e5e7eb; margin-bottom: 20px; }
        .checkout-details { background-color: #1f2937; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        p { color: #e5e7eb; margin: 10px 0; }
        #rzp-button1 {
            background-color: #3b82f6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        #rzp-button1:hover { background-color: #2563eb; }
    </style>
</head>
<body>
    <h2>Checkout: <?php echo htmlspecialchars($event['title']); ?></h2>
    
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