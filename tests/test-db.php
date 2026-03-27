<?php
header('Content-Type: application/json');

try {
    $host = 'db';
    $user = 'pixbuy_user';
    $pass = 'pixbuy123';
    $db = 'pixbuy_db';
    
    $conn = new mysqli($host, $user, $pass, $db);
    
    if ($conn->connect_error) {
        echo json_encode([
            'status' => 'error', 
            'message' => $conn->connect_error,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        // Testar se o banco de dados existe
        $result = $conn->query("SELECT DATABASE() as db_name");
        $row = $result->fetch_assoc();
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Conectado ao MySQL',
            'database' => $row['db_name'],
            'mysql_version' => $conn->server_info,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        $conn->close();
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>