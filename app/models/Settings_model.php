<?php
class Settings_model {
    private $conn;
    private $table = 'system_settings';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->initializeTable();
    }

    private function initializeTable() {
        $query = "CREATE TABLE IF NOT EXISTS " . $this->table . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(255) NOT NULL UNIQUE,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        try {
            $this->conn->exec($query);
            
            // Seed default values if empty
            $this->seedDefaults();
        } catch(PDOException $e) {
            // Table might already exist or permission error
        }
    }

    private function seedDefaults() {
        $defaults = [
            'system_name' => 'CPMS TU-North',
            'site_logo' => '',
            'site_favicon' => '',
            'site_copyright' => '© 2025 TU-North CPMS. All rights reserved.',
            'telegram_api_token' => '',
            'telegram_chat_id' => '',
            'system_description' => 'ระบบจัดการโครงงานคอมพิวเตอร์ ม.6',
            'institute_name' => 'โรงเรียนเตรียมอุดมศึกษา ภาคเหนือ',
            'submission_mode' => 'open' // open, sequential
        ];

        foreach ($defaults as $key => $value) {
            $this->set($key, $value, true); // true = only if not exists
        }
    }

    public function getAll() {
        $query = "SELECT setting_key, setting_value FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function get($key) {
        $query = "SELECT setting_value FROM " . $this->table . " WHERE setting_key = :key LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['setting_value'] : null;
    }

    public function set($key, $value, $onlyIfNotExists = false) {
        if ($onlyIfNotExists) {
            $check = "SELECT id FROM " . $this->table . " WHERE setting_key = :key";
            $stmt = $this->conn->prepare($check);
            $stmt->bindParam(':key', $key);
            $stmt->execute();
            if ($stmt->rowCount() > 0) return;
        }

        $query = "INSERT INTO " . $this->table . " (setting_key, setting_value) 
                  VALUES (:key, :value) 
                  ON DUPLICATE KEY UPDATE setting_value = :value";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':key', $key);
        $stmt->bindParam(':value', $value);
        return $stmt->execute();
    }
}
