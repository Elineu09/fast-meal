# 📋 RECURSOS DE TESTE - Sumário Completo

## 🎯 Ficheiros de Teste Criados

### 📄 Documentação de Teste

```
✅ START_HERE.md              ← COMECE AQUI (3 passos simples)
✅ RUN_TESTS.md               ← Guia rápido de execução
✅ TESTE_COMPLETO.md          ← Guia detalhado passo a passo
✅ TESTING.md                 ← Testes individuais com cURL
```

### 🔧 Scripts Automatizados

```
✅ test-api.ps1               ← Script PowerShell que executa 12 testes automaticamente
```

### 📚 Documentação de Referência

```
✅ API_DOCUMENTATION.md       ← Referência completa da API
✅ SETUP.md                   ← Instalação e configuração
✅ README.md                  ← Visão geral do projeto
```

---

## 🚀 COMEÇAR OS TESTES

### Opção 1: Automático (Recomendado) - 15 minutos

```powershell
# 1. Preparar BD
mysql -u root -p < backend\database\schema.sql

# 2. Iniciar servidor (deixe aberto)
cd backend\public
php -S localhost:8000

# 3. Executar testes (em novo PowerShell)
cd C:\Users\Myself\Documents\ES II\fast-meal
.\backend\test-api.ps1
```

→ Ver **START_HERE.md** para instruções detalhadas

### Opção 2: Manual - 20 minutos

Cada teste é executado manualmente com cURL:

```powershell
# Registar
curl -X POST http://localhost:8000/api/auth/register ...

# Login
curl -X POST http://localhost:8000/api/auth/login ...

# Get user
curl -X GET http://localhost:8000/api/auth/user ...
```

→ Ver **TESTE_COMPLETO.md** para todos os testes com explicações

---

## 📊 O QUE CADA FICHEIRO FAZ

### START_HERE.md
- **Finalidade:** Guia super rápido e simples
- **Para quem:** Quer começar logo os testes
- **Tempo:** 2 minutos de leitura
- **Conteúdo:** 3 passos principais

### RUN_TESTS.md
- **Finalidade:** Referência rápida de como executar
- **Para quem:** Já conhece o projeto
- **Tempo:** 1 minuto de leitura
- **Conteúdo:** Comandos exatos, troubleshooting

### TESTE_COMPLETO.md
- **Finalidade:** Guia completo passo a passo
- **Para quem:** Quer entender cada teste em detalhe
- **Tempo:** 15 minutos de leitura
- **Conteúdo:** 12 testes com explicações completas

### TESTING.md
- **Finalidade:** Testes unitários com cURL
- **Para quem:** Prefere testar manualmente
- **Tempo:** Variável (teste por teste)
- **Conteúdo:** Cada teste individual com exemplos

### test-api.ps1
- **Finalidade:** Script automatizado de testes
- **Para quem:** Quer validação rápida
- **Tempo:** ~1 minuto de execução
- **Conteúdo:** 12 testes automatizados com relatório

---

## ✅ TESTES DISPONÍVEIS

### Testes Implementados (12 total)

| # | Teste | Ficheiro | Script |
|---|-------|----------|--------|
| 1 | Registar (sucesso) | TESTE_COMPLETO.md | ✓ |
| 2 | Email duplicado | TESTE_COMPLETO.md | ✓ |
| 3 | Password fraca | TESTE_COMPLETO.md | ✓ |
| 4 | Email inválido | TESTE_COMPLETO.md | ✓ |
| 5 | Login (sucesso) | TESTE_COMPLETO.md | ✓ |
| 6 | Password errada | TESTE_COMPLETO.md | ✓ |
| 7 | Email não existe | TESTE_COMPLETO.md | ✓ |
| 8 | Get user (com token) | TESTE_COMPLETO.md | ✓ |
| 9 | Get user (sem token) | TESTE_COMPLETO.md | ✓ |
| 10 | Token inválido | TESTE_COMPLETO.md | ✓ |
| 11 | Segundo utilizador | TESTE_COMPLETO.md | ✓ |
| 12 | Verificação BD | TESTE_COMPLETO.md | ✓ |

---

## 🎯 FLUXO RECOMENDADO

### Para Iniciante

```
1. Ler: START_HERE.md (2 min)
   ↓
2. Executar: test-api.ps1 (1 min)
   ↓
3. Resultado: ✓ Todos os testes passaram!
```

### Para Aprofundado

```
1. Ler: TESTE_COMPLETO.md (15 min)
   ↓
2. Executar: Testes manuais de TESTING.md (20 min)
   ↓
3. Verificar: API_DOCUMENTATION.md (10 min)
   ↓
4. Resultado: Entender completamente o backend
```

### Para Verificação Rápida

```
1. Executar: test-api.ps1 (1 min)
   ↓
2. Ver: RUN_TESTS.md (1 min)
   ↓
3. Resultado: ✓ Backend validado
```

---

## 📈 RESULTADO ESPERADO

### Execução do Script (test-api.ps1)

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

Se vir isto: **✅ Backend está 100% funcional**

---

## 🔍 COMO USAR CADA FICHEIRO

### Não tenho tempo

```
Abra: START_HERE.md
Execute: test-api.ps1
Pronto!
```

### Quero entender tudo

```
Leia: TESTE_COMPLETO.md (todo)
Após: Consulte TESTING.md para variações
```

### Estou debugando um erro

```
Veja: RUN_TESTS.md (troubleshooting section)
Depois: Consulte backend\storage\logs\error.log
```

### Quero integrar com Angular

```
Consulte: API_DOCUMENTATION.md
Modelo: Veja exemplos TypeScript no final
```

---

## 💾 FICHEIROS NO DISCO

### Localização

```
backend/
├── START_HERE.md           ← 🎯 Comece aqui
├── RUN_TESTS.md            ← Guia rápido
├── TESTE_COMPLETO.md       ← Guia completo
├── TESTING.md              ← Testes manuais
├── test-api.ps1            ← Script automático
├── API_DOCUMENTATION.md    ← Referência API
├── SETUP.md                ← Instalação
├── README.md               ← Visão geral
└── storage/logs/           ← Logs dos testes
    ├── app.log
    └── error.log
```

---

## 📞 SUPORTE E TROUBLESHOOTING

### Script não executa

```
Solução: Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
Depois:  .\backend\test-api.ps1
```

### Servidor não responde

```
Solução: cd backend\public
         php -S localhost:8000
         (deixe o terminal aberto)
```

### Base de dados com erro

```
Solução: mysql -u root -p < backend\database\schema.sql
         (password em branco, só Enter)
```

### Testes falhados

```
Verificar: backend\storage\logs\error.log
Consultar: TESTE_COMPLETO.md secção "Troubleshooting"
```

---

## ✨ PRÓXIMAS FASES

Após testes passarem com sucesso:

1. **Integração Angular** - Usar AuthService
2. **Ticket Management** - Criar senhas digitais
3. **Queue System** - Fila em tempo real
4. **Reports** - Exportação PDF/CSV
5. **Admin Dashboard** - Painel administrativo

---

## 📋 CHECKLIST

- [ ] Ler START_HERE.md
- [ ] Executar passo 1 (BD)
- [ ] Executar passo 2 (servidor)
- [ ] Executar passo 3 (testes)
- [ ] Ver resultado com ✓ 12/12
- [ ] Consultar documentação conforme necessário
- [ ] Backend pronto para integração Angular

---

**Status:** ✅ Tudo pronto para testes

**Última atualização:** 10 de maio de 2026

**Comece em:** START_HERE.md →
