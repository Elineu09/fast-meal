# 🚀 EXECUTAR TESTES - Fast Meal Backend

## ⚡ Execução Rápida (15 minutos)

### PASSO 1: Preparar a Base de Dados

```powershell
# Abra PowerShell e execute:
cd "C:\Users\Myself\Documents\ES II\fast-meal"

# Criar base de dados
mysql -u root -p < backend\database\schema.sql
# (Quando pedir password, pressione Enter - sem password)
```

✓ Se não vir erros, BD foi criada com sucesso

---

### PASSO 2: Iniciar o Servidor PHP

Abra **novo PowerShell** (importante: não feche depois!):

```powershell
cd "C:\Users\Myself\Documents\ES II\fast-meal\backend\public"

# Iniciar servidor
php -S localhost:8000
```

**Deixe este terminal aberto durante os testes!**

Deve ver:
```
Development Server (http://localhost:8000) started on [Fri May 10 10:30:00 2026]
```

---

### PASSO 3: Executar os Testes Automaticamente

Abra **outro PowerShell novo** (não feche o anterior!):

```powershell
# Navegar para o projeto
cd "C:\Users\Myself\Documents\ES II\fast-meal"

# Permitir execução de scripts (primeira vez)
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

# Executar testes
.\backend\test-api.ps1
```

Verá algo como:

```
╔════════════════════════════════════════════╗
║   Fast Meal Backend - Test Suite v1.0      ║
╚════════════════════════════════════════════╝

📋 TEST: POST /api/auth/register (SUCCESS)
   ✓ Status: 201 (Expected: 201)
   Response: User registered successfully

📋 TEST: POST /api/auth/register (DUPLICATE EMAIL)
   ✓ Status: 409 (Expected: 409)
   Response: Email already registered

[... mais testes ...]

📊 RESUMO DOS TESTES

✓ Testes Passados: 12
✗ Testes Falhados: 0

Taxa de Sucesso: 100% (12/12)

╔════════════════════════════════════════════╗
║   ✓ TODOS OS TESTES PASSARAM! 🎉          ║
║   Backend pronto para produção            ║
╚════════════════════════════════════════════╝
```

---

## 📋 O que os Testes Verificam

| # | Teste | O que Testa |
|---|-------|------------|
| 1 | Registar (sucesso) | Novo utilizador é criado |
| 2 | Email duplicado | Sistema rejeita email já usado |
| 3 | Password fraca | Sistema valida força da password |
| 4 | Email inválido | Sistema valida formato de email |
| 5 | Login (sucesso) | Utilizador consegue fazer login |
| 6 | Password errada | Sistema rejeita password incorreta |
| 7 | Email não existe | Sistema rejeita login de email novo |
| 8 | Get user (com token) | Utilizador pode ver os seus dados |
| 9 | Get user (sem token) | Sistema rejeita acesso não autenticado |
| 10 | Token inválido | Sistema rejeita token fake |
| 11 | Segundo utilizador | Múltiplos utilizadores funcionam |
| 12 | Verificação BD | Dados estão corretos na base de dados |

---

## 🧪 TESTES MANUAIS (alternativa)

Se preferir fazer testes manual, abra PowerShell e execute:

```powershell
# TESTE 1: Registar
curl -X POST http://localhost:8000/api/auth/register `
  -H "Content-Type: application/json" `
  -d '{
    "nome": "Test User",
    "email": "test@example.com", 
    "password": "TestPass123!"
  }'

# TESTE 2: Login
curl -X POST http://localhost:8000/api/auth/login `
  -H "Content-Type: application/json" `
  -d '{
    "email": "test@example.com",
    "password": "TestPass123!"
  }'

# TESTE 3: Get user (substitua TOKEN pela resposta do login)
curl -X GET http://localhost:8000/api/auth/user `
  -H "Authorization: Bearer TOKEN_AQUI"
```

Para testes manuais completos, consulte: **TESTE_COMPLETO.md**

---

## 🔍 TROUBLESHOOTING

### Erro: "The term 'test-api.ps1' is not recognized"
```
Solução: Verifique o caminho com: dir backend\test-api.ps1
```

### Erro: "Cannot be loaded because running scripts is disabled"
```
Solução: Execute: Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### Erro: "Access denied for user 'root'@'localhost'"
```
Solução: Verifique .env no backend - DB_PASSWORD deve estar em branco para root sem senha
```

### Erro: "Connection refused" / "curl: command not found"
```
Solução 1: Servidor PHP não está a rodar - verifique terminal com "php -S localhost:8000"
Solução 2: Use Invoke-WebRequest em vez de curl no PowerShell
```

---

## ✅ RESULTADO ESPERADO

Se todos os testes passarem (100%), significa:

- ✅ Registar funcionando
- ✅ Validação de passwords funcionando  
- ✅ Validação de emails funcionando
- ✅ Login funcionando
- ✅ Geração de tokens funcionando
- ✅ Autenticação por token funcionando
- ✅ Base de dados funcionando
- ✅ Encriptação de passwords funcionando
- ✅ CORS funcionando
- ✅ **Backend 100% pronto para integração Angular**

---

## 📊 PRÓXIMAS FASES

Depois de validar todos os testes com sucesso:

1. **Ticket Management** - Criar, listar, cancelar senhas
2. **Queue Management** - Fila de espera em tempo real
3. **Reports** - Exportar PDF/CSV
4. **Admin Dashboard** - Painel administrativo

---

## 💡 DICA

Para uma análise mais detalhada, consulte:
- **TESTE_COMPLETO.md** - Guia passo a passo com explicações
- **API_DOCUMENTATION.md** - Referência completa da API
- **TESTING.md** - Testes individuais com cURL

---

**Última atualização:** 10 de maio de 2026
**Versão:** 1.0
