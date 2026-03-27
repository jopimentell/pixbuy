<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
include '../includes/header.php';

$db = new Database();
$conn = $db->getConnection();

// Stats
$total_pedidos = $conn->query("SELECT COUNT(*) as total FROM pedidos")->fetch_assoc()['total'];
$total_produtos = $conn->query("SELECT COUNT(*) as total FROM produtos")->fetch_assoc()['total'];
$pedidos_pendentes = $conn->query("SELECT COUNT(*) as total FROM pedidos WHERE status = 'pending'")->fetch_assoc()['total'];
$pedidos_pagos = $conn->query("SELECT COUNT(*) as total FROM pedidos WHERE status = 'paid'")->fetch_assoc()['total'];
$valor_total = $conn->query("SELECT SUM(valor) as total FROM pedidos WHERE status = 'paid'")->fetch_assoc()['total'];

$ultimos_pedidos = $conn->query("
    SELECT p.*, pr.titulo as produto_titulo 
    FROM pedidos p 
    JOIN produtos pr ON p.produto_id = pr.id 
    ORDER BY p.id DESC LIMIT 5
");
?>

<div class="container mt-4">
    <h1 class="mb-4">Dashboard</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total de Pedidos</div>
            <div class="stat-value"><?php echo $total_pedidos; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total de Produtos</div>
            <div class="stat-value"><?php echo $total_produtos; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pedidos Pendentes</div>
            <div class="stat-value"><?php echo $pedidos_pendentes; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pedidos Pagos</div>
            <div class="stat-value"><?php echo $pedidos_pagos; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Valor Total Recebido</div>
            <div class="stat-value">R$ <?php echo number_format($valor_total ?? 0, 2, ',', '.'); ?></div>
        </div>
    </div>
    
    <div class="card mt-4">
        <h3 class="mb-3">Últimos Pedidos</h3>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produto</th>
                        <th>Cliente</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($pedido = $ultimos_pedidos->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $pedido['id']; ?></td>
                            <td><?php echo htmlspecialchars($pedido['produto_titulo']); ?></td>
                            <td><?php echo htmlspecialchars($pedido['nome_cliente']); ?></td>
                            <td>R$ <?php echo number_format($pedido['valor'], 2, ',', '.'); ?></td>
                            <td>
                                <span class="badge-status <?php echo $pedido['status'] == 'paid' ? 'status-paid' : 'status-pending'; ?>">
                                    <?php echo $pedido['status'] == 'paid' ? 'Pago' : 'Pendente'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>