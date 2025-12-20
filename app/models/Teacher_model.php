<?php
class Teacher_model {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function getPendingSubmissions($advisor_id = null, $status = null) {
        $query = "SELECT 
                    s.id, s.status, s.file_path, s.comment, s.submitted_at,
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

        $query .= " ORDER BY s.submitted_at DESC";
                
        $stmt = $this->db->prepare($query);
        
        if ($advisor_id !== null) {
            $stmt->bindValue(':aid', $advisor_id);
        }
        if ($status !== null) {
            $stmt->bindValue(':status', $status);
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
}