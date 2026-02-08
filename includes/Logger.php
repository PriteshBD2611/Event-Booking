<?php
/**
 * Logger Class
 * Handles all application logging operations
 */

class Logger {
    private $logDir;
    private $currentDate;
    private $logLevels = ['debug', 'info', 'warning', 'error', 'critical'];

    public function __construct($logDir = null) {
        $this->logDir = $logDir ?? env('LOG_PATH', __DIR__ . '/../logs/');
        $this->currentDate = date('Y-m-d');
        
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    /**
     * Log a message
     * 
     * @param string $message
     * @param string $level
     * @param array $context
     */
    public function log($message, $level = 'info', $context = []) {
        if (!in_array($level, $this->logLevels)) {
            $level = 'info';
        }

        $logFile = $this->getLogFilePath($level);
        $timestamp = date('Y-m-d H:i:s');
        
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message$contextStr\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        // Also log to debug file if in development mode
        if (env('APP_ENV') === 'development') {
            $this->logToDebugFile($logEntry);
        }
    }

    /**
     * Log debug message
     * 
     * @param string $message
     * @param array $context
     */
    public function debug($message, $context = []) {
        $this->log($message, 'debug', $context);
    }

    /**
     * Log info message
     * 
     * @param string $message
     * @param array $context
     */
    public function info($message, $context = []) {
        $this->log($message, 'info', $context);
    }

    /**
     * Log warning message
     * 
     * @param string $message
     * @param array $context
     */
    public function warning($message, $context = []) {
        $this->log($message, 'warning', $context);
    }

    /**
     * Log error message
     * 
     * @param string $message
     * @param array $context
     */
    public function error($message, $context = []) {
        $this->log($message, 'error', $context);
    }

    /**
     * Log critical message
     * 
     * @param string $message
     * @param array $context
     */
    public function critical($message, $context = []) {
        $this->log($message, 'critical', $context);
    }

    /**
     * Log security-related event
     * 
     * @param string $message
     * @param array $context
     */
    public function security($message, $context = []) {
        $context['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $this->log($message, 'warning', $context);
    }

    /**
     * Log authentication event
     * 
     * @param string $event (login, logout, failed_login, registration)
     * @param string $user
     * @param array $additionalData
     */
    public function logAuth($event, $user, $additionalData = []) {
        $context = array_merge(['user' => $user], $additionalData);
        $this->log("Auth event: $event", 'info', $context);
    }

    /**
     * Log database query (for development/debugging)
     * 
     * @param string $query
     * @param array $params
     * @param float $executionTime
     */
    public function logQuery($query, $params = [], $executionTime = 0) {
        if (env('APP_ENV') !== 'development') {
            return;
        }
        
        $context = [
            'query' => $query,
            'params' => $params,
            'execution_time' => $executionTime . 'ms'
        ];
        $this->log("Database query executed", 'debug', $context);
    }

    /**
     * Get log file path for level
     * 
     * @param string $level
     * @return string
     */
    private function getLogFilePath($level) {
        return $this->logDir . "{$this->currentDate}-{$level}.log";
    }

    /**
     * Log to combined debug file
     * 
     * @param string $logEntry
     */
    private function logToDebugFile($logEntry) {
        $debugFile = $this->logDir . 'debug.log';
        file_put_contents($debugFile, $logEntry, FILE_APPEND);
    }

    /**
     * Get logs for a specific date and level
     * 
     * @param string $date (YYYY-MM-DD)
     * @param string $level
     * @return array
     */
    public function getLogs($date = null, $level = null) {
        $date = $date ?? $this->currentDate;
        $logFile = $this->logDir . "$date-{$level}.log";
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        return file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    /**
     * Clear logs older than specified days
     * 
     * @param int $daysOld
     * @return int (number of files deleted)
     */
    public function clearOldLogs($daysOld = 30) {
        $files = glob($this->logDir . '*.log');
        $deleted = 0;
        $cutoffTime = time() - ($daysOld * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }
}

// Initialize global logger instance
$logger = new Logger();
?>
