<?php
// config/database.php

class Database {
    private $host = "db";
    private $db_name = "pixbuy_db";
    private $username = "root";
    private $password = "root123";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            $this->conn->set_charset("utf8mb4");
        } catch(Exception $e) {
            error_log("Erro de conexão: " . $e->getMessage());
        }
        return $this->conn;
    }
}
?>