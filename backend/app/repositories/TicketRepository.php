<?php
/**
 * Ticket Repository
 * 
 * Path: backend/app/repositories/TicketRepository.php
 * 
 * Data access layer for ticket operations:
 * - Create new tickets
 * - Fetch tickets by various criteria
 * - Update ticket status
 * - Query queue status
 * 
 * Implements CRUD operations with PDO prepared statements
 */

require_once __DIR__ . '/../config/database.php';

class TicketRepository {
    private $db;

    /**
     * Initialize TicketRepository with database connection
     */
    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getPDO();
        } catch (Exception $e) {
            throw new Exception('Repository Initialization Error: ' . $e->getMessage());
        }
    }

    /**
     * Create a new ticket
     * 
     * @param string $ticketNumber Unique ticket number (e.g., A001, P001)
     * @param string $type Ticket type: 'normal' or 'priority'
     * @param int $userId User ID who requested the ticket
     * 
     * @return array Created ticket data
     * @throws Exception If creation fails
     */
    public function create($ticketNumber, $type, $userId) {
        try {
            $query = 'INSERT INTO tickets (ticket_number, type, status, user_id, created_at, updated_at) 
                      VALUES (?, ?, ?, ?, NOW(), NOW())';
            $stmt = $this->db->prepare($query);
            $stmt->execute([$ticketNumber, $type, 'pending', $userId]);

            $ticketId = $this->db->lastInsertId();

            return [
                'id' => $ticketId,
                'ticket_number' => $ticketNumber,
                'type' => $type,
                'status' => 'pending',
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ];
        } catch (PDOException $e) {
            throw new Exception('Failed to create ticket: ' . $e->getMessage());
        }
    }

    /**
     * Get ticket by ID
     * 
     * @param int $ticketId Ticket ID
     * 
     * @return array|null Ticket data or null if not found
     */
    public function getById($ticketId) {
        try {
            $query = 'SELECT * FROM tickets WHERE id = ?';
            $stmt = $this->db->prepare($query);
            $stmt->execute([$ticketId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Failed to fetch ticket: ' . $e->getMessage());
        }
    }

    /**
     * Get ticket by ticket number
     * 
     * @param string $ticketNumber Ticket number (e.g., A001)
     * 
     * @return array|null Ticket data or null if not found
     */
    public function getByNumber($ticketNumber) {
        try {
            $query = 'SELECT * FROM tickets WHERE ticket_number = ?';
            $stmt = $this->db->prepare($query);
            $stmt->execute([$ticketNumber]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Failed to fetch ticket: ' . $e->getMessage());
        }
    }

    /**
     * Get user's pending or in-attendance ticket
     * 
     * @param int $userId User ID
     * 
     * @return array|null Active ticket data or null
     */
    public function getActiveTicketByUser($userId) {
        try {
            $query = 'SELECT * FROM tickets 
                      WHERE user_id = ? AND status IN ("pending", "in_attendance")
                      ORDER BY created_at DESC
                      LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->execute([$userId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Failed to fetch active ticket: ' . $e->getMessage());
        }
    }

    /**
     * Get all tickets for a user
     * 
     * @param int $userId User ID
     * @param int $limit Number of records to fetch (default: 10)
     * 
     * @return array Array of tickets
     */
    public function getTicketsByUser($userId, $limit = 10) {
        try {
            $query = 'SELECT * FROM tickets 
                      WHERE user_id = ?
                      ORDER BY created_at DESC
                      LIMIT ?';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $userId, PDO::PARAM_INT);
            $stmt->bindParam(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Failed to fetch user tickets: ' . $e->getMessage());
        }
    }

    /**
     * Get next ticket number
     * Generates sequential number based on type (A001, A002... or P001, P002...)
     * Uses highest existing number + 1 to ensure uniqueness
     * 
     * @param string $type Ticket type: 'normal' or 'priority'
     * 
     * @return string Next ticket number
     */
    public function getNextTicketNumber($type) {
        try {
            $prefix = ($type === 'priority') ? 'P' : 'A';
            
            // Find the highest ticket number for this type
            $query = 'SELECT ticket_number FROM tickets 
                      WHERE type = ? AND ticket_number LIKE ?
                      ORDER BY CAST(SUBSTRING(ticket_number, 2) AS UNSIGNED) DESC
                      LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->execute([$type, $prefix . '%']);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Extract number and increment
            if ($result && $result['ticket_number']) {
                $currentNumber = intval(substr($result['ticket_number'], 1));
                $nextNumber = $currentNumber + 1;
            } else {
                $nextNumber = 1;
            }
            
            return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            throw new Exception('Failed to generate ticket number: ' . $e->getMessage());
        }
    }

    /**
     * Update ticket status
     * 
     * @param int $ticketId Ticket ID
     * @param string $status New status
     * 
     * @return bool True if successful
     */
    public function updateStatus($ticketId, $status) {
        try {
            $query = 'UPDATE tickets SET status = ?, updated_at = NOW() WHERE id = ?';
            $stmt = $this->db->prepare($query);
            $stmt->execute([$status, $ticketId]);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception('Failed to update ticket status: ' . $e->getMessage());
        }
    }

    /**
     * Get all pending tickets in queue order
     * Priority tickets (P) come first, then normal tickets (A)
     * Within same type, ordered by creation time (FIFO)
     * 
     * @return array Array of pending tickets
     */
    public function getPendingQueue() {
        try {
            $query = 'SELECT t.*, u.nome as user_name 
                      FROM tickets t
                      JOIN users u ON t.user_id = u.id
                      WHERE t.status IN ("pending", "in_attendance")
                      ORDER BY 
                        CASE WHEN t.type = "priority" THEN 0 ELSE 1 END,
                        t.created_at ASC';
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Failed to fetch queue: ' . $e->getMessage());
        }
    }

    /**
     * Get the next pending ticket in service order.
     *
     * @return array|null Next pending ticket with user info or null if queue is empty
     */
    public function getNextPendingTicket() {
        try {
            $query = 'SELECT t.*, u.nome as user_name, u.role as user_role
                      FROM tickets t
                      JOIN users u ON t.user_id = u.id
                      WHERE t.status = "pending"
                      ORDER BY
                        CASE WHEN t.type = "priority" THEN 0 ELSE 1 END,
                        t.created_at ASC
                      LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Failed to fetch next pending ticket: ' . $e->getMessage());
        }
    }

    /**
     * Create an attendance record when a ticket is called.
     *
     * @param int $ticketId Ticket ID
     * @param int|null $counterNumber Service counter number
     *
     * @return bool True if attendance was created
     */
    public function createAttendance($ticketId, $counterNumber = null) {
        try {
            $query = 'INSERT INTO attendances (ticket_id, called_at, counter_number, created_at, updated_at)
                      VALUES (?, NOW(), ?, NOW(), NOW())';
            $stmt = $this->db->prepare($query);
            $stmt->execute([$ticketId, $counterNumber]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception('Failed to create attendance: ' . $e->getMessage());
        }
    }

    /**
     * Get completed attendances (last N records) for time estimation
     * 
     * @param int $limit Number of records (default: 10)
     * 
     * @return array Array of completed attendances with duration
     */
    public function getCompletedAttendances($limit = 10) {
        try {
            $query = 'SELECT 
                        a.id,
                        t.ticket_number,
                        a.called_at,
                        a.finished_at,
                        TIMEDIFF(a.finished_at, a.called_at) as duration,
                        UNIX_TIMESTAMP(a.finished_at) - UNIX_TIMESTAMP(a.called_at) as duration_seconds
                      FROM attendances a
                      JOIN tickets t ON a.ticket_id = t.id
                      WHERE a.finished_at IS NOT NULL
                      ORDER BY a.finished_at DESC
                      LIMIT ?';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Failed to fetch completed attendances: ' . $e->getMessage());
        }
    }

    /**
     * Get tickets ahead of a specific ticket in queue
     * 
     * @param int $ticketId Ticket ID
     * 
     * @return int Number of tickets ahead
     */
    public function getTicketsAhead($ticketId) {
        try {
            // Get ticket type and creation time
            $ticket = $this->getById($ticketId);
            if (!$ticket) {
                return 0;
            }

            // Count tickets ahead (priority first, then by time)
            if ($ticket['type'] === 'priority') {
                // Priority tickets: count other priority tickets created before this
                $query = 'SELECT COUNT(*) as count FROM tickets 
                          WHERE status IN ("pending", "in_attendance") 
                          AND type = "priority"
                          AND created_at < ?';
                $stmt = $this->db->prepare($query);
                $stmt->execute([$ticket['created_at']]);
            } else {
                // Normal tickets: count all priority tickets + normal tickets created before
                $query = 'SELECT COUNT(*) as count FROM tickets 
                          WHERE status IN ("pending", "in_attendance")
                          AND (
                            type = "priority" 
                            OR (type = "normal" AND created_at < ?)
                          )';
                $stmt = $this->db->prepare($query);
                $stmt->execute([$ticket['created_at']]);
            }

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return intval($result['count']);
        } catch (PDOException $e) {
            throw new Exception('Failed to count tickets ahead: ' . $e->getMessage());
        }
    }

    /**
     * Get queue statistics
     * 
     * @return array Queue statistics
     */
    public function getQueueStats() {
        try {
            $query = 'SELECT 
                        COUNT(*) as total_pending,
                        SUM(CASE WHEN type = "priority" THEN 1 ELSE 0 END) as priority_count,
                        SUM(CASE WHEN type = "normal" THEN 1 ELSE 0 END) as normal_count
                      FROM tickets 
                      WHERE status IN ("pending", "in_attendance")';
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Failed to fetch queue stats: ' . $e->getMessage());
        }
    }
}
?>
