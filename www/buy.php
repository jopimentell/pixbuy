<?php
// buy.php - Página de produto com URL amigável
require_once 'config/database.php';
include 'includes/header.php';

// Pegar o slug da URL
$slug = $_GET['slug'] ?? '';
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM produtos WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$produto = $result->fetch_assoc();

if(!$produto) {
    header('Location: /');
    exit;
}
?>

<section class="section" style="padding: var(--space-8) 0;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <h1 class="card-title" style="font-size: 1.75rem;"><?php echo htmlspecialchars($produto['titulo']); ?></h1>
                    <div class="card-text mb-4">
                        <?php echo nl2br(htmlspecialchars($produto['descricao'])); ?>
                    </div>
                    <div class="mb-4">
                        <span style="font-size: 2rem; font-weight: 700;">
                            <?php echo 'R$ ' . number_format($produto['valor'], 2, ',', '.'); ?>
                        </span>
                    </div>
                    
                    <h3 style="margin-bottom: var(--space-4);">Informações para pagamento</h3>
                    <form action="/pagamento/pix.php" method="POST">
                        <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                        <input type="hidden" name="valor" value="<?php echo $produto['valor']; ?>">
                        
                        <div class="form-group">
                            <label class="form-label">Nome completo</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">CPF</label>
                            <input type="text" name="cpf" class="form-control" placeholder="000.000.000-00" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" class="form-control" placeholder="(00) 00000-0000" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">Gerar Pagamento</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Máscara para CPF e Telefone
document.querySelector('input[name="cpf"]').addEventListener('input', function(e) {
    let value = this.value.replace(/\D/g, '');
    if(value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        this.value = value;
    }
});

document.querySelector('input[name="telefone"]').addEventListener('input', function(e) {
    let value = this.value.replace(/\D/g, '');
    if(value.length <= 11) {
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
        this.value = value;
    }
});
</script>

<?php include 'includes/footer.php'; ?>