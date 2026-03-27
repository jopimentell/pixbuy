<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
include '../includes/header.php';

$db = new Database();
$conn = $db->getConnection();

$page = $_GET['page'] ?? 1;
$limit = 50;
$offset = ($page - 1) * $limit;

$total = $conn->query("SELECT COUNT(*) as total FROM logs")->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

$logs = $conn->query("SELECT * FROM logs ORDER BY id DESC LIMIT $offset, $limit");

if(isset($_POST['limpar'])) {
    $conn->query("TRUNCATE TABLE logs");
    $conn->query("INSERT INTO logs (acao, descricao, ip) VALUES ('logs_limpos', 'Logs do sistema foram limpos', '{$_SERVER['REMOTE_ADDR']}')");
    header('Location: logs.php');
    exit;
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Logs do Sistema</h1>
        <form method="POST" class="delete-form">
            <button type="submit" name="limpar" class="btn btn-danger">Limpar Logs</button>
        </form>
    </div>
    
    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data/Hora</th>
                        <th>Ação</th>
                        <th>Descrição</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($log = $logs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $log['id']; ?></td>
                            <td><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($log['acao']); ?></td>
                            <td><?php echo htmlspecialchars($log['descricao']); ?></td>
                            <td><?php echo $log['ip']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($total_pages > 1): ?>
            <div class="pagination-container">
                <nav>
                    <ul class="pagination">
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>