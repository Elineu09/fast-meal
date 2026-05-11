<?php
/**
 * Ticket Routes
 * 
 * Path: backend/app/routes/tickets.php
 * 
 * API Endpoints for ticket management:
 * 
 * PUBLIC ENDPOINTS (User accessible):
 * POST   /api/tickets/request         - Request a new ticket
 * GET    /api/tickets/my-tickets      - Get user's ticket history
 * GET    /api/tickets/active          - Get user's active ticket
 * POST   /api/tickets/{id}/cancel     - Cancel user's ticket
 * GET    /api/tickets/my-position     - Get user's queue position
 * GET    /api/tickets/estimate/{id}   - Get wait time estimate for ticket
 * 
 * PUBLIC INFO ENDPOINTS (No auth required):
 * GET    /api/tickets/queue           - Get public queue status
 * GET    /api/tickets/analytics       - Get queue analytics
 * POST   /api/tickets/call-next       - Call next pending ticket
 * 
 * This file defines routing logic and dispatches requests to controller methods
 */

require_once __DIR__ . '/../controllers/TicketController.php';

// Create controller instance
$ticketController = new TicketController();

// Get request method and URI
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Extract route parts from URI
// Example: /fast-meal/backend/public/api/tickets/request
// We need to extract the meaningful parts after 'tickets'
$routeParts = explode('/', $requestUri);
$ticketsIndex = array_search('tickets', $routeParts);

if ($ticketsIndex === false) {
    Response::error('Invalid route', 404);
    exit;
}

// Get the action and potential ID from the route
// Format: /api/tickets/{action}/{id}
$action = $routeParts[$ticketsIndex + 1] ?? null;
$id = $routeParts[$ticketsIndex + 2] ?? null;

/**
 * Route dispatcher
 * 
 * Supported routes patterns:
 * - /api/tickets/request
 * - /api/tickets/my-tickets
 * - /api/tickets/active
 * - /api/tickets/{id}/cancel
 * - /api/tickets/my-position
 * - /api/tickets/estimate/{id}
 * - /api/tickets/queue
 * - /api/tickets/analytics
 */

switch ($action) {

    // REQUEST NEW TICKET
    // POST /api/tickets/request
    case 'request':
        if ($requestMethod === 'POST') {
            $ticketController->requestTicket();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    // GET USER'S TICKETS
    // GET /api/tickets/my-tickets
    case 'my-tickets':
        if ($requestMethod === 'GET') {
            $ticketController->getUserTickets();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    // GET USER'S ACTIVE TICKET
    // GET /api/tickets/active
    case 'active':
        if ($requestMethod === 'GET') {
            $ticketController->getActiveTicket();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    // GET USER'S QUEUE POSITION
    // GET /api/tickets/my-position
    case 'my-position':
        if ($requestMethod === 'GET') {
            $ticketController->getUserQueuePosition();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    // GET QUEUE STATUS
    // GET /api/tickets/queue
    case 'queue':
        if ($requestMethod === 'GET') {
            $ticketController->getQueueStatus();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    // GET QUEUE ANALYTICS
    // GET /api/tickets/analytics
    case 'analytics':
        if ($requestMethod === 'GET') {
            $ticketController->getQueueAnalytics();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    // CALL NEXT TICKET
    // POST /api/tickets/call-next
    case 'call-next':
        if ($requestMethod === 'POST') {
            $ticketController->callNextTicket();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    // TICKET-SPECIFIC ACTIONS
    // Handles: /api/tickets/{id}/cancel, /api/tickets/estimate/{id}
    default:
        // Check if this is a numeric ID (ticket ID for cancel)
        if (is_numeric($action) && $id === 'cancel') {
            if ($requestMethod === 'POST') {
                $ticketId = intval($action);
                $ticketController->cancelTicket($ticketId);
            } else {
                Response::error('Method not allowed', 405);
            }
        }
        // Check if action is 'estimate' with ticket ID
        elseif ($action === 'estimate' && is_numeric($id)) {
            if ($requestMethod === 'GET') {
                $ticketId = intval($id);
                $ticketController->estimateWaitTime($ticketId);
            } else {
                Response::error('Method not allowed', 405);
            }
        }
        // Route not found
        else {
            Response::error('Route not found', 404);
        }
        break;
}
?>
