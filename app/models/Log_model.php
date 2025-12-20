<?php
class Log_model {
    private $db;
    private $table = 'activity_logs';

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->initializeTable();
    }

    private function initializeTable() {
        $query = "CREATE TABLE IF NOT EXISTS " . $this->table . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            user_role VARCHAR(50) NULL,
            action VARCHAR(100) NOT NULL,
            description TEXT NULL,
            ip_address VARCHAR(45) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        try {
            $this->db->exec($query);
        } catch(PDOException $e) {
            // Table might exist
        }
    }

    public function log($user_id, $user_role, $action, $description = '') {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        
        $query = "INSERT INTO " . $this->table . " (user_id, user_role, action, description, ip_address) 
                  VALUES (:uid, :role, :action, :desc, :ip)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':uid' => $user_id,
            ':role' => $user_role,
            ':action' => $action,
            ':desc' => $description,
            ':ip' => $ip
        ]);
    }

    public function getLogs($limit = 100) {
        $query = "SELECT l.*, u.full_name, u.student_id 
                  FROM " . $this->table . " l 
                  LEFT JOIN users u ON l.user_id = u.id 
                  ORDER BY l.created_at DESC LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
