# Fast Meal - Ticket Management API Documentation

## Overview

This document describes the Ticket Management API endpoints for the Fast Meal queue management system. These endpoints implement intelligent queue management with wait time estimation.

## Authentication

All endpoints that require authentication expect the user ID to be provided via:
- **JSON POST body**: `{"user_id": 123}`
- **Query parameter**: `?user_id=123`

Future implementations will support JWT tokens via Authorization header.

## Endpoints

### 1. Request a New Ticket
**Endpoint:** `POST /api/tickets/request`

**Authentication:** Required

**Description:** Request a new ticket for queue management. Implements business rule RN03: user cannot have multiple active tickets.

**Request:**
```json
{
  "user_id": 5
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Ticket requested successfully",
  "data": {
    "id": 1,
    "ticket_number": "A001",
    "type": "normal",
    "status": "pending",
    "user_id": 5,
    "user_name": "João Silva",
    "user_role": "student",
    "priority": false,
    "created_at": "2026-05-10 14:30:00"
  }
}
```

**Error Responses:**
- `401 Unauthorized`: User not logged in
- `409 Conflict`: User already has an active ticket
- `400 Bad Request`: Invalid request

**Business Rules Applied:**
- RN02: Ticket type based on user role (student="normal"/employee="priority")
- RN03: One active ticket per user at a time
- Ticket numbering: A001, A002... (normal) or P001, P002... (priority)

---

### 2. Get User's Active Ticket
**Endpoint:** `GET /api/tickets/active`

**Authentication:** Required

**Description:** Retrieve user's current active ticket (pending or in_attendance status).

**Request:**
```
GET /api/tickets/active?user_id=5
```

**Success Response (200 - with ticket):**
```json
{
  "success": true,
  "message": "Active ticket found",
  "data": {
    "id": 1,
    "ticket_number": "A001",
    "type": "normal",
    "status": "pending",
    "user_id": 5,
    "priority": false,
    "created_at": "2026-05-10 14:30:00"
  }
}
```

**Success Response (200 - no ticket):**
```json
{
  "success": true,
  "message": "No active ticket",
  "data": null
}
```

---

### 3. Get User's Ticket History
**Endpoint:** `GET /api/tickets/my-tickets`

**Authentication:** Required

**Query Parameters:**
- `limit`: Number of records (default: 20, max: 100)

**Description:** Retrieve user's complete ticket history (all statuses).

**Request:**
```
GET /api/tickets/my-tickets?user_id=5&limit=10
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "User tickets retrieved",
  "data": [
    {
      "id": 3,
      "ticket_number": "A003",
      "type": "normal",
      "status": "completed",
      "user_id": 5,
      "priority": false,
      "created_at": "2026-05-09 10:15:00",
      "updated_at": "2026-05-09 10:25:00"
    },
    {
      "id": 2,
      "ticket_number": "A002",
      "type": "normal",
      "status": "completed",
      "user_id": 5,
      "priority": false,
      "created_at": "2026-05-08 14:30:00"
    },
    {
      "id": 1,
      "ticket_number": "A001",
      "type": "normal",
      "status": "cancelled",
      "user_id": 5,
      "priority": false,
      "created_at": "2026-05-08 09:00:00"
    }
  ]
}
```

---

### 4. Cancel a Ticket
**Endpoint:** `POST /api/tickets/{id}/cancel`

**Authentication:** Required

**Description:** Cancel user's active ticket (pending or in_attendance). Implements business rule RN05: only certain statuses can be cancelled.

**Request:**
```json
{
  "user_id": 5
}
```

**URL:** `/api/tickets/1/cancel?user_id=5`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Ticket cancelled successfully",
  "data": {
    "id": 1,
    "ticket_number": "A001",
    "type": "normal",
    "status": "cancelled",
    "user_id": 5,
    "cancelled_at": "2026-05-10 14:35:00"
  }
}
```

**Error Responses:**
- `401 Unauthorized`: User not logged in
- `403 Forbidden`: Cannot cancel other users' tickets
- `404 Not Found`: Ticket does not exist
- `400 Bad Request`: Ticket cannot be cancelled (already completed/cancelled)

**Business Rules:**
- RN05: Only pending or in_attendance tickets can be cancelled
- User can only cancel their own tickets

---

### 5. Get User's Queue Position
**Endpoint:** `GET /api/tickets/my-position`

**Authentication:** Required

**Description:** Get current queue position and wait time estimate for user's active ticket.

**Request:**
```
GET /api/tickets/my-position?user_id=5
```

**Success Response (200 - with ticket):**
```json
{
  "success": true,
  "message": "User queue position retrieved",
  "data": {
    "has_active_ticket": true,
    "ticket_number": "A003",
    "status": "pending",
    "estimated_wait_seconds": 360,
    "estimated_wait_formatted": "06:00",
    "tickets_ahead": 2,
    "average_service_time": 180,
    "average_service_time_formatted": "03:00",
    "estimated_call_time": "2026-05-10 14:46:00",
    "total_in_queue": 5
  }
}
```

**Success Response (200 - no active ticket):**
```json
{
  "success": true,
  "message": "User queue position retrieved",
  "data": {
    "has_active_ticket": false,
    "message": "No active ticket in queue"
  }
}
```

**INTELLIGENT FEATURE:** Wait time is calculated using average service time from last 10 completed attendances.

---

### 6. Get Wait Time Estimate (Specific Ticket)
**Endpoint:** `GET /api/tickets/estimate/{id}`

**Authentication:** Not required

**Description:** Get detailed wait time estimate for a specific ticket.

**Request:**
```
GET /api/tickets/estimate/3
```

**Success Response (200 - in queue):**
```json
{
  "success": true,
  "message": "Wait time estimated",
  "data": {
    "ticket_number": "A005",
    "status": "pending",
    "estimated_wait_seconds": 540,
    "estimated_wait_formatted": "09:00",
    "tickets_ahead": 3,
    "average_service_time": 180,
    "average_service_time_formatted": "03:00",
    "estimated_call_time": "2026-05-10 14:49:00"
  }
}
```

**Success Response (200 - not in queue):**
```json
{
  "success": true,
  "message": "Wait time estimated",
  "data": {
    "estimated_wait_seconds": 0,
    "estimated_wait_formatted": "00:00",
    "status": "completed",
    "message": "This ticket is not in queue"
  }
}
```

**INTELLIGENT FEATURE:** 
- Formula: `estimated_wait = tickets_ahead * average_service_time`
- Average service time calculated from last 10 completed attendances
- Provides real-time wait predictions

---

### 7. Get Queue Status
**Endpoint:** `GET /api/tickets/queue`

**Authentication:** Not required

**Description:** Get complete queue status with all pending tickets and their estimated wait times.

**Request:**
```
GET /api/tickets/queue
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Queue status retrieved",
  "data": {
    "queue": [
      {
        "id": 1,
        "ticket_number": "P001",
        "type": "priority",
        "status": "pending",
        "user_name": "José Silva",
        "position": 1,
        "tickets_ahead": 0,
        "estimated_wait_seconds": 0,
        "estimated_wait_formatted": "00:00",
        "created_at": "2026-05-10 14:30:00"
      },
      {
        "id": 2,
        "ticket_number": "A001",
        "type": "normal",
        "status": "pending",
        "user_name": "Maria Santos",
        "position": 2,
        "tickets_ahead": 1,
        "estimated_wait_seconds": 180,
        "estimated_wait_formatted": "03:00",
        "created_at": "2026-05-10 14:32:00"
      },
      {
        "id": 3,
        "ticket_number": "A002",
        "type": "normal",
        "status": "pending",
        "user_name": "João Oliveira",
        "position": 3,
        "tickets_ahead": 2,
        "estimated_wait_seconds": 360,
        "estimated_wait_formatted": "06:00",
        "created_at": "2026-05-10 14:35:00"
      }
    ],
    "statistics": {
      "total_pending": 3,
      "priority_tickets": 1,
      "normal_tickets": 2,
      "average_service_time": 180,
      "average_service_time_formatted": "03:00",
      "longest_wait_estimated": 360,
      "longest_wait_formatted": "06:00"
    },
    "timestamp": "2026-05-10 14:40:00"
  }
}
```

**Queue Ordering:**
1. Priority tickets first (employees, prefix 'P')
2. Normal tickets by creation time (students, prefix 'A')
3. FIFO (First In, First Out) within each category

---

### 8. Get Queue Analytics
**Endpoint:** `GET /api/tickets/analytics`

**Authentication:** Not required

**Description:** Get analytical data about current queue for monitoring and admin dashboards.

**Request:**
```
GET /api/tickets/analytics
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Queue analytics retrieved",
  "data": {
    "queue_size": 5,
    "average_wait_time": 450,
    "average_wait_time_formatted": "07:30",
    "total_estimated_queue_time": 2250,
    "average_service_time": 180,
    "average_service_time_formatted": "03:00",
    "priority_tickets_count": 1,
    "normal_tickets_count": 4,
    "timestamp": "2026-05-10 14:40:00"
  }
}
```

**Metrics:**
- `queue_size`: Number of pending/in_attendance tickets
- `average_wait_time`: Average wait time across all users
- `total_estimated_queue_time`: Sum of all estimated wait times
- `average_service_time`: Average duration per completed service
- Priority/normal split for load analysis

---

## Ticket Status Lifecycle

```
pending -> in_attendance -> completed
       \                /
        -> cancelled --'
```

**Status Definitions:**
- **pending**: Ticket created, waiting to be called
- **in_attendance**: Currently being served
- **completed**: Service finished successfully
- **cancelled**: User cancelled or service cancelled

---

## Intelligent Features

### 1. Average Service Time Calculation
- Analyzes last 10 completed attendances
- Calculates average duration in seconds
- Falls back to 180 seconds (3 minutes) if no historical data
- Provides `min_time` and `max_time` for variance analysis

### 2. Wait Time Estimation
- **Formula:** `estimated_wait = tickets_ahead × average_service_time`
- Accounts for ticket priority (priority tickets skip the line)
- Provides estimated call time for user planning
- Updates in real-time as queue changes

### 3. Queue Analytics
- Tracks priority vs normal distribution
- Monitors peak hours and congestion
- Helps identify service efficiency patterns
- Enables admin decision-making

---

## Error Codes

| Code | Meaning | Common Causes |
|------|---------|---------------|
| 200 | OK | Successful request |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid input, user has active ticket |
| 401 | Unauthorized | User not authenticated |
| 403 | Forbidden | User unauthorized for action |
| 404 | Not Found | Resource doesn't exist |
| 409 | Conflict | Business rule violation (e.g., duplicate active ticket) |
| 500 | Server Error | Internal server error |

---

## Example cURL Requests

### Request a ticket
```bash
curl -X POST http://localhost:8000/api/tickets/request \
  -H "Content-Type: application/json" \
  -d '{"user_id": 5}'
```

### Get user's queue position
```bash
curl -X GET http://localhost:8000/api/tickets/my-position?user_id=5
```

### Get queue status
```bash
curl -X GET http://localhost:8000/api/tickets/queue
```

### Cancel a ticket
```bash
curl -X POST http://localhost:8000/api/tickets/1/cancel \
  -H "Content-Type: application/json" \
  -d '{"user_id": 5}'
```

### Get wait time estimate
```bash
curl -X GET http://localhost:8000/api/tickets/estimate/3
```

---

## Implementation Notes

### Architecture Pattern
- **Controller** (TicketController.php): HTTP request handling
- **Service** (TicketService.php + QueueService.php): Business logic
- **Repository** (TicketRepository.php): Data persistence
- **Separation of Concerns**: Each layer has single responsibility

### Key Design Decisions
1. **No QR Code Generation in API**: Returns data only; QR code generated client-side
2. **JSON-Only Responses**: All responses in JSON format
3. **PDO Prepared Statements**: All queries use prepared statements for security
4. **User ID in Request**: Simple authentication mechanism; can be upgraded to JWT
5. **Priority-Based Queue**: Employee tickets served before student tickets

### Performance Considerations
- Ticket numbering resets daily (A001 starts each day)
- Average service time calculated from last 10 records (configurable)
- Queue queries indexed by status and creation time
- Attendance history stored separately for analytics

---

## Future Enhancements

- JWT token authentication
- Real-time WebSocket updates for queue position
- SMS/Email notifications for queue position changes
- Advanced analytics dashboard
- Machine learning for peak hour prediction
- Integration with counter/display systems
