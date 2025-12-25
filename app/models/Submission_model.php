<?php
class Submission_model {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function getStepsWithStatus($group_id) {
        $query = "SELECT ps.*, s.status, s.file_path, s.comment, s.score, s.submitted_at 
                  FROM project_steps ps 
                  LEFT JOIN submissions s ON ps.id = s.step_id AND s.group_id = :group_id
                  ORDER BY ps.step_order ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':group_id' => $group_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // แก้ไข: เพิ่ม parameter $user_id
    public function submitWork($group_id, $step_id, $file_path, $user_id) {
        $check = "SELECT id FROM submissions WHERE group_id = :gid AND step_id = :sid";
        $stmt = $this->db->prepare($check);
        $stmt->execute([':gid' => $group_id, ':sid' => $step_id]);
        
        if ($stmt->fetch()) {
            $query = "UPDATE submissions SET file_path = :file, user_id = :uid, status = 'PENDING', submitted_at = NOW() 
                      WHERE group_id = :gid AND step_id = :sid";
        } else {
            $query = "INSERT INTO submissions (group_id, step_id, user_id, file_path, status) 
                      VALUES (:gid, :sid, :uid, :file, 'PENDING')";
        }
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':gid' => $group_id, 
            ':sid' => $step_id, 
            ':uid' => $user_id, // บันทึก ID คนส่ง
            ':file' => $file_path
        ]);
    }

    // 1. คำนวณเปอร์เซ็นต์ความคืบหน้าของกลุ่ม
    public function getCalculateProgress($group_id) {
        // นับขั้นตอนทั้งหมดที่ Admin ตั้งไว้
        $totalStepsQuery = $this->db->query("SELECT COUNT(*) as total FROM project_steps");
        $totalSteps = $totalStepsQuery->fetch(PDO::FETCH_ASSOC)['total'];

        if ($totalSteps == 0) return 0;

        // นับขั้นตอนที่กลุ่มนี้ได้รับการ APPROVED แล้ว
        $approvedQuery = $this->db->prepare("SELECT COUNT(*) as count FROM submissions WHERE group_id = :gid AND status = 'APPROVED'");
        $approvedQuery->execute([':gid' => $group_id]);
        $approvedSteps = $approvedQuery->fetch(PDO::FETCH_ASSOC)['count'];

        return round(($approvedSteps / $totalSteps) * 100);
    }

    // 2. ดึงงานถัดไปที่ "ยังไม่ผ่าน" (เพื่อนำไปโชว์ในช่อง สิ่งที่ต้องทำ)
    public function getNextPendingTask($group_id) {
        $query = "SELECT ps.step_name 
                FROM project_steps ps
                LEFT JOIN submissions s ON ps.id = s.step_id AND s.group_id = :gid
                WHERE s.status IS NULL OR s.status != 'APPROVED'
                ORDER BY ps.step_order ASC LIMIT 1";
                
        $stmt = $this->db->prepare($query);
        $stmt->execute([':gid' => $group_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}