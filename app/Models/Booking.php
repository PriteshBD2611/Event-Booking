<?php
/**
 * Booking Model
 * Handles all booking-related database operations
 */

require_once __DIR__ . '/Model.php';

class Booking extends Model {
    protected $table = 'bookings';
    protected $fillable = ['user_id', 'event_id', 'seat_number', 'payment_status', 'created_at'];

    /**
     * Create a new booking
     * 
     * @param int $userId
     * @param int $eventId
     * @param string $seatNumber
     * @param string $paymentStatus
     * @return bool|string (booking ID on success, false on failure)
     */
    public function createBooking($userId, $eventId, $seatNumber = 'General', $paymentStatus = 'Pending') {
        // Validate inputs
        if (!sanitizeInt($userId) || !sanitizeInt($eventId)) {
            logMessage("Invalid booking parameters", 'warning');
            return false;
        }
        
        // Check if event exists
        $eventStmt = $this->pdo->prepare("SELECT id FROM events WHERE id = ?");
        $eventStmt->execute([$eventId]);
        if (!$eventStmt->fetch()) {
            logMessage("Booking failed: Event $eventId not found", 'warning');
            return false;
        }
        
        // Check if seat is already booked
        if ($seatNumber !== 'General') {
            $seatStmt = $this->pdo->prepare("
                SELECT id FROM {$this->table}
                WHERE event_id = ? AND seat_number = ? AND payment_status IN ('Paid', 'Pending')
            ");
            $seatStmt->execute([$eventId, $seatNumber]);
            if ($seatStmt->fetch()) {
                logMessage("Booking failed: Seat $seatNumber already booked for event $eventId", 'warning');
                return false;
            }
        }
        
        $data = [
            'user_id' => $userId,
            'event_id' => $eventId,
            'seat_number' => sanitizeInput($seatNumber),
            'payment_status' => in_array($paymentStatus, ['Paid', 'Pending', 'Cancelled']) ? $paymentStatus : 'Pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            if ($this->insert($data)) {
                $bookingId = $this->lastInsertId();
                logMessage("Booking created: ID $bookingId, User $userId, Event $eventId", 'info');
                return $bookingId;
            }
            return false;
        } catch (Exception $e) {
            logMessage("Booking creation error: " . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Get user bookings with event details
     * 
     * @param int $userId
     * @return array
     */
    public function getUserBookings($userId) {
        $stmt = $this->pdo->prepare("
            SELECT b.*, e.title, e.event_date, e.price, e.location_url, e.image_path
            FROM {$this->table} b
            JOIN events e ON b.event_id = e.id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get event bookings
     * 
     * @param int $eventId
     * @return array
     */
    public function getEventBookings($eventId) {
        $stmt = $this->pdo->prepare("
            SELECT b.*, u.username, u.email
            FROM {$this->table} b
            JOIN users u ON b.user_id = u.id
            WHERE b.event_id = ?
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get booking details with event info
     * 
     * @param int $bookingId
     * @return array|bool
     */
    public function getBookingDetails($bookingId) {
        $stmt = $this->pdo->prepare("
            SELECT b.*, e.title, e.event_date, e.price, e.location_url, u.username, u.email
            FROM {$this->table} b
            JOIN events e ON b.event_id = e.id
            JOIN users u ON b.user_id = u.id
            WHERE b.id = ?
        ");
        $stmt->execute([$bookingId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update payment status
     * 
     * @param int $bookingId
     * @param string $status
     * @return bool
     */
    public function updatePaymentStatus($bookingId, $status) {
        if (!in_array($status, ['Paid', 'Pending', 'Cancelled'])) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("UPDATE {$this->table} SET payment_status = ? WHERE id = ?");
            $result = $stmt->execute([$status, $bookingId]);
            if ($result) {
                logMessage("Booking payment updated: ID $bookingId, Status $status", 'info');
            }
            return $result;
        } catch (Exception $e) {
            logMessage("Payment status update error: " . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Cancel booking
     * 
     * @param int $bookingId
     * @param int $userId
     * @return bool
     */
    public function cancelBooking($bookingId, $userId) {
        $booking = $this->find($bookingId);
        
        if (!$booking || $booking['user_id'] != $userId) {
            logMessage("Unauthorized booking cancellation attempt: User $userId, Booking $bookingId", 'warning');
            return false;
        }
        
        try {
            $result = $this->updatePaymentStatus($bookingId, 'Cancelled');
            if ($result) {
                logMessage("Booking cancelled: ID $bookingId by user $userId", 'info');
            }
            return $result;
        } catch (Exception $e) {
            logMessage("Booking cancellation error: " . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Get booking statistics for an event
     * 
     * @param int $eventId
     * @return array
     */
    public function getEventStats($eventId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN payment_status = 'Paid' THEN 1 ELSE 0 END) as paid_bookings,
                SUM(CASE WHEN payment_status = 'Pending' THEN 1 ELSE 0 END) as pending_bookings,
                SUM(CASE WHEN payment_status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_bookings
            FROM {$this->table}
            WHERE event_id = ?
        ");
        $stmt->execute([$eventId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Bulk create bookings
     * 
     * @param int $userId
     * @param int $eventId
     * @param int $quantity
     * @param string $seatNumber
     * @param string $paymentStatus
     * @return array (created booking IDs)
     */
    public function bulkCreate($userId, $eventId, $quantity = 1, $seatNumber = 'General', $paymentStatus = 'Pending') {
        $createdBookings = [];
        
        try {
            $this->beginTransaction();
            
            for ($i = 0; $i < $quantity; $i++) {
                $bookingId = $this->createBooking($userId, $eventId, $seatNumber, $paymentStatus);
                if ($bookingId) {
                    $createdBookings[] = $bookingId;
                } else {
                    throw new Exception("Failed to create booking $i");
                }
            }
            
            $this->commit();
            logMessage("Bulk booking created: $quantity bookings for user $userId, event $eventId", 'info');
            
        } catch (Exception $e) {
            $this->rollback();
            logMessage("Bulk booking error: " . $e->getMessage(), 'error');
        }
        
        return $createdBookings;
    }
}
?>
