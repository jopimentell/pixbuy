<?php
// pagamento/pix.php - Versão com QR Code funcionando

require_once '../config/database.php';
require_once '../includes/pix-helper.php';
include '../includes/header.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Receber dados do formulário
$produto_id = (int)$_POST['produto_id'];
$nome_cliente = trim($_POST['nome']);
$email_cliente = trim($_POST['email']);
$cpf_cliente = trim($_POST['cpf']);
$telefone_cliente = trim($_POST['telefone']);

// Buscar produto
$stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->bind_param("i", $produto_id);
$stmt->execute();
$produto = $stmt->get_result()->fetch_assoc();

if(!$produto) {
    header('Location: /');
    exit;
}

// Buscar configuração PIX
$config = $conn->query("SELECT * FROM config_pix LIMIT 1")->fetch_assoc();

if(!$config) {
    // Configuração padrão
    $config = [
        'chave_pix' => '+5599991313341',
        'nome_titular' => 'JO B PIMENTEL',
        'cidade' => 'CODO'
    ];
}

// Valor do produto
$valor = floatval($produto['valor']);

// Salvar pedido primeiro para ter o ID
$status = 'pending';
$stmt = $conn->prepare("INSERT INTO pedidos (produto_id, nome_cliente, email_cliente, cpf_cliente, telefone_cliente, valor, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssds", $produto_id, $nome_cliente, $email_cliente, $cpf_cliente, $telefone_cliente, $valor, $status);
$stmt->execute();
$pedido_id = $conn->insert_id;

// Gerar ID da transação com o número do pedido
$idTransacao = "PED" . str_pad($pedido_id, 8, '0', STR_PAD_LEFT);

// Gerar código PIX usando o helper do MCO2
$codigoPix = PixHelper::geraPix(
    $config['chave_pix'],      // Chave PIX
    $idTransacao,               // Identificador da transação
    $valor,                     // Valor
    $config['nome_titular'],    // Nome do recebedor
    $config['cidade']           // Cidade
);

// Atualizar pedido com o código PIX e TXID
$stmt = $conn->prepare("UPDATE pedidos SET pix_codigo = ?, pix_txid = ? WHERE id = ?");
$stmt->bind_param("ssi", $codigoPix, $idTransacao, $pedido_id);
$stmt->execute();

// Gerar QR Code
$qrCodeUrl = PixHelper::gerarQRCode($codigoPix, 280);

// Tentar gerar QR Code em base64 (fallback)
$qrCodeBase64 = PixHelper::gerarQRCodeBase64($codigoPix, 280);
$usarBase64 = ($qrCodeBase64 !== false);

// Registrar log
$conn->query("INSERT INTO logs (acao, descricao, ip) VALUES ('pagamento_gerado', 'Pedido #$pedido_id - R$ " . number_format($valor, 2, ',', '.') . "', '{$_SERVER['REMOTE_ADDR']}')");
?>

<section class="section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card text-center">
                    <div class="card-body p-5">
                        <!-- Ícone -->
                        <div class="mb-4">
                            <i class="bi bi-qr-code" style="font-size: 4rem; color: #195ddc;"></i>
                        </div>
                        
                        <h2 class="mb-2">Pagamento via Pix</h2>
                        <p class="text-secondary mb-4">Escaneie o QR Code ou copie o código para pagar</p>
                        
                        <!-- QR Code -->
                        <div class="qr-code-container mb-4 p-3" style="background: white; border-radius: 1rem;">
                            <?php if($usarBase64): ?>
                                <img src="<?php echo $qrCodeBase64; ?>" 
                                     alt="QR Code Pix" 
                                     style="max-width: 280px; width: 100%; margin: 0 auto; border-radius: 0.5rem;">
                            <?php else: ?>
                                <img src="<?php echo $qrCodeUrl; ?>" 
                                     alt="QR Code Pix" 
                                     style="max-width: 280px; width: 100%; margin: 0 auto; border-radius: 0.5rem;"
                                     onerror="this.onerror=null; this.src='https://quickchart.io/qr?size=280&text=<?php echo urlencode($codigoPix); ?>';">
                            <?php endif; ?>
                        </div>
                        
                        <!-- Código para copiar -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Código Pix para copiar</label>
                            <div class="input-group">
                                <input type="text" id="pix-codigo" class="form-control font-monospace" 
                                       value="<?php echo htmlspecialchars($codigoPix); ?>" readonly 
                                       style="font-size: 11px; background: #f8f9fa; word-break: break-all;">
                                <button class="btn btn-dark copy-btn" data-copy="<?php echo htmlspecialchars($codigoPix); ?>">
                                    <i class="bi bi-copy"></i> Copiar
                                </button>
                            </div>
                        </div>
                        
                        <!-- Detalhes do pedido -->
                        <div class="row text-start mb-4">
                            <div class="col-md-6">
                                <p><strong>📦 Produto:</strong> <?php echo htmlspecialchars($produto['titulo']); ?></p>
                                <p><strong>💰 Valor:</strong> <span class="fw-bold text-primary">R$ <?php echo number_format($valor, 2, ',', '.'); ?></span></p>
                                <p><strong>🔢 Pedido:</strong> #<?php echo $pedido_id; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>👤 Cliente:</strong> <?php echo htmlspecialchars($nome_cliente); ?></p>
                                <p><strong>📧 E-mail:</strong> <?php echo htmlspecialchars($email_cliente); ?></p>
                                <p><strong>📱 Telefone:</strong> <?php echo htmlspecialchars($telefone_cliente); ?></p>
                            </div>
                        </div>
                        
                        <!-- Informações do recebedor -->
                        <div class="alert alert-light text-start mb-4">
                            <small>
                                <strong>🔑 Chave PIX:</strong> <?php echo htmlspecialchars($config['chave_pix']); ?><br>
                                <strong>👨‍💼 Recebedor:</strong> <?php echo htmlspecialchars($config['nome_titular']); ?><br>
                                <strong>📍 Cidade:</strong> <?php echo htmlspecialchars($config['cidade']); ?>
                            </small>
                        </div>
                        
                        <!-- Botões -->
                        <div class="d-flex gap-3 justify-content-center">
                            <a href="/pagamento/confirmar.php?pedido=<?php echo $pedido_id; ?>" class="btn btn-success btn-lg">
                                <i class="bi bi-check-lg"></i> Já paguei
                            </a>
                            <a href="/" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-secondary">
                                <i class="bi bi-clock"></i> O pagamento será confirmado em até 5 minutos
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Função para copiar código
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const textToCopy = this.dataset.copy;
        navigator.clipboard.writeText(textToCopy).then(() => {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="bi bi-check-lg"></i> Copiado!';
            setTimeout(() => {
                this.innerHTML = originalText;
            }, 2000);
        }).catch(() => {
            // Fallback para navegadores antigos
            const textarea = document.createElement('textarea');
            textarea.value = textToCopy;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="bi bi-check-lg"></i> Copiado!';
            setTimeout(() => {
                this.innerHTML = originalText;
            }, 2000);
        });
    });
});
</script>

<style>
.qr-code-container {
    background: white;
    border-radius: 1rem;
    display: inline-block;
    margin: 0 auto;
}
.qr-code-container img {
    display: block;
    margin: 0 auto;
}
</style>

<?php include '../includes/footer.php'; ?>