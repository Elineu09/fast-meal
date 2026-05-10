<?php
/**
 * Input Validator
 * 
 * Path: backend/app/utils/Validator.php
 * 
 * Validates user inputs to prevent injection attacks and ensure data integrity
 */

class Validator {
    
    /**
     * Validate email format
     * 
     * @param string $email Email address to validate
     * @return bool True if email is valid
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate password strength
     * 
     * Minimum 8 characters
     * At least one uppercase letter
     * At least one lowercase letter
     * At least one number
     * At least one special character
     * 
     * @param string $password Password to validate
     * @return bool True if password meets requirements
     */
    public static function validatePassword($password) {
        if (strlen($password) < 8) {
            return false;
        }
        
        $has_upper = preg_match('/[A-Z]/', $password);
        $has_lower = preg_match('/[a-z]/', $password);
        $has_number = preg_match('/[0-9]/', $password);
        $has_special = preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\|`~]/', $password);
        
        return $has_upper && $has_lower && $has_number && $has_special;
    }

    /**
     * Validate name field
     * 
     * @param string $name Name to validate
     * @return bool True if name is valid
     */
    public static function validateName($name) {
        return strlen(trim($name)) >= 3 && strlen(trim($name)) <= 100;
    }

    /**
     * Sanitize string input
     * 
     * @param string $input Input to sanitize
     * @return string Sanitized input
     */
    public static function sanitizeString($input) {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Validate required fields
     * 
     * @param array $data Data to validate
     * @param array $required Required field names
     * @return array Validation result with 'valid' and 'missing' keys
     */
    public static function validateRequired($data, $required) {
        $missing = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missing[] = $field;
            }
        }
        
        return [
            'valid' => empty($missing),
            'missing' => $missing
        ];
    }
}
?>
