<?php 
session_start();
include 'db_connect.php'; 
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">

    <title>Connect - Financial Seminars</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #111827; color: #000000; }
        .nav { background: #1F2937; padding: 10px; color: #000000; margin-bottom: 20px; }
        .nav a { color: #000000; margin-right: 15px; text-decoration: none; }
        .event-card {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 8px;
            background: #1F2937;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: box-shadow 0.2s, transform 0.2s;
            cursor: pointer;
            width: 100%;
            color: #000000;
        }
        .event-card:hover {
            box-shadow: 0 8px 24px rgba(44, 62, 80, 0.18);
            transform: translateY(-4px) scale(1.02);
            border-color: #8B5CF6;
        }
        .events-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        .search-box { padding: 15px; background: #1F2937; margin-bottom: 20px; color: #000000; }
        .search-box input, .search-box select { background: #111827; color: #000000; border: 1px solid #9CA3AF; padding: 5px; }
        .search-box button { background-color: #8B5CF6; color: #000000; border: none; padding: 8px 12px; cursor: pointer; }
        .search-box button:hover { background-color: #00E5FF; }
    </style>
</head>
<body>

<div class="nav">
        <strong>CONNECT</strong>
        <a href="index.php">Home</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="my_bookings.php">My Tickets</a>
            <?php if($_SESSION['role'] == 'admin'): ?>
                <a href="admin_dashboard.php">Admin Dashboard</a>
                <a href="add_event.php">Post Event</a>
            <?php endif; ?>
            <a href="logout.php" style="float:right;">Logout (<?php echo $_SESSION['username']; ?>)</a>
        <?php else: ?>
            <a href="login.php" style="float:right;">Login / Register</a>
        <?php endif; ?>
    </div>

    <div class="search-box">
        <form method="GET" action="index.php">
            <input type="text" name="search" placeholder="Search event name..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
            <select name="category">
                <option value="">All Categories</option>
                    <option value="Stock Market">Stock Market</option>
            <option value="Crypto">Crypto</option>
            <option value="Real Estate">Real Estate</option>
            <option value="Banking">Banking</option>
            <option value="Finance">Finance</option>
            <option value="Networking">Networking</option>
            <option value="Technology">Technology</option>
            <option value="Career Guidance">Career Guidance</option>
            </select>
            <button type="submit">Search</button>
        </form>
    </div>
    <?php
    // Dynamic Query Builder for Search
    $sql = "SELECT * FROM events WHERE 1=1";

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $_GET['search'];
        $sql .= " AND title LIKE '%$search%'";
    }
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $cat = $_GET['category'];
        $sql .= " AND category = '$cat'";
    }

    $sql .= " ORDER BY event_date DESC";
    $result = mysqli_query($conn, $sql);
    ?>
    <div class="container" style="background-color:#1F2937;">
    <div class="card">
        <h1 style="color: #000000;">Available Events</h1>

    <?php if (mysqli_num_rows($result) > 0) {
        echo "<div class='events-grid'>";
        while ($row = mysqli_fetch_assoc($result)) {
            $event_id_loop = $row['id'];
            $review_q = "SELECT AVG(rating) as avg_rating FROM reviews WHERE event_id = '$event_id_loop'";
            $rev_res = mysqli_query($conn, $review_q);
            $rev = mysqli_fetch_assoc($rev_res);
            $avg_rating = $rev['avg_rating'] ? round($rev['avg_rating'],1) : '4.5';

            $att_q = "SELECT COUNT(*) as attendees FROM bookings WHERE event_id = '$event_id_loop'";
            $att_res = mysqli_query($conn, $att_q);
            $att = mysqli_fetch_assoc($att_res);
            $attendees = $att['attendees'];

            echo "<div class='event-card' style='background-color:white; text-color:black;' onclick=\"window.location.href='view_event.php?id=" . $row['id'] . "'\">";
            $img = !empty($row['image_path']) ? $row['image_path'] : 'uploads/1769186605_photo.avif';
            echo "<div class='event-media' style=\"background-image: url('" . $img . "');\">";
            echo "<span class='tag'>" . htmlspecialchars($row['category']) . "</span>";
            echo "<span class='fav'>&hearts;</span>";
            echo "<div class='rating'>★ " . $avg_rating . "</div>";
            echo "</div>";

            echo "<div class='body'>";
            echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
            $venue_text = (isset($row['location_url']) && !empty($row['location_url'])) ? 'Venue' : '';
            echo "<div class='meta'>" . ($attendees > 0 ? $attendees . "+" : "0") . " • " . $venue_text . "</div>";
            echo "<div class='footer'>";
            echo "<div class='price'>₹" . number_format($row['price']) . "</div>";
            echo "<button class='cta' onclick=\"window.location.href='view_event.php?id=" . $row['id'] . "'\">Book Now</button>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p>No seminars found. Try a different search.</p>";
    }
    ?>
    </div>
</div>
</div>
</body>
</html>