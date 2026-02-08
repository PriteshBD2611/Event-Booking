<?php
/**
 * Base Controller Class
 * Provides common controller functionality
 */

class Controller {
    protected $pdo;
    protected $logger;

    public function __construct($pdo, $logger = null) {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    /**
     * Render a view file with data
     * 
     * @param string $view
     * @param array $data
     */
    protected function render($view, $data = []) {
        extract($data);
        $viewFile = __DIR__ . "/../app/Views/$view.php";
        
        if (!file_exists($viewFile)) {
            die("View file not found: $viewFile");
        }
        
        require $viewFile;
    }

    /**
     * Redirect to URL
     * 
     * @param string $url
     */
    protected function redirect($url) {
        header("Location: $url");
        exit();
    }

    /**
     * Return JSON response
     * 
     * @param array $data
     * @param int $statusCode
     */
    protected function json($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }

    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get request method
     * 
     * @return string
     */
    protected function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Check if request is POST
     * 
     * @return bool
     */
    protected function isPost() {
        return $this->getMethod() === 'POST';
    }

    /**
     * Check if request is GET
     * 
     * @return bool
     */
    protected function isGet() {
        return $this->getMethod() === 'GET';
    }

    /**
     * Set flash message
     * 
     * @param string $message
     * @param string $type (success, error, warning, info)
     */
    protected function setFlash($message, $type = 'info') {
        $_SESSION['flash'] = [
            'message' => $message,
            'type' => $type
        ];
    }

    /**
     * Get flash message
     * 
     * @return array|null
     */
    protected function getFlash() {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}
?>
