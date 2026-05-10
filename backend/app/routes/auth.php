<?php
/**
 * Authentication Routes
 * 
 * Path: backend/app/routes/auth.php
 * 
 * API Endpoints:
 * POST /api/auth/register - Register new user
 * POST /api/auth/login - Login user
 * GET /api/auth/user - Get current user (requires token)
 * 
 * This file defines routing logic for authentication endpoints
 * and dispatches requests to appropriate controller methods
 */

require_once __DIR__ . '/../controllers/AuthController.php';

// Create controller instance
$authController = new AuthController();

// Get request method and URI
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Extract route from URI
// Example: /fast-meal/backend/public/api/auth/register
// We need to extract 'register' from the end
$routeParts = explode('/', $requestUri);
$action = end($routeParts); // Get last segment

/**
 * Route dispatcher
 * 
 * Supported routes:
 * - POST /api/auth/register
 * - POST /api/auth/login
 * - GET /api/auth/user
 */

switch ($action) {
    
    // User Registration
    // POST /api/auth/register
    case 'register':
        if ($requestMethod === 'POST') {
            $authController->register();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    // User Login
    // POST /api/auth/login
    case 'login':
        if ($requestMethod === 'POST') {
            $authController->login();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    // Get Current User
    // GET /api/auth/user
    case 'user':
        if ($requestMethod === 'GET') {
            $authController->getCurrentUser();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    // Route not found
    default:
        Response::error('Endpoint not found', 404);
        break;
}
?>
