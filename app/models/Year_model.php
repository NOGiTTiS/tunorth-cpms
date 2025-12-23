<?php
class Year_model {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        // Check/Create Table
        $this->initTable();
    }

    private function initTable() {
        // Auto-migrate table if not exists (Safety check)
        $sql = "CREATE TABLE IF NOT EXISTS academic_years (
            id INT AUTO_INCREMENT PRIMARY KEY,
            year VARCHAR(4) NOT NULL,
            term VARCHAR(1) DEFAULT '1',
            is_current TINYINT(1) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);
        
        // Seed if empty
        $stmt = $this->db->query("SELECT COUNT(*) FROM academic_years");
        if ($stmt->fetchColumn() == 0) {
            $currentYear = date("Y") + 543;
            if (date("m") < 5) $currentYear--;
            
            $this->create(['year' => $currentYear, 'term' => '1', 'is_current' => 1]); // Current
            $this->create(['year' => $currentYear-1, 'term' => '1', 'is_current' => 0]); // Previous
        }
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM academic_years ORDER BY year DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getActive() {
        $stmt = $this->db->query("SELECT * FROM academic_years WHERE is_active = 1 ORDER BY year DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCurrentYear() {
        $stmt = $this->db->query("SELECT * FROM academic_years WHERE is_current = 1 LIMIT 1");
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($res) return $res;
        
        // Fallback
        return ['year' => date("Y")+543, 'term' => '1'];
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM academic_years WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO academic_years (year, term, is_current, is_active) VALUES (:year, :term, :cur, :active)";
        $stmt = $this->db->prepare($query);
        
        // If setting as current, unset others first
        if (!empty($data['is_current']) && $data['is_current'] == 1) {
            $this->db->exec("UPDATE academic_years SET is_current = 0");
        }

        return $stmt->execute([
            ':year' => $data['year'],
            ':term' => $data['term'] ?? '1',
            ':cur' => $data['is_current'] ?? 0,
            ':active' => $data['is_active'] ?? 1
        ]);
    }

    public function update($data) {
        $query = "UPDATE academic_years SET year = :year, term = :term, is_active = :active WHERE id = :id";
        
         // If setting as current, unset others first
        if (!empty($data['is_current']) && $data['is_current'] == 1) {
            $this->db->exec("UPDATE academic_years SET is_current = 0");
            $query = "UPDATE academic_years SET year = :year, term = :term, is_current = 1, is_active = :active WHERE id = :id";
        }

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':year' => $data['year'],
            ':term' => $data['term'],
            ':active' => isset($data['is_active']) ? $data['is_active'] : 1,
            ':id' => $data['id']
        ]);
    }
    
    public function setCurrent($id) {
        $this->db->exec("UPDATE academic_years SET is_current = 0");
        $stmt = $this->db->prepare("UPDATE academic_years SET is_current = 1 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function delete($id) {
        // Prevent deleting the current year
        $check = $this->getById($id);
        if ($check['is_current']) return false;

        $stmt = $this->db->prepare("DELETE FROM academic_years WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
