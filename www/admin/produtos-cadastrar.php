<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
include '../includes/header.php';

$db = new Database();
$conn = $db->getConnection();
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug = trim($_POST['slug']);
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    
    $valor_raw = $_POST['valor'];
    
    // Remove espaços
    $valor_raw = trim($valor_raw);
    
    // Remove R$ se existir
    $valor_raw = str_replace('R$', '', $valor_raw);
    $valor_raw = str_replace('R$', '', $valor_raw);
    
    // Remove pontos de milhar
    $valor_raw = str_replace('.', '', $valor_raw);
    
    // Troca vírgula por ponto (separador decimal)
    $valor_raw = str_replace(',', '.', $valor_raw);
    
    // Converte para float
    $valor = floatval($valor_raw);
    
    // Debug (remover depois)
    error_log("Valor raw: " . $_POST['valor']);
    error_log("Valor processado: " . $valor);
    
    if(empty($slug) || empty($titulo)) {
        $error = 'Preencha todos os campos obrigatórios';
    } elseif($valor <= 0) {
        $error = 'Valor inválido. Use formato como 49,90 ou 100,00';
    } else {
        $stmt = $conn->prepare("INSERT INTO produtos (slug, titulo, descricao, valor) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssd", $slug, $titulo, $descricao, $valor);
        
        if($stmt->execute()) {
            $success = 'Produto cadastrado com sucesso! Valor: R$ ' . number_format($valor, 2, ',', '.');
            $conn->query("INSERT INTO logs (acao, descricao, ip) VALUES ('produto_cadastrado', 'Produto: $titulo - Valor: R$ " . number_format($valor, 2, ',', '.') . "', '{$_SERVER['REMOTE_ADDR']}')");
            
            // Limpar formulário
            $_POST = array();
        } else {
            $error = 'Erro ao cadastrar produto: ' . $conn->error;
        }
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Cadastrar Produto</h1>
        <a href="produtos.php" class="btn btn-secondary">Voltar</a>
    </div>
    
    <div class="card" style="max-width: 800px;">
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" id="formProduto">
            <div class="form-group">
                <label class="form-label">Slug (URL amigável) *</label>
                <input type="text" name="slug" class="form-control" placeholder="ex: camiseta-promocao" value="<?php echo htmlspecialchars($_POST['slug'] ?? ''); ?>" required>
                <small class="text-secondary">Usado na URL: /buy/camiseta-promocao</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Título *</label>
                <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Descrição</label>
                <textarea name="descricao" class="form-control" rows="5"><?php echo htmlspecialchars($_POST['descricao'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Valor *</label>
                <input type="text" name="valor" id="valor" class="form-control" placeholder="Ex: 49,90" value="<?php echo htmlspecialchars($_POST['valor'] ?? ''); ?>" required>
                <small class="text-secondary">Use vírgula para centavos. Exemplo: 49,90 ou 100,00</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </div>
</div>

<script>
// Formatador de moeda em tempo real
const valorInput = document.getElementById('valor');

valorInput.addEventListener('input', function(e) {
    let value = this.value;
    
    // Remove tudo que não é número ou vírgula
    value = value.replace(/[^\d,]/g, '');
    
    // Se tiver mais de uma vírgula, remove as extras
    let parts = value.split(',');
    if(parts.length > 2) {
        value = parts[0] + ',' + parts.slice(1).join('');
    }
    
    // Limita a 2 casas decimais
    if(parts.length === 2 && parts[1].length > 2) {
        value = parts[0] + ',' + parts[1].substring(0, 2);
    }
    
    this.value = value;
});

// Validação antes de enviar
document.getElementById('formProduto').addEventListener('submit', function(e) {
    let valor = document.getElementById('valor').value;
    
    // Verifica se o valor está vazio
    if(!valor) {
        alert('Por favor, informe o valor do produto');
        e.preventDefault();
        return false;
    }
    
    // Verifica se tem vírgula
    if(valor.indexOf(',') === -1) {
        alert('Use vírgula para os centavos. Exemplo: 49,90');
        e.preventDefault();
        return false;
    }
    
    // Remove R$ se existir
    valor = valor.replace('R$', '').trim();
    
    // Verifica se é um número válido
    let valorNumerico = parseFloat(valor.replace(',', '.'));
    if(isNaN(valorNumerico) || valorNumerico <= 0) {
        alert('Informe um valor válido maior que zero');
        e.preventDefault();
        return false;
    }
    
    return true;
});
</script>

<?php include '../includes/footer.php'; ?>