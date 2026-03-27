<?php
// ============================================
// PEDIDOS.PHP - VERSÃO SIMPLIFICADA
// ============================================

// Incluir autenticação 
require_once '../includes/auth.php';
require_once '../config/database.php';

// Inicializar conexão
$db = new Database();
$conn = $db->getConnection();

// Processar atualização de status
if(isset($_POST['atualizar_status'])) {
    $pedido_id = (int)$_POST['pedido_id'];
    $novo_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $novo_status, $pedido_id);
    
    if($stmt->execute()) {
        // Registrar log
        $conn->query("INSERT INTO logs (acao, descricao, ip) VALUES ('status_atualizado', 'Pedido #$pedido_id alterado para $novo_status', '{$_SERVER['REMOTE_ADDR']}')");
        
        // Redirecionar para evitar reenvio
        $params = [];
        if(!empty($_GET['status'])) $params['status'] = $_GET['status'];
        if(!empty($_GET['busca'])) $params['busca'] = $_GET['busca'];
        $params['msg'] = 'success';
        
        header("Location: pedidos.php?" . http_build_query($params));
        exit;
    }
}

// Buscar mensagem de retorno
$msg_success = isset($_GET['msg']) && $_GET['msg'] == 'success';

// Filtros
$filtro_status = $_GET['status'] ?? 'todos';
$busca = trim($_GET['busca'] ?? '');

// Montar query
$sql = "SELECT p.*, pr.titulo as produto_titulo 
        FROM pedidos p 
        JOIN produtos pr ON p.produto_id = pr.id 
        WHERE 1=1";

if($filtro_status != 'todos') {
    $sql .= " AND p.status = '" . $conn->real_escape_string($filtro_status) . "'";
}

if(!empty($busca)) {
    $busca = $conn->real_escape_string($busca);
    $sql .= " AND (p.id LIKE '%$busca%' OR p.nome_cliente LIKE '%$busca%' OR p.email_cliente LIKE '%$busca%')";
}

$sql .= " ORDER BY p.id DESC";
$pedidos = $conn->query($sql);

// Incluir header
include '../includes/header.php';
?>

<div class="container mt-4">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gerenciar Pedidos</h1>
        <a href="index.php" class="btn btn-secondary">← Dashboard</a>
    </div>
    
    <!-- Mensagem de sucesso -->
    <?php if($msg_success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> Status atualizado com sucesso!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="todos" <?= $filtro_status == 'todos' ? 'selected' : '' ?>>Todos</option>
                        <option value="pending" <?= $filtro_status == 'pending' ? 'selected' : '' ?>>⏳ Pendentes</option>
                        <option value="paid" <?= $filtro_status == 'paid' ? 'selected' : '' ?>>✓ Pagos</option>
                        <option value="expired" <?= $filtro_status == 'expired' ? 'selected' : '' ?>>⏰ Expirados</option>
                        <option value="cancelled" <?= $filtro_status == 'cancelled' ? 'selected' : '' ?>>✗ Cancelados</option>
                    </select>
                </div>
                <div class="col-md-7">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="busca" class="form-control" placeholder="Nome, email ou ID" value="<?= htmlspecialchars($busca) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">🔍 Buscar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Lista de Pedidos -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px">ID</th>
                        <th>Produto</th>
                        <th>Cliente</th>
                        <th style="width: 100px">Valor</th>
                        <th style="width: 140px">Status</th>
                        <th style="width: 140px">Data</th>
                        <th style="width: 60px"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($pedidos && $pedidos->num_rows > 0): ?>
                        <?php while($p = $pedidos->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold">#<?= $p['id'] ?></td>
                                <td><?= htmlspecialchars($p['produto_titulo']) ?></td>
                                <td>
                                    <?= htmlspecialchars($p['nome_cliente']) ?><br>
                                    <small class="text-secondary"><?= htmlspecialchars($p['email_cliente']) ?></small>
                                </td>
                                <td class="fw-bold">R$ <?= number_format($p['valor'], 2, ',', '.') ?></td>
                                <td>
                                    <form method="POST" class="status-form" style="display: inline;">
                                        <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="atualizar_status" value="1">
                                        <select name="status" class="form-select form-select-sm status-select" style="width: 110px; cursor: pointer;">
                                            <option value="pending" <?= $p['status'] == 'pending' ? 'selected' : '' ?> class="status-pending">⏳ Pendente</option>
                                            <option value="paid" <?= $p['status'] == 'paid' ? 'selected' : '' ?> class="status-paid">✓ Pago</option>
                                            <option value="expired" <?= $p['status'] == 'expired' ? 'selected' : '' ?> class="status-expired">⏰ Expirado</option>
                                            <option value="cancelled" <?= $p['status'] == 'cancelled' ? 'selected' : '' ?> class="status-cancelled">✗ Cancelado</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                                <td>
                                    <a href="/pagamento/confirmar.php?pedido=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="Ver detalhes">
                                        👁️
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-secondary">📭 Nenhum pedido encontrado</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.status-select {
    font-size: 0.75rem;
    font-weight: 500;
    transition: all 0.2s;
}
.status-select:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.status-pending { background-color: #fef3c7; color: #d97706; }
.status-paid { background-color: #d1fae5; color: #059669; }
.status-expired { background-color: #fee2e2; color: #dc2626; }
.status-cancelled { background-color: #f3f4f6; color: #6b7280; }
.alert-success { background-color: #d1fae5; color: #065f46; border: none; }
</style>

<script>
// Confirmação antes de alterar status
document.querySelectorAll('.status-select').forEach(select => {
    let valorOriginal = select.value;
    
    select.addEventListener('change', function(e) {
        const novoStatus = this.value;
        const textoStatus = this.options[this.selectedIndex].textContent.trim();
        
        if(confirm(`Alterar status para "${textoStatus}"?`)) {
            this.closest('form').submit();
        } else {
            this.value = valorOriginal;
        }
    });
    
    select.addEventListener('focus', function() {
        valorOriginal = this.value;
    });
});

// Auto-fechar alertas
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(alert => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 3000);
</script>

<?php include '../includes/footer.php'; ?>