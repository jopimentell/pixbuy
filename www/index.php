<?php
require_once 'config/database.php';
include 'includes/header.php';

$db = new Database();
$conn = $db->getConnection();

$query = "SELECT * FROM produtos ORDER BY id DESC";
$result = $conn->query($query);
?>

<section class="hero">
    <div class="container">
        <div class="badge badge-light" style="display: inline-block; margin-bottom: var(--space-4);">
            🚀 Pagamento rápido e seguro
        </div>
        <h1>Compre com <span style="color: var(--color-primary);">Pix</span><br>em poucos segundos</h1>
        <p>Gerencie seus pagamentos de forma simples e eficiente. Receba via Pix com taxa zero para seus clientes.</p>
        <div class="hero-buttons" style="margin-top: var(--space-6);">
            <a href="/admin/login.php" class="btn btn-primary btn-lg">Ver produtos</a>
            <a href="/admin/login.php" class="btn btn-secondary btn-lg">Área do vendedor</a>
        </div>
    </div>
</section>


<?php include 'includes/footer.php'; ?>