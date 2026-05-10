# 🧪 GUIA PRÁTICO - Testar Autenticação Fast Meal

## ⏱️ Tempo Estimado: 15 minutos

Siga este guia passo a passo para testar todos os endpoints de autenticação.

---

## PASSO 1: Preparar o Ambiente (5 minutos)

### 1.1 Verificar PHP e MySQL

Abra PowerShell e execute:

```powershell
# Verificar versão do PHP
php -v

# Deve mostrar algo como: PHP 7.4.0 ou superior
```

```powershell
# Verificar MySQL
mysql -V

# Deve mostrar algo como: mysql Ver 8.0.x
```

### 1.2 Criar a Base de Dados

```powershell
# Navegar para a pasta do projeto
cd "C:\Users\Myself\Documents\ES II\fast-meal"

# Criar a base de dados
mysql -u root -p < backend\database\schema.sql

# Quando pedir password, deixe em branco (Enter)
```

**Resultado esperado:** Sem erros, direto para nova linha

---

## PASSO 2: Configurar o Servidor (2 minutos)

### 2.1 Criar ficheiro .env

```powershell
# Copiar template
Copy-Item backend\.env.example backend\.env
```

### 2.2 Editar .env (abrir em editor)

Abra `backend\.env` e verifique:

```
DB_HOST=localhost
DB_NAME=fast_meal
DB_USER=root
DB_PASSWORD=
```

Deixe como está (sem password para root é comum em desenvolvimento).

### 2.3 Criar pasta de logs

```powershell
# Criar directório
New-Item -ItemType Directory -Force -Path "backend\storage\logs" | Out-Null

Write-Host "✓ Pasta de logs criada"
```

---

## PASSO 3: Iniciar o Servidor (2 minutos)

### 3.1 Abrir Terminal Novo

Pressione `Ctrl + Shift + \`` para abrir novo terminal integrado no VS Code.

### 3.2 Iniciar Servidor PHP

```powershell
# Navegar para pasta public
cd "C:\Users\Myself\Documents\ES II\fast-meal\backend\public"

# Iniciar servidor
php -S localhost:8000

# Deve mostrar:
# Development Server (http://localhost:8000) started on...
```

**Deixe este terminal aberto durante os testes!**

---

## PASSO 4: Testar Endpoints (6 minutos)

### Abra NOVO PowerShell para os testes

**NÃO feche o terminal com o servidor!**

Abra outro PowerShell:

```powershell
cd "C:\Users\Myself\Documents\ES II\fast-meal"
```

---

## TESTE 1: Verificar se API está online ✓

```powershell
curl http://localhost:8000/

# Resultado esperado:
# {
#   "success": true,
#   "name": "Fast Meal API",
#   "version": "1.0"
# }
```

Se vir isto, a API está funcionando! ✓

---

## TESTE 2: Registar Utilizador (Sucesso) ✓

```powershell
curl -X POST http://localhost:8000/api/auth/register `
  -H "Content-Type: application/json" `
  -d '{
    "nome": "João Silva",
    "email": "joao@example.com",
    "password": "SecurePass123!"
  }' | ConvertFrom-Json | ConvertTo-Json
```

**Resultado esperado (201):**
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

✓ **Se vir isto, o registo funcionou!**

---

## TESTE 3: Registar com Email Duplicado (Erro) ✗

```powershell
curl -X POST http://localhost:8000/api/auth/register `
  -H "Content-Type: application/json" `
  -d '{
    "nome": "Outro Utilizador",
    "email": "joao@example.com",
    "password": "AnotherPass123!"
  }' | ConvertFrom-Json | ConvertTo-Json
```

**Resultado esperado (409):**
```json
{
  "success": false,
  "message": "Email already registered",
  "errors": null
}
```

✓ **Correto! Impediu email duplicado**

---

## TESTE 4: Registar com Password Fraca (Erro) ✗

```powershell
curl -X POST http://localhost:8000/api/auth/register `
  -H "Content-Type: application/json" `
  -d '{
    "nome": "Test User",
    "email": "test@example.com",
    "password": "weak"
  }' | ConvertFrom-Json | ConvertTo-Json
```

**Resultado esperado (400):**
```json
{
  "success": false,
  "message": "Password must be at least 8 characters with uppercase, lowercase, number and special character",
  "errors": null
}
```

✓ **Correto! Password foi validada**

---

## TESTE 5: Login com Credenciais Válidas ✓

```powershell
curl -X POST http://localhost:8000/api/auth/login `
  -H "Content-Type: application/json" `
  -d '{
    "email": "joao@example.com",
    "password": "SecurePass123!"
  }' | ConvertFrom-Json | ConvertTo-Json
```

**Resultado esperado (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "id": 1,
    "nome": "João Silva",
    "email": "joao@example.com",
    "role": "user",
    "token": "MTo6MjczODI4MjU6MjEwZGZjOTJmNTg3ZjAyMDNjYmFmZTJmZjc4ZTUwODc=",
    "created_at": "2026-05-10 10:00:00"
  },
  "timestamp": "2026-05-10 10:30:00"
}
```

✓ **Login funcionou! Guarde o TOKEN para o próximo teste**

---

## TESTE 6: Login com Password Errada ✗

```powershell
curl -X POST http://localhost:8000/api/auth/login `
  -H "Content-Type: application/json" `
  -d '{
    "email": "joao@example.com",
    "password": "WrongPassword123!"
  }' | ConvertFrom-Json | ConvertTo-Json
```

**Resultado esperado (401):**
```json
{
  "success": false,
  "message": "Invalid email or password",
  "errors": null
}
```

✓ **Correto! Password foi validada**

---

## TESTE 7: Obter Dados do Utilizador (Com Token) ✓

```powershell
# SUBSTITUA o token pelo que obteve no TESTE 5
$token = "MTo6MjczODI4MjU6MjEwZGZjOTJmNTg3ZjAyMDNjYmFmZTJmZjc4ZTUwODc="

curl -X GET http://localhost:8000/api/auth/user `
  -H "Authorization: Bearer $token" | ConvertFrom-Json | ConvertTo-Json
```

**Resultado esperado (200):**
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

✓ **Token funcionou! Utilizador autenticado**

---

## TESTE 8: Aceder Sem Token (Erro) ✗

```powershell
curl -X GET http://localhost:8000/api/auth/user | ConvertFrom-Json | ConvertTo-Json
```

**Resultado esperado (401):**
```json
{
  "success": false,
  "message": "Missing authorization token",
  "errors": null
}
```

✓ **Correto! Proteção funcionou**

---

## TESTE 9: Criar Segundo Utilizador ✓

```powershell
curl -X POST http://localhost:8000/api/auth/register `
  -H "Content-Type: application/json" `
  -d '{
    "nome": "Maria Santos",
    "email": "maria@example.com",
    "password": "SecurePass456!"
  }' | ConvertFrom-Json | ConvertTo-Json
```

**Resultado esperado:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "id": 2,
    "nome": "Maria Santos",
    "email": "maria@example.com",
    "role": "user"
  }
}
```

✓ **Segundo utilizador criado com sucesso**

---

## TESTE 10: Verificar Base de Dados

```powershell
# Verificar utilizadores criados
mysql -u root fast_meal -e "SELECT id, nome, email, role FROM users;"
```

**Resultado esperado:**
```
+----+--------------+------------------+------+
| id | nome         | email            | role |
+----+--------------+------------------+------+
|  1 | João Silva   | joao@example.com | user |
|  2 | Maria Santos | maria@example.com| user |
+----+--------------+------------------+------+
```

✓ **Ambos os utilizadores na base de dados**

---

## TESTE 11: Verificar Password Hash

```powershell
# Verificar que passwords estão encriptadas
mysql -u root fast_meal -e "SELECT id, email, LEFT(password, 30) as password_hash FROM users;"
```

**Resultado esperado:**
```
+----+------------------+--------------------------------+
| id | email            | password_hash                  |
+----+------------------+--------------------------------+
|  1 | joao@example.com | $2y$12$abcd1234efgh5678ijkl...  |
|  2 | maria@example.com| $2y$12$wxyz9876zyxw5432utsrq...  |
+----+------------------+--------------------------------+
```

✓ **Passwords estão encriptadas com bcrypt ($2y$12$...)**

---

## TESTE 12: Verificar Logs

```powershell
# Ver se foram criados ficheiros de log
dir backend\storage\logs\

# Deve mostrar ficheiros como:
# app.log, error.log (se houver erros)
```

```powershell
# Ver conteúdo do log
cat backend\storage\logs\app.log

# Deve mostrar operações de registro/login
```

---

## 📋 CHECKLIST DE TESTES

```
✓ Teste 1:  API Online
✓ Teste 2:  Registar (sucesso)
✓ Teste 3:  Email duplicado (erro esperado)
✓ Teste 4:  Password fraca (erro esperado)
✓ Teste 5:  Login (sucesso + token)
✓ Teste 6:  Login password errada (erro esperado)
✓ Teste 7:  Get user com token (sucesso)
✓ Teste 8:  Get user sem token (erro esperado)
✓ Teste 9:  Criar segundo utilizador
✓ Teste 10: Verificar BD (2 utilizadores)
✓ Teste 11: Verificar passwords encriptadas
✓ Teste 12: Verificar logs criados
```

---

## 🐛 TROUBLESHOOTING

### Erro: "Connection refused"
```
Problema: Servidor PHP não está rodando
Solução:  Verifique que o terminal com "php -S localhost:8000" está aberto
```

### Erro: "SQLSTATE[28000]: Access denied"
```
Problema: Credenciais da base de dados erradas
Solução:  Edite .env com as credenciais correctas
Teste:   mysql -u root -p (sem password, Enter)
```

### Erro: "No such file or directory"
```
Problema: Ficheiros não encontrados
Solução:  Verifique os caminhos com: dir backend\app\config\
```

### Response vazia ou erro 500
```
Problema: Erro no servidor
Solução:  Verifique os logs: cat backend\storage\logs\error.log
```

---

## 📊 RESUMO DOS TESTES

| # | Teste | Método | Status Esperado | Resultado |
|---|-------|--------|-----------------|-----------|
| 1 | API Online | GET | 200 | ✓ |
| 2 | Register (OK) | POST | 201 | ✓ |
| 3 | Email Duplicado | POST | 409 | ✓ |
| 4 | Password Fraca | POST | 400 | ✓ |
| 5 | Login (OK) | POST | 200 | ✓ |
| 6 | Login (Erro) | POST | 401 | ✓ |
| 7 | Get User (OK) | GET | 200 | ✓ |
| 8 | Get User (Erro) | GET | 401 | ✓ |
| 9 | Register User 2 | POST | 201 | ✓ |
| 10 | BD Verificação | SQL | - | ✓ |
| 11 | Hash Verificação | SQL | - | ✓ |
| 12 | Logs Verificação | File | - | ✓ |

---

## ✅ TESTE COMPLETO PRONTO?

Se todos os testes acima funcionarem:

```
✅ Backend de autenticação está 100% funcional
✅ Pronto para integração com Angular
✅ Segurança implementada corretamente
✅ Base de dados funcionando
```

---

## 🎯 PRÓXIMOS PASSOS

Depois de validar todos os testes:

1. **Integrar com Angular** - Usar AuthService.ts
2. **Criar Ticket Management** - Nova camada
3. **Implementar Queue** - Real-time updates
4. **Adicionar Reports** - PDF/CSV export

---

## 📝 NOTAS IMPORTANTES

- **Token é válido apenas para a sessão** - Base64 encoded
- **Passwords nunca são retornadas** - Apenas no set inicial
- **Logs podem ser consultados** para debugging
- **CORS está configurado** - Integração fronted OK

---

**Data:** 10 de maio de 2026
**Versão:** 1.0
**Status:** Pronto para testes ✅

