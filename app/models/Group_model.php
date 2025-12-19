<?php
class Group_model {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // ตรวจสอบว่านักเรียนคนนี้มีกลุ่มหรือยัง
    public function getGroupByUser($user_id) {
        // แก้ไข SQL: ดึงข้อมูลจากตาราง project_groups ตรงๆ 
        // เพราะชื่อครูถูกเก็บใน g.advisor_name แล้ว
        $query = "SELECT g.* 
                FROM project_groups g 
                JOIN group_members gm ON g.id = gm.group_id 
                WHERE gm.user_id = :user_id";
                
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ดึงสมาชิกในกลุ่ม
    public function getMembers($group_id) {
        // เพิ่ม u.id AS user_id เข้าไปใน SELECT
        $query = "SELECT u.id AS user_id, u.full_name, u.student_id, u.email, u.room 
                FROM users u 
                JOIN group_members gm ON u.id = gm.user_id 
                WHERE gm.group_id = :group_id";
                
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':group_id', $group_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ดึงรายชื่อครูทั้งหมดเพื่อใช้เลือกที่ปรึกษา
    public function getTeachers() {
        $query = "SELECT id, full_name FROM users WHERE role = 'TEACHER'";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    // สร้างกลุ่มใหม่
    public function createGroup($data, $creator_id) {
        try {
            $this->db->beginTransaction();
            $query = "INSERT INTO project_groups (project_name_th, project_name_en, advisor_name, room) 
                    VALUES (:name_th, :name_en, :advisor_name, :room)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':name_th' => $data['project_name_th'],
                ':name_en' => $data['project_name_en'],
                ':advisor_name' => $data['advisor_name'],
                ':room' => $data['room'] // เพิ่มการบันทึกห้อง
            ]);
            
            $group_id = $this->db->lastInsertId();
            $queryMember = "INSERT INTO group_members (group_id, user_id) VALUES (:group_id, :user_id)";
            $stmtMember = $this->db->prepare($queryMember);
            $stmtMember->execute([':group_id' => $group_id, ':user_id' => $creator_id]);

            $this->db->commit();
            return $group_id;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // แก้ไขฟังก์ชัน updateGroup
    public function updateGroup($id, $data) {
        $query = "UPDATE project_groups SET 
                    project_name_th = :name_th, 
                    project_name_en = :name_en, 
                    advisor_name = :advisor,
                    room = :room 
                WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':name_th' => $data['project_name_th'],
            ':name_en' => $data['project_name_en'],
            ':advisor' => $data['advisor_name'],
            ':room' => $data['room'], // เพิ่มการอัปเดตห้อง
            ':id' => $id
        ]);
    }

    public function deleteGroup($group_id) {
        try {
            $this->db->beginTransaction();

            // 1. ดึงไฟล์ออกมาเตรียมลบ
            $stmt = $this->db->prepare("SELECT file_path FROM submissions WHERE group_id = :gid");
            $stmt->execute([':gid' => $group_id]);
            $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. ลบกลุ่ม (ข้อมูลใน group_members และ submissions จะถูกลบตามเพราะ CASCADE)
            $query = "DELETE FROM project_groups WHERE id = :gid";
            $stmtDelete = $this->db->prepare($query);
            $stmtDelete->execute([':gid' => $group_id]);

            $this->db->commit();
            return $files;
        } catch (PDOException $e) { // เปลี่ยนเป็น PDOException เพื่อดักจับ Error จาก DB
            $this->db->rollBack();
            // บันทึก Error ลง log เพื่อดูภายหลัง (ถ้ามี)
            error_log("Delete Group Error: " . $e->getMessage());
            return false;
        }
    }

    // 1. ค้นหานักเรียนที่ "ยังไม่มีกลุ่ม" เพื่อเชิญเข้ากลุ่ม
    public function getAvailableStudents($search = '', $exclude_id = 0) {
        $sql = "SELECT id, full_name, student_id, room FROM users 
                WHERE role = 'STUDENT' 
                AND id != :exclude_id
                AND id NOT IN (SELECT user_id FROM group_members)";
        
        if (!empty($search)) {
            $sql .= " AND (full_name LIKE :search OR student_id LIKE :search)";
        }
        
        $sql .= " ORDER BY room ASC, student_id ASC LIMIT 20";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':exclude_id', $exclude_id);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%");
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. เพิ่มสมาชิกเข้ากลุ่ม
    public function addMember($group_id, $user_id) {
        // เช็คอีกครั้งว่าเด็กคนนี้แอบไปเข้ากลุ่มอื่นระหว่างที่รอเรากดเชิญหรือไม่
        $check = $this->db->prepare("SELECT group_id FROM group_members WHERE user_id = :uid");
        $check->execute([':uid' => $user_id]);
        if ($check->fetch()) return false;

        $query = "INSERT INTO group_members (group_id, user_id) VALUES (:gid, :uid)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':gid' => $group_id, ':uid' => $user_id]);
    }

    // 3. ลบสมาชิกออกจากกลุ่ม
    public function removeMember($group_id, $user_id) {
        $query = "DELETE FROM group_members WHERE group_id = :gid AND user_id = :uid";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':gid' => $group_id, ':uid' => $user_id]);
    }

    public function getAvailableStudentsByRoom($room) {
        // ดึงนักเรียนในห้องที่เลือก และต้องเป็นคนที่ยังไม่มีกลุ่มเท่านั้น
        $sql = "SELECT id, full_name, student_id FROM users 
                WHERE role = 'STUDENT' 
                AND room = :room 
                AND id NOT IN (SELECT user_id FROM group_members)
                ORDER BY student_id ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':room', $room);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}