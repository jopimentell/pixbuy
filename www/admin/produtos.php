<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
include '../includes/header.php';

$db = new Database();
$conn = $db->getConnection();

$produtos = $conn->query("SELECT * FROM produtos ORDER BY id DESC");
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gerenciar Produtos</h1>
        <div>
            <a href="produtos-cadastrar.php" class="btn btn-primary">+ Novo Produto</a>
            <a href="pedidos.php" class="btn btn-secondary">📋 Ver Pedidos</a>
        </div>
    </div>
    
    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Slug</th>
                        <th>Título</th>
                        <th>Valor</th>
                        <th>Link</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($produto = $produtos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $produto['id']; ?></td>
                            <td><?php echo htmlspecialchars($produto['slug']); ?></td>
                            <td><?php echo htmlspecialchars($produto['titulo']); ?></td>
                            <td>R$ <?php echo number_format($produto['valor'], 2, ',', '.'); ?></td>
                            <td>
                                <a href="/buy/<?php echo $produto['slug']; ?>" class="btn btn-sm btn-info" target="_blank">
                                    🔗 Ver Página
                                </a>
                            </td>
                            <td>
                                <a href="produtos-editar.php?id=<?php echo $produto['id']; ?>" class="btn btn-sm btn-secondary">Editar</a>
                                <form action="produtos-excluir.php" method="POST" class="delete-form d-inline">
                                    <input type="hidden" name="id" value="<?php echo $produto['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.btn-info {
    background: var(--color-gray-100);
    color: var(--color-gray-700);
    border: 1px solid var(--color-gray-200);
}
.btn-info:hover {
    background: var(--color-gray-200);
}
</style>

<?php include '../includes/footer.php'; ?>