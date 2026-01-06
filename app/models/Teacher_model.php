<?php
class Teacher_model
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function getPendingSubmissions($advisor_id = null, $status = null, $room = null, $year = null, $group_id = null, $limitRooms = null)
    {
        $this->ensureScoreColumn();
        $query = "SELECT 
                    s.id, s.status, s.file_path, s.comment, s.score, s.submitted_at, s.group_id,
                    g.project_name_th, g.advisor_name,
                    ps.step_name, ps.id as step_id,
                    u_std.full_name as submitter_name,
                    u_std.room as student_room
                FROM submissions s
                JOIN project_groups g ON s.group_id = g.id
                JOIN project_steps ps ON s.step_id = ps.id
                LEFT JOIN users u_std ON s.user_id = u_std.id
                WHERE 1=1";

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

        if ($group_id !== null && $group_id !== '') {
            $query .= " AND s.group_id = :gid";
        }

        // Restricted Rooms (New)
        if ($limitRooms !== null && is_array($limitRooms)) {
            if (empty($limitRooms)) {
                // If assigned list is empty but variable passed, likely means "No Access"
                // But typically we pass null if no restriction. 
                // Let's assume empty array means no access.
                return [];
            }
            // Use manually constructed IN clause with placeholders
            $inQuery = implode(',', array_map(function ($i) {
                return ":lr$i";
            }, array_keys($limitRooms)));
            $query .= " AND u_std.room IN ($inQuery)";
        }

        $query .= " ORDER BY s.submitted_at DESC";

        $stmt = $this->db->prepare($query);

        if ($advisor_id !== null) $stmt->bindValue(':aid', $advisor_id);
        if ($status !== null) $stmt->bindValue(':status', $status);
        if ($room !== null && $room !== '') $stmt->bindValue(':room', $room);
        if ($year !== null && $year !== '') $stmt->bindValue(':year', $year);
        if ($group_id !== null && $group_id !== '') $stmt->bindValue(':gid', $group_id);

        if ($limitRooms !== null && is_array($limitRooms)) {
            foreach ($limitRooms as $k => $v) {
                $stmt->bindValue(":lr$k", $v);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ensureScoreColumn()
    {
        try {
            $this->db->exec("ALTER TABLE submissions ADD COLUMN score INT NULL DEFAULT NULL");
        } catch (PDOException $e) {
            // Column likely exists
        }
    }

    public function updateReview($submission_id, $status, $comment, $score = null)
    {
        $this->ensureScoreColumn();
        $query = "UPDATE submissions SET status = :status, comment = :comment, score = :score WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':status' => $status,
            ':comment' => $comment,
            ':score' => ($score !== '' ? $score : null),
            ':id' => $submission_id
        ]);
    }

    public function getGradeSheetData($advisor_id = null)
    {
        // 1. ดึงข้อมูลนักเรียนและกลุ่มทั้งหมด
        $sql = "SELECT 
                    u.student_id, u.full_name, u.room, 
                    g.project_name_th, g.advisor_name,
                    s.step_id, s.status, s.score, ps.step_name
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
