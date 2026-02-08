<?php
session_start();
include 'config/db.php';

if (!isset($_GET['id'])) {
    die("Event ID missing.");
}

$event_id = intval($_GET['id']);
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit();
}
$user_id = $_SESSION['user_id'];

// Get event details for price
$event_stmt = $conn->prepare("SELECT id, title, price FROM events WHERE id = ?");
$event_stmt->execute([$event_id]);
$event = $event_stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Event not found.");
}

// Get List of Already Booked Seats
$booked_seats = [];
$stmt = $conn->prepare("SELECT seat_number FROM bookings WHERE event_id = ? AND payment_status IN ('Paid', 'Pending')");
$stmt->execute([$event_id]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $booked_seats[] = $row['seat_number'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Select Seats - <?php echo htmlspecialchars($event['title']); ?></title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #111827;
            color: #e5e7eb;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            color: #e5e7eb;
            margin-bottom: 10px;
        }
        .event-info {
            background: #1F2937;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .event-info p {
            margin: 5px 0;
            color: #9CA3AF;
        }
        .screen {
            background: #333;
            color: white;
            text-align: center;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 18px;
        }
        .seat-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 10px;
            margin-bottom: 30px;
            background: #1F2937;
            padding: 20px;
            border-radius: 8px;
        }
        .seat {
            padding: 15px;
            text-align: center;
            border: 2px solid #22d3ee;
            background-color: #10b981;
            cursor: pointer;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .seat:hover {
            background-color: #059669;
            transform: scale(1.05);
        }
        .seat.booked {
            background-color: #ef4444;
            border-color: #dc2626;
            cursor: not-allowed;
            opacity: 0.6;
        }
        .seat.selected {
            background-color: #8B5CF6;
            border-color: #7c3aed;
            box-shadow: 0 0 10px rgba(139, 92, 246, 0.5);
            transform: scale(1.1);
        }
        .legend {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .legend-box {
            width: 30px;
            height: 30px;
            border-radius: 4px;
            border: 2px solid;
        }
        .legend-available {
            background-color: #10b981;
            border-color: #22d3ee;
        }
        .legend-booked {
            background-color: #ef4444;
            border-color: #dc2626;
        }
        .legend-selected {
            background-color: #8B5CF6;
            border-color: #7c3aed;
        }
        .summary {
            background: #1F2937;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .summary h3 {
            color: #e5e7eb;
            margin-top: 0;
        }
        .selected-seats {
            background: #111827;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            min-height: 40px;
        }
        .selected-seats p {
            margin: 0 0 10px 0;
            color: #9CA3AF;
        }
        .seats-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .seat-badge {
            background: #6366f1;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
        }
        .price-info {
            background: #064e3b;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            color: #86efac;
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
        #confirm-btn {
            background: linear-gradient(135deg, #6366f1, #22d3ee);
            color: #040025;
            flex: 1;
            min-width: 150px;
        }
        #confirm-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }
        #confirm-btn:disabled {
            background: #6b7280;
            cursor: not-allowed;
            opacity: 0.6;
        }
        #clear-btn {
            background: #6b7280;
            color: white;
            flex: 1;
            min-width: 150px;
        }
        #clear-btn:hover {
            background: #4b5563;
        }
        .back-btn {
            background: #6b7280;
            color: white;
            flex: 1;
            min-width: 150px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .back-btn:hover {
            background: #4b5563;
        }
        .error-message {
            background: #7f1d1d;
            color: #fecaca;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #dc2626;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎫 Select Your Seats</h1>
        
        <div class="event-info">
            <p><strong>Event:</strong> <?php echo htmlspecialchars($event['title']); ?></p>
            <p><strong>Price per Seat:</strong> ₹<?php echo number_format($event['price'], 2); ?></p>
        </div>

        <div class="legend">
            <div class="legend-item">
                <div class="legend-box legend-available"></div>
                <span>Available</span>
            </div>
            <div class="legend-item">
                <div class="legend-box legend-booked"></div>
                <span>Booked</span>
            </div>
            <div class="legend-item">
                <div class="legend-box legend-selected"></div>
                <span>Selected</span>
            </div>
        </div>

        <div class="screen">🎬 STAGE / SCREEN</div>

        <div class="seat-grid" id="seatGrid">
            <?php
            // Generate 50 Seats (5 rows of 10)
            for ($i = 1; $i <= 50; $i++) {
                $isBooked = in_array($i, $booked_seats);
                $bookedClass = $isBooked ? 'booked' : '';
                $seatId = 'seat-' . $i;
                
                echo "<div class='seat $bookedClass' id='$seatId' data-seat='$i'" . 
                     ($isBooked ? " disabled" : " onclick='toggleSeat($i)'") . 
                     ">$i</div>";
            }
            ?>
        </div>

        <div class="summary">
            <h3>📋 Your Selection</h3>
            <div class="selected-seats">
                <p>Selected Seats:</p>
                <div class="seats-list" id="selectedList">
                    <span style="color: #6b7280; font-style: italic;">No seats selected</span>
                </div>
            </div>
            
            <div class="price-info">
                <strong>Total Price:</strong> ₹<span id="totalPrice">0.00</span>
                <span style="margin-left: 20px;">(<span id="seatCount">0</span> seat<span id="seatPlural">s</span>)</span>
            </div>

            <div class="button-group">
                <button id="confirm-btn" onclick="confirmSelection()" disabled>
                    ✓ Confirm & Proceed to Payment
                </button>
                <button id="clear-btn" onclick="clearSelection()">
                    ✕ Clear Selection
                </button>
                <a href="view_event.php?id=<?php echo $event_id; ?>" class="back-btn">
                    ← Back to Event
                </a>
            </div>
        </div>
    </div>

    <script>
        const pricePerSeat = <?php echo $event['price']; ?>;
        const eventId = <?php echo $event_id; ?>;
        let selectedSeats = [];

        function toggleSeat(seatNumber) {
            const seatElement = document.getElementById('seat-' + seatNumber);
            
            // Check if seat is booked
            if (seatElement.classList.contains('booked')) {
                return;
            }

            // Toggle selection
            if (selectedSeats.includes(seatNumber)) {
                selectedSeats = selectedSeats.filter(s => s !== seatNumber);
                seatElement.classList.remove('selected');
            } else {
                selectedSeats.push(seatNumber);
                seatElement.classList.add('selected');
            }

            // Sort seats numerically
            selectedSeats.sort((a, b) => a - b);
            
            updateSummary();
        }

        function updateSummary() {
            const count = selectedSeats.length;
            const totalPrice = count * pricePerSeat;

            // Update selected seats display
            const selectedList = document.getElementById('selectedList');
            if (count === 0) {
                selectedList.innerHTML = '<span style="color: #6b7280; font-style: italic;">No seats selected</span>';
            } else {
                const badges = selectedSeats.map(s => `<span class="seat-badge">Seat ${s}</span>`).join('');
                selectedList.innerHTML = badges;
            }

            // Update price
            document.getElementById('totalPrice').textContent = totalPrice.toFixed(2);
            document.getElementById('seatCount').textContent = count;
            document.getElementById('seatPlural').textContent = count === 1 ? '' : 's';

            // Enable/disable confirm button
            const confirmBtn = document.getElementById('confirm-btn');
            confirmBtn.disabled = count === 0;
        }

        function clearSelection() {
            // Remove selected class from all seats
            selectedSeats.forEach(seatNumber => {
                const seatElement = document.getElementById('seat-' + seatNumber);
                seatElement.classList.remove('selected');
            });

            // Clear array
            selectedSeats = [];
            
            updateSummary();
        }

        function confirmSelection() {
            if (selectedSeats.length === 0) {
                alert('Please select at least one seat!');
                return;
            }

            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'buy_ticket.php';

            // Add hidden fields
            const eventIdInput = document.createElement('input');
            eventIdInput.type = 'hidden';
            eventIdInput.name = 'event_id';
            eventIdInput.value = eventId;
            form.appendChild(eventIdInput);

            const seatsInput = document.createElement('input');
            seatsInput.type = 'hidden';
            seatsInput.name = 'seats';
            seatsInput.value = JSON.stringify(selectedSeats);
            form.appendChild(seatsInput);

            // Submit form
            document.body.appendChild(form);
            form.submit();
        }

        // Initialize summary on page load
        updateSummary();
    </script>
</body>
</html>