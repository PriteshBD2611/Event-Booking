<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please <a href='login.php'>login</a> to buy tickets.");
}

$event_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch event details to get the price
$sql = "SELECT * FROM events WHERE id = '$event_id'";
$result = mysqli_query($conn, $sql);
$event = mysqli_fetch_assoc($result);
$price = $event['price'];
$seat_number = $_GET['seat']; // Get the seat user clicked
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">

    <title>Buy Ticket</title>
    <script src="https://www.paypal.com/sdk/js?client-id=Ad3PJuzwknTqCnmIMI767plher_kOKbRB2R3JK_dooW8GNFt0Gh4o3GBsaYCyI09CBhzNFdvlLTzc_UK&currency=USD"></script>
    <style>
        body { font-family: sans-serif; background-color: #111827; color: #000000; padding: 20px; }
        h2 { color: #000000; }
        p { color: #000000; }
        #paypal-button-container { margin-top: 20px; }
    </style>
</head>
<body>
    <h2>Checkout: <?php echo $event['title']; ?></h2>
    <p>Price: ₹<?php echo $price; ?></p>

    <div id="paypal-button-container"></div>

    <script>
        // Check if price is valid
        var price = '<?php echo $price; ?>';
        if (!price || price === 'undefineld' || price === '' || isNaN(parseFloat(price))) {
            document.getElementById('paypal-button-container').innerHTML = '<p style="color: red;">Error: Invalid price. Please contact support.</p>';
        } else {
            paypal.Buttons({
                createOrder: function(data, actions) {
                    console.log('Creating order with amount:', price);
                    // Set up the transaction
                    return actions.order.create({
                        purchase_units: [{
                            amount: {
                                currency_code: 'INR',
                                value: price
                            }
                        }]
                    });
                },
                onApprove: function(data, actions) {
                    // This function runs when payment is successful
                    return actions.order.capture().then(function(details) {
                        console.log('Payment captured:', details);
                        // Redirect after payment is confirmed with a slight delay
                        setTimeout(function() {
                            window.location.href = "save_booking.php?event_id=<?php echo $event_id; ?>&seat=<?php echo $seat_number; ?>&status=Paid";
                        }, 1000);
                    });
                },
                onError: function(err) {
                    console.error('Payment error:', err);
                    alert('Error: ' + (err.message || 'An error occurred during the transaction. Please try again.'));
                }
            }).render('#paypal-button-container');
        }
    </script>
</body>
</html>