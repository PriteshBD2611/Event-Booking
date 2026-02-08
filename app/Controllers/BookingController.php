<?php
/**
 * BookingController
 * Handles ticket booking operations
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Booking.php';
require_once __DIR__ . '/../Models/Event.php';

class BookingController extends Controller {
    private $bookingModel;
    private $eventModel;

    public function __construct($pdo, $logger = null) {
        parent::__construct($pdo, $logger);
        $this->bookingModel = new Booking($pdo);
        $this->eventModel = new Event($pdo);
    }

    /**
     * Show seat selection page
     */
    public function showSeatSelection() {
        requireLogin();

        $eventId = sanitizeInt($_GET['id'] ?? 0);

        if (!$eventId) {
            $this->redirect(env('APP_URL') . '/index.php');
        }

        $event = $this->eventModel->find($eventId);

        if (!$event) {
            $this->setFlash('Event not found.', 'error');
            $this->redirect(env('APP_URL') . '/index.php');
        }

        // Get booked seats
        $bookedSeats = $this->getBookedSeats($eventId);

        $this->render('bookings/select-seat', [
            'pageTitle' => 'Select Seat',
            'event' => $event,
            'bookedSeats' => $bookedSeats
        ]);
    }

    /**
     * Get booked seats for an event
     */
    private function getBookedSeats($eventId) {
        $stmt = $this->pdo->prepare("
            SELECT seat_number FROM bookings
            WHERE event_id = ? AND payment_status IN ('Paid', 'Pending')
        ");
        $stmt->execute([$eventId]);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return array_filter($results, fn($seat) => $seat !== 'General');
    }

    /**
     * Show booking confirmation page
     */
    public function showConfirmation() {
        requireLogin();

        $eventId = sanitizeInt($_GET['event_id'] ?? 0);
        $quantity = sanitizeInt($_GET['qty'] ?? 1) ?: 1;
        $seat = sanitizeInput($_GET['seat'] ?? 'General');

        if (!$eventId || $quantity < 1 || $quantity > 10) {
            $this->redirect(env('APP_URL') . '/index.php');
        }

        $event = $this->eventModel->find($eventId);

        if (!$event) {
            $this->setFlash('Event not found.', 'error');
            $this->redirect(env('APP_URL') . '/index.php');
        }

        $totalPrice = $event['price'] * $quantity;

        $this->render('bookings/confirm', [
            'pageTitle' => 'Confirm Booking',
            'event' => $event,
            'quantity' => $quantity,
            'seat' => $seat,
            'totalPrice' => $totalPrice
        ]);
    }

    /**
     * Process booking
     */
    public function processBooking() {
        requireLogin();

        if (!$this->isPost()) {
            $this->redirect(env('APP_URL') . '/index.php');
        }

        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('Security token expired. Please try again.', 'error');
            $this->redirect(env('APP_URL') . '/index.php');
        }

        $eventId = sanitizeInt($_POST['event_id'] ?? 0);
        $quantity = sanitizeInt($_POST['quantity'] ?? 1) ?: 1;
        $seat = sanitizeInput($_POST['seat'] ?? 'General');

        if (!$eventId || $quantity < 1 || $quantity > 10) {
            $this->setFlash('Invalid booking details.', 'error');
            $this->redirect(env('APP_URL') . '/index.php');
        }

        $event = $this->eventModel->find($eventId);

        if (!$event) {
            $this->setFlash('Event not found.', 'error');
            $this->redirect(env('APP_URL') . '/index.php');
        }

        // Create bookings
        $bookingIds = $this->bookingModel->bulkCreate(
            $_SESSION['user_id'],
            $eventId,
            $quantity,
            $seat,
            'Paid'
        );

        if (!empty($bookingIds)) {
            $this->setFlash("Successfully booked $quantity ticket(s) for {$event['title']}!", 'success');
            $this->redirect(env('APP_URL') . '/my-bookings.php');
        } else {
            $this->setFlash('Booking failed. Please try again.', 'error');
            $this->redirect(env('APP_URL') . "/buy-ticket.php?id=$eventId");
        }
    }

    /**
     * Show user bookings
     */
    public function myBookings() {
        requireLogin();

        $bookings = $this->bookingModel->getUserBookings($_SESSION['user_id']);

        $this->render('bookings/my-bookings', [
            'pageTitle' => 'My Bookings',
            'bookings' => $bookings
        ]);
    }

    /**
     * Show booking details
     */
    public function viewBooking() {
        requireLogin();

        $bookingId = sanitizeInt($_GET['id'] ?? 0);

        if (!$bookingId) {
            $this->redirect(env('APP_URL') . '/my-bookings.php');
        }

        $booking = $this->bookingModel->getBookingDetails($bookingId);

        if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
            $this->setFlash('Booking not found.', 'error');
            $this->redirect(env('APP_URL') . '/my-bookings.php');
        }

        $this->render('bookings/view', [
            'pageTitle' => 'Booking Details',
            'booking' => $booking
        ]);
    }

    /**
     * Cancel booking
     */
    public function cancelBooking() {
        requireLogin();

        $bookingId = sanitizeInt($_GET['id'] ?? 0);

        if (!$bookingId) {
            $this->redirect(env('APP_URL') . '/my-bookings.php');
        }

        if ($this->bookingModel->cancelBooking($bookingId, $_SESSION['user_id'])) {
            $this->setFlash('Booking cancelled successfully!', 'success');
            $this->redirect(env('APP_URL') . '/my-bookings.php');
        } else {
            $this->setFlash('Failed to cancel booking.', 'error');
            $this->redirect(env('APP_URL') . '/my-bookings.php');
        }
    }

    /**
     * Get event booking statistics (admin only)
     */
    public function getEventStats() {
        requireLogin();
        requireAdmin();

        $eventId = sanitizeInt($_GET['event_id'] ?? 0);

        if (!$eventId) {
            $this->json(['error' => 'Invalid event'], 400);
        }

        // Verify admin owns this event
        $event = $this->eventModel->find($eventId);
        if (!$event || $event['created_by'] != $_SESSION['user_id']) {
            $this->json(['error' => 'Unauthorized'], 403);
        }

        $stats = $this->bookingModel->getEventStats($eventId);

        $this->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Export bookings to CSV (admin only)
     */
    public function exportBookings() {
        requireLogin();
        requireAdmin();

        $eventId = sanitizeInt($_GET['event_id'] ?? 0);

        if (!$eventId) {
            $this->redirect(env('APP_URL') . '/admin/dashboard.php');
        }

        // Verify admin owns this event
        $event = $this->eventModel->find($eventId);
        if (!$event || $event['created_by'] != $_SESSION['user_id']) {
            $this->setFlash('Unauthorized access.', 'error');
            $this->redirect(env('APP_URL') . '/admin/dashboard.php');
        }

        $bookings = $this->bookingModel->getEventBookings($eventId);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="bookings_' . $eventId . '_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Booking ID', 'User', 'Email', 'Seat', 'Payment Status', 'Date']);

        foreach ($bookings as $booking) {
            fputcsv($output, [
                $booking['id'],
                $booking['username'],
                $booking['email'],
                $booking['seat_number'],
                $booking['payment_status'],
                formatDate($booking['created_at'])
            ]);
        }

        fclose($output);
        exit();
    }
}
?>
