<?php
/**
 * Event Model
 * Handles all event-related database operations
 */

require_once __DIR__ . '/Model.php';

class Event extends Model {
    protected $table = 'events';
    protected $fillable = ['title', 'description', 'location_url', 'image_path', 'price', 'event_date', 'speaker', 'category', 'created_by', 'created_at'];

    /**
     * Create a new event
     * 
     * @param array $data
     * @param int $userId
     * @return bool|string (event ID on success, false on failure)
     */
    public function createEvent($data, $userId) {
        // Validate required fields
        $required = ['title', 'description', 'location_url', 'price', 'event_date', 'category'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                logMessage("Event creation failed: Missing field $field", 'warning');
                return false;
            }
        }
        
        // Validate event date
        if (!isValidDate($data['event_date'])) {
            logMessage("Event creation failed: Invalid date format", 'warning');
            return false;
        }
        
        // Validate price
        $price = sanitizeFloat($data['price']);
        if ($price === false || $price < 0) {
            logMessage("Event creation failed: Invalid price", 'warning');
            return false;
        }
        
        $eventData = [
            'title' => sanitizeInput($data['title']),
            'description' => sanitizeInput($data['description']),
            'location_url' => sanitizeInput($data['location_url']),
            'price' => $price,
            'event_date' => $data['event_date'],
            'speaker' => isset($data['speaker']) ? sanitizeInput($data['speaker']) : null,
            'category' => sanitizeInput($data['category']),
            'image_path' => isset($data['image_path']) ? $data['image_path'] : null,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            if ($this->insert($eventData)) {
                $eventId = $this->lastInsertId();
                logMessage("Event created: ID $eventId by user $userId", 'info');
                return $eventId;
            }
            return false;
        } catch (Exception $e) {
            logMessage("Event creation error: " . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Get event details with creator info
     * 
     * @param int $id
     * @return array|bool
     */
    public function getEventWithCreator($id) {
        $stmt = $this->pdo->prepare("
            SELECT e.*, u.username as creator_name
            FROM {$this->table} e
            LEFT JOIN users u ON e.created_by = u.id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get events by category
     * 
     * @param string $category
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByCategory($category, $limit = 10, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE category = ?
            ORDER BY event_date ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$category, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search events
     * 
     * @param string $keyword
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function search($keyword, $limit = 10, $offset = 0) {
        $searchTerm = '%' . sanitizeInput($keyword) . '%';
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE title LIKE ? OR description LIKE ? OR speaker LIKE ?
            ORDER BY event_date ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get upcoming events
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getUpcoming($limit = 10, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE event_date >= CURDATE()
            ORDER BY event_date ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update event
     * 
     * @param int $id
     * @param array $data
     * @param int $userId (creator verification)
     * @return bool
     */
    public function updateEvent($id, $data, $userId) {
        $event = $this->find($id);
        
        if (!$event || $event['created_by'] != $userId) {
            logMessage("Unauthorized event update attempt: User $userId, Event $id", 'warning');
            return false;
        }
        
        $allowedFields = ['title', 'description', 'location_url', 'price', 'event_date', 'speaker', 'category', 'image_path'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        foreach ($updateData as $key => $value) {
            if ($key === 'price') {
                $updateData[$key] = sanitizeFloat($value);
            } elseif ($key !== 'image_path') {
                $updateData[$key] = sanitizeInput($value);
            }
        }
        
        try {
            $result = $this->update($id, $updateData);
            if ($result) {
                logMessage("Event updated: ID $id by user $userId", 'info');
            }
            return $result;
        } catch (Exception $e) {
            logMessage("Event update error: " . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Delete event
     * 
     * @param int $id
     * @param int $userId (creator verification)
     * @return bool
     */
    public function deleteEvent($id, $userId) {
        $event = $this->find($id);
        
        if (!$event || $event['created_by'] != $userId) {
            logMessage("Unauthorized event delete attempt: User $userId, Event $id", 'warning');
            return false;
        }
        
        try {
            $result = $this->delete($id);
            if ($result) {
                logMessage("Event deleted: ID $id by user $userId", 'info');
            }
            return $result;
        } catch (Exception $e) {
            logMessage("Event delete error: " . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Get events created by user
     * 
     * @param int $userId
     * @return array
     */
    public function getByCreator($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE created_by = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
