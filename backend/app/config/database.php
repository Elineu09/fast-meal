<?php
/**
 * Database Configuration and Connection
 * 
 * Path: backend/app/config/database.php
 * 
 * Configures PDO connection to MySQL/MariaDB with:
 * - Prepared statements for SQL injection prevention
 * - Exception error mode for proper error handling
 * - UTF-8 charset for internationalization support
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'fast_meal';
    private $username = 'root';
    private $password = 'justChilling#Clear@007';
    private $pdo;

    /**
     * Connect to database using PDO
     * 
     * @throws Exception If connection fails
     * @return PDO Database connection instance
     */
    public function connect() {
        $this->pdo = null;

        try {
            // DSN (Data Source Name) configuration
            $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name;
            
            // PDO options for enhanced security and error handling
            $options = [
                PDO::ATTR_PERSISTENT => false,           // Non-persistent connections
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch as associative arrays
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4" // UTF-8 support
            ];

            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            
            return $this->pdo;
        } catch (PDOException $e) {
            throw new Exception('Database Connection Error: ' . $e->getMessage());
        }
    }

    /**
     * Get active PDO connection
     * 
     * @return PDO Current database connection
     */
    public function getPDO() {
        if ($this->pdo === null) {
            $this->connect();
        }
        return $this->pdo;
    }

    /**
     * Execute a prepared statement
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind to placeholders
     * @return PDOStatement Executed prepared statement
     * @throws Exception If query execution fails
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->getPDO()->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception('Query Execution Error: ' . $e->getMessage());
        }
    }

    /**
     * Fetch a single row from query result
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array|null Single row as associative array or null if no results
     */
    public function fetchOne($query, $params = []) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetch();
    }

    /**
     * Fetch all rows from query result
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array All rows as associative arrays
     */
    public function fetchAll($query, $params = []) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get the ID of the last inserted row
     * 
     * @return string Last insert ID
     */
    public function lastInsertId() {
        return $this->getPDO()->lastInsertId();
    }

    /**
     * Get row count from last query
     * 
     * @param PDOStatement $stmt The statement to count rows from
     * @return int Number of affected rows
     */
    public function rowCount($stmt) {
        return $stmt->rowCount();
    }
}
?>
