#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Fast Meal - Ticket Management API Test Suite
    
.DESCRIPTION
    PowerShell script to test all ticket management endpoints
    Includes tests for intelligent wait time estimation
#>

function Write-Success {
    param([string]$Message)
    Write-Host $Message -ForegroundColor Green
}

function Write-ErrorCustom {
    param([string]$Message)
    Write-Host $Message -ForegroundColor Red
}

function Write-InfoCustom {
    param([string]$Message)
    Write-Host $Message -ForegroundColor Cyan
}

function Write-WarningCustom {
    param([string]$Message)
    Write-Host $Message -ForegroundColor Yellow
}

function Get-ErrorResponse {
    param($Exception)
    try {
        $errorStream = $Exception.Response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($errorStream)
        $responseText = $reader.ReadToEnd()
        $reader.Dispose()
        return ($responseText | ConvertFrom-Json)
    }
    catch {
        return $null
    }
}

$baseUrl = "http://localhost:8000/api"
$testsPassed = 0
$testsFailed = 0
$userId = 1

Write-InfoCustom "Fast Meal - Ticket Management API Test Suite"
Write-InfoCustom "Testing Intelligent Queue Features"
Write-InfoCustom ""

# TEST 1: Request a New Ticket or Get Existing
Write-InfoCustom "TEST 1: Request a New Ticket - POST /api/tickets/request"

try {
    $response = Invoke-WebRequest -Uri "$baseUrl/tickets/request" `
        -Method POST `
        -Headers @{"Content-Type" = "application/json"} `
        -Body (ConvertTo-Json @{"user_id" = $userId}) `
        -UseBasicParsing `
        -ErrorAction Stop

    $data = $response.Content | ConvertFrom-Json
    
    if ($response.StatusCode -eq 201 -and $data.success) {
        Write-Success "PASS: Ticket created successfully"
        Write-Success "  Ticket Number: $($data.data.ticket_number)"
        $ticketId = $data.data.id
        $testsPassed++
    }
    else {
        Write-ErrorCustom "FAIL: Unexpected response"
        $testsFailed++
    }
}
catch {
    if ($_.Exception.Response.StatusCode.Value__ -eq 409) {
        Write-WarningCustom "INFO: User has existing ticket (409), retrieving it..."
        try {
            $activeResponse = Invoke-WebRequest -Uri "$baseUrl/tickets/active?user_id=$userId" `
                -Method GET `
                -UseBasicParsing `
                -ErrorAction Stop
            $activeData = $activeResponse.Content | ConvertFrom-Json
            if ($activeData.data -and $activeData.data.id) {
                $ticketId = $activeData.data.id
                Write-Success "PASS: Using existing ticket (ID: $ticketId)"
                $testsPassed++
            }
            else {
                Write-WarningCustom "INFO: No active ticket found, tests will skip"
                $testsPassed++
            }
        }
        catch {
            Write-ErrorCustom "FAIL: $($_.Exception.Message)"
            $testsFailed++
        }
    }
    else {
        Write-ErrorCustom "FAIL: $($_.Exception.Message)"
        $testsFailed++
    }
}

Write-InfoCustom ""

# TEST 2: Get User's Active Ticket
Write-InfoCustom "TEST 2: Get User's Active Ticket - GET /api/tickets/active"

try {
    $response = Invoke-WebRequest -Uri "$baseUrl/tickets/active?user_id=$userId" `
        -Method GET `
        -UseBasicParsing `
        -ErrorAction Stop

    $data = $response.Content | ConvertFrom-Json
    
    if ($response.StatusCode -eq 200 -and $data.success) {
        Write-Success "PASS: Active ticket retrieved"
        $testsPassed++
    }
    else {
        Write-ErrorCustom "FAIL: Unexpected response"
        $testsFailed++
    }
}
catch {
    Write-ErrorCustom "FAIL: $($_.Exception.Message)"
    $testsFailed++
}

Write-InfoCustom ""

# TEST 3: Test Business Rule RN03 (One Active Ticket Per User)
Write-InfoCustom "TEST 3: Business Rule RN03 - One Active Ticket Per User"

try {
    $response = Invoke-WebRequest -Uri "$baseUrl/tickets/request" `
        -Method POST `
        -Headers @{"Content-Type" = "application/json"} `
        -Body (ConvertTo-Json @{"user_id" = $userId}) `
        -UseBasicParsing `
        -ErrorAction Stop

    Write-ErrorCustom "FAIL: Should have returned error 409"
    $testsFailed++
}
catch {
    if ($_.Exception.Response.StatusCode.Value__ -eq 409) {
        Write-Success "PASS: Correctly rejected duplicate ticket"
        $testsPassed++
    }
    else {
        Write-ErrorCustom "FAIL: Wrong status code: $($_.Exception.Response.StatusCode.Value__)"
        $testsFailed++
    }
}

Write-InfoCustom ""

# TEST 4: Get Queue Status
Write-InfoCustom "TEST 4: Get Queue Status - GET /api/tickets/queue"

try {
    $response = Invoke-WebRequest -Uri "$baseUrl/tickets/queue" `
        -Method GET `
        -UseBasicParsing `
        -ErrorAction Stop

    $data = $response.Content | ConvertFrom-Json
    
    if ($response.StatusCode -eq 200 -and $data.success) {
        Write-Success "PASS: Queue status retrieved"
        $testsPassed++
    }
    else {
        Write-ErrorCustom "FAIL: Unexpected response"
        $testsFailed++
    }
}
catch {
    Write-ErrorCustom "FAIL: $($_.Exception.Message)"
    $testsFailed++
}

Write-InfoCustom ""

# TEST 5: Get Wait Time Estimate
Write-InfoCustom "TEST 5: Get Wait Time Estimate - GET /api/tickets/estimate/{id}"

if ($ticketId) {
    try {
        $response = Invoke-WebRequest -Uri "$baseUrl/tickets/estimate/$ticketId" `
            -Method GET `
            -UseBasicParsing `
            -ErrorAction Stop

        $data = $response.Content | ConvertFrom-Json
        
        if ($response.StatusCode -eq 200 -and $data.success) {
            Write-Success "PASS: Wait time estimate calculated"
            Write-Success "  Tickets ahead: $($data.data.tickets_ahead)"
            Write-Success "  Estimated wait: $($data.data.estimated_wait_formatted)"
            $testsPassed++
        }
        else {
            Write-ErrorCustom "FAIL: Unexpected response"
            $testsFailed++
        }
    }
    catch {
        Write-ErrorCustom "FAIL: $($_.Exception.Message)"
        $testsFailed++
    }
}
else {
    Write-ErrorCustom "FAIL: No ticket ID available"
    $testsFailed++
}

Write-InfoCustom ""

# TEST 6: Get User's Queue Position
Write-InfoCustom "TEST 6: Get User's Queue Position - GET /api/tickets/my-position"

try {
    $response = Invoke-WebRequest -Uri "$baseUrl/tickets/my-position?user_id=$userId" `
        -Method GET `
        -UseBasicParsing `
        -ErrorAction Stop

    $data = $response.Content | ConvertFrom-Json
    
    if ($response.StatusCode -eq 200 -and $data.success) {
        Write-Success "PASS: User queue position retrieved"
        $testsPassed++
    }
    else {
        Write-ErrorCustom "FAIL: Unexpected response"
        $testsFailed++
    }
}
catch {
    Write-ErrorCustom "FAIL: $($_.Exception.Message)"
    $testsFailed++
}

Write-InfoCustom ""

# TEST 7: Get Queue Analytics
Write-InfoCustom "TEST 7: Get Queue Analytics - GET /api/tickets/analytics"

try {
    $response = Invoke-WebRequest -Uri "$baseUrl/tickets/analytics" `
        -Method GET `
        -UseBasicParsing `
        -ErrorAction Stop

    $data = $response.Content | ConvertFrom-Json
    
    if ($response.StatusCode -eq 200 -and $data.success) {
        Write-Success "PASS: Queue analytics retrieved"
        $testsPassed++
    }
    else {
        Write-ErrorCustom "FAIL: Unexpected response"
        $testsFailed++
    }
}
catch {
    Write-ErrorCustom "FAIL: $($_.Exception.Message)"
    $testsFailed++
}

Write-InfoCustom ""

# TEST 8: Get User Tickets History
Write-InfoCustom "TEST 8: Get User Tickets History - GET /api/tickets/my-tickets"

try {
    $response = Invoke-WebRequest -Uri "$baseUrl/tickets/my-tickets?user_id=$userId&limit=5" `
        -Method GET `
        -UseBasicParsing `
        -ErrorAction Stop

    $data = $response.Content | ConvertFrom-Json
    
    if ($response.StatusCode -eq 200 -and $data.success) {
        Write-Success "PASS: User tickets history retrieved"
        $testsPassed++
    }
    else {
        Write-ErrorCustom "FAIL: Unexpected response"
        $testsFailed++
    }
}
catch {
    Write-ErrorCustom "FAIL: $($_.Exception.Message)"
    $testsFailed++
}

Write-InfoCustom ""

# TEST 9: Cancel a Ticket
Write-InfoCustom "TEST 9: Cancel a Ticket - POST /api/tickets/{id}/cancel"

if ($ticketId) {
    try {
        $response = Invoke-WebRequest -Uri "$baseUrl/tickets/$ticketId/cancel" `
            -Method POST `
            -Headers @{"Content-Type" = "application/json"} `
            -Body (ConvertTo-Json @{"user_id" = $userId}) `
            -UseBasicParsing `
            -ErrorAction Stop

        $data = $response.Content | ConvertFrom-Json
        
        if ($response.StatusCode -eq 200 -and $data.success) {
            Write-Success "PASS: Ticket cancelled successfully"
            Write-Success "  Ticket: $($data.data.ticket_number)"
            Write-Success "  New status: $($data.data.status)"
            $testsPassed++
        }
        else {
            Write-ErrorCustom "FAIL: Unexpected response"
            $testsFailed++
        }
    }
    catch {
        Write-ErrorCustom "FAIL: $($_.Exception.Message)"
        $testsFailed++
    }
}
else {
    Write-ErrorCustom "FAIL: No ticket ID available"
    $testsFailed++
}

Write-InfoCustom ""

# TEST 10: Verify Business Rule RN05 (Cannot cancel already cancelled)
Write-InfoCustom "TEST 10: Cannot Cancel Already Cancelled - POST /api/tickets/{id}/cancel"

if ($ticketId) {
    try {
        $response = Invoke-WebRequest -Uri "$baseUrl/tickets/$ticketId/cancel" `
            -Method POST `
            -Headers @{"Content-Type" = "application/json"} `
            -Body (ConvertTo-Json @{"user_id" = $userId}) `
            -UseBasicParsing `
            -ErrorAction Stop

        Write-ErrorCustom "FAIL: Should have returned error 400"
        $testsFailed++
    }
    catch {
        if ($_.Exception.Response.StatusCode.Value__ -eq 400) {
            Write-Success "PASS: Correctly rejected cancellation of cancelled ticket"
            $testsPassed++
        }
        else {
            Write-ErrorCustom "FAIL: Wrong status code: $($_.Exception.Response.StatusCode.Value__)"
            $testsFailed++
        }
    }
}
else {
    Write-ErrorCustom "FAIL: No ticket ID available"
    $testsFailed++
}

Write-InfoCustom ""
Write-InfoCustom "=========================================="
Write-InfoCustom "TEST RESULTS"
Write-InfoCustom "=========================================="
Write-InfoCustom ""

Write-Success "Tests Passed: $testsPassed"
Write-ErrorCustom "Tests Failed: $testsFailed"

if ($testsFailed -eq 0) {
    Write-Success ""
    Write-Success "==========================================="
    Write-Success "ALL TESTS PASSED SUCCESSFULLY!"
    Write-Success "Intelligent Ticket Management System Working"
    Write-Success "==========================================="
    exit 0
}
else {
    Write-ErrorCustom ""
    Write-ErrorCustom "==========================================="
    Write-ErrorCustom "SOME TESTS FAILED"
    Write-ErrorCustom "Please check the errors above."
    Write-ErrorCustom "==========================================="
    exit 1
}
