# Fast Meal Backend - Authentication API Documentation

## Overview

This documentation covers the authentication endpoints for the Fast Meal API. All communication is done via JSON over HTTP with proper CORS headers configured.

## Base URL

```
http://localhost:8000/api/auth
```

## Security Features

- ✅ Prepared statements (PDO) prevent SQL injection
- ✅ Bcrypt password hashing (cost: 12)
- ✅ Strong password validation (8+ chars, uppercase, lowercase, number, special char)
- ✅ Email format validation
- ✅ CORS headers configured for frontend integration
- ✅ Session token generation on login
- ✅ Input sanitization and validation

## Endpoints

### 1. Register User

**Endpoint:** `POST /api/auth/register`

**Description:** Register a new user in the system

**Request Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "nome": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123!"
}
```

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "id": 1,
    "nome": "John Doe",
    "email": "john@example.com",
    "role": "user"
  },
  "timestamp": "2026-05-10 10:30:00"
}
```

**Error Response (400 Bad Request):**
```json
{
  "success": false,
  "message": "Password must be at least 8 characters with uppercase, lowercase, number and special character",
  "errors": null,
  "timestamp": "2026-05-10 10:30:00"
}
```

**Error Response (409 Conflict - Email exists):**
```json
{
  "success": false,
  "message": "Email already registered",
  "errors": null,
  "timestamp": "2026-05-10 10:30:00"
}
```

**Password Requirements:**
- Minimum 8 characters
- At least one uppercase letter (A-Z)
- At least one lowercase letter (a-z)
- At least one number (0-9)
- At least one special character (!@#$%^&*...)

**Example cURL:**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "John Doe",
    "email": "john@example.com",
    "password": "SecurePass123!"
  }'
```

---

### 2. Login User

**Endpoint:** `POST /api/auth/login`

**Description:** Authenticate user and obtain session token

**Request Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "SecurePass123!"
}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "id": 1,
    "nome": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "token": "MTo2MjczODI4MjU6MjEwZGZjOTJmNTg3ZjAyMDNjYmFmZTJmZjc4ZTUwODc=",
    "created_at": "2026-05-10 10:00:00"
  },
  "timestamp": "2026-05-10 10:30:00"
}
```

**Error Response (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Invalid email or password",
  "errors": null,
  "timestamp": "2026-05-10 10:30:00"
}
```

**Example cURL:**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePass123!"
  }'
```

---

### 3. Get Current User

**Endpoint:** `GET /api/auth/user`

**Description:** Retrieve information about the currently authenticated user

**Request Headers:**
```
Content-Type: application/json
Authorization: Bearer MTo2MjczODI4MjU6MjEwZGZjOTJmNTg3ZjAyMDNjYmFmZTJmZjc4ZTUwODc=
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "User data retrieved",
  "data": {
    "id": 1,
    "nome": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "created_at": "2026-05-10 10:00:00"
  },
  "timestamp": "2026-05-10 10:30:00"
}
```

**Error Response (401 Unauthorized - Missing token):**
```json
{
  "success": false,
  "message": "Missing authorization token",
  "errors": null,
  "timestamp": "2026-05-10 10:30:00"
}
```

**Example cURL:**
```bash
curl -X GET http://localhost:8000/api/auth/user \
  -H "Authorization: Bearer MTo2MjczODI4MjU6MjEwZGZjOTJmNTg3ZjAyMDNjYmFmZTJmZjc4ZTUwODc="
```

---

## HTTP Status Codes

| Code | Meaning | Use Case |
|------|---------|----------|
| 200 | OK | Successful request (login, get user) |
| 201 | Created | Resource created (registration) |
| 400 | Bad Request | Invalid input, missing fields |
| 401 | Unauthorized | Invalid credentials, missing token |
| 404 | Not Found | Endpoint doesn't exist |
| 405 | Method Not Allowed | Wrong HTTP method |
| 409 | Conflict | Email already registered |
| 500 | Server Error | Database or server error |
| 501 | Not Implemented | Endpoint not yet implemented |

---

## Database Schema

The authentication system requires these tables:

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

---

## Frontend Integration Example (Angular)

```typescript
// auth.service.ts
import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private apiUrl = 'http://localhost:8000/api/auth';

  constructor(private http: HttpClient) {}

  register(nome: string, email: string, password: string) {
    return this.http.post(`${this.apiUrl}/register`, {
      nome,
      email,
      password
    });
  }

  login(email: string, password: string) {
    return this.http.post(`${this.apiUrl}/login`, {
      email,
      password
    });
  }

  getCurrentUser(token: string) {
    return this.http.get(`${this.apiUrl}/user`, {
      headers: { Authorization: `Bearer ${token}` }
    });
  }
}
```

---

## Error Handling

All errors follow a consistent JSON structure:

```json
{
  "success": false,
  "message": "Human-readable error message",
  "errors": null,
  "timestamp": "2026-05-10 10:30:00"
}
```

### Common Errors

1. **Missing Required Fields**
   - Status: 400
   - Message: "Missing required fields: nome, email"

2. **Invalid Email Format**
   - Status: 400
   - Message: "Invalid email format"

3. **Weak Password**
   - Status: 400
   - Message: "Password must be at least 8 characters with uppercase, lowercase, number and special character"

4. **Email Already Registered**
   - Status: 409
   - Message: "Email already registered"

5. **Invalid Credentials**
   - Status: 401
   - Message: "Invalid email or password"

---

## Testing

### Using Postman

1. **Register:**
   - Method: POST
   - URL: `http://localhost:8000/api/auth/register`
   - Body (JSON):
     ```json
     {
       "nome": "Test User",
       "email": "test@example.com",
       "password": "TestPass123!"
     }
     ```

2. **Login:**
   - Method: POST
   - URL: `http://localhost:8000/api/auth/login`
   - Body (JSON):
     ```json
     {
       "email": "test@example.com",
       "password": "TestPass123!"
     }
     ```

3. **Get User:**
   - Method: GET
   - URL: `http://localhost:8000/api/auth/user`
   - Headers: `Authorization: Bearer {token_from_login}`

---

## Notes

- All timestamps are in UTC timezone
- Password hashing uses bcrypt with cost factor of 12
- Session tokens are base64-encoded and include user ID and timestamp
- CORS is enabled for all origins (configure based on deployment)
- PDO prepared statements prevent SQL injection attacks

