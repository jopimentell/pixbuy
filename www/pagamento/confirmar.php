<?php
require_once '../config/database.php';
include '../includes/header.php';

$pedido_id = $_GET['pedido'] ?? 0;

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("
    SELECT p.*, pr.titulo as produto_titulo 
    FROM pedidos p 
    JOIN produtos pr ON p.produto_id = pr.id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if(!$pedido) {
    header('Location: /');
    exit;
}
?>

<section class="section" style="padding: var(--space-8) 0;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card text-center">
                    <div style="margin-bottom: var(--space-4);">
                        <i class="bi bi-check-circle-fill" style="font-size: 4rem; color: var(--color-success);"></i>
                    </div>
                    <h2>Pedido realizado com sucesso!</h2>
                    <p class="text-secondary">Aguardando confirmação do pagamento</p>
                    
                    <div class="mt-4 text-start">
                        <p><strong>Número do pedido:</strong> #<?php echo $pedido['id']; ?></p>
                        <p><strong>Produto:</strong> <?php echo htmlspecialchars($pedido['produto_titulo']); ?></p>
                        <p><strong>Valor:</strong> R$ <?php echo number_format($pedido['valor'], 2, ',', '.'); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="badge-status <?php echo $pedido['status'] == 'paid' ? 'status-paid' : 'status-pending'; ?>">
                                <?php echo $pedido['status'] == 'paid' ? 'Pago' : 'Aguardando pagamento'; ?>
                            </span>
                        </p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="https://wa.me/55<?php echo preg_replace('/[^0-9]/', '', $pedido['telefone_cliente']); ?>?text=Olá! Meu pedido #<?php echo $pedido['id']; ?> foi realizado. Segue comprovante:" 
                           class="btn btn-success" target="_blank">
                            <i class="bi bi-whatsapp"></i> Enviar comprovante via WhatsApp
                        </a>
                        <a href="/" class="btn btn-secondary">Voltar para home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>