<?php
class User_model {
    private $db;
    private $table = "users";

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // ค้นหาผู้ใช้จาก Email
    public function getUserByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // สร้าง User ใหม่ (สำหรับทดสอบ หรือ Admin สร้าง)
    public function register($data) {
        $query = "INSERT INTO " . $this->table . " (full_name, email, password, role, student_id) 
                  VALUES (:full_name, :email, :password, :role, :student_id)";
        $stmt = $this->db->prepare($query);
        
        // Hash รหัสผ่านก่อนบันทึก
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':student_id', $data['student_id']);

        return $stmt->execute();
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->table . " WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($id, $new_password) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE " . $this->table . " SET password = :pass WHERE id = :id");
        return $stmt->execute([':pass' => $hashed, ':id' => $id]);
    }
}