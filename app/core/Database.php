<?php
class Database {
    private $host = "localhost"; // ชื่อ service ใน docker-compose
    private $db_name = "krusitti_cpms_db";
    private $username = "krusitti_db";
    private $password = "HpXENgteAbC8CzDuXXrQ";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}