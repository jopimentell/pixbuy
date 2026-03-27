<?php
header('Content-Type: application/json');
try {
    $host = 'db';
    $user = 'pixbuy_user';
    $pass = 'pixbuy123';
    $db = 'pixbuy_db';
    
    $conn = new mysqli($host, $user, $pass, $db);
    
    if ($conn->connect_error) {
        echo json_encode(['status' => 'error', 'message' => $conn->connect_error]);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Conectado ao MySQL']);
        $conn->close();
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
