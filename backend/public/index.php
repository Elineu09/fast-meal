<?php
/**
 * API Entry Point
 * 
 * Path: backend/public/index.php
 * 
 * Main router that handles all API requests
 * Routes requests to appropriate endpoints
 * Sets up response headers and error handling
 */

// Enable error reporting for development (disable in production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set default timezone
date_default_timezone_set('UTC');

// Import utilities
require_once __DIR__ . '/../app/utils/Response.php';

// Set JSON headers and handle CORS
Response::setJsonHeaders();
Response::handleCORS();

// Get request URI and method
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

/**
 * Router configuration
 * Maps URL patterns to route files
 */

// Remove base path from URI
// Example: /fast-meal/backend/public/api/auth/login -> /api/auth/login
$basePath = '/fast-meal/backend/public';
$currentPath = str_replace($basePath, '', $requestUri);

// Remove leading slash for cleaner routing
$currentPath = ltrim($currentPath, '/');

try {
    /**
     * Route dispatcher
     * 
     * Supported route groups:
     * - /api/auth/* -> Authentication routes
     * - /api/tickets/* -> Ticket management routes (future)
     * - /api/queue/* -> Queue management routes (future)
     * - /api/reports/* -> Report generation routes (future)
     * - /api/users/* -> User management routes (future)
     */

    if (strpos($currentPath, 'api/auth') === 0) {
        // Authentication routes
        require_once __DIR__ . '/../app/routes/auth.php';
    
    } elseif (strpos($currentPath, 'api/tickets') === 0) {
        // Ticket management routes
        require_once __DIR__ . '/../app/routes/tickets.php';
    
    } elseif (strpos($currentPath, 'api/queue') === 0) {
        // Queue routes (to be implemented)
        Response::error('Queue endpoint not yet implemented', 501);
    
    } elseif (strpos($currentPath, 'api/reports') === 0) {
        // Report routes (to be implemented)
        Response::error('Reports endpoint not yet implemented', 501);
    
    } elseif (strpos($currentPath, 'api/users') === 0) {
        // User management routes (to be implemented)
        Response::error('Users endpoint not yet implemented', 501);
    
    } else {
        // Root endpoint - API info
        if ($currentPath === '' || $currentPath === '/') {
            Response::success(
                [
                    'version' => '1.0',
                    'name' => 'Fast Meal API',
                    'endpoints' => [
                        'POST /api/auth/register' => 'Register new user',
                        'POST /api/auth/login' => 'Login user',
                        'GET /api/auth/user' => 'Get current user'
                    ]
                ],
                'Welcome to Fast Meal API'
            );
        }
        
        Response::error('Endpoint not found', 404);
    }

} catch (Exception $e) {
    // Catch all unhandled exceptions
    Response::error(
        'Server Error: ' . $e->getMessage(),
        500
    );
}
?>
