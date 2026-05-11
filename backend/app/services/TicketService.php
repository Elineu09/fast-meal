<?php
/**
 * Ticket Service
 * 
 * Path: backend/app/services/TicketService.php
 * 
 * Business logic for ticket operations:
 * - Generate new tickets with business rules
 * - Validate ticket requests
 * - Manage ticket lifecycle
 * - Enforce one-ticket-per-user rule
 * 
 * Business Rules Implemented:
 * RN02 - Tipologia e Prioridade de Senhas:
 *   - Estudantes recebem prefixo 'A' (normal)
 *   - Funcionários recebem prefixo 'P' (priority)
 * 
 * RN03 - Limite de Solicitação:
 *   - Um utilizador só pode ter uma senha "pendente" ou "em_atendimento"
 *   - Para pedir uma nova, a anterior deve ser concluída ou cancelada
 */

require_once __DIR__ . '/../repositories/TicketRepository.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Validator.php';

class TicketService {
    private $ticketRepository;
    private $db;

    /**
     * Initialize TicketService
     */
    public function __construct() {
        $this->ticketRepository = new TicketRepository();
        try {
            $database = new Database();
            $this->db = $database->getPDO();
        } catch (Exception $e) {
            throw new Exception('Service Initialization Error: ' . $e->getMessage());
        }
    }

    /**
     * Request a new ticket
     * 
     * Business rules enforced:
     * 1. User must exist and have a valid role
     * 2. User cannot have an active (pending/in_attendance) ticket
     * 3. Generate ticket number based on user role:
     *    - 'employee' role -> 'priority' type -> 'P' prefix
     *    - 'student' role -> 'normal' type -> 'A' prefix
     * 4. Store ticket in database with 'pending' status
     * 
     * @param int $userId User ID requesting the ticket
     * 
     * @return array Created ticket data with all details
     * @throws Exception If user validation fails or ticket creation fails
     */
    public function requestTicket($userId) {
        try {
            // Validate user exists
            $user = $this->getUserById($userId);
            if (!$user) {
                throw new Exception('User not found');
            }

            // Check if user already has an active ticket (RN03)
            $activeTicket = $this->ticketRepository->getActiveTicketByUser($userId);
            if ($activeTicket) {
                throw new Exception(
                    'User already has an active ticket: ' . $activeTicket['ticket_number'] . 
                    '. Complete or cancel it before requesting a new one.',
                    400
                );
            }

            // Determine ticket type based on user role
            $type = $this->getTicketTypeByRole($user['role']);
            
            // Generate unique ticket number
            $ticketNumber = $this->ticketRepository->getNextTicketNumber($type);

            // Create ticket
            $ticket = $this->ticketRepository->create($ticketNumber, $type, $userId);

            // Return ticket with user info
            $ticket['user_role'] = $user['role'];
            $ticket['user_name'] = $user['nome'];
            $ticket['priority'] = ($type === 'priority') ? true : false;

            return $ticket;
        } catch (Exception $e) {
            // Re-throw with better error message
            throw new Exception('Failed to request ticket: ' . $e->getMessage());
        }
    }

    /**
     * Get ticket type based on user role
     * 
     * RN02 - Tipologia e Prioridade de Senhas:
     * - Funcionários/Employees recebem 'priority' (prefixo P)
     * - Estudantes/Students recebem 'normal' (prefixo A)
     * 
     * @param string $role User role: 'admin', 'employee', 'student'
     * 
     * @return string Ticket type: 'priority' or 'normal'
     */
    private function getTicketTypeByRole($role) {
        switch (strtolower($role)) {
            case 'employee':
            case 'funcionário':
                return 'priority';
            case 'admin':
                return 'priority'; // Admins get priority if they need tickets
            case 'student':
            case 'estudante':
            default:
                return 'normal';
        }
    }

    /**
     * Cancel a ticket
     * 
     * RN05 - Integridade do Atendimento:
     * - Uma vez que o atendimento seja 'completed' ou 'cancelled',
     *   o estado não pode ser revertido
     * 
     * Restrictions:
     * - Only 'pending' or 'in_attendance' tickets can be cancelled
     * - Cannot cancel a completed or already cancelled ticket
     * - User can only cancel their own tickets
     * 
     * @param int $ticketId Ticket ID
     * @param int $userId User ID (for authorization)
     * 
     * @return array Updated ticket data
     * @throws Exception If cancellation is not allowed
     */
    public function cancelTicket($ticketId, $userId) {
        try {
            $ticket = $this->ticketRepository->getById($ticketId);

            if (!$ticket) {
                throw new Exception('Ticket not found', 404);
            }

            // Check authorization
            if ($ticket['user_id'] != $userId) {
                throw new Exception('Unauthorized: You can only cancel your own tickets', 403);
            }

            // Check if ticket can be cancelled
            if (!in_array($ticket['status'], ['pending', 'in_attendance'])) {
                throw new Exception(
                    'Cannot cancel ticket with status: ' . $ticket['status'] . 
                    '. Only pending or in-attendance tickets can be cancelled.',
                    400
                );
            }

            // Update status to cancelled
            $success = $this->ticketRepository->updateStatus($ticketId, 'cancelled');

            if (!$success) {
                throw new Exception('Failed to update ticket status');
            }

            // Return updated ticket
            $ticket['status'] = 'cancelled';
            $ticket['cancelled_at'] = date('Y-m-d H:i:s');

            return $ticket;
        } catch (Exception $e) {
            throw new Exception('Failed to cancel ticket: ' . $e->getMessage());
        }
    }

    /**
     * Get ticket details
     * 
     * @param int $ticketId Ticket ID
     * @param int|null $userId User ID (for authorization check, optional)
     * 
     * @return array Ticket details
     * @throws Exception If ticket not found
     */
    public function getTicket($ticketId, $userId = null) {
        try {
            $ticket = $this->ticketRepository->getById($ticketId);

            if (!$ticket) {
                throw new Exception('Ticket not found', 404);
            }

            // If userId is provided, check authorization
            if ($userId && $ticket['user_id'] != $userId) {
                throw new Exception('Unauthorized: Cannot view other users\' tickets', 403);
            }

            // Get user information
            $user = $this->getUserById($ticket['user_id']);
            $ticket['user_name'] = $user['nome'];
            $ticket['user_role'] = $user['role'];
            $ticket['priority'] = ($ticket['type'] === 'priority') ? true : false;

            return $ticket;
        } catch (Exception $e) {
            throw new Exception('Failed to fetch ticket: ' . $e->getMessage());
        }
    }

    /**
     * Get user's tickets with status
     * 
     * @param int $userId User ID
     * @param int $limit Number of records (default: 20)
     * 
     * @return array Array of user's tickets
     */
    public function getUserTickets($userId, $limit = 20) {
        try {
            $tickets = $this->ticketRepository->getTicketsByUser($userId, $limit);
            
            // Add priority flag to each ticket
            foreach ($tickets as &$ticket) {
                $ticket['priority'] = ($ticket['type'] === 'priority') ? true : false;
            }

            return $tickets;
        } catch (Exception $e) {
            throw new Exception('Failed to fetch user tickets: ' . $e->getMessage());
        }
    }

    /**
     * Get user by ID
     * 
     * @param int $userId User ID
     * 
     * @return array|null User data or null
     */
    private function getUserById($userId) {
        try {
            $query = 'SELECT id, nome, email, role FROM users WHERE id = ?';
            $stmt = $this->db->prepare($query);
            $stmt->execute([$userId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Failed to fetch user: ' . $e->getMessage());
        }
    }

    /**
     * Get user's active ticket if exists
     * 
     * @param int $userId User ID
     * 
     * @return array|null Active ticket or null
     */
    public function getUserActiveTicket($userId) {
        try {
            $activeTicket = $this->ticketRepository->getActiveTicketByUser($userId);
            
            if (!$activeTicket) {
                return null;
            }

            $activeTicket['priority'] = ($activeTicket['type'] === 'priority') ? true : false;
            
            return $activeTicket;
        } catch (Exception $e) {
            throw new Exception('Failed to fetch active ticket: ' . $e->getMessage());
        }
    }

    /**
     * Validate ticket can be called (admin function)
     * 
     * @param int $ticketId Ticket ID
     * 
     * @return array Ticket details if valid for calling
     * @throws Exception If ticket cannot be called
     */
    public function validateTicketForCalling($ticketId) {
        try {
            $ticket = $this->ticketRepository->getById($ticketId);

            if (!$ticket) {
                throw new Exception('Ticket not found', 404);
            }

            if ($ticket['status'] !== 'pending') {
                throw new Exception(
                    'Only pending tickets can be called. Current status: ' . $ticket['status'],
                    400
                );
            }

            return $ticket;
        } catch (Exception $e) {
            throw new Exception('Ticket validation failed: ' . $e->getMessage());
        }
    }

    /**
     * Update ticket status to in_attendance
     * Called when admin calls a ticket
     * 
     * @param int $ticketId Ticket ID
     * 
     * @return bool True if successful
     */
    public function markTicketInAttendance($ticketId) {
        try {
            return $this->ticketRepository->updateStatus($ticketId, 'in_attendance');
        } catch (Exception $e) {
            throw new Exception('Failed to mark ticket in attendance: ' . $e->getMessage());
        }
    }

    /**
     * Update ticket status to completed
     * Called when admin finishes attendance
     * 
     * @param int $ticketId Ticket ID
     * 
     * @return bool True if successful
     */
    public function markTicketCompleted($ticketId) {
        try {
            return $this->ticketRepository->updateStatus($ticketId, 'completed');
        } catch (Exception $e) {
            throw new Exception('Failed to mark ticket completed: ' . $e->getMessage());
        }
    }

    /**
     * Call the next pending ticket in queue order.
     *
     * @param int $adminUserId Admin user ID
     * @param int|null $counterNumber Service counter number
     *
     * @return array Called ticket data
     * @throws Exception If user is not admin or queue is empty
     */
    public function callNextTicket($adminUserId, $counterNumber = null) {
        try {
            $admin = $this->getUserById($adminUserId);

            if (!$admin || $admin['role'] !== 'admin') {
                throw new Exception('Only administrators can call the next ticket');
            }

            $ticket = $this->ticketRepository->getNextPendingTicket();

            if (!$ticket) {
                throw new Exception('No pending tickets in queue');
            }

            $this->ticketRepository->updateStatus($ticket['id'], 'in_attendance');
            $this->ticketRepository->createAttendance($ticket['id'], $counterNumber);

            $ticket['status'] = 'in_attendance';
            $ticket['priority'] = ($ticket['type'] === 'priority');
            $ticket['called_at'] = date('Y-m-d H:i:s');
            $ticket['counter_number'] = $counterNumber;

            return $ticket;
        } catch (Exception $e) {
            throw new Exception('Failed to call next ticket: ' . $e->getMessage());
        }
    }
}
?>
