<?php
/**
 * EventController
 * Handles event-related operations (create, update, delete, view)
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Event.php';

class EventController extends Controller {
    private $eventModel;

    public function __construct($pdo, $logger = null) {
        parent::__construct($pdo, $logger);
        $this->eventModel = new Event($pdo);
    }

    /**
     * Show event listing page
     */
    public function index() {
        $page = sanitizeInt($_GET['page'] ?? 1) ?: 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $events = $this->eventModel->getUpcoming($limit, $offset);
        $totalEvents = $this->eventModel->count();
        $totalPages = ceil($totalEvents / $limit);

        $this->render('events/index', [
            'pageTitle' => 'Events',
            'events' => $events,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Show event details
     */
    public function view() {
        $eventId = sanitizeInt($_GET['id'] ?? 0);

        if (!$eventId) {
            $this->redirect(env('APP_URL') . '/index.php');
        }

        $event = $this->eventModel->getEventWithCreator($eventId);

        if (!$event) {
            $this->setFlash('Event not found.', 'error');
            $this->redirect(env('APP_URL') . '/index.php');
        }

        $this->render('events/view', [
            'pageTitle' => $event['title'],
            'event' => $event
        ]);
    }

    /**
     * Show event creation form
     */
    public function showCreate() {
        requireLogin();
        requireAdmin();

        $this->render('admin/create-event', [
            'pageTitle' => 'Create Event'
        ]);
    }

    /**
     * Handle event creation
     */
    public function create() {
        requireLogin();
        requireAdmin();

        if (!$this->isPost()) {
            $this->redirect(env('APP_URL') . '/admin/add-event.php');
        }

        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('Security token expired. Please try again.', 'error');
            $this->redirect(env('APP_URL') . '/admin/add-event.php');
        }

        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'location_url' => $_POST['location'] ?? '',
            'price' => $_POST['price'] ?? 0,
            'event_date' => $_POST['date'] ?? '',
            'speaker' => $_POST['speaker'] ?? '',
            'category' => $_POST['category'] ?? ''
        ];

        // Handle file upload
        if (!empty($_FILES['event_image']['name'])) {
            $uploadDir = env('UPLOAD_DIR', __DIR__ . '/../../uploads/');
            $filename = saveUploadedFile($_FILES['event_image'], $uploadDir);
            if ($filename) {
                $data['image_path'] = $uploadDir . $filename;
            }
        }

        $eventId = $this->eventModel->createEvent($data, $_SESSION['user_id']);

        if ($eventId) {
            $this->setFlash('Event created successfully!', 'success');
            $this->redirect(env('APP_URL') . "/view-event.php?id=$eventId");
        } else {
            $this->setFlash('Failed to create event. Please try again.', 'error');
            $this->redirect(env('APP_URL') . '/admin/add-event.php');
        }
    }

    /**
     * Show event edit form
     */
    public function showEdit() {
        requireLogin();
        requireAdmin();

        $eventId = sanitizeInt($_GET['id'] ?? 0);

        if (!$eventId) {
            $this->redirect(env('APP_URL') . '/admin/dashboard.php');
        }

        $event = $this->eventModel->find($eventId);

        if (!$event || $event['created_by'] != $_SESSION['user_id']) {
            $this->setFlash('Unauthorized access.', 'error');
            $this->redirect(env('APP_URL') . '/admin/dashboard.php');
        }

        $this->render('admin/edit-event', [
            'pageTitle' => 'Edit Event',
            'event' => $event
        ]);
    }

    /**
     * Handle event update
     */
    public function update() {
        requireLogin();
        requireAdmin();

        if (!$this->isPost()) {
            $this->redirect(env('APP_URL') . '/admin/dashboard.php');
        }

        $eventId = sanitizeInt($_POST['event_id'] ?? 0);

        if (!$eventId) {
            $this->setFlash('Invalid event.', 'error');
            $this->redirect(env('APP_URL') . '/admin/dashboard.php');
        }

        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('Security token expired. Please try again.', 'error');
            $this->redirect(env('APP_URL') . '/admin/dashboard.php');
        }

        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'location_url' => $_POST['location'] ?? '',
            'price' => $_POST['price'] ?? 0,
            'event_date' => $_POST['date'] ?? '',
            'speaker' => $_POST['speaker'] ?? '',
            'category' => $_POST['category'] ?? ''
        ];

        // Handle file upload
        if (!empty($_FILES['event_image']['name'])) {
            $uploadDir = env('UPLOAD_DIR', __DIR__ . '/../../uploads/');
            $filename = saveUploadedFile($_FILES['event_image'], $uploadDir);
            if ($filename) {
                $data['image_path'] = $uploadDir . $filename;
            }
        }

        if ($this->eventModel->updateEvent($eventId, $data, $_SESSION['user_id'])) {
            $this->setFlash('Event updated successfully!', 'success');
            $this->redirect(env('APP_URL') . "/view-event.php?id=$eventId");
        } else {
            $this->setFlash('Failed to update event.', 'error');
            $this->redirect(env('APP_URL') . "/admin/edit-event.php?id=$eventId");
        }
    }

    /**
     * Handle event deletion
     */
    public function delete() {
        requireLogin();
        requireAdmin();

        $eventId = sanitizeInt($_GET['id'] ?? 0);

        if (!$eventId) {
            $this->setFlash('Invalid event.', 'error');
            $this->redirect(env('APP_URL') . '/admin/dashboard.php');
        }

        if ($this->eventModel->deleteEvent($eventId, $_SESSION['user_id'])) {
            $this->setFlash('Event deleted successfully!', 'success');
            $this->redirect(env('APP_URL') . '/admin/dashboard.php');
        } else {
            $this->setFlash('Failed to delete event.', 'error');
            $this->redirect(env('APP_URL') . '/admin/dashboard.php');
        }
    }

    /**
     * Search events
     */
    public function search() {
        $keyword = $_GET['q'] ?? '';
        $page = sanitizeInt($_GET['page'] ?? 1) ?: 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $events = $this->eventModel->search($keyword, $limit, $offset);

        $this->render('events/search', [
            'pageTitle' => "Search Results for: $keyword",
            'events' => $events,
            'keyword' => $keyword,
            'currentPage' => $page
        ]);
    }

    /**
     * Filter events by category
     */
    public function filterByCategory() {
        $category = sanitizeInput($_GET['category'] ?? '');
        $page = sanitizeInt($_GET['page'] ?? 1) ?: 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;

        if (!$category) {
            $this->redirect(env('APP_URL') . '/index.php');
        }

        $events = $this->eventModel->getByCategory($category, $limit, $offset);

        $this->render('events/category', [
            'pageTitle' => "Events - $category",
            'events' => $events,
            'category' => $category,
            'currentPage' => $page
        ]);
    }
}
?>
