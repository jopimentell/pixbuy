<?php
session_start();
require_once '../config/database.php';

// Verificar se já está logado
if(isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header('Location: /admin/');
    exit;
}

$error = '';
$success = '';

// Inicializar conexão
$db = new Database();
$conn = $db->getConnection();

// Verificar se há usuários no banco
$check = $conn->query("SELECT COUNT(*) as total FROM usuarios");
$total_usuarios = $check->fetch_assoc()['total'];

if($total_usuarios == 0) {
    // Criar usuário admin padrão se não existir
    $senha_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO usuarios (usuario, senha) VALUES ('admin', '$senha_hash')");
    $success = 'Usuário admin criado! Use: admin / admin123';
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $senha = $_POST['senha'];
    
    // Buscar usuário no banco
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verificar senha
        if(password_verify($senha, $user['senha'])) {
            $_SESSION['admin_logged'] = true;
            $_SESSION['admin_user'] = $user['usuario'];
            $_SESSION['admin_id'] = $user['id'];
            
            // Registrar login no log
            $conn->query("INSERT INTO logs (acao, descricao, ip) VALUES ('login_admin', 'Admin {$user['usuario']} fez login', '{$_SERVER['REMOTE_ADDR']}')");
            
            header('Location: /admin/');
            exit;
        } else {
            $error = 'Usuário ou senha inválidos';
        }
    } else {
        $error = 'Usuário ou senha inválidos';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - PixBuy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--color-gray-100);
        }
        .login-card {
            max-width: 400px;
            width: 100%;
            padding: var(--space-8);
        }
        .alert {
            margin-bottom: var(--space-4);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="text-center mb-4">
                <div style="font-size: 3rem; margin-bottom: var(--space-2);">💳</div>
                <h2 style="margin-bottom: var(--space-2);">PixBuy</h2>
                <p class="text-secondary">Área Administrativa</p>
            </div>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Usuário</label>
                    <input type="text" name="usuario" class="form-control" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Senha</label>
                    <input type="password" name="senha" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-secondary">Credenciais padrão: admin / admin123</small>
            </div>
        </div>
    </div>
</body>
</html>