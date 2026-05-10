# Fast Meal Backend - Setup Guide

## Project Structure

```
backend/
├── app/
│   ├── config/
│   │   └── database.php          # PDO database connection
│   ├── controllers/
│   │   └── AuthController.php    # Auth endpoints handler
│   ├── services/
│   │   └── AuthService.php       # Auth business logic
│   ├── routes/
│   │   └── auth.php              # Auth route definitions
│   ├── utils/
│   │   ├── Response.php          # JSON response handler
│   │   ├── Validator.php         # Input validation
│   │   ├── Helpers.php           # Utility functions
│   │   └── ErrorHandler.php      # Error handling
│   └── middleware/               # (Future) Auth middleware
├── database/
│   ├── schema.sql                # Database schema
│   ├── seed.sql                  # Sample data
│   └── migrations/               # (Future) Schema migrations
├── public/
│   └── index.php                 # API entry point
├── storage/
│   └── logs/                     # Log files
├── .env.example                  # Environment variables template
└── API_DOCUMENTATION.md          # API reference
```

## Requirements

- PHP 7.4+
- MySQL 5.7+ or MariaDB 10.3+
- PDO PHP extension
- Composer (optional, for package management)

## Installation

### 1. Clone or Extract Project

```bash
cd fast-meal/backend
```

### 2. Create Database

```bash
mysql -u root -p < database/schema.sql
```

### 3. Configure Environment

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Edit `.env` with your database credentials:

```env
DB_HOST=localhost
DB_NAME=fast_meal
DB_USER=root
DB_PASSWORD=your_password
```

### 4. Create Required Directories

```bash
mkdir -p storage/logs
chmod 755 storage/logs
```

### 5. Start Development Server

Using PHP built-in server:

```bash
cd public
php -S localhost:8000
```

The API will be available at: `http://localhost:8000`

---

## Architecture

### Technology Stack

- **Language:** PHP 7.4+
- **Database Access:** PDO with Prepared Statements
- **Password Hashing:** Bcrypt (password_hash)
- **Communication:** RESTful API via JSON/HTTP
- **CORS:** Configured for cross-origin requests

### Design Patterns

- **MVC Pattern:** Models, Views (JSON), Controllers
- **Service Layer:** Business logic separation
- **Repository Pattern:** Data access abstraction
- **Singleton Pattern:** Database connection
- **Factory Pattern:** Request routing

### Security Features

✅ **Prepared Statements** - SQL injection prevention
✅ **Password Hashing** - Bcrypt with cost 12
✅ **Input Validation** - Email, password, name validation
✅ **Input Sanitization** - XSS prevention with htmlspecialchars
✅ **CORS Headers** - Controlled cross-origin access
✅ **Error Handling** - Centralized exception handling
✅ **Logging** - Error and application logging

---

## API Endpoints

### Authentication Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register new user |
| POST | `/api/auth/login` | Authenticate user |
| GET | `/api/auth/user` | Get current user (requires token) |

### Response Format

**Success:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { /* response data */ },
  "timestamp": "2026-05-10 10:30:00"
}
```

**Error:**
```json
{
  "success": false,
  "message": "Error description",
  "errors": null,
  "timestamp": "2026-05-10 10:30:00"
}
```

---

## Usage Examples

### Register User

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "João Silva",
    "email": "joao@example.com",
    "password": "SecurePass123!"
  }'
```

### Login User

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "joao@example.com",
    "password": "SecurePass123!"
  }'
```

Response includes `token` which should be stored on frontend.

### Get Current User

```bash
curl -X GET http://localhost:8000/api/auth/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Development Workflow

### Adding New Endpoints

1. **Create Route File**
   - Location: `app/routes/new_feature.php`
   - Import controller

2. **Create Controller**
   - Location: `app/controllers/NewController.php`
   - Inject service and call methods

3. **Create Service**
   - Location: `app/services/NewService.php`
   - Implement business logic

4. **Register Route in index.php**
   - Add route pattern matching

### Code Style Guidelines

- Use camelCase for variables and methods
- Use PascalCase for classes
- Add PHP documentation comments (DocBlocks)
- Keep methods focused and small
- Separate concerns (UI, Logic, Data)
- Use exceptions for error handling
- Use prepared statements for all queries

---

## Database Schema

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_role (role)
);
```

---

## Logging

Logs are stored in `storage/logs/`:

- **app.log** - Application logs
- **error.log** - Error logs
- **fatal.log** - Fatal error logs

Access logs via:

```bash
tail -f storage/logs/app.log
```

---

## CORS Configuration

CORS headers are automatically set by `Response::setJsonHeaders()`:

```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

**For Production:**
- Set specific origin instead of `*`
- Use environment variables for dynamic CORS

---

## Common Issues

### 1. Database Connection Error

**Error:** "Database Connection Error: SQLSTATE[28000]"

**Solution:**
- Check database credentials in `.env`
- Verify MySQL/MariaDB is running
- Check user permissions

### 2. Method Not Allowed

**Error:** "Method not allowed" (405)

**Solution:**
- Use correct HTTP method (POST, GET, etc.)
- Check endpoint documentation

### 3. Missing Authorization Header

**Error:** "Missing authorization token" (401)

**Solution:**
- Include `Authorization: Bearer TOKEN` header
- Get token from login endpoint first

---

## Troubleshooting

### Enable Debug Mode

Edit `backend/public/index.php`:

```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Check Server Logs

```bash
tail -f storage/logs/error.log
```

### Test Database Connection

```php
php -r "
require 'app/config/database.php';
\$db = new Database();
echo \$db->connect() ? 'Connected!' : 'Failed';
"
```

---

## Future Enhancements

- [ ] JWT token implementation
- [ ] Rate limiting
- [ ] Database migrations system
- [ ] Query builder utility
- [ ] Middleware system
- [ ] Cache layer (Redis)
- [ ] API versioning
- [ ] OpenAPI/Swagger documentation

---

## Contributing

When adding new features:

1. Follow existing code structure
2. Add PHP documentation comments
3. Write meaningful commit messages
4. Test with cURL or Postman
5. Update API documentation

---

## License

This project is part of the Fast Meal application.

