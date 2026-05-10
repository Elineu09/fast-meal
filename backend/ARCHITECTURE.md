# Fast Meal - Ticket Management Architecture

## System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                         CLIENT LAYER                                │
│  (Angular Frontend - Future Implementation)                         │
│                                                                      │
│  - Request Ticket UI                                                │
│  - Queue Monitor Display                                            │
│  - Wait Time Display                                                │
│  - Personal Ticket History                                          │
└────────────────────────────┬────────────────────────────────────────┘
                             │
                      HTTP/JSON (REST)
                             │
┌────────────────────────────▼────────────────────────────────────────┐
│                      API LAYER                                       │
│                  (backend/public/index.php)                          │
│                                                                      │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │ Router: Routes requests to appropriate controllers          │   │
│  │ - Parses URI and extracts route                             │   │
│  │ - Sets CORS headers                                         │   │
│  │ - Handles JSON responses                                    │   │
│  └─────────────┬──────────────────────────────────┬────────────┘   │
│                │                                  │                 │
│            auth/*                            tickets/*              │
│                │                                  │                 │
│  ┌─────────────▼──────────────┐  ┌──────────────▼────────────────┐ │
│  │  AuthController            │  │  TicketController             │ │
│  │  - register()              │  │  - requestTicket()            │ │
│  │  - login()                 │  │  - getUserTickets()           │ │
│  │  - getUser()               │  │  - getActiveTicket()          │ │
│  └─────────────┬──────────────┘  │  - cancelTicket()             │ │
│                │                  │  - getQueueStatus()           │ │
│        (future: add auth          │  - estimateWaitTime()        │ │
│         middleware)               │  - getQueueAnalytics()       │ │
│                                   │  - getUserQueuePosition()     │ │
│                                   └────────────┬──────────────────┘ │
└─────────────────────────────────────────────────┬──────────────────┘
                                                   │
┌──────────────────────────────────────────────────▼──────────────────┐
│                    BUSINESS LOGIC LAYER                             │
│                    (Services)                                       │
│                                                                      │
│  ┌────────────────────────┐    ┌──────────────────────────────┐   │
│  │  TicketService         │    │  QueueService               │   │
│  │                        │    │                              │   │
│  │  Business Rules:       │    │  INTELLIGENT FEATURES:      │   │
│  │  - RN02: Role-based    │    │  - calculateAvgServiceTime()│   │
│  │    ticket types        │    │  - estimateWaitTime()       │   │
│  │  - RN03: One ticket    │    │  - getQueueStatus()         │   │
│  │    per user rule       │    │  - getQueueAnalytics()      │   │
│  │  - RN05: Status        │    │  - getUserQueuePosition()   │   │
│  │    integrity           │    │                              │   │
│  │                        │    │  Wait Time Formula:          │   │
│  │  Methods:              │    │  wait = tickets_ahead ×     │   │
│  │  - requestTicket()     │    │         avg_service_time    │   │
│  │  - cancelTicket()      │    │                              │   │
│  │  - getUserTickets()    │    │  Data Source:                │   │
│  │  - validateTicket...() │    │  Last 10 completed          │   │
│  │  - markInAttendance()  │    │  attendances                 │   │
│  │  - markCompleted()     │    │                              │   │
│  └────────────┬───────────┘    └─────────────┬────────────────┘   │
│               │                              │                     │
│               └──────────────┬───────────────┘                     │
│                              │                                     │
│                    Calls TicketRepository                          │
│                              │                                     │
└──────────────────────────────┼─────────────────────────────────────┘
                               │
┌──────────────────────────────▼─────────────────────────────────────┐
│                  DATA ACCESS LAYER                                  │
│              (TicketRepository)                                     │
│                                                                     │
│  PDO Prepared Statements:                                          │
│  - create()                     - getById()                        │
│  - getByNumber()                - getActiveTicketByUser()          │
│  - getTicketsByUser()           - getNextTicketNumber()            │
│  - updateStatus()               - getPendingQueue()                │
│  - getCompletedAttendances()    - getTicketsAhead()                │
│  - getQueueStats()                                                 │
│                                                                     │
│  Features:                                                         │
│  - Parameterized queries (SQL injection prevention)                │
│  - Transaction support                                             │
│  - Connection pooling ready                                        │
│  - Error handling and logging                                      │
│                                                                     │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
                        PDO Connection
                               │
┌──────────────────────────────▼──────────────────────────────────────┐
│                        DATABASE LAYER                               │
│                    (MySQL/MariaDB)                                  │
│                                                                      │
│  Tables:                                                            │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐    │
│  │    users        │  │    tickets      │  │  attendances    │    │
│  ├─────────────────┤  ├─────────────────┤  ├─────────────────┤    │
│  │ id (PK)         │  │ id (PK)         │  │ id (PK)         │    │
│  │ nome            │  │ ticket_number   │  │ ticket_id (FK)  │    │
│  │ email (UNIQUE)  │  │ type: enum      │  │ called_at       │    │
│  │ password (hash) │  │ status: enum    │  │ finished_at     │    │
│  │ role: enum      │◄─┤ user_id (FK)    │  │ counter_number  │    │
│  │ created_at      │  │ created_at      │  │ created_at      │    │
│  │ updated_at      │  │ updated_at      │  └─────────────────┘    │
│  └─────────────────┘  └─────────────────┘                          │
│                                                                      │
│  Indexes:                                                           │
│  - users: email (UNIQUE), role                                     │
│  - tickets: user_id, status, type, created_at                      │
│  - attendances: ticket_id, called_at, finished_at                  │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
```

## Request Flow Example: User Requests a Ticket

```
User Interface
      │
      │ POST /api/tickets/request
      │ { "user_id": 5 }
      ▼
┌─────────────────────────┐
│ Backend Router          │
│ (index.php)             │
│ Identifies: /tickets    │
└──────────┬──────────────┘
           │
           ▼
┌─────────────────────────┐
│ TicketController        │
│ requestTicket()         │
└──────────┬──────────────┘
           │
           ▼
┌─────────────────────────────────────────────┐
│ TicketService.requestTicket($userId)        │
│                                              │
│ 1. Validate user exists                     │
│ 2. Check: No active ticket (RN03)           │
│ 3. Determine type by role (RN02):           │
│    - employee → "priority" (prefix P)       │
│    - student → "normal" (prefix A)          │
│ 4. Generate next ticket number              │
│ 5. Call repository to create                │
└──────────┬──────────────────────────────────┘
           │
           ▼
┌──────────────────────────────────┐
│ TicketRepository.create(...)     │
│                                  │
│ INSERT INTO tickets:             │
│ - ticket_number: "A001"          │
│ - type: "normal"                 │
│ - status: "pending"              │
│ - user_id: 5                     │
└──────────┬───────────────────────┘
           │
           ▼
┌──────────────────────────────────┐
│ Database                         │
│ CREATE ticket record             │
│ RETURN id: 1                     │
└──────────┬───────────────────────┘
           │
           ▼ Response Flow (reverse)
      API Response
      {
        "id": 1,
        "ticket_number": "A001",
        "type": "normal",
        "status": "pending",
        "user_id": 5,
        "created_at": "2026-05-10 14:30:00"
      }
```

## Request Flow Example: Get Wait Time Estimate

```
User Interface
      │
      │ GET /api/tickets/estimate/3
      ▼
┌─────────────────────────┐
│ Backend Router          │
│ (index.php)             │
└──────────┬──────────────┘
           │
           ▼
┌──────────────────────────┐
│ TicketController         │
│ estimateWaitTime(3)      │
└──────────┬───────────────┘
           │
           ▼
┌────────────────────────────────────────────────────┐
│ QueueService.estimateWaitTime($ticketId)           │
│                                                    │
│ STEP 1: Get ticket details                        │
│ ✓ Check ticket exists and status is pending       │
│                                                    │
│ STEP 2: Calculate Average Service Time            │
│ ✓ Query last 10 completed attendances             │
│ ✓ Calculate average: sum / count = seconds        │
│ → Result: 180 seconds (3 minutes)                 │
│                                                    │
│ STEP 3: Count Tickets Ahead                       │
│ ✓ If ticket type = "priority":                    │
│   Count other priority tickets before this        │
│ ✓ If ticket type = "normal":                      │
│   Count ALL priority + normal tickets before      │
│ → Result: 2 tickets ahead                         │
│                                                    │
│ STEP 4: Calculate Wait Time                       │
│ wait = tickets_ahead × average_service_time       │
│ wait = 2 × 180 = 360 seconds (6 minutes)          │
│                                                    │
│ STEP 5: Calculate Estimated Call Time             │
│ now + 360 seconds = 14:46:00                      │
│                                                    │
└──────────┬───────────────────────────────────────┘
           │
           ▼ Response
      API Response
      {
        "estimated_wait_seconds": 360,
        "estimated_wait_formatted": "06:00",
        "tickets_ahead": 2,
        "average_service_time": 180,
        "estimated_call_time": "2026-05-10 14:46:00"
      }
```

## Key Design Patterns

### 1. Layered Architecture
```
Presentation Layer (Controllers)
       ↓ (HTTP Requests)
Business Logic Layer (Services)
       ↓ (Business Rules)
Data Access Layer (Repositories)
       ↓ (SQL Queries)
Database (MySQL)
```

**Benefits:**
- Clear separation of concerns
- Easy to test each layer independently
- Easy to modify database implementation
- Reusable business logic

### 2. Repository Pattern
```
Service Layer
      ↓
TicketRepository
      ↓
Database

All database queries go through repository
- Encapsulates SQL logic
- Makes data access testable
- Enables easy database swaps
```

### 3. Service Layer Business Logic
```
TicketService:
- Validates business rules (RN02, RN03, RN05)
- Coordinates multiple repository calls
- Implements transaction logic

QueueService:
- Calculates complex metrics
- Provides intelligent features
- Aggregates data from multiple sources
```

## Ticket Type Assignment (RN02)

```
User Role          →    Ticket Type    →    Prefix    →    Priority
─────────────────────────────────────────────────────────────────────
employee           →    "priority"     →    "P"       →    High
estudante          →    "priority"     →    "P"       →    High
admin              →    "priority"     →    "P"       →    High
                                        
student            →    "normal"       →    "A"       →    Normal
user               →    "normal"       →    "A"       →    Normal
(default)          →    "normal"       →    "A"       →    Normal
```

## Queue Ordering Algorithm

```
QUEUE ORDER:
1. First: All PRIORITY tickets (P001, P002, P003...)
   - Ordered by creation time (FIFO)
   
2. Second: All NORMAL tickets (A001, A002, A003...)
   - Ordered by creation time (FIFO)

STATUS FILTER:
- Only "pending" and "in_attendance" tickets shown
- Completed and cancelled tickets not in active queue

POSITION CALCULATION:
For any ticket, position = tickets_ahead + 1
- Count all priority tickets created before this
- Count all normal tickets created before this (if this is normal)
```

## Intelligent Wait Time Algorithm

```
FORMULA: estimated_wait = tickets_ahead × average_service_time

WHERE:
  tickets_ahead = number of tickets ahead in queue
  
  average_service_time = average duration from last 10 completed
    completed_duration = finished_at - called_at
    average = sum(durations) / count(durations)
    fallback = 180 seconds (3 minutes) if no data

UPDATES:
- Real-time as queue changes
- Recalculates on each request
- Accounts for priority/normal ordering

ACCURACY FACTORS:
✓ Considers actual service patterns
✓ Weighted by position (closer = more accurate)
✓ Adapts to system load variations
✗ Doesn't account for: service complexity, unforeseen delays
```

## Error Handling Strategy

```
HTTP Level:
400 - Bad Request (invalid input, business rule violation)
401 - Unauthorized (no authentication)
403 - Forbidden (user unauthorized for action)
404 - Not Found (resource doesn't exist)
409 - Conflict (business rule violation like duplicate ticket)
500 - Server Error (unexpected failure)

Business Logic Level:
- Each service throws Exception with descriptive message
- Controller catches and maps to appropriate HTTP status
- Response includes detailed error message for debugging

Database Level:
- Prepared statements prevent SQL injection
- Foreign keys ensure referential integrity
- Unique constraints prevent duplicates
- Transactions maintain consistency
```

## Performance Considerations

### Database Indexes
```
users:
- INDEX idx_email (email)          - Fast login lookup
- INDEX idx_role (role)             - Filter by role

tickets:
- INDEX idx_user_id (user_id)       - Find user's tickets
- INDEX idx_status (status)         - Filter queue
- INDEX idx_type (type)             - Priority filtering
- INDEX idx_created_at (created_at) - Time-based queries

attendances:
- INDEX idx_ticket_id (ticket_id)   - Link to ticket
- INDEX idx_called_at (called_at)   - Time range queries
- INDEX idx_finished_at (finished_at) - Analytics queries
```

### Query Optimization
1. **Ticket number generation**: Only counts today's tickets (uses CURDATE())
2. **Queue status**: Single query with ORDER BY for priority
3. **Completed attendances**: Limited to last 10 records
4. **Statistics**: Aggregation queries with SUM/COUNT

### Scalability Notes
- Ticket counters reset daily (keeps numbers small)
- Archiving old attendances possible (> 30 days)
- Queue operations are O(n) where n = pending tickets (typically < 50)
- Service time calculation cached per minute (future optimization)

## Security Measures

1. **SQL Injection Prevention**
   - All queries use PDO prepared statements
   - No string concatenation in SQL

2. **Authorization**
   - User can only cancel own tickets
   - User can only view own ticket history
   - Admin routes can be protected (future)

3. **Data Validation**
   - User ID must be integer
   - Ticket ID must be integer
   - Status values validated against ENUM

4. **CORS Protection**
   - Response::handleCORS() sets proper headers
   - Prevents cross-origin abuse

## File Structure

```
backend/
├── app/
│   ├── controllers/
│   │   ├── AuthController.php
│   │   └── TicketController.php ← NEW
│   ├── services/
│   │   ├── AuthService.php
│   │   ├── TicketService.php ← NEW
│   │   └── QueueService.php ← NEW (Intelligent Features)
│   ├── repositories/
│   │   └── TicketRepository.php ← NEW
│   ├── routes/
│   │   ├── auth.php
│   │   └── tickets.php ← NEW
│   ├── config/
│   │   └── database.php
│   ├── utils/
│   │   ├── Response.php
│   │   ├── Validator.php
│   │   ├── Helpers.php
│   │   └── ErrorHandler.php
│   └── models/
│       └── (future: ticket and user models)
├── database/
│   ├── schema.sql
│   └── seed.sql
├── public/
│   └── index.php (updated router)
├── TICKETS_API.md ← NEW (comprehensive API docs)
└── ARCHITECTURE.md ← THIS FILE
```

## Next Steps (Future Implementation)

1. **Admin Panel Features**
   - Call next ticket endpoint
   - Mark ticket as in_attendance/completed
   - Admin dashboard with real-time queue view

2. **Authentication Enhancement**
   - JWT token implementation
   - Authorization middleware
   - Role-based access control

3. **Frontend Integration**
   - Angular components for ticket request
   - Real-time queue monitor using polling/WebSocket
   - User notification system

4. **Analytics Dashboard**
   - Peak hours visualization
   - Service efficiency metrics
   - Wait time trends
   - User satisfaction correlation

5. **QR Code Integration**
   - Generate QR codes with ticket number
   - Display on ticket receipt
   - Scan for attendance verification

6. **Notifications**
   - SMS/Email when ticket called
   - Wait time updates
   - Service feedback requests

7. **Testing**
   - Unit tests for services
   - Integration tests for endpoints
   - Load testing for concurrent users
