# Fast Meal Backend API

> RESTful API Backend for Canteen Queue Management System

## 🎯 Quick Start

### Prerequisites
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.3+
- PDO PHP extension

### Setup (5 minutes)

```bash
# 1. Create database
mysql -u root -p < database/schema.sql

# 2. Configure environment
cp .env.example .env
# Edit .env with your database credentials

# 3. Start server
cd public
php -S localhost:8000

# 4. Test
curl http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"nome":"Test","email":"test@example.com","password":"SecurePass123!"}'
```

## 📚 Documentation

| Document | Purpose |
|----------|---------|
| [API_DOCUMENTATION.md](API_DOCUMENTATION.md) | Complete API reference with examples |
| [SETUP.md](SETUP.md) | Installation and configuration guide |
| [TESTING.md](TESTING.md) | Manual testing with cURL examples |
| [../IMPLEMENTATION_SUMMARY.md](../IMPLEMENTATION_SUMMARY.md) | Architecture overview |

## 🔗 API Endpoints

### Authentication
```
POST   /api/auth/register      → Register new user
POST   /api/auth/login         → Authenticate user  
GET    /api/auth/user          → Get current user (requires token)
```

All responses are JSON. Include `Authorization: Bearer TOKEN` header for protected endpoints.

## 🏗️ Project Structure

```
backend/
├── app/
│   ├── config/       → Database connection
│   ├── controllers/  → HTTP request handlers
│   ├── services/     → Business logic
│   ├── routes/       → Endpoint definitions
│   └── utils/        → Helpers, validators, response handlers
├── database/         → Schema and migrations
├── public/
│   └── index.php    → API entry point
├── storage/logs/     → Application logs
└── composer.json     → Project metadata
```

## 🔐 Security Features

✅ **Bcrypt Password Hashing** - Cost factor 12
✅ **Prepared Statements** - SQL injection prevention  
✅ **Input Validation** - Email, password, name validation
✅ **CORS Headers** - Cross-origin request handling
✅ **Error Handling** - Centralized exception handling
✅ **Session Tokens** - Base64-encoded auth tokens

## 📋 Example Requests

### Register
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "João Silva",
    "email": "joao@example.com",
    "password": "SecurePass123!"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "joao@example.com",
    "password": "SecurePass123!"
  }'
```

### Get User
```bash
curl -X GET http://localhost:8000/api/auth/user \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 📊 Response Format

**Success (200/201):**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { /* response data */ },
  "timestamp": "2026-05-10 10:30:00"
}
```

**Error (4xx/5xx):**
```json
{
  "success": false,
  "message": "Error description",
  "errors": null,
  "timestamp": "2026-05-10 10:30:00"
}
```

## 🗄️ Database

Required table:
```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🧪 Testing

Run manual tests:
```bash
# See TESTING.md for complete test cases
bash test.sh
```

Or test individual endpoints:
```bash
# Run tests from TESTING.md file
```

## 🎓 Key Implementation Details

### Password Security
- Algorithm: **bcrypt** (PASSWORD_BCRYPT)
- Cost Factor: **12** (strong security)
- Verification: **password_verify()** (constant-time)

### Database Access
- Type: **PDO** with Prepared Statements
- Prevention: SQL Injection, Parameter Injection
- Error Mode: Exception-based

### Input Validation
- Email: RFC-compliant format validation
- Password: 8+ chars, uppercase, lowercase, number, special char
- Name: 3-100 characters

## 🚀 Running the API

```bash
# Development server
cd backend/public
php -S localhost:8000

# Or use composer script
cd backend
composer start

# For network access
php -S 0.0.0.0:8000
```

## 🔍 Troubleshooting

**Database Connection Error**
```bash
# Check credentials in .env file
# Verify MySQL is running
# Test connection: mysql -u root -p fast_meal
```

**500 Internal Error**
```bash
# Check logs
tail -f storage/logs/error.log

# Enable debug mode in public/index.php
ini_set('display_errors', 1);
```

**CORS Issues**
- Headers are automatically set by Response class
- For production, update CORS origin in app/utils/Response.php

## 📦 Dependencies

**Required (built-in PHP):**
- PDO extension
- JSON extension
- Standard library

**Zero external dependencies** - Pure PHP implementation

## 🛠️ Development

### Adding New Endpoints

1. Create **Controller** → `app/controllers/MyController.php`
2. Create **Service** → `app/services/MyService.php`  
3. Create **Routes** → `app/routes/myfeature.php`
4. Register **Router** → `public/index.php`

### Code Style

- PHP 7.4+ syntax
- PSR-12 standards
- Complete PHPDoc comments
- Separation of concerns (MVC)
- DRY principle

## 📝 Logs

Application logs stored in `storage/logs/`:
- **app.log** - General application logs
- **error.log** - Error logs
- **fatal.log** - Fatal error logs

## 🔄 Frontend Integration

The API is ready for Angular/TypeScript integration:

```typescript
// Angular HTTP Client
this.http.post('/api/auth/login', credentials)
  .subscribe(response => {
    // Save token
    localStorage.setItem('token', response.data.token);
    // Navigate to dashboard
  });
```

## 📞 Support

- Full API documentation: See [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- Setup instructions: See [SETUP.md](SETUP.md)
- Testing guide: See [TESTING.md](TESTING.md)
- Architecture: See [../IMPLEMENTATION_SUMMARY.md](../IMPLEMENTATION_SUMMARY.md)

## 📄 License

Part of the Fast Meal project

## ✅ Status

**Phase 1 - Authentication:** ✅ Complete
- User Registration with validation
- Secure Login with password verification
- Session token generation
- User data retrieval

**Phase 2 - Tickets:** ⏳ Pending
**Phase 3 - Queue Management:** ⏳ Pending  
**Phase 4 - Reports:** ⏳ Pending

---

**Last Updated:** May 10, 2026
**Version:** 1.0.0

