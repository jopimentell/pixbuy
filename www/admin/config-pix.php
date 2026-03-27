<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
include '../includes/header.php';

$db = new Database();
$conn = $db->getConnection();
$success = '';
$error = '';

// Buscar configuração atual
$result = $conn->query("SELECT * FROM config_pix LIMIT 1");
$config = $result->fetch_assoc();

if(!$config) {
    // Criar configuração padrão se não existir
    $conn->query("INSERT INTO config_pix (chave_pix, tipo_chave, nome_titular, cidade) VALUES ('+5599991313341', 'telefone', 'JO B PIMENTEL', 'CODO')");
    $result = $conn->query("SELECT * FROM config_pix LIMIT 1");
    $config = $result->fetch_assoc();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chave_pix = trim($_POST['chave_pix']);
    $tipo_chave = $_POST['tipo_chave'];
    $nome_titular = trim($_POST['nome_titular']);
    $cidade = trim($_POST['cidade']);
    $valor_padrao = !empty($_POST['valor_padrao']) ? str_replace(',', '.', str_replace('.', '', $_POST['valor_padrao'])) : null;
    $info_adicional = trim($_POST['info_adicional']);
    
    $stmt = $conn->prepare("UPDATE config_pix SET chave_pix = ?, tipo_chave = ?, nome_titular = ?, cidade = ?, valor_padrao = ?, info_adicional = ? WHERE id = 1");
    $stmt->bind_param("ssssds", $chave_pix, $tipo_chave, $nome_titular, $cidade, $valor_padrao, $info_adicional);
    
    if($stmt->execute()) {
        $success = 'Configurações PIX salvas com sucesso!';
        $conn->query("INSERT INTO logs (acao, descricao, ip) VALUES ('config_pix_atualizada', 'Configurações PIX atualizadas por admin', '{$_SERVER['REMOTE_ADDR']}')");
        
        // Atualizar dados
        $result = $conn->query("SELECT * FROM config_pix LIMIT 1");
        $config = $result->fetch_assoc();
    } else {
        $error = 'Erro ao salvar configurações: ' . $conn->error;
    }
}

// Função para gerar exemplo
function gerarExemploPIX($chave, $nome, $cidade, $valor) {
    // Limpar chave PIX (remover não numéricos para telefone)
    $chave_limpa = preg_replace('/[^0-9]/', '', $chave);
    
    // Formatar valor com 2 casas decimais
    $valor_formatado = number_format($valor, 2, '.', '');
    
    // Gerar código PIX conforme padrão BR Code
    $payload = "000201";
    $payload .= "010211"; // Versão do QR Code
    $payload .= "26360014br.gov.bcb.pix"; // DOM (BR.GOV.BCB.PIX)
    $payload .= "01" . strlen($chave_limpa) . $chave_limpa; // Chave PIX
    $payload .= "52040000"; // Merchant Category Code (0000)
    $payload .= "5303986"; // Moeda BRL
    $payload .= "54" . strlen($valor_formatado) . $valor_formatado; // Valor
    $payload .= "5802BR"; // País
    $payload .= "59" . strlen($nome) . $nome; // Nome do destinatário
    $payload .= "60" . strlen($cidade) . $cidade; // Cidade
    $payload .= "62070503***"; // Campo adicional (***)
    $payload .= "6304"; // CRC16 (calculado depois)
    
    // Calcular CRC16
    $crc = crc16($payload);
    $payload .= strtoupper(dechex($crc));
    
    return $payload;
}

// Função CRC16 para PIX
function crc16($str) {
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($str); $i++) {
        $crc ^= ord($str[$i]) << 8;
        for ($j = 0; $j < 8; $j++) {
            $crc = ($crc & 0x8000) ? ($crc << 1) ^ 0x1021 : $crc << 1;
        }
    }
    return $crc & 0xFFFF;
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Configuração PIX</h1>
        <a href="index.php" class="btn btn-secondary">← Voltar ao Dashboard</a>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white" style="border-bottom: 1px solid var(--color-gray-200); padding: var(--space-4);">
                    <h3 class="mb-0">Dados da Conta PIX</h3>
                    <small class="text-secondary">Configure os dados da sua chave PIX para gerar pagamentos</small>
                </div>
                
                <div class="card-body">
                    <?php if($success): ?>
                        <div class="alert alert-success">✓ <?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger">⚠️ <?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Chave PIX *</label>
                            <input type="text" name="chave_pix" class="form-control" value="<?php echo htmlspecialchars($config['chave_pix']); ?>" required>
                            <small class="text-secondary">Ex: +5599991313341 (telefone), email@exemplo.com, 123.456.789-00 (CPF)</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Tipo da Chave</label>
                            <select name="tipo_chave" class="form-select">
                                <option value="telefone" <?php echo $config['tipo_chave'] == 'telefone' ? 'selected' : ''; ?>>Telefone</option>
                                <option value="email" <?php echo $config['tipo_chave'] == 'email' ? 'selected' : ''; ?>>E-mail</option>
                                <option value="cpf" <?php echo $config['tipo_chave'] == 'cpf' ? 'selected' : ''; ?>>CPF</option>
                                <option value="cnpj" <?php echo $config['tipo_chave'] == 'cnpj' ? 'selected' : ''; ?>>CNPJ</option>
                                <option value="aleatoria" <?php echo $config['tipo_chave'] == 'aleatoria' ? 'selected' : ''; ?>>Chave Aleatória</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Nome do Titular *</label>
                            <input type="text" name="nome_titular" class="form-control" value="<?php echo htmlspecialchars($config['nome_titular']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="cidade" class="form-control" value="<?php echo htmlspecialchars($config['cidade']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Valor Padrão (opcional)</label>
                            <input type="text" name="valor_padrao" class="form-control currency-input" placeholder="Deixe em branco para usar valor do produto" value="<?php echo $config['valor_padrao'] ? number_format($config['valor_padrao'], 2, ',', '.') : ''; ?>">
                            <small class="text-secondary">Se preenchido, este valor será usado em vez do valor do produto</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Informações Adicionais</label>
                            <textarea name="info_adicional" class="form-control" rows="3"><?php echo htmlspecialchars($config['info_adicional']); ?></textarea>
                            <small class="text-secondary">Texto que aparecerá na descrição do pagamento</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Salvar Configuração</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white" style="border-bottom: 1px solid var(--color-gray-200);">
                    <h3 class="mb-0">📱 Teste de Geração</h3>
                </div>
                <div class="card-body">
                    <p class="text-secondary">Teste a geração do código PIX com seus dados</p>
                    
                    <div class="form-group">
                        <label class="form-label">Valor para teste</label>
                        <input type="text" id="teste_valor" class="form-control currency-input" value="5,00">
                    </div>
                    
                    <button type="button" class="btn btn-secondary w-100 mb-3" onclick="gerarTestePIX()">
                        Gerar código PIX de teste
                    </button>
                    
                    <div id="teste_resultado" style="display: none;">
                        <div class="alert alert-info">
                            <strong>Código PIX gerado:</strong>
                            <div class="bg-light p-2 mt-2" style="word-break: break-all; font-size: 11px;">
                                <code id="codigo_pix_teste"></code>
                            </div>
                            <button class="btn btn-sm btn-primary mt-2" onclick="copiarTeste()">Copiar código</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Máscara para valor
document.querySelectorAll('.currency-input').forEach(input => {
    input.addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, '');
        value = (value / 100).toFixed(2);
        this.value = value.replace('.', ',');
    });
});

function gerarTestePIX() {
    const valor = document.getElementById('teste_valor').value.replace(',', '.');
    const chave = document.querySelector('input[name="chave_pix"]').value;
    const nome = document.querySelector('input[name="nome_titular"]').value;
    const cidade = document.querySelector('input[name="cidade"]').value;
    
    // Limpar chave (remover caracteres não numéricos)
    const chaveLimpa = chave.replace(/\D/g, '');
    const valorFormatado = parseFloat(valor).toFixed(2);
    
    // Gerar payload PIX
    let payload = "000201";
    payload += "010211";
    payload += "26360014br.gov.bcb.pix";
    payload += "01" + String(chaveLimpa.length).padStart(2, '0') + chaveLimpa;
    payload += "52040000";
    payload += "5303986";
    payload += "54" + String(valorFormatado.length).padStart(2, '0') + valorFormatado;
    payload += "5802BR";
    payload += "59" + String(nome.length).padStart(2, '0') + nome;
    payload += "60" + String(cidade.length).padStart(2, '0') + cidade;
    payload += "62070503***";
    payload += "6304";
    
    // Calcular CRC16
    const crc = crc16(payload);
    payload += crc.toString(16).toUpperCase().padStart(4, '0');
    
    document.getElementById('codigo_pix_teste').textContent = payload;
    document.getElementById('teste_resultado').style.display = 'block';
}

function crc16(str) {
    let crc = 0xFFFF;
    for (let i = 0; i < str.length; i++) {
        crc ^= str.charCodeAt(i) << 8;
        for (let j = 0; j < 8; j++) {
            crc = (crc & 0x8000) ? (crc << 1) ^ 0x1021 : crc << 1;
        }
    }
    return crc & 0xFFFF;
}

function copiarTeste() {
    const codigo = document.getElementById('codigo_pix_teste').textContent;
    navigator.clipboard.writeText(codigo);
    alert('Código PIX copiado!');
}
</script>

<?php include '../includes/footer.php'; ?>