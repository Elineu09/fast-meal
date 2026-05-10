<?php
/**
 * Authentication Service
 * 
 * Path: backend/app/services/AuthService.php
 * 
 * Business logic for user authentication:
 * - User registration with password hashing
 * - User login with password verification
 * - Session management
 * 
 * Implements security best practices:
 * - password_hash() with bcrypt algorithm
 * - Prepared statements to prevent SQL injection
 * - Input validation before database operations
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Validator.php';

class AuthService {
    private $db;

    /**
     * Initialize AuthService with database connection
     */
    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getPDO();
        } catch (Exception $e) {
            throw new Exception('Service Initialization Error: ' . $e->getMessage());
        }
    }

    /**
     * Register a new user
     * 
     * Validation steps:
     * 1. Check if all required fields are provided
     * 2. Validate email format
     * 3. Validate password strength
     * 4. Check if email already exists
     * 5. Hash password with bcrypt
     * 6. Insert user into database
     * 
     * @param string $name User's full name
     * @param string $email User's email address
     * @param string $password User's password
     * @param string $role User's role (default: 'student')
     * 
     * @return array User data if successful
     * @throws Exception If registration fails
     */
    public function register($name, $email, $password, $role = 'student') {
        // Validate inputs
        if (!Validator::validateName($name)) {
            throw new Exception('Name must be between 3 and 100 characters');
        }

        if (!Validator::validateEmail($email)) {
            throw new Exception('Invalid email format');
        }

        if (!Validator::validatePassword($password)) {
            throw new Exception('Password must be at least 8 characters with uppercase, lowercase, number and special character');
        }

        // Sanitize inputs
        $name = Validator::sanitizeString($name);
        $email = Validator::sanitizeString($email);

        try {
            // Check if email already exists
            $query = 'SELECT id FROM users WHERE email = ?';
            $stmt = $this->db->prepare($query);
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                throw new Exception('Email already registered');
            }

            // Hash password using bcrypt
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            // Insert new user into database
            $query = 'INSERT INTO users (nome, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())';
            $stmt = $this->db->prepare($query);
            $stmt->execute([$name, $email, $hashedPassword, $role]);

            $userId = $this->db->lastInsertId();

            // Return user data (without password)
            return [
                'id' => $userId,
                'nome' => $name,
                'email' => $email,
                'role' => $role
            ];

        } catch (PDOException $e) {
            throw new Exception('Database Error: ' . $e->getMessage());
        }
    }

    /**
     * Authenticate user login
     * 
     * Validation steps:
     * 1. Check if email and password are provided
     * 2. Find user by email
     * 3. Verify password hash
     * 4. Create session token
     * 5. Return user data with session token
     * 
     * @param string $email User's email address
     * @param string $password User's password
     * 
     * @return array User data with session token if successful
     * @throws Exception If login fails
     */
    public function login($email, $password) {
        // Validate inputs
        if (empty($email) || empty($password)) {
            throw new Exception('Email and password are required');
        }

        if (!Validator::validateEmail($email)) {
            throw new Exception('Invalid email format');
        }

        // Sanitize email
        $email = Validator::sanitizeString($email);

        try {
            // Fetch user by email
            $query = 'SELECT id, nome, email, password, role, created_at FROM users WHERE email = ?';
            $stmt = $this->db->prepare($query);
            $stmt->execute([$email]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Invalid email or password');
            }

            $user = $stmt->fetch();

            // Verify password against hash
            if (!password_verify($password, $user['password'])) {
                throw new Exception('Invalid email or password');
            }

            // Generate session token
            $sessionToken = $this->generateSessionToken($user['id']);

            // Return user data with token (without password)
            return [
                'id' => $user['id'],
                'nome' => $user['nome'],
                'email' => $user['email'],
                'role' => $user['role'],
                'token' => $sessionToken,
                'created_at' => $user['created_at']
            ];

        } catch (PDOException $e) {
            throw new Exception('Database Error: ' . $e->getMessage());
        }
    }

    /**
     * Generate a session token for user
     * 
     * Creates a secure token using random bytes
     * Can be stored in database or used with JWT in future
     * 
     * @param int $userId User ID
     * @return string Base64-encoded session token
     */
    private function generateSessionToken($userId) {
        $token = bin2hex(random_bytes(32));
        $timestamp = time();
        
        // Create composite token with user ID and timestamp
        $sessionData = $userId . ':' . $timestamp . ':' . $token;
        
        return base64_encode($sessionData);
    }

    /**
     * Verify user email exists
     * 
     * @param string $email User's email address
     * @return bool True if email exists
     */
    public function emailExists($email) {
        try {
            $query = 'SELECT id FROM users WHERE email = ?';
            $stmt = $this->db->prepare($query);
            $stmt->execute([Validator::sanitizeString($email)]);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception('Database Error: ' . $e->getMessage());
        }
    }

    /**
     * Get user by ID
     * 
     * @param int $userId User ID
     * @return array|null User data or null if not found
     */
    public function getUserById($userId) {
        try {
            $query = 'SELECT id, nome, email, role, created_at FROM users WHERE id = ?';
            $stmt = $this->db->prepare($query);
            $stmt->execute([$userId]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception('Database Error: ' . $e->getMessage());
        }
    }
}
?>
