# Testes Manuais - Fast Meal Backend

## Prerequisitos

1. Servidor PHP a correr: `php -S localhost:8000` (pasta backend/public)
2. Base de dados criada no Workbench
3. Abra um PowerShell novo

---

## TESTE 1: Verificar API Online

```powershell
curl http://localhost:8000/
```

**Resultado esperado:**
- Vê a resposta JSON da API

---

## TESTE 2: Registar Primeiro Utilizador

```powershell
curl -X POST http://localhost:8000/api/auth/register `
  -H "Content-Type: application/json" `
  -d '{"nome":"Joao Silva","email":"joao@example.com","password":"SecurePass123!"}'
```

**Resultado esperado: (Status 201)**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "id": 1,
    "nome": "Joao Silva",
    "email": "joao@example.com"
  }
}
```

---

## TESTE 3: Email Duplicado (Deve Falhar)

```powershell
curl -X POST http://localhost:8000/api/auth/register `
  -H "Content-Type: application/json" `
  -d '{"nome":"Outro","email":"joao@example.com","password":"AnotherPass123!"}'
```

**Resultado esperado: (Status 409)**
```json
{
  "success": false,
  "message": "Email already registered"
}
```

---

## TESTE 4: Password Fraca (Deve Falhar)

```powershell
curl -X POST http://localhost:8000/api/auth/register `
  -H "Content-Type: application/json" `
  -d '{"nome":"Test","email":"test@example.com","password":"weak"}'
```

**Resultado esperado: (Status 400)**
```json
{
  "success": false,
  "message": "Password must contain..."
}
```

---

## TESTE 5: Login com Credenciais Validas

```powershell
curl -X POST http://localhost:8000/api/auth/login `
  -H "Content-Type: application/json" `
  -d '{"email":"joao@example.com","password":"SecurePass123!"}'
```

**Resultado esperado: (Status 200)**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "id": 1,
    "nome": "Joao Silva",
    "email": "joao@example.com",
    "token": "MTo6MjczODI4MjU6MjEwZGZj..."
  }
}
```

**GUARDE O TOKEN!** Vai usar no teste seguinte.

---

## TESTE 6: Login com Password Errada (Deve Falhar)

```powershell
curl -X POST http://localhost:8000/api/auth/login `
  -H "Content-Type: application/json" `
  -d '{"email":"joao@example.com","password":"WrongPassword123!"}'
```

**Resultado esperado: (Status 401)**
```json
{
  "success": false,
  "message": "Invalid email or password"
}
```

---

## TESTE 7: Get User com Token (Substitua TOKEN)

```powershell
# SUBSTITUA "SEU_TOKEN_AQUI" pelo token obtido no TESTE 5
curl -X GET http://localhost:8000/api/auth/user `
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

**Resultado esperado: (Status 200)**
```json
{
  "success": true,
  "message": "User data retrieved successfully",
  "data": {
    "id": 1,
    "nome": "Joao Silva",
    "email": "joao@example.com",
    "role": "student"
  }
}
```

---

## TESTE 8: Get User sem Token (Deve Falhar)

```powershell
curl -X GET http://localhost:8000/api/auth/user
```

**Resultado esperado: (Status 401)**
```json
{
  "success": false,
  "message": "Missing authorization token"
}
```

---

## TESTE 9: Registar Segundo Utilizador

```powershell
curl -X POST http://localhost:8000/api/auth/register `
  -H "Content-Type: application/json" `
  -d '{"nome":"Maria Santos","email":"maria@example.com","password":"SecurePass456!"}'
```

**Resultado esperado: (Status 201)**
- Novo utilizador criado

---

## TESTE 10: Verificar Base de Dados

Abra o MySQL Workbench e execute:

```sql
SELECT id, nome, email, role FROM users;
```

**Resultado esperado:**
```
id | nome           | email              | role
1  | Joao Silva     | joao@example.com   | student
2  | Maria Santos   | maria@example.com  | student
```

---

## RESUMO

Se todos os testes passarem:

✓ Registar funcionando
✓ Validacao de password funcionando
✓ Email duplicado bloqueado
✓ Login funcionando
✓ Token gerado
✓ Autenticacao por token funcionando
✓ Base de dados funcionando

**Backend 100% pronto!**

---

## Troubleshooting

Se der erro "Connection refused":
- Verifique se o servidor PHP esta a correr
- Terminal com `php -S localhost:8000` deve estar aberto

Se der erro "Access denied" no database test:
- Verifique as credenciais em `backend/app/config/database.php`
- Defina username e password corretos
