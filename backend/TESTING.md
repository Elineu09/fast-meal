# Fast Meal Backend - Testing Guide

## Manual Testing with cURL

### Prerequisites
```bash
# Start the development server
cd backend/public
php -S localhost:8000

# In another terminal, run the test commands below
```

---

## Test Cases

### 1. ✅ Register New User - Valid Data

**Command:**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "João Silva",
    "email": "joao@example.com",
    "password": "SecurePass123!"
  }' | jq
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "id": 1,
    "nome": "João Silva",
    "email": "joao@example.com",
    "role": "user"
  },
  "timestamp": "2026-05-10 10:30:00"
}
```

---

### 2. ✅ Register New User - Duplicate Email

**Command:**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Another User",
    "email": "joao@example.com",
    "password": "AnotherPass123!"
  }' | jq
```

**Expected Response (409):**
```json
{
  "success": false,
  "message": "Email already registered",
  "errors": null,
  "timestamp": "2026-05-10 10:30:00"
}
```

---

### 3. ❌ Register New User - Weak Password

**Command:**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Test User",
    "email": "test@example.com",
    "password": "weak"
  }' | jq
```

**Expected Response (400):**
```json
{
  "success": false,
  "message": "Password must be at least 8 characters with uppercase, lowercase, number and special character",
  "errors": null,
  "timestamp": "2026-05-10 10:30:00"
}
```

---

### 4. ❌ Register - Invalid Email

**Command:**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Test User",
    "email": "invalid-email",
    "password": "SecurePass123!"
  }' | jq
```

**Expected Response (400):**
```json
{
  "success": false,
  "message": "Invalid email format",
  "errors": null
}
```

---

### 5. ❌ Register - Missing Fields

**Command:**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Test User"
  }' | jq
```

**Expected Response (400):**
```json
{
  "success": false,
  "message": "Missing required fields: email, password",
  "errors": ["email", "password"]
}
```

---

### 6. ✅ Login - Valid Credentials

**Command:**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "joao@example.com",
    "password": "SecurePass123!"
  }' | jq
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "id": 1,
    "nome": "João Silva",
    "email": "joao@example.com",
    "role": "user",
    "token": "MTo2MjczODI4MjU6MjEwZGZjOTJmNTg3ZjAyMDNjYmFmZTJmZjc4ZTUwODc=",
    "created_at": "2026-05-10 10:00:00"
  },
  "timestamp": "2026-05-10 10:30:00"
}
```

**Save the token for next test:**
```bash
TOKEN="MTo2MjczODI4MjU6MjEwZGZjOTJmNTg3ZjAyMDNjYmFmZTJmZjc4ZTUwODc="
```

---

### 7. ❌ Login - Invalid Password

**Command:**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "joao@example.com",
    "password": "WrongPassword123!"
  }' | jq
```

**Expected Response (401):**
```json
{
  "success": false,
  "message": "Invalid email or password",
  "errors": null
}
```

---

### 8. ❌ Login - Non-existent Email

**Command:**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "nonexistent@example.com",
    "password": "SecurePass123!"
  }' | jq
```

**Expected Response (401):**
```json
{
  "success": false,
  "message": "Invalid email or password",
  "errors": null
}
```

---

### 9. ✅ Get Current User - Valid Token

**Command:**
```bash
curl -X GET http://localhost:8000/api/auth/user \
  -H "Authorization: Bearer MTo2MjczODI4MjU6MjEwZGZjOTJmNTg3ZjAyMDNjYmFmZTJmZjc4ZTUwODc=" | jq
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "User data retrieved",
  "data": {
    "id": 1,
    "nome": "João Silva",
    "email": "joao@example.com",
    "role": "user",
    "created_at": "2026-05-10 10:00:00"
  },
  "timestamp": "2026-05-10 10:30:00"
}
```

---

### 10. ❌ Get Current User - Missing Token

**Command:**
```bash
curl -X GET http://localhost:8000/api/auth/user | jq
```

**Expected Response (401):**
```json
{
  "success": false,
  "message": "Missing authorization token",
  "errors": null
}
```

---

### 11. ❌ Get Current User - Invalid Token

**Command:**
```bash
curl -X GET http://localhost:8000/api/auth/user \
  -H "Authorization: Bearer INVALID_TOKEN" | jq
```

**Expected Response (401):**
```json
{
  "success": false,
  "message": "Invalid token",
  "errors": null
}
```

---

### 12. ❌ Invalid HTTP Method

**Command:**
```bash
curl -X GET http://localhost:8000/api/auth/register | jq
```

**Expected Response (405):**
```json
{
  "success": false,
  "message": "Method not allowed",
  "errors": null
}
```

---

### 13. ❌ Non-existent Endpoint

**Command:**
```bash
curl -X POST http://localhost:8000/api/auth/invalid | jq
```

**Expected Response (404):**
```json
{
  "success": false,
  "message": "Endpoint not found",
  "errors": null
}
```

---

### 14. ✅ CORS Preflight Request

**Command:**
```bash
curl -X OPTIONS http://localhost:8000/api/auth/register \
  -H "Origin: http://localhost:4200" \
  -H "Access-Control-Request-Method: POST" \
  -v
```

**Expected Headers:**
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

---

## Batch Test Script

Save as `test.sh`:

```bash
#!/bin/bash

BASE_URL="http://localhost:8000/api/auth"
TEST_EMAIL="testuser@example.com"
TEST_PASSWORD="TestPass123!"
TEST_NAME="Test User"

echo "🧪 Starting API Tests..."
echo ""

# Test 1: Register
echo "📝 Test 1: User Registration"
REGISTER=$(curl -s -X POST $BASE_URL/register \
  -H "Content-Type: application/json" \
  -d "{
    \"nome\": \"$TEST_NAME\",
    \"email\": \"$TEST_EMAIL\",
    \"password\": \"$TEST_PASSWORD\"
  }")
echo "$REGISTER" | jq
echo ""

# Test 2: Register duplicate (should fail)
echo "❌ Test 2: Duplicate Email Registration"
curl -s -X POST $BASE_URL/register \
  -H "Content-Type: application/json" \
  -d "{
    \"nome\": \"Another User\",
    \"email\": \"$TEST_EMAIL\",
    \"password\": \"$TEST_PASSWORD\"
  }" | jq
echo ""

# Test 3: Login
echo "🔐 Test 3: User Login"
LOGIN=$(curl -s -X POST $BASE_URL/login \
  -H "Content-Type: application/json" \
  -d "{
    \"email\": \"$TEST_EMAIL\",
    \"password\": \"$TEST_PASSWORD\"
  }")
echo "$LOGIN" | jq
TOKEN=$(echo "$LOGIN" | jq -r '.data.token')
echo "Token: $TOKEN"
echo ""

# Test 4: Get user with token
echo "👤 Test 4: Get Current User"
curl -s -X GET $BASE_URL/user \
  -H "Authorization: Bearer $TOKEN" | jq
echo ""

# Test 5: Invalid login
echo "❌ Test 5: Invalid Credentials"
curl -s -X POST $BASE_URL/login \
  -H "Content-Type: application/json" \
  -d "{
    \"email\": \"$TEST_EMAIL\",
    \"password\": \"WrongPassword123!\"
  }" | jq
echo ""

echo "✅ All tests completed!"
```

**Run tests:**
```bash
chmod +x test.sh
./test.sh
```

---

## Performance Testing

### Load Test with Apache Bench

```bash
# Single request benchmark
ab -n 100 -c 10 http://localhost:8000/api/auth/user

# Expected: ~50-100ms per request
```

---

## Database Verification

### Check Registered Users

```bash
mysql fast_meal -u root -p -e "SELECT id, nome, email, role FROM users;"
```

### Verify Password Hashing

```bash
mysql fast_meal -u root -p -e "SELECT id, email, LEFT(password, 20) FROM users LIMIT 1;"
```

**Output should show:** `$2y$12$...` (bcrypt hash)

---

## Debugging Tips

### Enable Debug Output

Edit `backend/public/index.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Check Server Logs

```bash
tail -f backend/storage/logs/error.log
```

### Test Database Connection

```php
php -r "
require 'app/config/database.php';
try {
  \$db = new Database();
  \$db->connect();
  echo 'Database connected successfully!';
} catch (Exception \$e) {
  echo 'Error: ' . \$e->getMessage();
}
"
```

---

## Postman Collection

Import this into Postman:

```json
{
  "info": {
    "name": "Fast Meal Auth API",
    "version": "1.0"
  },
  "item": [
    {
      "name": "Register",
      "request": {
        "method": "POST",
        "url": "{{base_url}}/api/auth/register",
        "body": {
          "mode": "raw",
          "raw": "{\"nome\":\"John\",\"email\":\"john@example.com\",\"password\":\"SecurePass123!\"}"
        }
      }
    },
    {
      "name": "Login",
      "request": {
        "method": "POST",
        "url": "{{base_url}}/api/auth/login",
        "body": {
          "mode": "raw",
          "raw": "{\"email\":\"john@example.com\",\"password\":\"SecurePass123!\"}"
        }
      }
    }
  ]
}
```

---

## Expected Test Results

| Test | Expected Status | Pass/Fail |
|------|-----------------|-----------|
| Valid Registration | 201 | ✅ |
| Duplicate Email | 409 | ✅ |
| Weak Password | 400 | ✅ |
| Invalid Email | 400 | ✅ |
| Valid Login | 200 | ✅ |
| Wrong Password | 401 | ✅ |
| Get User (with token) | 200 | ✅ |
| Get User (no token) | 401 | ✅ |
| Invalid Method | 405 | ✅ |
| Not Found | 404 | ✅ |

