# 🎯 COMECE AQUI - Testar Backend Fast Meal

## 3 PASSOS SIMPLES (15 minutos)

---

## ✅ PASSO 1: Preparar Base de Dados

Abra **PowerShell** e execute:

```powershell
cd "C:\Users\Myself\Documents\ES II\fast-meal"

mysql -u root -p < backend\database\schema.sql
```

Quando pedir password: **Pressione Enter** (sem password)

✓ Pronto se não houver erros

---

## ✅ PASSO 2: Iniciar Servidor (Deixe Aberto!)

Abra **novo PowerShell** (não feche o anterior):

```powershell
cd "C:\Users\Myself\Documents\ES II\fast-meal\backend\public"

php -S localhost:8000
```

Verá:
```
Development Server (http://localhost:8000) started on...
```

✓ **DEIXE ESTE TERMINAL ABERTO DURANTE OS TESTES**

---

## ✅ PASSO 3: Executar Testes

Abra **outro PowerShell novo** (agora tem 3 terminais):

```powershell
cd "C:\Users\Myself\Documents\ES II\fast-meal"

Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

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

[... 10 testes mais ...]

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

## 🎉 SUCESSO!

Se vir a mensagem acima, significa:

✅ Backend está 100% funcional
✅ Base de dados está funcionando
✅ Autenticação está segura
✅ Pronto para integração Angular

---

## 📚 DOCUMENTAÇÃO DISPONÍVEL

| Ficheiro | Para |
|----------|------|
| **RUN_TESTS.md** | Resumo rápido dos testes |
| **TESTE_COMPLETO.md** | Guia detalhado passo a passo |
| **TESTING.md** | Testes individuais com cURL |
| **API_DOCUMENTATION.md** | Referência completa da API |
| **SETUP.md** | Instalação e configuração |
| **README.md** | Visão geral do backend |

---

## ❌ SE ALGO DER ERRADO

### "Connection refused"
- Verifique se o servidor PHP está a correr (Passo 2)
- Terminal com `php -S localhost:8000` deve estar aberto

### "Access denied for user 'root'"
- Verifique .env: `DB_PASSWORD=` (em branco)
- ou execute: `mysql -u root` (sem -p)

### "The term 'test-api.ps1' is not recognized"
- Verifique o caminho: `dir backend\test-api.ps1`
- Pode estar em `/backend/` e não `/backend\`

### "Cannot be loaded because running scripts is disabled"
- Execute:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

---

## 🔗 PRÓXIMOS PASSOS (após testes passarem)

1. **Integrar com Angular** - Usar AuthService
2. **Implementar Tickets** - Criar senhas
3. **Implementar Queue** - Filas em tempo real
4. **Adicionar Reports** - PDF/CSV export

---

## 💡 DICA RÁPIDA

Se preferir testar manualmente sem script:

```powershell
# Registar
curl -X POST http://localhost:8000/api/auth/register `
  -H "Content-Type: application/json" `
  -d '{"nome":"Test","email":"test@example.com","password":"SecurePass123!"}'

# Login
curl -X POST http://localhost:8000/api/auth/login `
  -H "Content-Type: application/json" `
  -d '{"email":"test@example.com","password":"SecurePass123!"}'

# Get user (substitua TOKEN)
curl -X GET http://localhost:8000/api/auth/user `
  -H "Authorization: Bearer TOKEN"
```

---

**Estou pronto! Vá ao Passo 1 →**
