<?php
/**
 * Error Handler
 * 
 * Path: backend/app/utils/ErrorHandler.php
 * 
 * Centralized error handling for the application
 * Converts errors and exceptions to JSON responses
 */

class ErrorHandler {
    
    /**
     * Register error handlers
     * 
     * Sets up handlers for:
     * - Fatal errors
     * - Warnings and notices
     * - Exceptions
     */
    public static function register() {
        // Handle exceptions
        set_exception_handler([self::class, 'handleException']);
        
        // Handle errors
        set_error_handler([self::class, 'handleError']);
        
        // Handle fatal errors
        register_shutdown_function([self::class, 'handleFatalError']);
    }

    /**
     * Handle exceptions
     * 
     * @param Throwable $exception The exception
     */
    public static function handleException($exception) {
        $statusCode = 500;
        $message = 'Internal Server Error';

        // Determine status code based on exception type
        if (strpos($exception->getMessage(), 'not found') !== false) {
            $statusCode = 404;
            $message = $exception->getMessage();
        } elseif (strpos($exception->getMessage(), 'unauthorized') !== false) {
            $statusCode = 401;
            $message = $exception->getMessage();
        } elseif (strpos($exception->getMessage(), 'forbidden') !== false) {
            $statusCode = 403;
            $message = $exception->getMessage();
        } else {
            $message = $exception->getMessage();
        }

        // Log exception
        self::logError($exception);

        // Return JSON error response
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => null,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        exit;
    }

    /**
     * Handle errors
     * 
     * @param int $errno Error number
     * @param string $errstr Error string
     * @param string $errfile Error file
     * @param int $errline Error line
     * @return bool True to stop error propagation
     */
    public static function handleError($errno, $errstr, $errfile, $errline) {
        // Convert PHP error to exception
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Handle fatal errors
     * 
     * Called when script terminates
     */
    public static function handleFatalError() {
        $error = error_get_last();
        
        if ($error !== null) {
            $errno = $error['type'];
            $errstr = $error['message'];
            $errfile = $error['file'];
            $errline = $error['line'];

            // Log fatal error
            self::logFatalError($errno, $errstr, $errfile, $errline);

            // Return JSON error response
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Fatal Error: ' . $errstr,
                'errors' => null,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Log error to file
     * 
     * @param Throwable $exception Exception object
     */
    private static function logError($exception) {
        $logFile = __DIR__ . '/../../storage/logs/error.log';
        
        // Create directory if needed
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }

        $message = sprintf(
            "[%s] %s\nFile: %s:%d\nTrace: %s\n---\n",
            date('Y-m-d H:i:s'),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        file_put_contents($logFile, $message, FILE_APPEND);
    }

    /**
     * Log fatal error to file
     * 
     * @param int $errno Error type
     * @param string $errstr Error message
     * @param string $errfile Error file
     * @param int $errline Error line
     */
    private static function logFatalError($errno, $errstr, $errfile, $errline) {
        $logFile = __DIR__ . '/../../storage/logs/fatal.log';
        
        // Create directory if needed
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }

        $message = sprintf(
            "[%s] FATAL ERROR Type %d: %s\nFile: %s:%d\n---\n",
            date('Y-m-d H:i:s'),
            $errno,
            $errstr,
            $errfile,
            $errline
        );

        file_put_contents($logFile, $message, FILE_APPEND);
    }
}
?>
