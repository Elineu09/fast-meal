<?php
/**
 * Authentication Controller
 * 
 * Path: backend/app/controllers/AuthController.php
 * 
 * Handles HTTP requests for authentication endpoints:
 * - POST /api/auth/register - User registration
 * - POST /api/auth/login - User login
 * 
 * Responsibilities:
 * - Parse and validate request data
 * - Call appropriate service methods
 * - Return JSON responses with appropriate HTTP status codes
 * - Handle errors with descriptive messages
 */

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

class AuthController {
    private $authService;

    /**
     * Initialize AuthController with AuthService
     */
    public function __construct() {
        $this->authService = new AuthService();
    }

    /**
     * Handle user registration request
     * 
     * Expected POST body (JSON):
     * {
     *     "nome": "John Doe",
     *     "email": "john@example.com",
     *     "password": "SecurePass123!"
     * }
     * 
     * Response 201 (Success):
     * {
     *     "success": true,
     *     "message": "User registered successfully",
     *     "data": {
     *         "id": 1,
     *         "nome": "John Doe",
     *         "email": "john@example.com",
     *         "role": "user"
     *     }
     * }
     * 
     * Response 400/409 (Error):
     * {
     *     "success": false,
     *     "message": "Error description",
     *     "errors": null
     * }
     */
    public function register() {
        try {
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            $validation = Validator::validateRequired(
                $input ?? [],
                ['nome', 'email', 'password']
            );

            if (!$validation['valid']) {
                Response::error(
                    'Missing required fields: ' . implode(', ', $validation['missing']),
                    400,
                    $validation['missing']
                );
            }

            // Call AuthService to register user
            $userData = $this->authService->register(
                $input['nome'],
                $input['email'],
                $input['password']
            );

            // Return success response with 201 Created status
            Response::success(
                $userData,
                'User registered successfully',
                201
            );

        } catch (Exception $e) {
            // Check if email already exists (409 Conflict)
            $statusCode = strpos($e->getMessage(), 'already registered') !== false ? 409 : 400;
            Response::error(
                $e->getMessage(),
                $statusCode
            );
        }
    }

    /**
     * Handle user login request
     * 
     * Expected POST body (JSON):
     * {
     *     "email": "john@example.com",
     *     "password": "SecurePass123!"
     * }
     * 
     * Response 200 (Success):
     * {
     *     "success": true,
     *     "message": "Login successful",
     *     "data": {
     *         "id": 1,
     *         "nome": "John Doe",
     *         "email": "john@example.com",
     *         "role": "user",
     *         "token": "base64_encoded_token",
     *         "created_at": "2026-05-10 10:30:00"
     *     }
     * }
     * 
     * Response 400/401 (Error):
     * {
     *     "success": false,
     *     "message": "Invalid email or password",
     *     "errors": null
     * }
     */
    public function login() {
        try {
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            $validation = Validator::validateRequired(
                $input ?? [],
                ['email', 'password']
            );

            if (!$validation['valid']) {
                Response::error(
                    'Missing required fields: ' . implode(', ', $validation['missing']),
                    400,
                    $validation['missing']
                );
            }

            // Call AuthService to authenticate user
            $userData = $this->authService->login(
                $input['email'],
                $input['password']
            );

            // Return success response with user data and token
            Response::success(
                $userData,
                'Login successful',
                200
            );

        } catch (Exception $e) {
            // Return 401 Unauthorized for authentication failures
            $statusCode = strpos($e->getMessage(), 'Invalid') !== false ? 401 : 400;
            Response::error(
                $e->getMessage(),
                $statusCode
            );
        }
    }

    /**
     * Get current user information
     * 
     * Requires authorization token in header
     * Used to verify active session
     * 
     * Expected header:
     * Authorization: Bearer base64_encoded_token
     * 
     * Response 200 (Success):
     * {
     *     "success": true,
     *     "message": "User data retrieved",
     *     "data": {
     *         "id": 1,
     *         "nome": "John Doe",
     *         "email": "john@example.com",
     *         "role": "user"
     *     }
     * }
     */
    public function getCurrentUser() {
        try {
            // Extract token from Authorization header
            $headers = getallheaders();
            $token = null;

            if (isset($headers['Authorization'])) {
                $parts = explode(' ', $headers['Authorization']);
                if (count($parts) === 2 && $parts[0] === 'Bearer') {
                    $token = $parts[1];
                }
            }

            if (!$token) {
                Response::error('Missing authorization token', 401);
            }

            // Decode token to extract user ID
            $tokenData = base64_decode($token);
            $parts = explode(':', $tokenData);
            $userId = $parts[0] ?? null;

            if (!$userId || !is_numeric($userId)) {
                Response::error('Invalid token', 401);
            }

            // Get user data
            $userData = $this->authService->getUserById($userId);

            if (!$userData) {
                Response::error('User not found', 404);
            }

            Response::success(
                $userData,
                'User data retrieved',
                200
            );

        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}
?>
