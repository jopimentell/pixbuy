<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
include '../includes/header.php';

$id = $_GET['id'] ?? 0;
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$produto = $stmt->get_result()->fetch_assoc();

if(!$produto) {
    header('Location: produtos.php');
    exit;
}

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
    
    // Debug
    error_log("Edit - Valor raw: " . $_POST['valor']);
    error_log("Edit - Valor processado: " . $valor);
    
    if(empty($slug) || empty($titulo)) {
        $error = 'Preencha todos os campos obrigatórios';
    } elseif($valor <= 0) {
        $error = 'Valor inválido. Use formato como 49,90 ou 100,00';
    } else {
        $stmt = $conn->prepare("UPDATE produtos SET slug = ?, titulo = ?, descricao = ?, valor = ? WHERE id = ?");
        $stmt->bind_param("sssdi", $slug, $titulo, $descricao, $valor, $id);
        
        if($stmt->execute()) {
            $success = 'Produto atualizado com sucesso! Valor: R$ ' . number_format($valor, 2, ',', '.');
            $conn->query("INSERT INTO logs (acao, descricao, ip) VALUES ('produto_editado', 'Produto: $titulo - Valor: R$ " . number_format($valor, 2, ',', '.') . "', '{$_SERVER['REMOTE_ADDR']}')");
            
            // Atualizar dados do produto
            $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $produto = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'Erro ao atualizar produto: ' . $conn->error;
        }
    }
}

// Formatar valor para exibição no formulário
$valor_formatado = number_format($produto['valor'], 2, ',', '.');
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Editar Produto</h1>
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
                <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($produto['slug']); ?>" required>
                <small class="text-secondary">Usado na URL: /buy/<?php echo $produto['slug']; ?></small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Título *</label>
                <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($produto['titulo']); ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Descrição</label>
                <textarea name="descricao" class="form-control" rows="5"><?php echo htmlspecialchars($produto['descricao']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Valor *</label>
                <input type="text" name="valor" id="valor" class="form-control" value="<?php echo $valor_formatado; ?>" required>
                <small class="text-secondary">Use vírgula para centavos. Exemplo: 49,90 ou 100,00</small>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="/buy/<?php echo $produto['slug']; ?>" class="btn btn-secondary" target="_blank">Ver página do produto</a>
            </div>
        </form>
    </div>
</div>

<script>
const valorInput = document.getElementById('valor');

// Formatador de moeda em tempo real
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
    
    if(!valor) {
        alert('Por favor, informe o valor do produto');
        e.preventDefault();
        return false;
    }
    
    if(valor.indexOf(',') === -1) {
        alert('Use vírgula para os centavos. Exemplo: 49,90');
        e.preventDefault();
        return false;
    }
    
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