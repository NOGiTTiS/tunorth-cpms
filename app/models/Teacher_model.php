<?php
class Teacher_model {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function getPendingSubmissions($advisor_id = null) {
        $query = "SELECT 
                    s.id, s.status, s.file_path, s.comment, s.submitted_at,
                    g.project_name_th, g.advisor_name,
                    ps.step_name, 
                    u_std.full_name as submitter_name,
                    u_std.room as student_room -- ดึงห้องของคนส่งงาน
                FROM submissions s
                JOIN project_groups g ON s.group_id = g.id
                JOIN project_steps ps ON s.step_id = ps.id
                LEFT JOIN users u_std ON s.user_id = u_std.id";

        if ($advisor_id !== null) {
            $query .= " WHERE g.advisor_id = :aid";
        }
        $query .= " ORDER BY s.submitted_at DESC";
                
        $stmt = $this->db->prepare($query);
        if ($advisor_id !== null) {
            $stmt->execute([':aid' => $advisor_id]);
        } else {
            $stmt->execute();
        }
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