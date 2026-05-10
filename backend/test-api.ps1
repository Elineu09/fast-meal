# Fast Meal Backend - Simple Test Script

$API = "http://localhost:8000"
$PASS = 0
$FAIL = 0

Write-Host ""
Write-Host "=== FAST MEAL BACKEND TEST ==="
Write-Host ""

# TESTE 1: Registar
Write-Host "[1] Registando primeiro utilizador..." -ForegroundColor Cyan
try {
    $body = @{nome="Joao Silva"; email="joao@example.com"; password="SecurePass123!"} | ConvertTo-Json
    $r = Invoke-WebRequest -Uri "$API/api/auth/register" -Method POST -Body $body -ContentType "application/json" -UseBasicParsing
    Write-Host "OK - Status 201" -ForegroundColor Green
    $PASS++
} catch {
    Write-Host "ERRO - Status $($_.Exception.Response.StatusCode.Value__)" -ForegroundColor Red
    $FAIL++
}

# TESTE 2: Email duplicado
Write-Host "[2] Testando email duplicado (deve falhar)..." -ForegroundColor Cyan
try {
    $body = @{nome="Outro"; email="joao@example.com"; password="Pass123!"} | ConvertTo-Json
    $r = Invoke-WebRequest -Uri "$API/api/auth/register" -Method POST -Body $body -ContentType "application/json" -UseBasicParsing -ErrorAction SilentlyContinue
} catch {
    if ($_.Exception.Response.StatusCode.Value__ -eq 409) {
        Write-Host "OK - Status 409" -ForegroundColor Green
        $PASS++
    } else {
        Write-Host "ERRO" -ForegroundColor Red
        $FAIL++
    }
}

# TESTE 3: Password fraca
Write-Host "[3] Testando password fraca (deve falhar)..." -ForegroundColor Cyan
try {
    $body = @{nome="Test"; email="test@example.com"; password="weak"} | ConvertTo-Json
    $r = Invoke-WebRequest -Uri "$API/api/auth/register" -Method POST -Body $body -ContentType "application/json" -UseBasicParsing -ErrorAction SilentlyContinue
} catch {
    if ($_.Exception.Response.StatusCode.Value__ -eq 400) {
        Write-Host "OK - Status 400" -ForegroundColor Green
        $PASS++
    } else {
        Write-Host "ERRO" -ForegroundColor Red
        $FAIL++
    }
}

# TESTE 4: Login
Write-Host "[4] Fazendo login..." -ForegroundColor Cyan
try {
    $body = @{email="joao@example.com"; password="SecurePass123!"} | ConvertTo-Json
    $r = Invoke-WebRequest -Uri "$API/api/auth/login" -Method POST -Body $body -ContentType "application/json" -UseBasicParsing
    $TOKEN = ($r.Content | ConvertFrom-Json).data.token
    Write-Host "OK - Token obtido" -ForegroundColor Green
    $PASS++
} catch {
    Write-Host "ERRO" -ForegroundColor Red
    $FAIL++
}

# TESTE 5: Login com password errada
Write-Host "[5] Testando login com password errada (deve falhar)..." -ForegroundColor Cyan
try {
    $body = @{email="joao@example.com"; password="Wrong123!"} | ConvertTo-Json
    $r = Invoke-WebRequest -Uri "$API/api/auth/login" -Method POST -Body $body -ContentType "application/json" -UseBasicParsing -ErrorAction SilentlyContinue
} catch {
    if ($_.Exception.Response.StatusCode.Value__ -eq 401) {
        Write-Host "OK - Status 401" -ForegroundColor Green
        $PASS++
    } else {
        Write-Host "ERRO" -ForegroundColor Red
        $FAIL++
    }
}

# TESTE 6: Get user com token
if ($TOKEN) {
    Write-Host "[6] Obtendo dados do usuario com token..." -ForegroundColor Cyan
    try {
        $headers = @{Authorization="Bearer $TOKEN"}
        $r = Invoke-WebRequest -Uri "$API/api/auth/user" -Method GET -Headers $headers -UseBasicParsing
        Write-Host "OK - Status 200" -ForegroundColor Green
        $PASS++
    } catch {
        Write-Host "ERRO" -ForegroundColor Red
        $FAIL++
    }
}

# TESTE 7: Get user sem token
Write-Host "[7] Testando acesso sem token (deve falhar)..." -ForegroundColor Cyan
try {
    $r = Invoke-WebRequest -Uri "$API/api/auth/user" -Method GET -UseBasicParsing -ErrorAction SilentlyContinue
} catch {
    if ($_.Exception.Response.StatusCode.Value__ -eq 401) {
        Write-Host "OK - Status 401" -ForegroundColor Green
        $PASS++
    } else {
        Write-Host "ERRO" -ForegroundColor Red
        $FAIL++
    }
}

# TESTE 8: Registar segundo usuario
Write-Host "[8] Registando segundo utilizador..." -ForegroundColor Cyan
try {
    $body = @{nome="Maria Santos"; email="maria@example.com"; password="SecurePass456!"} | ConvertTo-Json
    $r = Invoke-WebRequest -Uri "$API/api/auth/register" -Method POST -Body $body -ContentType "application/json" -UseBasicParsing
    Write-Host "OK - Status 201" -ForegroundColor Green
    $PASS++
} catch {
    Write-Host "ERRO" -ForegroundColor Red
    $FAIL++
}

# RESUMO
Write-Host ""
Write-Host "========= RESUMO =========" -ForegroundColor Cyan
Write-Host "Passados: $PASS" -ForegroundColor Green
Write-Host "Falhados: $FAIL" -ForegroundColor Red

if ($FAIL -eq 0) {
    Write-Host ""
    Write-Host "SUCESSO! Todos os testes passaram." -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "Alguns testes falharam." -ForegroundColor Red
}
Write-Host ""
