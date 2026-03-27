# Script de Teste Automático - PixBuy
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "     INICIANDO TESTES AUTOMATIZADOS" -ForegroundColor Cyan
Write-Host "     Projeto: PixBuy" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$erros = 0

# TESTE 1: Verificar se os containers estão rodando
Write-Host "[TESTE 1] Verificando containers Docker..." -ForegroundColor Yellow
$containers = docker ps --format "table {{.Names}}"

if ($containers -match "pixbuy_web" -and $containers -match "pixbuy_db") {
    Write-Host "  ✅ Web e DB estão rodando" -ForegroundColor Green
} else {
    Write-Host "  ❌ ERRO: Containers não estão rodando" -ForegroundColor Red
    $erros++
}

# TESTE 2: Verificar se o site está acessível
Write-Host ""
Write-Host "[TESTE 2] Verificando site (http://localhost:8080)..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8080" -UseBasicParsing -TimeoutSec 5
    if ($response.StatusCode -eq 200) {
        Write-Host "  ✅ Site está acessível (Status: $($response.StatusCode))" -ForegroundColor Green
    } else {
        Write-Host "  ❌ ERRO: Site retornou status $($response.StatusCode)" -ForegroundColor Red
        $erros++
    }
} catch {
    Write-Host "  ❌ ERRO: Site não respondeu" -ForegroundColor Red
    $erros++
}

# TESTE 3: Verificar conexão com MySQL via PHP
Write-Host ""
Write-Host "[TESTE 3] Verificando conexão com MySQL..." -ForegroundColor Yellow

# Criar arquivo de teste temporário
$testFile = "./www/test-db.php"
$testContent = @"
<?php
header('Content-Type: application/json');
try {
    `$host = 'db';
    `$user = 'pixbuy_user';
    `$pass = 'pixbuy123';
    `$db = 'pixbuy_db';
    
    `$conn = new mysqli(`$host, `$user, `$pass, `$db);
    
    if (`$conn->connect_error) {
        echo json_encode(['status' => 'error', 'message' => `$conn->connect_error]);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Conectado ao MySQL']);
        `$conn->close();
    }
} catch (Exception `$e) {
    echo json_encode(['status' => 'error', 'message' => `$e->getMessage()]);
}
?>
"@

Set-Content -Path $testFile -Value $testContent

try {
    $response = Invoke-WebRequest -Uri "http://localhost:8080/test-db.php" -UseBasicParsing -TimeoutSec 5
    $data = $response.Content | ConvertFrom-Json
    
    if ($data.status -eq "success") {
        Write-Host "  ✅ MySQL conectado: $($data.message)" -ForegroundColor Green
    } else {
        Write-Host "  ❌ ERRO MySQL: $($data.message)" -ForegroundColor Red
        $erros++
    }
} catch {
    Write-Host "  ❌ ERRO: Teste MySQL falhou" -ForegroundColor Red
    $erros++
}

# TESTE 4: Verificar se o phpMyAdmin está acessível
Write-Host ""
Write-Host "[TESTE 4] Verificando phpMyAdmin (http://localhost:8081)..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8081" -UseBasicParsing -TimeoutSec 5
    if ($response.StatusCode -eq 200) {
        Write-Host "  ✅ phpMyAdmin está acessível" -ForegroundColor Green
    } else {
        Write-Host "  ❌ ERRO: phpMyAdmin retornou status $($response.StatusCode)" -ForegroundColor Red
        $erros++
    }
} catch {
    Write-Host "  ❌ ERRO: phpMyAdmin não respondeu" -ForegroundColor Red
    $erros++
}

# TESTE 5: Verificar se o volume de dados existe
Write-Host ""
Write-Host "[TESTE 5] Verificando volume de persistência..." -ForegroundColor Yellow
$volumes = docker volume ls --format "{{.Name}}"
if ($volumes -match "pixbuy_db_data") {
    Write-Host "  ✅ Volume db_data encontrado" -ForegroundColor Green
} else {
    Write-Host "  ❌ ERRO: Volume db_data não encontrado" -ForegroundColor Red
    $erros++
}

# TESTE 6: Verificar se os arquivos PHP existem
Write-Host ""
Write-Host "[TESTE 6] Verificando arquivos do projeto..." -ForegroundColor Yellow
$arquivos = @(
    "www/index.php",
    "www/config/database.php",
    "www/admin/index.php",
    "www/admin/login.php"
)

foreach ($arquivo in $arquivos) {
    if (Test-Path $arquivo) {
        Write-Host "  ✅ $arquivo encontrado" -ForegroundColor Green
    } else {
        Write-Host "  ❌ ERRO: $arquivo não encontrado" -ForegroundColor Red
        $erros++
    }
}

# RESUMO
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "        RESULTADO DOS TESTES" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

if ($erros -eq 0) {
    Write-Host "✅ TODOS OS TESTES PASSARAM!" -ForegroundColor Green
    Write-Host "✅ Sistema está funcionando corretamente" -ForegroundColor Green
    exit 0
} else {
    Write-Host "❌ $erros TESTE(S) FALHARAM!" -ForegroundColor Red
    Write-Host "❌ Verifique os erros acima e corrija" -ForegroundColor Red
    exit 1
}