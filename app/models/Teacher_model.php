<?php
class Teacher_model {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function getPendingSubmissions($advisor_id = null, $status = null, $room = null, $year = null) {
        $query = "SELECT 
                    s.id, s.status, s.file_path, s.comment, s.submitted_at, s.group_id,
                    g.project_name_th, g.advisor_name,
                    ps.step_name, 
                    u_std.full_name as submitter_name,
                    u_std.room as student_room
                FROM submissions s
                JOIN project_groups g ON s.group_id = g.id
                JOIN project_steps ps ON s.step_id = ps.id
                LEFT JOIN users u_std ON s.user_id = u_std.id
                WHERE 1=1"; // ใช้ WHERE 1=1 เพื่อให้ต่อ SQL ง่ายขึ้น

        if ($advisor_id !== null) {
            $query .= " AND g.advisor_id = :aid";
        }

        if ($status !== null) {
            $query .= " AND s.status = :status";
        }
        
        if ($room !== null && $room !== '') {
            $query .= " AND u_std.room = :room";
        }

        if ($year !== null && $year !== '') {
            $query .= " AND g.academic_year = :year";
        }

        $query .= " ORDER BY s.submitted_at DESC";
                
        $stmt = $this->db->prepare($query);
        
        if ($advisor_id !== null) {
            $stmt->bindValue(':aid', $advisor_id);
        }
        if ($status !== null) {
            $stmt->bindValue(':status', $status);
        }
        if ($room !== null && $room !== '') {
            $stmt->bindValue(':room', $room);
        }
        if ($year !== null && $year !== '') {
            $stmt->bindValue(':year', $year);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateReview($submission_id, $status, $comment) {
        $query = "UPDATE submissions SET status = :status, comment = :comment WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':status' => $status,
            ':comment' => $comment,
            ':id' => $submission_id
        ]);
    }

    public function getGradeSheetData($advisor_id = null) {
        // 1. ดึงข้อมูลนักเรียนและกลุ่มทั้งหมด
        $sql = "SELECT 
                    u.student_id, u.full_name, u.room, 
                    g.project_name_th, g.advisor_name,
                    s.step_id, s.status, ps.step_name
                FROM users u
                JOIN group_members gm ON u.id = gm.user_id
                JOIN project_groups g ON gm.group_id = g.id
                LEFT JOIN submissions s ON g.id = s.group_id
                LEFT JOIN project_steps ps ON s.step_id = ps.id
                WHERE u.role = 'STUDENT'";
        
        if ($advisor_id) {
            $sql .= " AND g.advisor_id = :aid";
        }
        
        $sql .= " ORDER BY u.room ASC, u.student_id ASC, ps.step_order ASC";

        $stmt = $this->db->prepare($sql);
        if ($advisor_id) {
            $stmt->bindValue(':aid', $advisor_id);
        }
        $stmt->execute();
        $raw_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. ดึงขั้นตอนทั้งหมดมาเป็น Header
        $stmtSteps = $this->db->query("SELECT id, step_name FROM project_steps ORDER BY step_order ASC");
        $steps = $stmtSteps->fetchAll(PDO::FETCH_ASSOC);

        return ['students' => $raw_data, 'steps' => $steps];
    }
}