# Event-Booking Website

A PHP-based Event Booking System with user authentication, event management, and ticket booking functionality.

## 📁 Project Structure

```
Event-Booking/
├── config/
│   └── db.php              # Database connection configuration
├── admin/
│   ├── dashboard.php       # Admin dashboard
│   ├── add_event.php       # Add new event form
│   ├── edit_event.php      # Edit event form
│   └── delete_event.php    # Delete event handler
├── assets/
│   ├── css/
│   │   └── style.css       # Main stylesheet
│   ├── js/                 # JavaScript files (future)
│   └── img/                # Image assets
├── includes/               # Reusable PHP components (prepared)
├── uploads/
│   └── posters/            # Event poster images
├── index.php               # Homepage
├── login.php               # User login
├── register.php            # User registration
├── view_event.php          # Event details page
├── buy_ticket.php          # Ticket purchase page
├── select_seat.php         # Seat selection
├── my_bookings.php         # User bookings history
├── rate_event.php          # Event rating system
├── save_booking.php        # Booking handler
└── logout.php              # User logout
```

## 🚀 Features

- ✅ User Authentication (Login/Register)
- ✅ Event Listing & Details
- ✅ Ticket Booking System
- ✅ Seat Selection
- ✅ Admin Panel for Event Management
- ✅ Event Rating System
- ✅ User Booking History
- ✅ Responsive Design

## 📋 Requirements

- PHP 7.4+
- MySQL/MariaDB
- XAMPP or similar local server
- Web Browser

## 🔧 Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/PriteshBD/Event-Booking.git
   cd Event-Booking
   ```

2. **Setup Database:**
   - Create a MySQL database named `connect_db`
   - Import any SQL dump files if available

3. **Configure Database Connection:**
   - Edit `config/db.php` with your database credentials
   - Ensure MySQL server is running

4. **Access the Application:**
   - Place project in `htdocs/` or web root
   - Start Apache & MySQL in XAMPP
   - Navigate to: `http://localhost/Event-Booking/`

## 🔐 Security Notes

- **Important:** `config/db.php` is in `.gitignore` and should never be committed
- Update database credentials in `config/db.php` before deploying
- Use parameterized queries to prevent SQL injection
- Sanitize user inputs properly

## 📝 Database Configuration

Update `config/db.php` with your credentials:

```php
<?php
$servername = "localhost";
$username = "root";
$password = ""; // Your password
$dbname = "connect_db";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```

## 👤 User Roles

- **Regular User:** Can browse events, book tickets, rate events
- **Admin:** Can manage events (add, edit, delete), view bookings, access dashboard

## 📸 Event Image Upload

Event posters are uploaded to `uploads/` directory with timestamp-based filenames for uniqueness.

## 🎨 Styling

Main stylesheet: `assets/css/style.css`
- Dark theme with purple/cyan accent colors
- Responsive grid layout for event cards
- Modern UI components

## 🐛 Troubleshooting

**Database Connection Issues:**
- Verify MySQL is running in XAMPP
- Check credentials in `config/db.php`
- Ensure database `connect_db` exists

**File Upload Errors:**
- Check `uploads/` directory has write permissions
- Verify file size limits in PHP config

**Admin Access Denied:**
- Ensure user role is set to 'admin' in database
- Clear browser cache/cookies

## 📄 License

This project is open source. Feel free to modify and use as needed.

## 👨‍💻 Author

PriteshBD - [GitHub Profile](https://github.com/PriteshBD)

## 🤝 Contributing

Feel free to fork, modify, and submit pull requests!

---

**Last Updated:** February 2026
**Version:** 1.0
