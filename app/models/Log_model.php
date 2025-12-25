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

    public function getLogs($limit = 100, $filters = []) {
        $sql = "SELECT l.*, u.full_name, u.student_id 
                FROM " . $this->table . " l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE 1=1 ";
        
        $params = [];

        // Filter by Role
        if (!empty($filters['role'])) {
            $sql .= " AND l.user_role = :role ";
            $params[':role'] = $filters['role'];
        }

        // Filter by Date
        if (!empty($filters['date'])) {
            $sql .= " AND DATE(l.created_at) = :date ";
            $params[':date'] = $filters['date'];
        }

        // Filter by Search Keyword
        if (!empty($filters['search'])) {
            $sql .= " AND (u.full_name LIKE :search OR l.action LIKE :search OR l.description LIKE :search) ";
            $params[':search'] = "%" . $filters['search'] . "%";
        }

        $sql .= " ORDER BY l.created_at DESC LIMIT :limit";
        $params[':limit'] = (int)$limit;
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
