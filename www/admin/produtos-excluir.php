<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$id = $_POST['id'] ?? 0;

if($id) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $conn->query("INSERT INTO logs (acao, descricao, ip) VALUES ('produto_excluido', 'Produto ID: $id excluído', '{$_SERVER['REMOTE_ADDR']}')");
}

header('Location: produtos.php');
exit;
?>