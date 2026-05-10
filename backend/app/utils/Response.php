<?php
/**
 * Response Handler
 * 
 * Path: backend/app/utils/Response.php
 * 
 * Standardized JSON response format for all API endpoints
 * Sets appropriate HTTP headers and CORS configuration
 */

class Response {
    
    /**
     * Set JSON response headers
     * Includes CORS headers for frontend communication
     */
    public static function setJsonHeaders() {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    /**
     * Send success response
     * 
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code (default 200)
     */
    public static function success($data = null, $message = 'Success', $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }

    /**
     * Send error response
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code (default 400)
     * @param mixed $errors Additional error details (optional)
     */
    public static function error($message = 'Error', $statusCode = 400, $errors = null) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }

    /**
     * Handle preflight CORS requests
     */
    public static function handleCORS() {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            self::setJsonHeaders();
            http_response_code(200);
            exit();
        }
    }
}
?>
