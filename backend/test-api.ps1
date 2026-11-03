param(
    [string]$BaseUrl = "http://127.0.0.1:8080",
    [string]$Username = "admin",
    [string]$Password = "123456"
)

# Full API test: JWT login, one-time ticket issuance, ticket-protected writes,
# one-time (anti-replay) semantics, content moderation.
$script:Passed = 0
$script:Failed = 0
$RequestFile = Join-Path $env:TEMP "chaoyoung_api_request.tmp"
$ResponseFile = Join-Path $env:TEMP "chaoyoung_api_response.tmp"

function Invoke-Api {
    param(
        [string]$Method,
        [string]$Path,
        [hashtable]$Headers = @{},
        [string]$Body = $null,
        [string]$Token = $null
    )

    if ($null -ne $Body) {
        [IO.File]::WriteAllBytes($RequestFile, [Text.Encoding]::UTF8.GetBytes($Body))
    }

    $curlArgs = @('-s', '-S', '-o', $ResponseFile, '-w', '%{http_code}', '-X', $Method, "$BaseUrl$Path")
    foreach ($key in $Headers.Keys) {
        $curlArgs += @('-H', ("{0}: {1}" -f $key, $Headers[$key]))
    }
    if ($Token) {
        $curlArgs += @('-H', ("Authorization: Bearer {0}" -f $Token))
    }
    if ($null -ne $Body) {
        $curlArgs += @('-H', 'Content-Type: application/json; charset=utf-8')
        $curlArgs += @('--data-binary', ("@" + $RequestFile))
    }

    $statusText = (& curl.exe @curlArgs | Out-String).Trim()
    $status = 0
    if ($statusText -match '(\d{3})') { $status = [int]$Matches[1] }

    $content = $null
    if (Test-Path $ResponseFile) {
        $content = [IO.File]::ReadAllText($ResponseFile, [Text.Encoding]::UTF8)
    }
    $json = $null
    if ($content) { try { $json = $content | ConvertFrom-Json } catch { } }

    return [pscustomobject]@{
        Status = $status
        Body = $content
        Json = $json
    }
}

function Get-Ticket {
    param([string]$Token)
    $result = Invoke-Api -Method "GET" -Path "/api/auth/ticket" -Token $Token
    if ($result.Json -and $result.Json.data) { return $result.Json.data.ticket }
    return $null
}

function Record-Test {
    param(
        [string]$Name,
        [bool]$Success,
        [string]$Expected,
        [string]$Actual
    )
    if ($Success) {
        $script:Passed++
        Write-Host ("[PASS] {0}" -f $Name) -ForegroundColor Green
    } else {
        $script:Failed++
        Write-Host ("[FAIL] {0}" -f $Name) -ForegroundColor Red
    }
    Write-Host ("       expected: {0}" -f $Expected)
    Write-Host ("       actual  : {0}" -f $Actual)
}

function New-ProductBody {
    param([string]$Title, [string]$Description)
    return '{"title":"' + $Title + '","categoryId":1,"price":88,"stock":10,"coverImg":"products/test.jpg","description":"' + $Description + '"}'
}

Write-Host "=================================================="
Write-Host "Chaoyoung Java Backend API Test (Ticket Mode)"
Write-Host ("Target: {0}" -f $BaseUrl)
Write-Host "=================================================="

# 1. Login with correct password: HTTP 200 + code 200 + token
$loginBody = '{"username":"' + $Username + '","password":"' + $Password + '"}'
$login = Invoke-Api -Method "POST" -Path "/api/auth/login" -Body $loginBody
$token = $null
if ($login.Json -and $login.Json.data) { $token = $login.Json.data.token }
$loginCode = if ($login.Json) { $login.Json.code } else { $null }
Record-Test "1. Login with correct password" ($login.Status -eq 200 -and $loginCode -eq 200 -and $token) `
    "HTTP 200, code 200, token exists" ("HTTP {0}, code {1}, token={2}" -f $login.Status, $loginCode, $(if ($token) { "yes" } else { "no" }))

if (-not $token) {
    Write-Host ""
    Write-Host "Login failed, remaining tests aborted. Is the backend running on $BaseUrl ?" -ForegroundColor Red
    If (Test-Path $RequestFile) { Remove-Item $RequestFile -Force }
    If (Test-Path $ResponseFile) { Remove-Item $ResponseFile -Force }
    exit 1
}

# 2. GET /api/auth/me with token: HTTP 200 + code 200
$me = Invoke-Api -Method "GET" -Path "/api/auth/me" -Token $token
$meCode = if ($me.Json) { $me.Json.code } else { $null }
Record-Test "2. GET /api/auth/me with token" ($me.Status -eq 200 -and $meCode -eq 200) `
    "HTTP 200, code 200" ("HTTP {0}, code {1}" -f $me.Status, $meCode)

# 3. Login with wrong password: HTTP 422 + code 422
$wrongBody = '{"username":"' + $Username + '","password":"wrong-password"}'
$wrongLogin = Invoke-Api -Method "POST" -Path "/api/auth/login" -Body $wrongBody
$wrongCode = if ($wrongLogin.Json) { $wrongLogin.Json.code } else { $null }
Record-Test "3. Login with wrong password" ($wrongLogin.Status -eq 422 -and $wrongCode -eq 422) `
    "HTTP 422, code 422" ("HTTP {0}, code {1}" -f $wrongLogin.Status, $wrongCode)

# 4. Protected API without token: HTTP 401 + code 401
$noToken = Invoke-Api -Method "GET" -Path "/api/products/my/list"
$noTokenCode = if ($noToken.Json) { $noToken.Json.code } else { $null }
Record-Test "4. Protected API without token" ($noToken.Status -eq 401 -and $noTokenCode -eq 401) `
    "HTTP 401, code 401" ("HTTP {0}, code {1}" -f $noToken.Status, $noTokenCode)

# 5. Ticket endpoint without token: HTTP 401 (JWT required)
$ticketNoToken = Invoke-Api -Method "GET" -Path "/api/auth/ticket"
$ticketNoTokenCode = if ($ticketNoToken.Json) { $ticketNoToken.Json.code } else { $null }
Record-Test "5. GET /api/auth/ticket without token" ($ticketNoToken.Status -eq 401 -and $ticketNoTokenCode -eq 401) `
    "HTTP 401, code 401" ("HTTP {0}, code {1}" -f $ticketNoToken.Status, $ticketNoTokenCode)

# 6. Issue one-time ticket with token: HTTP 200 + code 200 + ticket
$ticketResult = Invoke-Api -Method "GET" -Path "/api/auth/ticket" -Token $token
$ticketCode = if ($ticketResult.Json) { $ticketResult.Json.code } else { $null }
$ticket = $null
if ($ticketResult.Json -and $ticketResult.Json.data) { $ticket = $ticketResult.Json.data.ticket }
Record-Test "6. Issue one-time ticket" ($ticketResult.Status -eq 200 -and $ticketCode -eq 200 -and $ticket) `
    "HTTP 200, code 200, ticket exists" ("HTTP {0}, code {1}, ticket={2}" -f $ticketResult.Status, $ticketCode, $(if ($ticket) { "yes" } else { "no" }))

if (-not $ticket) {
    Write-Host ""
    Write-Host "Ticket issuance failed, write tests aborted." -ForegroundColor Red
    If (Test-Path $RequestFile) { Remove-Item $RequestFile -Force }
    If (Test-Path $ResponseFile) { Remove-Item $ResponseFile -Force }
    exit 1
}

# 7. Publish product with the ticket: HTTP 200 + code 200 + product id
$stamp = Get-Date -Format "yyyyMMddHHmmss"
$publishBody = New-ProductBody -Title "API-Test-Product-$stamp" -Description "ticket publish test"
$publishHeaders = @{ "X-Ticket" = $ticket }
$created = Invoke-Api -Method "POST" -Path "/api/products" -Headers $publishHeaders -Body $publishBody -Token $token
$createdCode = if ($created.Json) { $created.Json.code } else { $null }
$productId = $null
if ($created.Json -and $created.Json.data) { $productId = $created.Json.data.id }
Record-Test "7. Publish product with valid ticket" ($created.Status -eq 200 -and $createdCode -eq 200 -and $productId) `
    "HTTP 200, code 200, product id exists" ("HTTP {0}, code {1}, id={2}" -f $created.Status, $createdCode, $productId)

# 8. Replay the same ticket: HTTP 401 (one-time only)
$replayBody = New-ProductBody -Title "Replay-Test-$stamp" -Description "replay test"
$replay = Invoke-Api -Method "POST" -Path "/api/products" -Headers @{ "X-Ticket" = $ticket } -Body $replayBody -Token $token
$replayCode = if ($replay.Json) { $replay.Json.code } else { $null }
Record-Test "8. Replay a used ticket" ($replay.Status -eq 401 -and $replayCode -eq 401) `
    "HTTP 401, code 401" ("HTTP {0}, code {1}" -f $replay.Status, $replayCode)

# 9. Write request without X-Ticket: HTTP 401
$noTicketBody = New-ProductBody -Title "No-Ticket-Test-$stamp" -Description "no ticket test"
$noTicket = Invoke-Api -Method "POST" -Path "/api/products" -Body $noTicketBody -Token $token
$noTicketCode = if ($noTicket.Json) { $noTicket.Json.code } else { $null }
Record-Test "9. Write request without X-Ticket" ($noTicket.Status -eq 401 -and $noTicketCode -eq 401) `
    "HTTP 401, code 401" ("HTTP {0}, code {1}" -f $noTicket.Status, $noTicketCode)

# 10. Sensitive word moderation with a fresh ticket: HTTP 422 + code 422
$sensitiveWord = ([char]0x8D4C).ToString() + ([char]0x535A).ToString()
$moderationTicket = Get-Ticket -Token $token
$moderationBody = New-ProductBody -Title "Moderation-Test-$stamp" -Description ("contains " + $sensitiveWord + " content")
$moderation = Invoke-Api -Method "POST" -Path "/api/products" -Headers @{ "X-Ticket" = $moderationTicket } -Body $moderationBody -Token $token
$moderationCode = if ($moderation.Json) { $moderation.Json.code } else { $null }
Record-Test "10. Sensitive word moderation" ($moderation.Status -eq 422 -and $moderationCode -eq 422) `
    "HTTP 422, code 422" ("HTTP {0}, code {1}" -f $moderation.Status, $moderationCode)

# Cleanup: delete the created product with a fresh ticket
if ($productId) {
    $deleteTicket = Get-Ticket -Token $token
    $deleted = Invoke-Api -Method "DELETE" -Path "/api/products/$productId" -Headers @{ "X-Ticket" = $deleteTicket } -Token $token
    Write-Host ""
    Write-Host ("[CLEANUP] Delete test product {0}: HTTP {1}" -f $productId, $deleted.Status) -ForegroundColor DarkGray
}

If (Test-Path $RequestFile) { Remove-Item $RequestFile -Force }
If (Test-Path $ResponseFile) { Remove-Item $ResponseFile -Force }

Write-Host ""
Write-Host "=================================================="
Write-Host ("Result: {0} passed, {1} failed" -f $script:Passed, $script:Failed)
Write-Host "=================================================="

if ($script:Failed -gt 0) { exit 1 } else { exit 0 }
