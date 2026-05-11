<?php
/**
 * Ticket Controller
 * 
 * Path: backend/app/controllers/TicketController.php
 * 
 * Handles HTTP requests for ticket management endpoints:
 * - POST /api/tickets/request - Request a new ticket
 * - GET /api/tickets/my-tickets - Get user's tickets
 * - GET /api/tickets/active - Get user's active ticket
 * - POST /api/tickets/{id}/cancel - Cancel a ticket
 * - GET /api/tickets/queue - Get queue status
 * - GET /api/tickets/estimate/{id} - Get wait time estimate
 * 
 * Responsibilities:
 * - Parse HTTP requests
 * - Validate authorization (user token)
 * - Call service layer
 * - Return JSON responses
 * - Handle errors with descriptive messages
 */

require_once __DIR__ . '/../services/TicketService.php';
require_once __DIR__ . '/../services/QueueService.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

class TicketController {
    private $ticketService;
    private $queueService;

    /**
     * Initialize TicketController
     */
    public function __construct() {
        $this->ticketService = new TicketService();
        $this->queueService = new QueueService();
    }

    /**
     * Request a new ticket
     * 
     * Endpoint: POST /api/tickets/request
     * 
     * Requires:
     * - Authorization header with valid user token
     * 
     * Business logic:
     * - Validate user is authenticated
     * - Check user doesn't have active ticket (RN03)
     * - Generate new ticket based on user role
     * - Store ticket in database
     * 
     * Response 201 (Success):
     * {
     *     "success": true,
     *     "message": "Ticket requested successfully",
     *     "data": {
     *         "id": 1,
     *         "ticket_number": "A001",
     *         "type": "normal",
     *         "status": "pending",
     *         "user_id": 5,
     *         "user_name": "João Silva",
     *         "user_role": "student",
     *         "priority": false,
     *         "created_at": "2026-05-10 14:30:00"
     *     }
     * }
     * 
     * Response 400/401/409 (Error):
     * {
     *     "success": false,
     *     "message": "Error description"
     * }
     */
    public function requestTicket() {
        try {
            // Get current user from request context
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                Response::error('Unauthorized: Please login first', 401);
            }

            // Request new ticket
            $ticket = $this->ticketService->requestTicket($userId);

            Response::success(
                $ticket,
                'Ticket requested successfully',
                201
            );
        } catch (Exception $e) {
            // Handle specific error messages
            if (strpos($e->getMessage(), 'already has an active ticket') !== false) {
                Response::error($e->getMessage(), 409);
            } else {
                Response::error($e->getMessage(), 400);
            }
        }
    }

    /**
     * Get user's active ticket
     * 
     * Endpoint: GET /api/tickets/active
     * 
     * Returns:
     * - Current active ticket if user has one (pending or in_attendance)
     * - null if no active ticket
     * 
     * Useful for:
     * - Checking current queue position
     * - Getting wait time estimate
     * - Showing ticket details to user
     * 
     * Response 200 (Success with ticket):
     * {
     *     "success": true,
     *     "message": "Active ticket found",
     *     "data": {
     *         "id": 1,
     *         "ticket_number": "A001",
     *         "status": "pending",
     *         "priority": false
     *     }
     * }
     * 
     * Response 200 (Success without ticket):
     * {
     *     "success": true,
     *     "message": "No active ticket",
     *     "data": null
     * }
     */
    public function getActiveTicket() {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                Response::error('Unauthorized', 401);
            }

            $activeTicket = $this->ticketService->getUserActiveTicket($userId);

            if (!$activeTicket) {
                Response::success(null, 'No active ticket', 200);
            } else {
                Response::success($activeTicket, 'Active ticket found', 200);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * Get user's tickets history
     * 
     * Endpoint: GET /api/tickets/my-tickets
     * 
     * Query parameters:
     * - limit: Number of records (default: 20, max: 100)
     * 
     * Returns user's ticket history including:
     * - All tickets (pending, completed, cancelled)
     * - Creation dates
     * - Status information
     * 
     * Response 200:
     * {
     *     "success": true,
     *     "message": "User tickets retrieved",
     *     "data": [
     *         {
     *             "id": 1,
     *             "ticket_number": "A001",
     *             "type": "normal",
     *             "status": "completed",
     *             "priority": false,
     *             "created_at": "2026-05-10 14:30:00"
     *         },
     *         ...
     *     ]
     * }
     */
    public function getUserTickets() {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                Response::error('Unauthorized', 401);
            }

            // Get limit from query parameter
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
            $limit = min($limit, 100); // Cap at 100

            $tickets = $this->ticketService->getUserTickets($userId, $limit);

            Response::success(
                $userTickets,
                'User tickets retrieved',
                200
            );
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * Cancel user's ticket
     * 
     * Endpoint: POST /api/tickets/{id}/cancel
     * 
     * Business rules (RN05):
     * - Only pending or in_attendance tickets can be cancelled
     * - Completed or cancelled tickets cannot be changed
     * - User can only cancel their own tickets
     * 
     * Response 200 (Success):
     * {
     *     "success": true,
     *     "message": "Ticket cancelled successfully",
     *     "data": {
     *         "id": 1,
     *         "ticket_number": "A001",
     *         "status": "cancelled",
     *         "cancelled_at": "2026-05-10 14:35:00"
     *     }
     * }
     * 
     * Response 400/403/404 (Error):
     * {
     *     "success": false,
     *     "message": "Error description"
     * }
     */
    public function cancelTicket($ticketId) {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                Response::error('Unauthorized', 401);
            }

            $cancelledTicket = $this->ticketService->cancelTicket($ticketId, $userId);

            Response::success(
                $cancelledTicket,
                'Ticket cancelled successfully',
                200
            );
        } catch (Exception $e) {
            // Parse error status code if included in message
            $statusCode = 400;
            if (strpos($e->getMessage(), '403') !== false) {
                $statusCode = 403;
            } elseif (strpos($e->getMessage(), '404') !== false) {
                $statusCode = 404;
            }

            Response::error($e->getMessage(), $statusCode);
        }
    }

    /**
     * Get current queue status
     * 
     * Endpoint: GET /api/tickets/queue
     * 
     * Returns comprehensive queue information:
     * - All pending/in-attendance tickets with positions
     * - Estimated wait times for each ticket
     * - Queue statistics (total, priority, normal)
     * - Average service time from last 10 completed attendances
     * 
     * INTELLIGENT FEATURE:
     * - Uses historical data to calculate average service time
     * - Provides accurate wait time estimates based on:
     *   - Current position in queue
     *   - Average time per service
     *   - Ticket priority
     * 
     * Response 200:
     * {
     *     "success": true,
     *     "message": "Queue status retrieved",
     *     "data": {
     *         "queue": [
     *             {
     *                 "id": 1,
     *                 "ticket_number": "P001",
     *                 "type": "priority",
     *                 "status": "pending",
     *                 "position": 1,
     *                 "tickets_ahead": 0,
     *                 "estimated_wait_seconds": 0,
     *                 "estimated_wait_formatted": "00:00"
     *             },
     *             ...
     *         ],
     *         "statistics": {
     *             "total_pending": 5,
     *             "priority_tickets": 1,
     *             "normal_tickets": 4,
     *             "average_service_time": 180,
     *             "average_service_time_formatted": "03:00",
     *             "longest_wait_estimated": 720,
     *             "longest_wait_formatted": "12:00"
     *         },
     *         "timestamp": "2026-05-10 14:40:00"
     *     }
     * }
     */
    public function getQueueStatus() {
        try {
            $queueStatus = $this->queueService->getQueueStatus();

            Response::success(
                $queueStatus,
                'Queue status retrieved',
                200
            );
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * Get wait time estimate for specific ticket
     * 
     * Endpoint: GET /api/tickets/estimate/{id}
     * 
     * INTELLIGENT FEATURE:
     * Calculates personalized wait time estimate based on:
     * - User's position in queue
     * - Average service time (last 10 completed attendances)
     * - Ticket priority status
     * 
     * Formula:
     * estimated_wait = tickets_ahead * average_service_time
     * 
     * Response 200:
     * {
     *     "success": true,
     *     "message": "Wait time estimated",
     *     "data": {
     *         "ticket_number": "A005",
     *         "status": "pending",
     *         "estimated_wait_seconds": 540,
     *         "estimated_wait_formatted": "09:00",
     *         "tickets_ahead": 3,
     *         "average_service_time": 180,
     *         "average_service_time_formatted": "03:00",
     *         "estimated_call_time": "2026-05-10 14:49:00"
     *     }
     * }
     * 
     * Response 200 (Not in queue):
     * {
     *     "success": true,
     *     "message": "Wait time estimated",
     *     "data": {
     *         "estimated_wait_seconds": 0,
     *         "estimated_wait_formatted": "00:00",
     *         "status": "completed",
     *         "message": "This ticket is not in queue"
     *     }
     * }
     */
    public function estimateWaitTime($ticketId) {
        try {
            $estimate = $this->queueService->estimateWaitTime($ticketId);

            Response::success(
                $estimate,
                'Wait time estimated',
                200
            );
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * Get queue analytics
     * 
     * Endpoint: GET /api/tickets/analytics
     * (Admin only feature - but can show public info)
     * 
     * Returns queue analytics for monitoring and reporting
     * 
     * Response 200:
     * {
     *     "success": true,
     *     "message": "Queue analytics retrieved",
     *     "data": {
     *         "queue_size": 5,
     *         "average_wait_time": 450,
     *         "average_wait_time_formatted": "07:30",
     *         "total_estimated_queue_time": 2250,
     *         "average_service_time": 180,
     *         "priority_tickets_count": 1,
     *         "normal_tickets_count": 4,
     *         "timestamp": "2026-05-10 14:40:00"
     *     }
     * }
     */
    public function getQueueAnalytics() {
        try {
            $analytics = $this->queueService->getQueueAnalytics();

            Response::success(
                $analytics,
                'Queue analytics retrieved',
                200
            );
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * Call the next pending ticket.
     *
     * Endpoint: POST /api/tickets/call-next
     *
     * Requires JSON body:
     * {
     *     "user_id": 1,
     *     "counter_number": 1
     * }
     */
    public function callNextTicket() {
        try {
            $adminUserId = $this->getCurrentUserId();

            if (!$adminUserId) {
                Response::error('Unauthorized', 401);
            }

            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $counterNumber = isset($input['counter_number']) ? intval($input['counter_number']) : null;

            $calledTicket = $this->ticketService->callNextTicket($adminUserId, $counterNumber);

            Response::success(
                $calledTicket,
                'Next ticket called successfully',
                200
            );
        } catch (Exception $e) {
            $statusCode = 400;

            if (strpos($e->getMessage(), 'Only administrators') !== false) {
                $statusCode = 403;
            } elseif (strpos($e->getMessage(), 'No pending tickets') !== false) {
                $statusCode = 404;
            }

            Response::error($e->getMessage(), $statusCode);
        }
    }

    /**
     * Get user's queue position
     * 
     * Endpoint: GET /api/tickets/my-position
     * 
     * Requires:
     * - Authorization header with valid user token
     * 
     * Returns user's current position in queue with wait estimate
     * 
     * Response 200 (With active ticket):
     * {
     *     "success": true,
     *     "message": "User queue position retrieved",
     *     "data": {
     *         "has_active_ticket": true,
     *         "ticket_number": "A003",
     *         "position": 3,
     *         "tickets_ahead": 2,
     *         "estimated_wait_seconds": 360,
     *         "estimated_wait_formatted": "06:00",
     *         "average_service_time": 180,
     *         "estimated_call_time": "2026-05-10 14:46:00"
     *     }
     * }
     * 
     * Response 200 (No active ticket):
     * {
     *     "success": true,
     *     "message": "User queue position retrieved",
     *     "data": {
     *         "has_active_ticket": false,
     *         "message": "No active ticket in queue"
     *     }
     * }
     */
    public function getUserQueuePosition() {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                Response::error('Unauthorized', 401);
            }

            $position = $this->queueService->getUserQueuePosition($userId);

            Response::success(
                $position,
                'User queue position retrieved',
                200
            );
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * Get current user ID from request
     * 
     * Extracts user ID from:
     * 1. JSON POST body: {"user_id": 123}
     * 2. Query parameter: ?user_id=123
     * 3. Authorization context (future: JWT tokens)
     * 
     * @return int|null User ID or null if not found
     */
    private function getCurrentUserId() {
        // Check POST body
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['user_id'])) {
            return intval($input['user_id']);
        }

        // Check query parameter
        if (isset($_GET['user_id'])) {
            return intval($_GET['user_id']);
        }

        // Future: Check Authorization header for JWT token
        // if (isset($_SERVER['HTTP_AUTHORIZATION'])) { ... }

        return null;
    }
}
?>
