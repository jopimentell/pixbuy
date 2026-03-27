<?php
// includes/header.php
// Verificar se a sessão já está ativa antes de iniciar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixBuy - Pagamentos via Pix</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a href="/" class="logo">💳 PixBuy</a>
        <div class="nav-links">
            <?php if(isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true): ?>
                <a href="/admin/">Dashboard</a>
                <a href="/admin/produtos.php">Produtos</a>
                <a href="/admin/pedidos.php">Pedidos</a>
                <a href="/admin/config-pix.php">Configurações</a>
                <a href="/admin/logs.php">Logs</a>
            <?php endif; ?>
        </div>
        <div class="nav-actions">
            <?php if(isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true): ?>
                <a href="/admin/logout.php" class="btn btn-secondary">Sair</a>
            <?php else: ?>
                <a href="/admin/login.php" class="btn btn-secondary">Admin</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main id="main">