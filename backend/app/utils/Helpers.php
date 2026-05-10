<?php
/**
 * Helper Functions
 * 
 * Path: backend/app/utils/Helpers.php
 * 
 * Utility functions for common operations
 */

/**
 * Log message to file
 * 
 * @param string $message Message to log
 * @param string $level Log level (INFO, ERROR, WARNING, DEBUG)
 */
function logMessage($message, $level = 'INFO') {
    $logFile = __DIR__ . '/../../storage/logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
    
    // Create logs directory if it doesn't exist
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * Generate a unique ticket number
 * 
 * @param string $prefix Ticket prefix (P for staff, A for student)
 * @param int $number Sequential number
 * @return string Formatted ticket number
 */
function generateTicketNumber($prefix, $number) {
    return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
}

/**
 * Format date to standard format
 * 
 * @param string $date Date string
 * @param string $format Output format (default: Y-m-d H:i:s)
 * @return string Formatted date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * Convert seconds to human readable time
 * 
 * @param int $seconds Number of seconds
 * @return string Human readable time
 */
function secondsToTime($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    
    $result = '';
    if ($hours > 0) $result .= $hours . 'h ';
    if ($minutes > 0) $result .= $minutes . 'm ';
    if ($secs > 0) $result .= $secs . 's';
    
    return trim($result) ?: '0s';
}

/**
 * Check if user is admin
 * 
 * @param string $role User role
 * @return bool True if user is admin
 */
function isAdmin($role) {
    return strtolower($role) === 'admin';
}

/**
 * Generate random string
 * 
 * @param int $length String length
 * @return string Random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Parse JWT token (basic implementation)
 * For production, use a proper JWT library
 * 
 * @param string $token JWT token
 * @return array|null Decoded token or null if invalid
 */
function parseToken($token) {
    try {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        
        $payload = base64_decode($parts[1], true);
        return json_decode($payload, true);
    } catch (Exception $e) {
        return null;
    }
}
?>
