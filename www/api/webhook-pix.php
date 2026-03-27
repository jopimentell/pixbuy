<?php
// api/webhook-pix.php - Para receber confirmações de pagamento
header('Content-Type: application/json');

require_once '../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

// Simulação - substituir por integração real com gateway PIX
$txid = $input['txid'] ?? '';
$status = $input['status'] ?? '';

if($txid && $status === 'paid') {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("UPDATE pedidos SET status = 'paid', data_pagamento = NOW() WHERE pix_txid = ?");
    $stmt->bind_param("s", $txid);
    $stmt->execute();
    
    $conn->query("INSERT INTO logs (acao, descricao, ip) VALUES ('pagamento_confirmado', 'Pagamento TXID: $txid confirmado', '{$_SERVER['REMOTE_ADDR']}')");
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>