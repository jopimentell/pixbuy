<?php
// init-db/02-create-admin.php
// Este script é executado automaticamente pelo MySQL container

$host = 'db';
$user = 'root';
$pass = 'root123';
$dbname = 'pixbuy_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Gerar hash da senha dinamicamente
    $senha = 'admin123';
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    
    // Verificar se admin já existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = 'admin'");
    $stmt->execute();
    $exists = $stmt->fetchColumn();
    
    if ($exists == 0) {
        // Criar admin
        $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, senha) VALUES ('admin', :senha)");
        $stmt->execute([':senha' => $hash]);
        echo "✅ Usuário admin criado com sucesso!\n";
    } else {
        // Atualizar senha para garantir
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE usuario = 'admin'");
        $stmt->execute([':senha' => $hash]);
        echo "✅ Senha do admin atualizada!\n";
    }
    
    echo "   Usuário: admin\n";
    echo "   Senha: admin123\n";
    
} catch(PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>